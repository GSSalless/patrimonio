<?php
/**
 * Controller de clientes / pessoas (Módulo 01). Acesso restrito a admin.
 * - index  : lista em cards (com botão WhatsApp)
 * - novo   : cria a pessoa (dados base) e leva ao cadastro completo
 * - editar : cadastro completo (dados + endereço + contatos + família + testamento)
 * - itemRemover : exclui um item de sub-tabela (telefone/email/endereço/…)
 */
class ClientesController extends Controller
{
    /** Colunas diretas editáveis da tabela `clientes`. */
    private const COLUNAS = [
        'tipo_pessoa', 'nome', 'nome_completo', 'apelido', 'cpf_cnpj',
        'rg', 'rg_orgao', 'rg_uf', 'data_nascimento', 'naturalidade', 'nacionalidade',
        'estado_civil', 'regime_bens', 'profissao', 'tipo_sanguineo',
        'nome_pai', 'nome_mae',
        'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf',
        'email', 'telefone', 'tem_testamento', 'testamento_obs', 'observacoes',
    ];

    /** Campos que, vazios, viram NULL (ENUM/DATE não aceitam ''). */
    private const NULOS = [
        'nome_completo', 'apelido', 'rg', 'rg_orgao', 'rg_uf', 'data_nascimento',
        'naturalidade', 'estado_civil', 'regime_bens', 'profissao', 'tipo_sanguineo',
        'nome_pai', 'nome_mae', 'cep', 'logradouro', 'numero', 'complemento',
        'bairro', 'cidade', 'uf', 'email', 'telefone', 'testamento_obs', 'observacoes',
    ];

    /** GET clientes — lista em cards. */
    public function index(): void
    {
        exige_admin();
        $clientes = Cliente::listarAtivos();
        $this->view('clientes/lista', compact('clientes'));
    }

    /** GET/POST clientes/novo — cadastro base (leva ao completo). */
    public function novo(): void
    {
        exige_admin();
        $erro = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $campos   = $this->camposDoPost();
            $senha    = trim($_POST['senha'] ?? '');

            if (!$campos['nome'] || !$campos['cpf_cnpj']) {
                $erro = 'Nome e CPF/CNPJ são obrigatórios.';
            } else {
                $campos['cpf_cnpj'] = Cliente::formatarDocumento(
                    preg_replace('/\D/', '', $campos['cpf_cnpj']), $campos['tipo_pessoa']
                );
                $campos['usuario_id'] = ($campos['email'] && $senha)
                    ? Cliente::criarUsuarioLogin($campos['nome'], $campos['email'], $senha)
                    : null;
                $id = Cliente::criar($campos);
                // segue direto para o cadastro completo (sub-tabelas exigem o id)
                $this->redirect('clientes/editar?id=' . $id);
            }
        }

