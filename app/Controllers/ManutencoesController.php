<?php
/**
 * Controller de manutenções do imóvel (acessado a partir da ficha).
 * O valor da manutenção gera lançamento de despesa no caixa do cliente.
 */
class ManutencoesController extends Controller
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
            'data'         => ($_POST['data'] ?? '') ?: date('Y-m-d'),
            'tipo'         => $_POST['tipo'] ?? 'outro',
            'descricao'    => trim($_POST['descricao'] ?? ''),
            'fornecedor'   => trim($_POST['fornecedor'] ?? '') ?: null,
            'valor'        => $this->dec('valor'),
            'garantia_ate' => ($_POST['garantia_ate'] ?? '') ?: null,
            'observacoes'  => trim($_POST['observacoes'] ?? '') ?: null,
        ];
    }

    /** GET/POST manutencoes/nova?imovel_id= */
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
                $manutId = Manutencao::criar($imovel_id, $campos);

                // Valor → lançamento de despesa no caixa
                if ($campos['valor']) {
                    db()->prepare('INSERT INTO lancamentos_financeiros (imovel_id, cliente_id, tipo, categoria, descricao, valor, data_competencia, pago, referencia_tipo, referencia_id) VALUES (?,?,?,?,?,?,?,?,?,?)')
                        ->execute([$imovel_id, $im['cliente_id'], 'despesa', 'manutencao', 'Manutenção: ' . $campos['descricao'],
                                   $campos['valor'], $campos['data'], 1, 'manutencao', $manutId]);
                }

                $this->processarUploads($im['cliente_id'], $manutId);
                $this->redirect('imoveis/ficha?id=' . $imovel_id . '&aba=manutencoes');
            }
        }

        $this->view('manutencoes/nova', compact('im', 'imovel_id', 'erro'));
    }

    /** GET/POST manutencoes/editar?id=&imovel_id= */
    public function editar(): void
    {
        exige_admin();
        $id = (int) ($_GET['id'] ?? 0);
        $imovel_id = (int) ($_GET['imovel_id'] ?? 0);
        $m = Manutencao::buscar($id);
        if (!$m) $this->redirect('imoveis/ficha?id=' . $imovel_id . '&aba=manutencoes');

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $campos = $this->camposComuns();
            if ($campos['descricao'] === '') {
                $erro = 'Descrição é obrigatória.';
            } else {
                Manutencao::atualizar($id, $campos);
                $this->redirect('imoveis/ficha?id=' . $m['imovel_id'] . '&aba=manutencoes');
            }
        }

        $d = $_POST ?: $m;
        $this->view('manutencoes/editar', compact('m', 'd', 'erro'));
    }

    private function processarUploads(int $clienteId, int $manutId): void
    {
        if (empty($_FILES['documentos']['name'][0])) return;
        $dir = APP_ROOT . '/uploads/' . $clienteId . '/manutencoes/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        foreach ($_FILES['documentos']['name'] as $i => $orig) {
            if (!$orig) continue;
            $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $nome = 'manut_' . $manutId . '_' . ($i + 1) . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['documentos']['tmp_name'][$i], $dir . $nome)) {
                db()->prepare('INSERT INTO documentos (cliente_id, tipo_referencia, referencia_id, categoria, nome_arquivo, caminho) VALUES (?,?,?,?,?,?)')
                    ->execute([$clienteId, 'manutencao', $manutId, 'manutencao', $orig, 'uploads/' . $clienteId . '/manutencoes/' . $nome]);
            }
        }
    }
}
