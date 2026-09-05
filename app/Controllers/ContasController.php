<?php
/**
 * Controller de Contas Financeiras (Módulo 09). Cadastro, edição, listagem e
 * histórico de saldos. Os campos asaas_* ficam prontos para o vínculo futuro
 * com a API do Asaas (saldo/extrato/cobranças).
 */
class ContasController extends Controller
{
    private const TIPOS = ['corrente', 'poupanca', 'pagamento', 'investimento', 'internacional', 'outro'];

    private function str(string $k): ?string
    {
        $v = trim($_POST[$k] ?? '');
        return $v !== '' ? $v : null;
    }

    private function dec(string $k): ?float
    {
        $v = trim($_POST[$k] ?? '');
        if ($v === '') return null;
        // Aceita valores negativos (conta no vermelho): preserva o sinal.
        $neg = str_starts_with($v, '-');
        $n = (float) str_replace(',', '.', str_replace('.', '', ltrim($v, '-')));
        return $neg ? -$n : $n;
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
        $tipo = $_POST['tipo'] ?? 'corrente';
        if (!in_array($tipo, self::TIPOS)) $tipo = 'corrente';
        $pixTipo = $_POST['pix_tipo'] ?? '';
        $integracao = ($_POST['integracao'] ?? 'nenhuma') === 'asaas' ? 'asaas' : 'nenhuma';

        return [
            'apelido'          => $this->str('apelido'),
            'tipo'             => $tipo,
            'instituicao'      => $this->str('instituicao'),
            'banco_codigo'     => $this->str('banco_codigo'),
            'agencia'          => $this->str('agencia'),
            'numero_conta'     => $this->str('numero_conta'),
            'moeda'            => strtoupper($this->str('moeda') ?? 'BRL'),
            'pix_tipo'         => in_array($pixTipo, ['cpf','cnpj','email','telefone','aleatoria']) ? $pixTipo : null,
            'pix_chave'        => $this->str('pix_chave'),
            'swift'            => $this->str('swift'),
            'iban'             => $this->str('iban'),
            'routing'          => $this->str('routing'),
            'titular_nome'     => $this->str('titular_nome'),
            'titular_cpf_cnpj' => $this->str('titular_cpf_cnpj'),
            'gerente_nome'     => $this->str('gerente_nome'),
            'gerente_contato'  => $this->str('gerente_contato'),
            'integracao'       => $integracao,
            'asaas_account_id' => $this->str('asaas_account_id'),
            'asaas_wallet_id'  => $this->str('asaas_wallet_id'),
            'asaas_api_key'    => $this->str('asaas_api_key'),
            'observacoes'      => $this->str('observacoes'),
        ];
    }

    /** Regrava saldo_atual/saldo_data da conta a partir do histórico mais recente. */
    private function espelharSaldo(int $contaId): void
    {
        $ultimo = ContaSaldo::listar($contaId)[0] ?? null;
        if ($ultimo) {
            ContaFinanceira::atualizar($contaId, [
                'saldo_atual' => $ultimo['saldo'],
                'saldo_data'  => $ultimo['data'],
            ]);
        }
    }

    /** GET contas — lista com filtros. */
    public function index(): void
    {
        exige_login();
        $usuario = usuario_logado();
        $cli     = $this->clienteEmContexto($usuario);

        $filtro_tipo  = $_GET['tipo'] ?? '';
        $filtro_busca = trim($_GET['busca'] ?? '');
        $contas = ContaFinanceira::listar($cli['id'], $filtro_tipo, $filtro_busca);

        $this->view('contas/lista', compact('cli', 'contas', 'filtro_tipo', 'filtro_busca'));
    }

    /** GET/POST contas/novo — cadastro. */
    public function novo(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->str('apelido')) {
                $erro = 'Apelido / nome da conta é obrigatório.';
            } else {
                $campos = array_merge(
                    ['cliente_id' => $cli['id'], 'codigo' => proximo_codigo_conta()],
                    $this->camposComuns()
                );
                $contaId = ContaFinanceira::criar($campos);
                salvar_upload_documentos(
                    ['doc_contrato' => 'contrato', 'doc_extrato' => 'extrato', 'doc_outros' => 'outro'],
                    'conta_financeira', $contaId, $cli['id'],
                    $cli['id'] . '/conta_' . $contaId
                );
                // Saldo inicial informado no cadastro vira o 1º registro do histórico
                $saldoInicial = $this->dec('saldo_inicial');
                if ($saldoInicial !== null) {
                    ContaSaldo::criar($contaId, [
                        'data'  => ($_POST['saldo_inicial_data'] ?? '') ?: date('Y-m-d'),
                        'saldo' => $saldoInicial,
                    ]);
                    $this->espelharSaldo($contaId);
                }
                $this->redirect('contas/editar?id=' . $contaId . '&ok=1');
            }
        }

        $d = $_POST;
        $this->view('contas/novo', compact('cli', 'erro', 'd'));
    }

    /** GET/POST contas/editar?id= — edição. */
    public function editar(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $id = (int) ($_GET['id'] ?? 0);
        $conta = ContaFinanceira::buscarDoCliente($id, $cli['id']);
        if (!$conta) $this->redirect('contas');

        $erro = ''; $ok = isset($_GET['ok']) ? 'Conta cadastrada com sucesso!' : '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->str('apelido')) {
                $erro = 'Apelido / nome da conta é obrigatório.';
            } else {
                ContaFinanceira::atualizar($id, $this->camposComuns());
                salvar_upload_documentos(
                    ['doc_contrato' => 'contrato', 'doc_extrato' => 'extrato', 'doc_outros' => 'outro'],
                    'conta_financeira', $id, $cli['id'],
                    $cli['id'] . '/conta_' . $id
                );
                $conta = ContaFinanceira::buscarDoCliente($id, $cli['id']);
                $ok = 'Conta atualizada com sucesso!';
            }
        }

        $d = $conta;
        $docs = db()->prepare('SELECT * FROM documentos WHERE tipo_referencia = ? AND referencia_id = ? ORDER BY criado_em DESC');
        $docs->execute(['conta_financeira', $id]);
        $docs_list = $docs->fetchAll();

        $saldos = ContaSaldo::listar($id);

        $this->view('contas/editar', compact('cli', 'conta', 'd', 'erro', 'ok', 'docs_list', 'saldos'));
    }

    /** GET/POST contas/saldo?conta_id=&id= — registro no histórico de saldos. */
    public function saldo(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $conta_id = (int) ($_GET['conta_id'] ?? 0);
        $conta = ContaFinanceira::buscarDoCliente($conta_id, $cli['id']);
        if (!$conta) $this->redirect('contas');

        $id = (int) ($_GET['id'] ?? 0);
        $sl = $id ? ContaSaldo::buscar($id) : null;

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $campos = [
                'data'        => ($_POST['data'] ?? '') ?: date('Y-m-d'),
                'saldo'       => $this->dec('saldo'),
                'origem'      => 'manual',
                'observacoes' => $this->str('observacoes'),
            ];
            if ($campos['saldo'] === null) {
                $erro = 'Informe o saldo.';
            } else {
                if ($sl) ContaSaldo::atualizar($id, $campos);
                else     ContaSaldo::criar($conta_id, $campos);
                $this->espelharSaldo($conta_id);
                $this->redirect('contas/editar?id=' . $conta_id . '#saldos');
            }
        }

        $d = $_POST ?: ($sl ?: []);
        $this->view('contas/saldo', compact('conta', 'conta_id', 'sl', 'd', 'erro'));
    }
}
