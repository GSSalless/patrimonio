<?php
/**
 * Serviço autenticado de arquivos enviados (Módulo de segurança — M2).
 *
 * Os arquivos em /uploads/ deixaram de ser servidos direto pelo Apache
 * (uploads/.htaccess = Require all denied). Todo acesso passa por aqui, que:
 *   1. exige login;
 *   2. resolve o cliente dono do arquivo pela linha do banco que o referencia
 *      (documentos.caminho / *.foto_principal / condominio_faturas.arquivo_boleto);
 *   3. autoriza (admin vê tudo; cliente só vê o próprio patrimônio — sem IDOR);
 *   4. registra a auditoria (quem acessou o quê e quando);
 *   5. entrega o arquivo (inline) com o Content-Type correto.
 */
class ArquivoController extends Controller
{
    public function servir(): void
    {
        exige_login();
        $u = usuario_logado();

        $docId = (int) ($_GET['doc'] ?? 0);
        $fParam = (string) ($_GET['f'] ?? '');

        if ($docId > 0) {
            $st = db()->prepare('SELECT * FROM documentos WHERE id = ?');
            $st->execute([$docId]);
            $row = $st->fetch();
            if (!$row) { $this->naoEncontrado(); }
            $clienteId = (int) $row['cliente_id'];
            $rel  = (string) $row['caminho'];
            $nome = $row['nome_arquivo'] ?: basename($rel);
        } elseif ($fParam !== '') {
            $rel = $this->pathSeguro($fParam);
            if ($rel === null) { http_response_code(400); exit('Caminho inválido.'); }
            $clienteId = $this->donoDoArquivo($rel);
            if ($clienteId === null) { $this->naoEncontrado(); }
            $nome = basename($rel);
        } else {
            http_response_code(400); exit('Requisição inválida.');
        }

        // Autorização: cliente só acessa o próprio patrimônio.
        if ($u['nivel'] !== 'admin') {
            $meu = $this->clienteDoUsuario((int) $u['id']);
            if ($meu === null || $meu !== $clienteId) {
                $this->auditar($u, 'NEGADO', $rel, $clienteId);
                http_response_code(403); exit('Acesso negado.');
            }
        }

        $abs = APP_ROOT . '/' . $rel;
        if (!is_file($abs)) { $this->naoEncontrado(); }

        $this->auditar($u, 'OK', $rel, $clienteId);

        header('Content-Type: ' . $this->mime($abs));
        header('Content-Length: ' . filesize($abs));
        header('Content-Disposition: inline; filename="' . rawurlencode($nome) . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=300');
        readfile($abs);
        exit;
    }

    /** Valida o caminho relativo: sob uploads/, sem traversal. */
    private function pathSeguro(string $p): ?string
    {
        $p = ltrim(trim($p), '/');
        if ($p === '' || strpos($p, '..') !== false || strpos($p, "\0") !== false) return null;
        if (strncmp($p, 'uploads/', 8) !== 0) return null;
        return $p;
    }

    /** Descobre o cliente dono de um arquivo referenciado por caminho. */
    private function donoDoArquivo(string $rel): ?int
    {
        foreach ([
            ['documentos',  'caminho'],
            ['imoveis',     'foto_principal'],
            ['veiculos',    'foto_principal'],
            ['outros_bens', 'foto_principal'],
        ] as [$tabela, $coluna]) {
            $s = db()->prepare("SELECT cliente_id FROM {$tabela} WHERE {$coluna} = ? LIMIT 1");
            $s->execute([$rel]);
            $v = $s->fetchColumn();
            if ($v !== false) return (int) $v;
        }
        // Boleto de condomínio → cliente via imóvel.
        $s = db()->prepare(
            'SELECT i.cliente_id FROM condominio_faturas f
               JOIN imoveis i ON i.id = f.imovel_id
              WHERE f.arquivo_boleto = ? LIMIT 1'
        );
        $s->execute([$rel]);
        $v = $s->fetchColumn();
        return $v !== false ? (int) $v : null;
    }

    private function clienteDoUsuario(int $usuarioId): ?int
    {
        $s = db()->prepare('SELECT id FROM clientes WHERE usuario_id = ? AND ativo = 1');
        $s->execute([$usuarioId]);
        $v = $s->fetchColumn();
        return $v !== false ? (int) $v : null;
    }

    private function mime(string $abs): string
    {
        if (function_exists('finfo_open')) {
            $fi = finfo_open(FILEINFO_MIME_TYPE);
            $m  = finfo_file($fi, $abs);
            finfo_close($fi);
            if ($m) return $m;
        }
        $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
        return [
            'pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png', 'webp' => 'image/webp',
        ][$ext] ?? 'application/octet-stream';
    }

    /** Trilha de auditoria de acesso a arquivos (vai para o error_log). */
    private function auditar(array $u, string $status, string $rel, int $clienteId): void
    {
        error_log(sprintf(
            '[AUDIT arquivo] %s | status=%s | usuario=%d (%s) | cliente=%d | ip=%s | arquivo=%s',
            date('c'), $status, (int) $u['id'], $u['nivel'], $clienteId,
            $_SERVER['REMOTE_ADDR'] ?? '-', $rel
        ));
    }

    private function naoEncontrado(): void
    {
        http_response_code(404);
        exit('Arquivo não encontrado.');
    }
}
