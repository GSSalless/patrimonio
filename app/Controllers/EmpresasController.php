<?php
/**
 * Controller de Empresas (Módulo 03). Cadastro de empresas/holdings do cliente
 * + quadro societário (sócios/administradores) na tela de edição.
 */
class EmpresasController extends Controller
{
    private const NATUREZAS = ['operacional','holding_patrimonial','holding_participacao','spe','outro'];
    private const REGIMES   = ['simples','lucro_presumido','lucro_real','mei','imune','outro'];
    private const SITUACOES = ['ativa','baixada','suspensa','inapta'];
    private const FUNCOES   = ['socio','administrador','socio_administrador','procurador','outro'];

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
        $nat = in_array($_POST['natureza'] ?? '', self::NATUREZAS, true) ? $_POST['natureza'] : 'operacional';
        $reg = in_array($_POST['regime_tributario'] ?? '', self::REGIMES, true) ? $_POST['regime_tributario'] : null;
        $sit = in_array($_POST['situacao'] ?? '', self::SITUACOES, true) ? $_POST['situacao'] : 'ativa';

        return [
            'razao_social'        => $this->str('razao_social'),
            'nome_fantasia'       => $this->str('nome_fantasia'),
            'cnpj'                => $this->str('cnpj'),
            'natureza'            => $nat,
            'natureza_juridica'   => $this->str('natureza_juridica'),
            'regime_tributario'   => $reg,
            'situacao'            => $sit,
            'inscricao_estadual'  => $this->str('inscricao_estadual'),
            'inscricao_municipal' => $this->str('inscricao_municipal'),
            'cnae_principal'      => $this->str('cnae_principal'),
            'cnaes_secundarios'   => $this->str('cnaes_secundarios'),
            'capital_social'      => $this->dec('capital_social'),
            'data_abertura'       => $this->dataOuNull('data_abertura'),
            'cep'                 => $this->str('cep'),
            'logradouro'          => $this->str('logradouro'),
            'numero'              => $this->str('numero'),
            'complemento'         => $this->str('complemento'),
            'bairro'              => $this->str('bairro'),
            'cidade'              => $this->str('cidade'),
            'estado'              => $this->str('estado') ? strtoupper(substr($this->str('estado'), 0, 2)) : null,
            'telefone'            => $this->str('telefone'),
            'email'               => $this->str('email'),
            'site'                => $this->str('site'),
            'contador_nome'       => $this->str('contador_nome'),
            'contador_contato'    => $this->str('contador_contato'),
            'observacoes'         => $this->str('observacoes'),
        ];
    }

    /** GET empresas — lista com filtros. */
    public function index(): void
    {
        exige_login();
        $usuario = usuario_logado();
        $cli     = $this->clienteEmContexto($usuario);

        $filtro_natureza = $_GET['natureza'] ?? '';
        $filtro_busca    = trim($_GET['busca'] ?? '');
        $empresas = Empresa::listar($cli['id'], $filtro_natureza, $filtro_busca);

        $this->view('empresas/lista', compact('cli', 'empresas', 'filtro_natureza', 'filtro_busca'));
    }

    /** GET/POST empresas/novo — cadastro. */
    public function novo(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->str('razao_social')) {
                $erro = 'Razão social é obrigatória.';
            } else {
                $campos = array_merge(
                    ['cliente_id' => $cli['id'], 'codigo' => proximo_codigo_empresa()],
                    $this->camposComuns()
                );
                $empresaId = Empresa::criar($campos);
                salvar_upload_documentos(
                    ['doc_contrato_social' => 'contrato_social', 'doc_cnpj' => 'cnpj', 'doc_outros' => 'outro'],
                    'empresa', $empresaId, $cli['id'],
                    $cli['id'] . '/empresa_' . $empresaId
                );
                $this->redirect('empresas/editar?id=' . $empresaId . '&ok=1');
            }
        }

        $d = $_POST;
        $this->view('empresas/novo', compact('cli', 'erro', 'd'));
    }

    /** GET/POST empresas/editar?id= — edição + quadro societário. */
    public function editar(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $id = (int) ($_GET['id'] ?? 0);
        $empresa = Empresa::buscarDoCliente($id, $cli['id']);
        if (!$empresa) $this->redirect('empresas');

        $erro = ''; $ok = isset($_GET['ok']) ? 'Empresa cadastrada com sucesso!' : '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->str('razao_social')) {
                $erro = 'Razão social é obrigatória.';
            } else {
                Empresa::atualizar($id, $this->camposComuns());
                salvar_upload_documentos(
                    ['doc_contrato_social' => 'contrato_social', 'doc_cnpj' => 'cnpj', 'doc_outros' => 'outro'],
                    'empresa', $id, $cli['id'],
                    $cli['id'] . '/empresa_' . $id
                );
                $empresa = Empresa::buscarDoCliente($id, $cli['id']);
                $ok = 'Empresa atualizada com sucesso!';
            }
        }

        $d = $empresa;
        $docs = db()->prepare('SELECT * FROM documentos WHERE tipo_referencia = ? AND referencia_id = ? ORDER BY criado_em DESC');
        $docs->execute(['empresa', $id]);
        $docs_list = $docs->fetchAll();

        $socios = Empresa::socios($id);
        $participacao_total = Empresa::participacaoTotal($id);

        $this->view('empresas/editar', compact('cli', 'empresa', 'd', 'erro', 'ok', 'docs_list', 'socios', 'participacao_total'));
    }

    /** POST empresas/socio?empresa_id= — adiciona sócio/administrador. */
    public function socio(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $empresa_id = (int) ($_GET['empresa_id'] ?? 0);
        $empresa = Empresa::buscarDoCliente($empresa_id, $cli['id']);
        if (!$empresa) $this->redirect('empresas');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->str('nome')) {
            $funcao = in_array($_POST['funcao'] ?? '', self::FUNCOES, true) ? $_POST['funcao'] : 'socio';
            Empresa::socioCriar($empresa_id, [
                'nome'         => $this->str('nome'),
                'cpf_cnpj'     => $this->str('cpf_cnpj'),
                'participacao' => $this->dec('participacao'),
                'funcao'       => $funcao,
                'observacoes'  => $this->str('observacoes'),
            ]);
        }
        $this->redirect('empresas/editar?id=' . $empresa_id . '#socios');
    }

    /** GET empresas/socio-remover?empresa_id=&id= — remove sócio. */
    public function socioRemover(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $empresa_id = (int) ($_GET['empresa_id'] ?? 0);
        $socio_id   = (int) ($_GET['id'] ?? 0);
        Empresa::socioExcluir($socio_id, $empresa_id, $cli['id']);
        $this->redirect('empresas/editar?id=' . $empresa_id . '#socios');
    }
}
