<?php
/**
 * Controller de Contratos (Módulo 12). Cadastro central de contratos, com
 * vínculo polimórfico opcional. A data_fim (contratos ativos) alimenta a Agenda.
 */
class ContratosController extends Controller
{
    private const TIPOS  = ['locacao','prestacao_servico','fornecimento','compra_venda','sociedade','emprestimo','financiamento','seguro','trabalho','outro'];
    private const STATUS = ['ativo','encerrado','suspenso','em_negociacao','rescindido'];
    private const PERIOD = ['unico','mensal','trimestral','semestral','anual','outro'];
    private const VINC_TIPOS = ['nenhum','imovel','veiculo','outro_bem','fornecedor','colaborador','empresa'];

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
    private function camposComuns(): array
    {
        $tipo   = in_array($_POST['tipo'] ?? '', self::TIPOS, true) ? $_POST['tipo'] : 'outro';
        $status = in_array($_POST['status'] ?? '', self::STATUS, true) ? $_POST['status'] : 'ativo';
        $period = in_array($_POST['periodicidade'] ?? '', self::PERIOD, true) ? $_POST['periodicidade'] : null;

        // Vínculo vem num único select "tipo:id"; vazio = nenhum.
        $vinculo_tipo = 'nenhum'; $vinculo_id = null;
        $vinc = trim($_POST['vinculo'] ?? '');
        if ($vinc !== '' && str_contains($vinc, ':')) {
            [$t, $i] = explode(':', $vinc, 2);
            if (in_array($t, self::VINC_TIPOS, true) && ctype_digit($i)) {
                $vinculo_tipo = $t; $vinculo_id = (int) $i;
            }
        }

        return [
            'numero'               => $this->str('numero'),
            'tipo'                 => $tipo,
            'objeto'               => $this->str('objeto'),
            'contraparte_nome'     => $this->str('contraparte_nome'),
            'contraparte_doc'      => $this->str('contraparte_doc'),
            'vinculo_tipo'         => $vinculo_tipo,
            'vinculo_id'           => $vinculo_id,
            'data_inicio'          => $this->dataOuNull('data_inicio'),
            'data_fim'             => $this->dataOuNull('data_fim'),
            'renovacao_automatica' => isset($_POST['renovacao_automatica']) ? 1 : 0,
            'prazo_renovacao'      => $this->str('prazo_renovacao'),
            'valor'                => $this->dec('valor'),
            'periodicidade'        => $period,
            'indice_reajuste'      => $this->str('indice_reajuste'),
            'status'               => $status,
            'observacoes'          => $this->str('observacoes'),
        ];
    }

    /** GET contratos — lista com filtros. */
    public function index(): void
    {
        exige_login();
        $usuario = usuario_logado();
        $cli     = $this->clienteEmContexto($usuario);

        $filtro_tipo   = $_GET['tipo'] ?? '';
        $filtro_status = $_GET['status'] ?? '';
        $filtro_busca  = trim($_GET['busca'] ?? '');
        $contratos = Contrato::listar($cli['id'], $filtro_tipo, $filtro_status, $filtro_busca);

        $this->view('contratos/lista', compact('cli', 'contratos', 'filtro_tipo', 'filtro_status', 'filtro_busca'));
    }

    /** GET/POST contratos/novo — cadastro. */
    public function novo(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->str('numero') && !$this->str('objeto') && !$this->str('contraparte_nome')) {
                $erro = 'Informe ao menos o número, o objeto ou a contraparte do contrato.';
            } else {
                $campos = array_merge(
                    ['cliente_id' => $cli['id'], 'codigo' => proximo_codigo_contrato()],
                    $this->camposComuns()
                );
                $contratoId = Contrato::criar($campos);
                salvar_upload_documentos(
                    ['doc_contrato' => 'contrato', 'doc_aditivo' => 'contrato', 'doc_outros' => 'outro'],
                    'contrato', $contratoId, $cli['id'],
                    $cli['id'] . '/contrato_' . $contratoId
                );
                $this->redirect('contratos/editar?id=' . $contratoId . '&ok=1');
            }
        }

        $d = $_POST;
        $itens_vinc = Contrato::itensVinculaveis($cli['id']);
        $this->view('contratos/novo', compact('cli', 'erro', 'd', 'itens_vinc'));
    }

    /** GET/POST contratos/editar?id= — edição. */
    public function editar(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $id = (int) ($_GET['id'] ?? 0);
        $contrato = Contrato::buscarDoCliente($id, $cli['id']);
        if (!$contrato) $this->redirect('contratos');

        $erro = ''; $ok = isset($_GET['ok']) ? 'Contrato cadastrado com sucesso!' : '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->str('numero') && !$this->str('objeto') && !$this->str('contraparte_nome')) {
                $erro = 'Informe ao menos o número, o objeto ou a contraparte do contrato.';
            } else {
                Contrato::atualizar($id, $this->camposComuns());
                salvar_upload_documentos(
                    ['doc_contrato' => 'contrato', 'doc_aditivo' => 'contrato', 'doc_outros' => 'outro'],
                    'contrato', $id, $cli['id'],
                    $cli['id'] . '/contrato_' . $id
                );
                $contrato = Contrato::buscarDoCliente($id, $cli['id']);
                $ok = 'Contrato atualizado com sucesso!';
            }
        }

        $d = $contrato;
        $docs = db()->prepare('SELECT * FROM documentos WHERE tipo_referencia = ? AND referencia_id = ? ORDER BY criado_em DESC');
        $docs->execute(['contrato', $id]);
        $docs_list = $docs->fetchAll();

        $itens_vinc = Contrato::itensVinculaveis($cli['id']);
        $this->view('contratos/editar', compact('cli', 'contrato', 'd', 'erro', 'ok', 'docs_list', 'itens_vinc'));
    }
}
