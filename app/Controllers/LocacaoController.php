<?php
/**
 * Controller de locação (contrato acessado a partir da ficha do imóvel).
 */
class LocacaoController extends Controller
{
    /** GET/POST locacao/novo?imovel_id= */
    public function novo(): void
    {
        exige_admin();
        $imovel_id = (int) ($_GET['imovel_id'] ?? 0);
        $im = Imovel::buscar($imovel_id);
        if (!$im) $this->redirect('imoveis');

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $loc_nome = trim($_POST['locatario_nome'] ?? '');
            $dt_ini   = $_POST['data_inicio'] ?? '';
            $aluguel  = (float) str_replace(['.', ','], ['', '.'], $_POST['valor_aluguel'] ?? '');

            if (!$loc_nome || !$dt_ini || !$aluguel) {
                $erro = 'Locatário, data de início e valor são obrigatórios.';
            } else {
                ContratoLocacao::encerrarAtivos($imovel_id);
                $contratoId = ContratoLocacao::criar($imovel_id, [
                    'locatario_nome'     => $loc_nome,
                    'locatario_cpf_cnpj' => trim($_POST['locatario_cpf_cnpj'] ?? '') ?: null,
                    'data_inicio'        => $dt_ini,
                    'data_fim'           => ($_POST['data_fim'] ?? '') ?: null,
                    'valor_aluguel'      => $aluguel,
                    'dia_vencimento'     => (int) ($_POST['dia_vencimento'] ?? 10),
                    'observacoes'        => trim($_POST['observacoes'] ?? '') ?: null,
                ]);

                if (!empty($_FILES['contrato']['name'])) {
                    $dir = APP_ROOT . '/uploads/' . $im['cliente_id'] . '/contratos/';
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    $ext  = strtolower(pathinfo($_FILES['contrato']['name'], PATHINFO_EXTENSION));
                    $nome = 'contrato_' . $contratoId . '.' . $ext;
                    if (move_uploaded_file($_FILES['contrato']['tmp_name'], $dir . $nome)) {
                        db()->prepare('INSERT INTO documentos (cliente_id, tipo_referencia, referencia_id, categoria, nome_arquivo, caminho) VALUES (?,?,?,?,?,?)')
                            ->execute([$im['cliente_id'], 'contrato_locacao', $contratoId, 'outro', $_FILES['contrato']['name'], 'uploads/' . $im['cliente_id'] . '/contratos/' . $nome]);
                    }
                }

                $this->redirect('imoveis/ficha?id=' . $imovel_id . '&aba=aluguel');
            }
        }

        $this->view('locacao/novo', compact('im', 'imovel_id', 'erro'));
    }
}
