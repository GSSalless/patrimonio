<?php
/**
 * Controller de Investimentos (Módulo 10). Cadastro da carteira + histórico de
 * movimentos. valor_atual entra no patrimônio consolidado; data_vencimento
 * alimenta a Agenda.
 */
class InvestimentosController extends Controller
{
    private const CLASSES  = ['renda_fixa','tesouro','fundo','multimercado','acoes','previdencia','offshore','cripto','outro'];
    private const INDEX    = ['pre','cdi','ipca','selic','cambio','misto','na'];
    private const LIQUIDEZ = ['D0','D1','D2','D30','D90','vencimento','outro'];
    private const STATUS   = ['ativo','resgatado','vencido'];
    private const MOV      = ['aplicacao','resgate','rendimento','ajuste'];

    private function str(string $k): ?string
    {
        $v = trim($_POST[$k] ?? '');
        return $v !== '' ? $v : null;
    }

    private function dec(string $k): ?float
    {
        $v = trim($_POST[$k] ?? '');
        if ($v === '') return null;
        return (float) str_replace(',', '.', str_replace('.', '', $v));
    }

    private function boolv(string $k): int
    {
        return !empty($_POST[$k]) ? 1 : 0;
    }

    private function dataOuNull(string $k): ?string
    {
        $v = trim($_POST[$k] ?? '');
        return $v !== '' ? $v : null;
    }

    private function clienteEmContexto(array $usuario): array
    {
        if ($usuario['nivel'] === 'admin') {
            $cli = cliente_selecionado();
            if (!$cli) $this->redirect('dashboard');
            return $cli;
        }
        $s = db()->prepare('SELECT * FROM clientes WHERE usuario_id = ? AND ativo = 1');
        $s->execute([$usuario['id']]);
        $cli = $s->fetch();
        if (!$cli) $this->redirect('login');
        return $cli;
    }

    /** Campos compartilhados por cadastro e edição (exceto cliente_id/codigo). */
    private function camposComuns(int $clienteId): array
    {
        $classe = in_array($_POST['classe'] ?? '', self::CLASSES, true) ? $_POST['classe'] : 'renda_fixa';
        $index  = in_array($_POST['indexador'] ?? '', self::INDEX, true) ? $_POST['indexador'] : null;
        $liq    = in_array($_POST['liquidez'] ?? '', self::LIQUIDEZ, true) ? $_POST['liquidez'] : null;
        $status = in_array($_POST['status'] ?? '', self::STATUS, true) ? $_POST['status'] : 'ativo';

        // conta_id só é aceito se a conta pertencer ao cliente (evita vínculo indevido).
        $conta_id = null;
        $cid = (int) ($_POST['conta_id'] ?? 0);
        if ($cid > 0) {
            $s = db()->prepare('SELECT id FROM contas_financeiras WHERE id = ? AND cliente_id = ? AND ativo = 1');
            $s->execute([$cid, $clienteId]);
            if ($s->fetchColumn()) $conta_id = $cid;
        }

        return [
            'conta_id'                 => $conta_id,
            'nome'                     => $this->str('nome'),
            'classe'                   => $classe,
            'instituicao'              => $this->str('instituicao'),
            'emissor'                  => $this->str('emissor'),
            'indexador'                => $index,
            'rentabilidade_contratada' => $this->str('rentabilidade_contratada'),
            'data_aplicacao'           => $this->dataOuNull('data_aplicacao'),
            'data_vencimento'          => $this->dataOuNull('data_vencimento'),
            'valor_aplicado'           => $this->dec('valor_aplicado'),
            'valor_atual'              => $this->dec('valor_atual'),
            'quantidade'               => $this->dec('quantidade'),
            'liquidez'                 => $liq,
            'carencia_ate'             => $this->dataOuNull('carencia_ate'),
            'ir_aliquota'              => $this->dec('ir_aliquota'),
            'tem_iof'                  => $this->boolv('tem_iof'),
            'come_cotas'               => $this->boolv('come_cotas'),
            'taxa_administracao'       => $this->dec('taxa_administracao'),
            'taxa_performance'         => $this->str('taxa_performance'),
            'status'                   => $status,
            'observacoes'              => $this->str('observacoes'),
        ];
    }

