<?php
/**
 * Controller de documentos.
 *  - index():   repositório central (Módulo 13) — todos os arquivos do cliente
 *  - upload():  upload avulso vinculado a um imóvel (via ficha)
 *  - excluir(): remoção pelo repositório (admin, com escopo)
 */
class DocumentosController extends Controller
{
    /** GET documentos — repositório central com filtros. */
    public function index(): void
    {
        exige_login();
        $usuario = usuario_logado();

        // Escopo: admin usa o cliente selecionado (ou todos); cliente vê o próprio.
        $cli = ($usuario['nivel'] === 'admin') ? cliente_selecionado() : null;
        if ($usuario['nivel'] === 'cliente') {
            $stmt = db()->prepare('SELECT * FROM clientes WHERE usuario_id = ? AND ativo = 1');
            $stmt->execute([$usuario['id']]);
            $cli = $stmt->fetch() ?: null;
        }
        $cliente_id = $cli['id'] ?? null;

        $filtros = [
            'categoria' => $_GET['categoria'] ?? '',
            'tipo'      => $_GET['tipo'] ?? '',
            'q'         => trim($_GET['q'] ?? ''),
            'validade'  => $_GET['validade'] ?? '',
        ];

        $docs     = Documento::listar($cliente_id, $filtros);
        $vinculos = Documento::resolverVinculos($docs);

        $this->view('documentos/index', [
            'docs'        => $docs,
            'vinculos'    => $vinculos,
            'filtros'     => $filtros,
            'categorias'  => Documento::categorias(),
            'tipos'       => Documento::tiposLabel(),
            'escopo_nome' => $cli['nome'] ?? null,
            'is_admin'    => $usuario['nivel'] === 'admin',
        ]);
    }

    /** POST documentos/excluir?id= — remove registro + arquivo (admin, com escopo). */
    public function excluir(): void
    {
        exige_admin();
        $id  = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $doc = Documento::buscar($id);

        if ($doc) {
            // Escopo: se há cliente selecionado, só apaga documento dele.
            $sel = cliente_selecionado();
            if (!$sel || (int) $sel['id'] === (int) $doc['cliente_id']) {
                $abs = APP_ROOT . '/' . ltrim($doc['caminho'], '/');
                if (is_file($abs) && str_starts_with(realpath($abs) ?: '', realpath(APP_ROOT . '/uploads') ?: '///')) {
                    @unlink($abs);
                }
                Documento::excluir($id);
            }
        }
        $this->redirect('documentos');
    }

    /** GET/POST documentos/upload?tipo=imovel&ref= */
    public function upload(): void
    {
        exige_admin();
        $tipo_ref = $_GET['tipo'] ?? 'imovel';
        $ref_id   = (int) ($_GET['ref'] ?? 0);

        $im = ($tipo_ref === 'imovel') ? Imovel::buscar($ref_id) : null;
        if (!$im) $this->redirect('imoveis');

        $back_url = base_url('imoveis/ficha?id=' . $ref_id . '&aba=documentos');
        $erro = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_FILES['arquivo']['name'])) {
                $erro = 'Selecione um arquivo.';
            } else {
                $nome_orig = $_FILES['arquivo']['name'];
                $ext       = strtolower(pathinfo($nome_orig, PATHINFO_EXTENSION));
                $allowed   = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];

                if (!in_array($ext, $allowed)) {
                    $erro = 'Tipo de arquivo não permitido. Use PDF, JPG ou PNG.';
                } elseif ($_FILES['arquivo']['size'] > 20 * 1024 * 1024) {
                    $erro = 'Arquivo muito grande (máximo 20 MB).';
                } else {
                    $dir = APP_ROOT . '/uploads/' . $im['cliente_id'] . '/docs/';
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    $nome_salvo = $tipo_ref . '_' . $ref_id . '_' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $dir . $nome_salvo)) {
                        Documento::criar([
                            'cliente_id'      => $im['cliente_id'],
                            'tipo_referencia' => $tipo_ref,
                            'referencia_id'   => $ref_id,
                            'categoria'       => $_POST['categoria'] ?? 'outro',
                            'nome_arquivo'    => $nome_orig,
                            'caminho'         => 'uploads/' . $im['cliente_id'] . '/docs/' . $nome_salvo,
                            'mime_type'       => $_FILES['arquivo']['type'],
                            'tamanho_bytes'   => $_FILES['arquivo']['size'],
                            'data_emissao'    => ($_POST['data_emissao'] ?? '') ?: null,
                            'data_validade'   => ($_POST['data_validade'] ?? '') ?: null,
                            'descricao'       => trim($_POST['descricao'] ?? '') ?: null,
                        ]);
                        $this->redirect('imoveis/ficha?id=' . $ref_id . '&aba=documentos');
                    } else {
                        $erro = 'Erro ao salvar o arquivo. Verifique as permissões da pasta uploads/.';
                    }
                }
            }
        }

        $this->view('documentos/upload', compact('im', 'tipo_ref', 'ref_id', 'back_url', 'erro'));
    }
}
