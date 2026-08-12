<?php
/**
 * Controller de Fornecedores e Parceiros (Módulo 04). Cadastro de prestadores
 * do cliente com classificação, contrato, dados de pagamento e avaliação.
 */
class FornecedoresController extends Controller
{
    private const CATEGORIAS = ['contabilidade','juridico','seguros','marina','saude','tecnologia','rh','imobiliaria','manutencao','construcao','financeiro','transporte','outro'];
    private const PIX = ['cpf','cnpj','email','telefone','aleatoria'];

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
        $cat = in_array($_POST['categoria'] ?? '', self::CATEGORIAS, true) ? $_POST['categoria'] : 'outro';
        $tp  = ($_POST['tipo_pessoa'] ?? 'PJ') === 'PF' ? 'PF' : 'PJ';
        $pix = in_array($_POST['pix_tipo'] ?? '', self::PIX, true) ? $_POST['pix_tipo'] : null;

        $nota = null;
        $n = (int) ($_POST['avaliacao_nota'] ?? 0);
        if ($n >= 1 && $n <= 5) $nota = $n;

        return [
            'tipo_pessoa'       => $tp,
            'nome'              => $this->str('nome'),
            'nome_fantasia'     => $this->str('nome_fantasia'),
            'cpf_cnpj'          => $this->str('cpf_cnpj'),
            'categoria'         => $cat,
            'contato_nome'      => $this->str('contato_nome'),
            'telefone'          => $this->str('telefone'),
            'email'             => $this->str('email'),
            'site'              => $this->str('site'),
            'cep'               => $this->str('cep'),
            'logradouro'        => $this->str('logradouro'),
            'numero'            => $this->str('numero'),
            'complemento'       => $this->str('complemento'),
            'bairro'            => $this->str('bairro'),
            'cidade'            => $this->str('cidade'),
            'estado'            => $this->str('estado') ? strtoupper(substr($this->str('estado'), 0, 2)) : null,
            'contrato_inicio'   => $this->dataOuNull('contrato_inicio'),
            'contrato_fim'      => $this->dataOuNull('contrato_fim'),
            'contrato_valor'    => $this->dec('contrato_valor'),
            'contrato_reajuste' => $this->str('contrato_reajuste'),
            'forma_pagamento'   => $this->str('forma_pagamento'),
            'banco'             => $this->str('banco'),
            'agencia'           => $this->str('agencia'),
            'conta'             => $this->str('conta'),
            'pix_tipo'          => $pix,
            'pix_chave'         => $this->str('pix_chave'),
            'avaliacao_nota'    => $nota,
            'sla'               => $this->str('sla'),
            'avaliacao_obs'     => $this->str('avaliacao_obs'),
            'observacoes'       => $this->str('observacoes'),
        ];
    }

    /** GET fornecedores — lista com filtros. */
    public function index(): void
    {
        exige_login();
        $usuario = usuario_logado();
        $cli     = $this->clienteEmContexto($usuario);

        $filtro_categoria = $_GET['categoria'] ?? '';
        $filtro_busca     = trim($_GET['busca'] ?? '');
        $fornecedores = Fornecedor::listar($cli['id'], $filtro_categoria, $filtro_busca);

        $this->view('fornecedores/lista', compact('cli', 'fornecedores', 'filtro_categoria', 'filtro_busca'));
    }

    /** GET/POST fornecedores/novo — cadastro. */
    public function novo(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->str('nome')) {
                $erro = 'Nome / razão social é obrigatório.';
            } else {
                $campos = array_merge(
                    ['cliente_id' => $cli['id'], 'codigo' => proximo_codigo_fornecedor()],
                    $this->camposComuns()
                );
                $fid = Fornecedor::criar($campos);
                salvar_upload_documentos(
                    ['doc_contrato' => 'contrato', 'doc_nf' => 'nf', 'doc_certidao' => 'certidao'],
                    'fornecedor', $fid, $cli['id'],
                    $cli['id'] . '/fornecedor_' . $fid
                );
                $this->redirect('fornecedores/editar?id=' . $fid . '&ok=1');
            }
        }

        $d = $_POST;
        $this->view('fornecedores/novo', compact('cli', 'erro', 'd'));
    }

    /** GET/POST fornecedores/editar?id= — edição. */
    public function editar(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $id = (int) ($_GET['id'] ?? 0);
        $fornecedor = Fornecedor::buscarDoCliente($id, $cli['id']);
        if (!$fornecedor) $this->redirect('fornecedores');

        $erro = ''; $ok = isset($_GET['ok']) ? 'Fornecedor cadastrado com sucesso!' : '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->str('nome')) {
                $erro = 'Nome / razão social é obrigatório.';
            } else {
                Fornecedor::atualizar($id, $this->camposComuns());
                salvar_upload_documentos(
                    ['doc_contrato' => 'contrato', 'doc_nf' => 'nf', 'doc_certidao' => 'certidao'],
                    'fornecedor', $id, $cli['id'],
                    $cli['id'] . '/fornecedor_' . $id
                );
                $fornecedor = Fornecedor::buscarDoCliente($id, $cli['id']);
                $ok = 'Fornecedor atualizado com sucesso!';
            }
        }

        $d = $fornecedor;
        $docs = db()->prepare('SELECT * FROM documentos WHERE tipo_referencia = ? AND referencia_id = ? ORDER BY criado_em DESC');
        $docs->execute(['fornecedor', $id]);
        $docs_list = $docs->fetchAll();

        $this->view('fornecedores/editar', compact('cli', 'fornecedor', 'd', 'erro', 'ok', 'docs_list'));
    }
}
