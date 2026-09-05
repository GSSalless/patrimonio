<?php
/**
 * Controller de Colaboradores (Módulo 02). Cadastro de RH do cliente +
 * dependentes e histórico (férias/promoções/advertências...) na edição.
 */
class ColaboradoresController extends Controller
{
    private const STATUS   = ['ativo','experiencia','afastado','ferias','desligado'];
    private const CONTRATO = ['clt','pj','autonomo','diarista','temporario','estagio','outro'];
    private const ESCOL    = ['fundamental','medio','tecnico','superior','pos','mestrado','doutorado','outro'];
    private const CIVIL    = ['solteiro','casado','divorciado','viuvo','uniao_estavel','separado'];
    private const SANGUE   = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
    private const PARENT   = ['conjuge','filho','filha','pai','mae','outro'];
    private const HIST     = ['salario','promocao','advertencia','avaliacao','ferias','atestado','treinamento','falta','beneficio','outro'];

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

    private function enum(string $k, array $permitidos): ?string
    {
        $v = $_POST[$k] ?? '';
        return in_array($v, $permitidos, true) ? $v : null;
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
        return [
            'nome'              => $this->str('nome'),
            'cpf'               => $this->str('cpf'),
            'rg'                => $this->str('rg'),
            'data_nascimento'   => $this->dataOuNull('data_nascimento'),
            'estado_civil'      => $this->enum('estado_civil', self::CIVIL),
            'tipo_sanguineo'    => $this->enum('tipo_sanguineo', self::SANGUE),
            'telefone'          => $this->str('telefone'),
            'email'             => $this->str('email'),
            'cep'               => $this->str('cep'),
            'logradouro'        => $this->str('logradouro'),
            'numero'            => $this->str('numero'),
            'complemento'       => $this->str('complemento'),
            'bairro'            => $this->str('bairro'),
            'cidade'            => $this->str('cidade'),
            'estado'            => $this->str('estado') ? strtoupper(substr($this->str('estado'), 0, 2)) : null,
            'cargo'             => $this->str('cargo'),
            'departamento'      => $this->str('departamento'),
            'gestor_nome'       => $this->str('gestor_nome'),
            'tipo_contrato'     => $this->enum('tipo_contrato', self::CONTRATO),
            'jornada'           => $this->str('jornada'),
            'data_admissao'     => $this->dataOuNull('data_admissao'),
            'data_demissao'     => $this->dataOuNull('data_demissao'),
            'salario'           => $this->dec('salario'),
            'status'            => $this->enum('status', self::STATUS) ?? 'ativo',
            'escolaridade'      => $this->enum('escolaridade', self::ESCOL),
            'formacao'          => $this->str('formacao'),
            'convenio_medico'   => $this->str('convenio_medico'),
            'alergias'          => $this->str('alergias'),
            'uniforme_camiseta' => $this->str('uniforme_camiseta'),
            'uniforme_camisa'   => $this->str('uniforme_camisa'),
            'uniforme_calca'    => $this->str('uniforme_calca'),
            'uniforme_calcado'  => $this->str('uniforme_calcado'),
            'vale_alimentacao'  => $this->dec('vale_alimentacao'),
            'plano_saude'       => $this->str('plano_saude'),
            'seguro_vida'       => $this->str('seguro_vida'),
            'outros_beneficios' => $this->str('outros_beneficios'),
            'observacoes'       => $this->str('observacoes'),
        ];
    }

    /** GET colaboradores — lista com filtros. */
    public function index(): void
    {
        exige_login();
        $usuario = usuario_logado();
        $cli     = $this->clienteEmContexto($usuario);

        $filtro_status = $_GET['status'] ?? '';
        $filtro_busca  = trim($_GET['busca'] ?? '');
        $colaboradores = Colaborador::listar($cli['id'], $filtro_status, $filtro_busca);

        $this->view('colaboradores/lista', compact('cli', 'colaboradores', 'filtro_status', 'filtro_busca'));
    }

