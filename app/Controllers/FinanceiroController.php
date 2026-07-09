<?php
/**
 * Controller financeiro (acessado a partir da ficha do imóvel):
 * IPTU, lançamentos avulsos e faturas de condomínio.
 */
class FinanceiroController extends Controller
{
    private function dinheiro(string $k): float
    {
        return (float) str_replace(['.', ','], ['', '.'], $_POST[$k] ?? '');
    }

    /** Carrega o imóvel do ?imovel_id= ou redireciona. */
    private function imovelOuRedireciona(): array
    {
        $im = Imovel::buscar((int) ($_GET['imovel_id'] ?? 0));
        if (!$im) $this->redirect('imoveis');
        return $im;
    }

    private function voltarFicha(int $imovelId): never
    {
        $this->redirect('imoveis/ficha?id=' . $imovelId . '&aba=financeiro');
    }

    /** GET/POST financeiro/iptu?imovel_id= */
    public function iptu(): void
    {
        exige_admin();
        $im = $this->imovelOuRedireciona();
        $imovel_id = (int) $im['id'];

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ano   = (int) ($_POST['ano'] ?? 0);
            $valor = $this->dinheiro('valor_total');
            if (!$ano || !$valor) {
                $erro = 'Ano e valor são obrigatórios.';
            } else {
                Financeiro::criarIptu($imovel_id, [
                    'ano'               => $ano,
                    'inscricao_iptu'    => trim($_POST['inscricao_iptu'] ?? '') ?: null,
                    'valor_total'       => $valor,
                    'parcelas'          => (int) ($_POST['parcelas'] ?? 1),
                    'data_vencimento_1' => ($_POST['data_vencimento_1'] ?? '') ?: null,
                ]);
                $this->voltarFicha($imovel_id);
            }
        }

        $this->view('financeiro/iptu', compact('im', 'imovel_id', 'erro'));
    }

    /** GET/POST financeiro/lancamento?imovel_id= */
    public function lancamento(): void
    {
        exige_admin();
        $im = $this->imovelOuRedireciona();
        $imovel_id = (int) $im['id'];

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $descricao   = trim($_POST['descricao'] ?? '');
            $valor       = $this->dinheiro('valor');
            $competencia = $_POST['data_competencia'] ?? '';
            if (!$descricao || !$valor || !$competencia) {
                $erro = 'Descrição, valor e competência são obrigatórios.';
            } else {
                Financeiro::criarLancamento($imovel_id, (int) $im['cliente_id'], [
                    'tipo'             => $_POST['tipo'] ?? 'despesa',
                    'categoria'        => $_POST['categoria'] ?? 'outro',
                    'descricao'        => $descricao,
                    'valor'            => $valor,
                    'data_competencia' => $competencia,
                    'pago'             => isset($_POST['pago']) ? 1 : 0,
                    'data_pagamento'   => ($_POST['data_pagamento'] ?? '') ?: null,
                ]);
                $this->voltarFicha($imovel_id);
            }
        }

        $this->view('financeiro/lancamento', compact('im', 'imovel_id', 'erro'));
    }

    /** GET/POST financeiro/fatura-condominio?imovel_id= */
    public function faturaCondominio(): void
    {
        exige_admin();
        $im = $this->imovelOuRedireciona();
        $imovel_id = (int) $im['id'];

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $competencia = ($_POST['competencia'] ?? '') . '-01';
            $valor       = $this->dinheiro('valor');
            if (trim($competencia) === '-01' || !$valor) {
                $erro = 'Competência e valor são obrigatórios.';
            } else {
                $pago   = isset($_POST['pago']) ? 1 : 0;
                $dt_pag = ($_POST['data_pagamento'] ?? '') ?: null;

                // Upload do boleto
                $arquivo_boleto = null;
                if (!empty($_FILES['boleto']['name'])) {
                    $dir = APP_ROOT . '/uploads/' . $im['cliente_id'] . '/boletos/';
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    $ext  = strtolower(pathinfo($_FILES['boleto']['name'], PATHINFO_EXTENSION));
                    $nome = 'cond_' . $imovel_id . '_' . str_replace('-', '', $competencia) . '.' . $ext;
                    if (move_uploaded_file($_FILES['boleto']['tmp_name'], $dir . $nome)) {
                        $arquivo_boleto = 'uploads/' . $im['cliente_id'] . '/boletos/' . $nome;
                    }
                }

                Financeiro::criarFaturaCondominio($imovel_id, [
                    'competencia'     => $competencia,
                    'valor'           => $valor,
                    'descricao_extra' => trim($_POST['descricao_extra'] ?? '') ?: null,
                    'pago'            => $pago,
                    'data_pagamento'  => $dt_pag,
                    'arquivo_boleto'  => $arquivo_boleto,
                ]);

                // Lançamento financeiro automático (regra de negócio)
                Financeiro::criarLancamento($imovel_id, (int) $im['cliente_id'], [
                    'tipo'             => 'despesa',
                    'categoria'        => 'condominio',
                    'descricao'        => 'Condomínio ' . date('m/Y', strtotime($competencia)),
                    'valor'            => $valor,
                    'data_competencia' => $competencia,
                    'pago'             => $pago,
                    'data_pagamento'   => $dt_pag,
                ]);

                $this->voltarFicha($imovel_id);
            }
        }

        $this->view('financeiro/fatura_condominio', compact('im', 'imovel_id', 'erro'));
    }
}
