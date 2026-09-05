<?php
/**
 * Controller de Seguros (Módulo 11). Cadastro central de apólices, com vínculo
 * opcional a um bem/pessoa (item_tipo + item_id). A vigência alimenta a Agenda.
 */
class SegurosController extends Controller
{
    private const TIPOS  = ['vida','saude','veiculo','residencial','imovel','embarcacao','empresarial','viagem','outro'];
    private const STATUS = ['vigente','vencida','cancelada','em_cotacao'];
    private const PGTO   = ['anual','semestral','mensal','parcelado','unico','outro'];
    private const ITEM_TIPOS = ['nenhum','imovel','veiculo','outro_bem','pessoa'];

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
        $status = in_array($_POST['status'] ?? '', self::STATUS, true) ? $_POST['status'] : 'vigente';
        $pgto   = in_array($_POST['forma_pagamento'] ?? '', self::PGTO, true) ? $_POST['forma_pagamento'] : null;

        // Vínculo vem num único select "tipo:id" (ex.: "veiculo:3"); vazio = nenhum.
        $item_tipo = 'nenhum'; $item_id = null;
        $vinc = trim($_POST['vinculo'] ?? '');
        if ($vinc !== '' && str_contains($vinc, ':')) {
            [$t, $i] = explode(':', $vinc, 2);
            if (in_array($t, self::ITEM_TIPOS, true) && ctype_digit($i)) {
                $item_tipo = $t; $item_id = (int) $i;
            }
        }

        return [
            'tipo'             => $tipo,
            'item_tipo'        => $item_tipo,
            'item_id'          => $item_id,
            'seguradora'       => $this->str('seguradora'),
            'corretora'        => $this->str('corretora'),
            'corretor_nome'    => $this->str('corretor_nome'),
            'corretor_contato' => $this->str('corretor_contato'),
            'numero_apolice'   => $this->str('numero_apolice'),
            'vigencia_inicio'  => $this->dataOuNull('vigencia_inicio'),
            'vigencia_fim'     => $this->dataOuNull('vigencia_fim'),
            'valor_segurado'   => $this->dec('valor_segurado'),
            'premio'           => $this->dec('premio'),
            'franquia'         => $this->dec('franquia'),
            'forma_pagamento'  => $pgto,
            'cobertura'        => $this->str('cobertura'),
            'beneficiarios'    => $this->str('beneficiarios'),
            'status'           => $status,
            'observacoes'      => $this->str('observacoes'),
        ];
    }

    /** GET seguros — lista com filtros. */
    public function index(): void
    {
        exige_login();
        $usuario = usuario_logado();
        $cli     = $this->clienteEmContexto($usuario);

        $filtro_tipo   = $_GET['tipo'] ?? '';
        $filtro_status = $_GET['status'] ?? '';
        $filtro_busca  = trim($_GET['busca'] ?? '');
        $seguros = Seguro::listar($cli['id'], $filtro_tipo, $filtro_status, $filtro_busca);

        $this->view('seguros/lista', compact('cli', 'seguros', 'filtro_tipo', 'filtro_status', 'filtro_busca'));
    }

    /** GET/POST seguros/novo — cadastro. */
    public function novo(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->str('seguradora') && !$this->str('numero_apolice')) {
                $erro = 'Informe ao menos a seguradora ou o número da apólice.';
            } else {
                $campos = array_merge(
                    ['cliente_id' => $cli['id'], 'codigo' => proximo_codigo_seguro()],
                    $this->camposComuns()
                );
                $seguroId = Seguro::criar($campos);
                salvar_upload_documentos(
                    ['doc_apolice' => 'apolice', 'doc_boleto' => 'boleto', 'doc_outros' => 'outro'],
                    'seguro', $seguroId, $cli['id'],
                    $cli['id'] . '/seguro_' . $seguroId
                );
                $this->redirect('seguros/editar?id=' . $seguroId . '&ok=1');
            }
        }

        $d = $_POST;
        $itens_vinc = Seguro::itensSeguraveis($cli['id']);
        $this->view('seguros/novo', compact('cli', 'erro', 'd', 'itens_vinc'));
    }

    /** GET/POST seguros/editar?id= — edição. */
    public function editar(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $id = (int) ($_GET['id'] ?? 0);
        $seguro = Seguro::buscarDoCliente($id, $cli['id']);
        if (!$seguro) $this->redirect('seguros');

        $erro = ''; $ok = isset($_GET['ok']) ? 'Seguro cadastrado com sucesso!' : '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->str('seguradora') && !$this->str('numero_apolice')) {
                $erro = 'Informe ao menos a seguradora ou o número da apólice.';
            } else {
                Seguro::atualizar($id, $this->camposComuns());
                salvar_upload_documentos(
                    ['doc_apolice' => 'apolice', 'doc_boleto' => 'boleto', 'doc_outros' => 'outro'],
                    'seguro', $id, $cli['id'],
                    $cli['id'] . '/seguro_' . $id
                );
                $seguro = Seguro::buscarDoCliente($id, $cli['id']);
                $ok = 'Seguro atualizado com sucesso!';
            }
        }

        $d = $seguro;
        $docs = db()->prepare('SELECT * FROM documentos WHERE tipo_referencia = ? AND referencia_id = ? ORDER BY criado_em DESC');
        $docs->execute(['seguro', $id]);
        $docs_list = $docs->fetchAll();

        $itens_vinc = Seguro::itensSeguraveis($cli['id']);
        $this->view('seguros/editar', compact('cli', 'seguro', 'd', 'erro', 'ok', 'docs_list', 'itens_vinc'));
    }
}