    /** GET/POST colaboradores/novo — cadastro. */
    public function novo(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->str('nome')) {
                $erro = 'Nome do colaborador é obrigatório.';
            } else {
                $campos = array_merge(
                    ['cliente_id' => $cli['id'], 'codigo' => proximo_codigo_colaborador()],
                    $this->camposComuns()
                );
                $coId = Colaborador::criar($campos);
                salvar_upload_documentos(
                    ['doc_contrato' => 'contrato', 'doc_identidade' => 'identidade', 'doc_outros' => 'outro'],
                    'colaborador', $coId, $cli['id'],
                    $cli['id'] . '/colaborador_' . $coId
                );
                $this->redirect('colaboradores/editar?id=' . $coId . '&ok=1');
            }
        }

        $d = $_POST;
        $this->view('colaboradores/novo', compact('cli', 'erro', 'd'));
    }

    /** GET/POST colaboradores/editar?id= — edição + dependentes + histórico. */
    public function editar(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $id = (int) ($_GET['id'] ?? 0);
        $colaborador = Colaborador::buscarDoCliente($id, $cli['id']);
        if (!$colaborador) $this->redirect('colaboradores');

        $erro = ''; $ok = isset($_GET['ok']) ? 'Colaborador cadastrado com sucesso!' : '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->str('nome')) {
                $erro = 'Nome do colaborador é obrigatório.';
            } else {
                Colaborador::atualizar($id, $this->camposComuns());
                salvar_upload_documentos(
                    ['doc_contrato' => 'contrato', 'doc_identidade' => 'identidade', 'doc_outros' => 'outro'],
                    'colaborador', $id, $cli['id'],
                    $cli['id'] . '/colaborador_' . $id
                );
                $colaborador = Colaborador::buscarDoCliente($id, $cli['id']);
                $ok = 'Colaborador atualizado com sucesso!';
            }
        }

        $d = $colaborador;
        $docs = db()->prepare('SELECT * FROM documentos WHERE tipo_referencia = ? AND referencia_id = ? ORDER BY criado_em DESC');
        $docs->execute(['colaborador', $id]);
        $docs_list = $docs->fetchAll();

        $dependentes = Colaborador::dependentes($id);
        $historico   = Colaborador::historico($id);

        $this->view('colaboradores/editar', compact('cli', 'colaborador', 'd', 'erro', 'ok', 'docs_list', 'dependentes', 'historico'));
    }

    /** POST colaboradores/dependente?colaborador_id= */
    public function dependente(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $co_id = (int) ($_GET['colaborador_id'] ?? 0);
        $co = Colaborador::buscarDoCliente($co_id, $cli['id']);
        if (!$co) $this->redirect('colaboradores');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->str('nome')) {
            Colaborador::dependenteCriar($co_id, [
                'nome'            => $this->str('nome'),
                'parentesco'      => $this->enum('parentesco', self::PARENT) ?? 'filho',
                'data_nascimento' => $this->dataOuNull('data_nascimento'),
                'cpf'             => $this->str('cpf'),
                'observacoes'     => $this->str('observacoes'),
            ]);
        }
        $this->redirect('colaboradores/editar?id=' . $co_id . '#dependentes');
    }

    /** GET colaboradores/dependente-remover?colaborador_id=&id= */
    public function dependenteRemover(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $co_id  = (int) ($_GET['colaborador_id'] ?? 0);
        $dep_id = (int) ($_GET['id'] ?? 0);
        Colaborador::dependenteExcluir($dep_id, $co_id, $cli['id']);
        $this->redirect('colaboradores/editar?id=' . $co_id . '#dependentes');
    }

    /** POST colaboradores/historico?colaborador_id= */
    public function historico(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $co_id = (int) ($_GET['colaborador_id'] ?? 0);
        $co = Colaborador::buscarDoCliente($co_id, $cli['id']);
        if (!$co) $this->redirect('colaboradores');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->dataOuNull('data')) {
            Colaborador::historicoCriar($co_id, [
                'data'        => $this->dataOuNull('data'),
                'data_fim'    => $this->dataOuNull('data_fim'),
                'tipo'        => $this->enum('tipo', self::HIST) ?? 'outro',
                'descricao'   => $this->str('descricao'),
                'valor'       => $this->dec('valor'),
                'observacoes' => $this->str('observacoes'),
            ]);
        }
        $this->redirect('colaboradores/editar?id=' . $co_id . '#historico');
    }

    /** GET colaboradores/historico-remover?colaborador_id=&id= */
    public function historicoRemover(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $co_id   = (int) ($_GET['colaborador_id'] ?? 0);
        $hist_id = (int) ($_GET['id'] ?? 0);
        Colaborador::historicoExcluir($hist_id, $co_id, $cli['id']);
        $this->redirect('colaboradores/editar?id=' . $co_id . '#historico');
    }
}