    /** GET investimentos — lista com filtros + totais. */
    public function index(): void
    {
        exige_login();
        $usuario = usuario_logado();
        $cli     = $this->clienteEmContexto($usuario);

        $filtro_classe = $_GET['classe'] ?? '';
        $filtro_status = $_GET['status'] ?? '';
        $filtro_busca  = trim($_GET['busca'] ?? '');
        $investimentos = Investimento::listar($cli['id'], $filtro_classe, $filtro_status, $filtro_busca);

        $this->view('investimentos/lista', compact('cli', 'investimentos', 'filtro_classe', 'filtro_status', 'filtro_busca'));
    }

    /** GET/POST investimentos/novo — cadastro. */
    public function novo(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->str('nome')) {
                $erro = 'Nome do investimento é obrigatório.';
            } else {
                $campos = array_merge(
                    ['cliente_id' => $cli['id'], 'codigo' => proximo_codigo_investimento()],
                    $this->camposComuns($cli['id'])
                );
                $invId = Investimento::criar($campos);
                salvar_upload_documentos(
                    ['doc_proposta' => 'proposta', 'doc_regulamento' => 'regulamento', 'doc_extrato' => 'extrato'],
                    'investimento', $invId, $cli['id'],
                    $cli['id'] . '/investimento_' . $invId
                );
                $this->redirect('investimentos/editar?id=' . $invId . '&ok=1');
            }
        }

        $d = $_POST;
        $contas = Investimento::contasDoCliente($cli['id']);
        $this->view('investimentos/novo', compact('cli', 'erro', 'd', 'contas'));
    }

    /** GET/POST investimentos/editar?id= — edição + movimentos. */
    public function editar(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $id = (int) ($_GET['id'] ?? 0);
        $inv = Investimento::buscarDoCliente($id, $cli['id']);
        if (!$inv) $this->redirect('investimentos');

        $erro = ''; $ok = isset($_GET['ok']) ? 'Investimento cadastrado com sucesso!' : '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->str('nome')) {
                $erro = 'Nome do investimento é obrigatório.';
            } else {
                Investimento::atualizar($id, $this->camposComuns($cli['id']));
                salvar_upload_documentos(
                    ['doc_proposta' => 'proposta', 'doc_regulamento' => 'regulamento', 'doc_extrato' => 'extrato'],
                    'investimento', $id, $cli['id'],
                    $cli['id'] . '/investimento_' . $id
                );
                $inv = Investimento::buscarDoCliente($id, $cli['id']);
                $ok = 'Investimento atualizado com sucesso!';
            }
        }

        $d = $inv;
        $docs = db()->prepare('SELECT * FROM documentos WHERE tipo_referencia = ? AND referencia_id = ? ORDER BY criado_em DESC');
        $docs->execute(['investimento', $id]);
        $docs_list = $docs->fetchAll();

        $contas = Investimento::contasDoCliente($cli['id']);
        $movimentos = Investimento::movimentos($id);
        $this->view('investimentos/editar', compact('cli', 'inv', 'd', 'erro', 'ok', 'docs_list', 'contas', 'movimentos'));
    }

    /** POST investimentos/movimento?investimento_id= — registra aplicação/resgate/rendimento. */
    public function movimento(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $inv_id = (int) ($_GET['investimento_id'] ?? 0);
        $inv = Investimento::buscarDoCliente($inv_id, $cli['id']);
        if (!$inv) $this->redirect('investimentos');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->dec('valor') !== null) {
            $tipo = in_array($_POST['tipo'] ?? '', self::MOV, true) ? $_POST['tipo'] : 'aplicacao';
            Investimento::movimentoCriar($inv_id, [
                'data'        => $this->dataOuNull('data') ?: date('Y-m-d'),
                'tipo'        => $tipo,
                'valor'       => $this->dec('valor'),
                'observacoes' => $this->str('observacoes'),
            ]);
        }
        $this->redirect('investimentos/editar?id=' . $inv_id . '#movimentos');
    }

    /** GET investimentos/movimento-remover?investimento_id=&id= */
    public function movimentoRemover(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $inv_id = (int) ($_GET['investimento_id'] ?? 0);
        $mov_id = (int) ($_GET['id'] ?? 0);
        Investimento::movimentoExcluir($mov_id, $inv_id, $cli['id']);
        $this->redirect('investimentos/editar?id=' . $inv_id . '#movimentos');
    }
}
