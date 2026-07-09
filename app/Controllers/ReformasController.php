<?php
/**
 * Controller de reformas (acessado a partir da ficha do imóvel).
 * Custo realizado gera lançamento no caixa do cliente (regra de negócio).
 */
class ReformasController extends Controller
{
    private function dec(string $k): ?float
    {
        $v = trim($_POST[$k] ?? '');
        if ($v === '') return null;
        return (float) str_replace(',', '.', str_replace('.', '', $v));
    }

    /** Campos compartilhados por cadastro e edição. */
    private function camposComuns(): array
    {
        return [
            'descricao'         => trim($_POST['descricao'] ?? ''),
            'status'            => $_POST['status'] ?? 'planejado',
            'data_inicio'       => ($_POST['data_inicio'] ?? '') ?: null,
            'data_fim_prevista' => ($_POST['data_fim_prevista'] ?? '') ?: null,
            'data_fim_real'     => ($_POST['data_fim_real'] ?? '') ?: null,
            'custo_previsto'    => $this->dec('custo_previsto') ?: null,
            'custo_realizado'   => $this->dec('custo_realizado') ?: null,
            'fornecedor'        => trim($_POST['fornecedor'] ?? '') ?: null,
            'observacoes'       => trim($_POST['observacoes'] ?? '') ?: null,
        ];
    }

    /** GET/POST reformas/nova?imovel_id= */
    public function nova(): void
    {
        exige_admin();
        $imovel_id = (int) ($_GET['imovel_id'] ?? 0);
        $im = Imovel::buscar($imovel_id);
        if (!$im) $this->redirect('imoveis');

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $campos = $this->camposComuns();
            if ($campos['descricao'] === '') {
                $erro = 'Descrição é obrigatória.';
            } else {
                $reformaId = Reforma::criar($imovel_id, $campos);

                // Custo realizado → lançamento de despesa no caixa
                if ($campos['custo_realizado']) {
                    db()->prepare('INSERT INTO lancamentos_financeiros (imovel_id, cliente_id, tipo, categoria, descricao, valor, data_competencia, pago, referencia_tipo, referencia_id) VALUES (?,?,?,?,?,?,?,?,?,?)')
                        ->execute([$imovel_id, $im['cliente_id'], 'despesa', 'reforma', 'Reforma: ' . $campos['descricao'],
                                   $campos['custo_realizado'], $campos['data_inicio'] ?? date('Y-m-d'), 1, 'reforma', $reformaId]);
                }

                $this->processarUploads($im['cliente_id'], $reformaId);
                $this->redirect('imoveis/ficha?id=' . $imovel_id . '&aba=reformas');
            }
        }

        $this->view('reformas/nova', compact('im', 'imovel_id', 'erro'));
    }

    /** GET/POST reformas/editar?id=&imovel_id= */
    public function editar(): void
    {
        exige_admin();
        $id = (int) ($_GET['id'] ?? 0);
        $imovel_id = (int) ($_GET['imovel_id'] ?? 0);
        $r = Reforma::buscar($id);
        if (!$r) $this->redirect('imoveis/ficha?id=' . $imovel_id . '&aba=reformas');

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $campos = $this->camposComuns();
            if ($campos['descricao'] === '') {
                $erro = 'Descrição é obrigatória.';
            } else {
                Reforma::atualizar($id, $campos);
                $this->redirect('imoveis/ficha?id=' . $r['imovel_id'] . '&aba=reformas');
            }
        }

        $d = $_POST ?: $r;
        $this->view('reformas/editar', compact('r', 'd', 'erro'));
    }

    private function processarUploads(int $clienteId, int $reformaId): void
    {
        if (empty($_FILES['documentos']['name'][0])) return;
        $dir = APP_ROOT . '/uploads/' . $clienteId . '/reformas/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        foreach ($_FILES['documentos']['name'] as $i => $orig) {
            if (!$orig) continue;
            $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $nome = 'ref_' . $reformaId . '_' . ($i + 1) . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['documentos']['tmp_name'][$i], $dir . $nome)) {
                db()->prepare('INSERT INTO documentos (cliente_id, tipo_referencia, referencia_id, categoria, nome_arquivo, caminho) VALUES (?,?,?,?,?,?)')
                    ->execute([$clienteId, 'reforma', $reformaId, 'nf', $orig, 'uploads/' . $clienteId . '/reformas/' . $nome]);
            }
        }
    }
}