        $cliente = null;
        $this->view('clientes/novo', compact('erro', 'cliente'));
    }

    /** GET/POST clientes/editar?id= — cadastro completo. */
    public function editar(): void
    {
        exige_admin();
        $id = (int) ($_GET['id'] ?? 0);
        $cliente = Cliente::buscar($id);
        if (!$cliente) {
            $this->redirect('clientes');
        }

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $secao = $_POST['secao'] ?? 'dados';

            if ($secao === 'dados') {
                $campos = $this->camposDoPost();
                if (!$campos['nome'] || !$campos['cpf_cnpj']) {
                    $erro = 'Nome e CPF/CNPJ são obrigatórios.';
                } else {
                    Cliente::atualizar($id, $campos);
                    // documento do testamento (opcional)
                    salvar_upload_documentos(
                        ['testamento_arquivo' => 'testamento'],
                        'cliente', $id, $id, 'clientes/' . $id
                    );
                    $this->redirect('clientes/editar?id=' . $id . '#dados');
                }
            } elseif ($secao === 'acesso') {
                $erro = (string) $this->salvarAcesso($id, $cliente);
                if ($erro === '') {
                    $this->redirect('clientes/editar?id=' . $id . '#acesso');
                }
            } else {
                $this->salvarSubItem($secao, $id);
                $this->redirect('clientes/editar?id=' . $id . '#' . $secao);
            }
        }

        // Status do acesso (login) do cliente, para a seção "Acesso do cliente".
        $login = null;
        if (!empty($cliente['usuario_id'])) {
            $st = db()->prepare('SELECT id, email, nivel, ativo FROM usuarios WHERE id = ?');
            $st->execute([$cliente['usuario_id']]);
            $login = $st->fetch() ?: null;
        }

        $subs = Pessoa::subListas($id);
        $docs = db()->prepare(
            "SELECT * FROM documentos WHERE tipo_referencia='cliente' AND referencia_id=? ORDER BY criado_em DESC"
        );
        $docs->execute([$id]);
        $documentos = $docs->fetchAll();

        $this->view('clientes/editar', compact('cliente', 'erro', 'subs', 'documentos', 'login'));
    }

    /**
     * Cria ou atualiza o login (acesso do cliente à própria área).
     * Retorna mensagem de erro ou null em caso de sucesso.
     */
    private function salvarAcesso(int $id, array $cliente): ?string
    {
        $email = trim($_POST['acesso_email'] ?? '');
        $senha = trim($_POST['acesso_senha'] ?? '');
        $uid   = $cliente['usuario_id'] ?? null;

        if (!$uid) {
            // Ainda não tem login → precisa de e-mail e senha para criar.
            if (!$email || !$senha)        return 'Informe e-mail e senha para criar o acesso do cliente.';
            if (strlen($senha) < 8)        return 'A senha deve ter ao menos 8 caracteres.';
            if (Cliente::emailEmUso($email)) return 'Este e-mail já está em uso por outro usuário.';
            $novoUid = Cliente::criarUsuarioLogin($cliente['nome'], $email, $senha);
            Cliente::atualizar($id, ['usuario_id' => $novoUid]);
            return null;
        }

        // Já tem login → atualizar e-mail e/ou redefinir senha.
        if (!$email && !$senha)                     return 'Informe um novo e-mail ou uma nova senha.';
        if ($email && Cliente::emailEmUso($email, (int) $uid)) return 'Este e-mail já está em uso por outro usuário.';
        if ($senha && strlen($senha) < 8)           return 'A senha deve ter ao menos 8 caracteres.';
        Cliente::atualizarLogin((int) $uid, $email ?: null, $senha ?: null);
        return null;
    }

    /** GET clientes/item-remover?tipo=&item=&id= — exclui item de sub-tabela. */
    public function itemRemover(): void
    {
        exige_admin();
        $tipo      = $_GET['tipo'] ?? '';
        $itemId    = (int) ($_GET['item'] ?? 0);
        $clienteId = (int) ($_GET['id'] ?? 0);
        if ($clienteId && $itemId && $tipo) {
            Pessoa::excluir($tipo, $clienteId, $itemId);
        }
        $this->redirect('clientes/editar?id=' . $clienteId . '#' . $tipo);
    }

    // ---------------------------------------------------------------------

    /** Monta o array [coluna=>valor] das colunas diretas a partir do POST. */
    private function camposDoPost(): array
    {
        $campos = [];
        foreach (self::COLUNAS as $col) {
            if ($col === 'tem_testamento') {
                $campos[$col] = isset($_POST['tem_testamento']) ? 1 : 0;
                continue;
            }
            $val = trim($_POST[$col] ?? '');
            if ($val === '' && in_array($col, self::NULOS, true)) {
                $val = null;
            }
            $campos[$col] = $val;
        }
        return $campos;
    }

    /** Grava um item numa das sub-tabelas conforme a seção. */
    private function salvarSubItem(string $secao, int $clienteId): void
    {
        $t = fn($k) => trim($_POST[$k] ?? '') ?: null;

        switch ($secao) {
            case 'telefones':
                if ($t('numero')) {
                    Pessoa::criar('telefones', $clienteId, [
                        'rotulo'   => $t('rotulo'),
                        'numero'   => $t('numero'),
                        'whatsapp' => isset($_POST['whatsapp']) ? 1 : 0,
                    ]);
                }
                break;

            case 'emails':
                if ($t('email')) {
                    Pessoa::criar('emails', $clienteId, [
                        'rotulo' => $t('rotulo'),
                        'email'  => $t('email'),
                    ]);
                }
                break;

            case 'enderecos':
                if ($t('logradouro') || $t('cep')) {
                    Pessoa::criar('enderecos', $clienteId, [
                        'rotulo'      => $t('rotulo'),
                        'cep'         => $t('cep'),
                        'logradouro'  => $t('logradouro'),
                        'numero'      => $t('numero'),
                        'complemento' => $t('complemento'),
                        'bairro'      => $t('bairro'),
                        'cidade'      => $t('cidade'),
                        'uf'          => $t('uf'),
                    ]);
                }
                break;

            case 'familiares':
                if ($t('nome')) {
                    Pessoa::criar('familiares', $clienteId, [
                        'parentesco'      => $_POST['parentesco'] ?? 'outro',
                        'nome'            => $t('nome'),
                        'cpf'             => $t('cpf'),
                        'data_nascimento' => $t('data_nascimento'),
                        'contato'         => $t('contato'),
                        'observacoes'     => $t('observacoes'),
                    ]);
                }
                break;

            case 'emergencia':
                if ($t('nome')) {
                    Pessoa::criar('emergencia', $clienteId, [
                        'nome'        => $t('nome'),
                        'parentesco'  => $t('parentesco'),
                        'telefone'    => $t('telefone'),
                        'observacoes' => $t('observacoes'),
                    ]);
                }
                break;
        }
    }
}
