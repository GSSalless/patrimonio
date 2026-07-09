<?php
/**
 * Controller de "outros bens". Cadastro em duas etapas (seletor de tipo →
 * formulário), edição e listagem com filtros.
 */
class OutrosController extends Controller
{
    private const TIPOS = ['embarcacao', 'joia', 'obra_de_arte', 'outro'];

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

    private function intv(string $k): ?int
    {
        $v = trim($_POST[$k] ?? '');
        return $v !== '' ? (int) $v : null;
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

    /** Campos compartilhados por cadastro e edição (exceto cliente_id/codigo/tipo/foto). */
    private function camposComuns(): array
    {
        return [
            'nome'                => $this->str('nome'),
            'descricao'           => $this->str('descricao'),
            'marca'               => $this->str('marca'),
            'modelo'              => $this->str('modelo'),
            'ano'                 => $this->intv('ano'),
            'cor'                 => $this->str('cor'),
            'tipo_embarcacao'     => $this->str('tipo_embarcacao'),
            'comprimento_m'       => $this->dec('comprimento_m'),
            'motor'               => $this->str('motor'),
            'horimetro'           => $this->intv('horimetro'),
            'registro_embarcacao' => $this->str('registro_embarcacao'),
            'marina'              => $this->str('marina'),
            'vaga_marina'         => $this->str('vaga_marina'),
            'mensalidade_marina'  => $this->dec('mensalidade_marina'),
            'material'            => $this->str('material'),
            'quilates'            => $this->dec('quilates'),
            'artista_autor'       => $this->str('artista_autor'),
            'dimensoes'           => $this->str('dimensoes'),
            'tecnica'             => $this->str('tecnica'),
            'certificado'         => $this->str('certificado'),
            'seguradora'          => $this->str('seguradora'),
            'apolice'             => $this->str('apolice'),
            'franquia'            => $this->dec('franquia'),
            'vencimento_seguro'   => $this->str('vencimento_seguro'),
            'data_aquisicao'      => $this->str('data_aquisicao'),
            'valor_aquisicao'     => $this->dec('valor_aquisicao'),
            'valor_mercado'       => $this->dec('valor_mercado'),
            'observacoes'         => $this->str('observacoes'),
        ];
    }

    private function processarUploads(int $clienteId, int $obId): void
    {
        $def = ['foto_principal' => 'foto', 'doc_laudo' => 'laudo', 'doc_apolice' => 'apolice', 'doc_outros' => 'outro'];
        $dirBase = APP_ROOT . '/uploads/' . $clienteId . '/outro_' . $obId . '/';
        if (!is_dir($dirBase)) mkdir($dirBase, 0755, true);
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];

        foreach ($def as $field => $cat) {
            $files = $_FILES[$field] ?? null;
            if (!$files || empty($files['name'])) continue;
            $names = is_array($files['name'])     ? $files['name']     : [$files['name']];
            $tmps  = is_array($files['tmp_name']) ? $files['tmp_name'] : [$files['tmp_name']];
            $sizes = is_array($files['size'])     ? $files['size']     : [$files['size']];
            $types = is_array($files['type'])     ? $files['type']     : [$files['type']];

            foreach ($names as $i => $orig) {
                if (empty($orig) || empty($tmps[$i])) continue;
                $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) continue;
                if ($sizes[$i] > 30 * 1024 * 1024) continue;

                $nomeSalvo = $field . '_' . ($i + 1) . '_' . time() . '.' . $ext;
                if (move_uploaded_file($tmps[$i], $dirBase . $nomeSalvo)) {
                    $rel = 'uploads/' . $clienteId . '/outro_' . $obId . '/' . $nomeSalvo;
                    db()->prepare('INSERT INTO documentos (cliente_id, tipo_referencia, referencia_id, categoria, nome_arquivo, caminho, mime_type, tamanho_bytes) VALUES (?,?,?,?,?,?,?,?)')
                        ->execute([$clienteId, 'outro_bem', $obId, $cat, $orig, $rel, $types[$i], $sizes[$i]]);
                    if ($field === 'foto_principal') {
                        OutroBem::definirFotoPrincipal($obId, $rel);
                    }
                }
            }
        }
    }

    /** GET outros — lista com filtros. */
    public function index(): void
    {
        exige_login();
        $usuario = usuario_logado();
        $cli     = $this->clienteEmContexto($usuario);

        $filtro_tipo  = $_GET['tipo'] ?? '';
        $filtro_busca = trim($_GET['busca'] ?? '');
        $bens = OutroBem::listar($cli['id'], $filtro_tipo, $filtro_busca);

        $novo_ob = null; $novo_pend = []; $novo_pend_total = 0;
        $novo_id = (int) ($_GET['novo'] ?? 0);
        if ($novo_id) {
            $novo_ob = OutroBem::buscarDoCliente($novo_id, $cli['id']);
            if ($novo_ob) {
                $novo_pend       = pendencias_outro_bem($novo_ob);
                $novo_pend_total = pendencias_total($novo_pend);
            }
        }

        $this->view('outros/lista', compact('cli', 'bens', 'filtro_tipo', 'filtro_busca', 'novo_ob', 'novo_pend', 'novo_pend_total'));
    }

    /** GET/POST outros/novo — seletor de tipo (etapa 1) + formulário (etapa 2). */
    public function novo(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $tipo = $_GET['tipo'] ?? $_POST['tipo'] ?? '';
        if (!in_array($tipo, self::TIPOS)) {
            $this->view('outros/selecionar_tipo', compact('cli'));
            return;
        }

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->str('nome')) {
                $erro = 'Nome / descrição é obrigatório.';
            } else {
                $campos = array_merge(
                    ['cliente_id' => $cli['id'], 'codigo' => proximo_codigo_outro_bem(), 'tipo' => $tipo],
                    $this->camposComuns()
                );
                $obId = OutroBem::criar($campos);
                $this->processarUploads($cli['id'], $obId);
                $this->redirect('outros?novo=' . $obId);
            }
        }

        $d = $_POST;
        $this->view('outros/novo', compact('cli', 'erro', 'tipo', 'd'));
    }

    /** GET/POST outros/editar?id= — edição. */
    public function editar(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $id = (int) ($_GET['id'] ?? 0);
        $ob = OutroBem::buscarDoCliente($id, $cli['id']);
        if (!$ob) $this->redirect('outros');

        $erro = ''; $ok = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->str('nome')) {
                $erro = 'Nome / descrição é obrigatório.';
            } else {
                OutroBem::atualizar($id, $this->camposComuns());
                $this->processarUploads($cli['id'], $id);
                $ob = OutroBem::buscarDoCliente($id, $cli['id']);
                $ok = 'Bem atualizado com sucesso!';
            }
        }

        $d    = $ob;
        $tipo = $ob['tipo'];
        $docs = db()->prepare('SELECT * FROM documentos WHERE tipo_referencia = ? AND referencia_id = ? ORDER BY criado_em DESC');
        $docs->execute(['outro_bem', $id]);
        $docs_list = $docs->fetchAll();

        $manutencoes = BemManutencao::listar($id);
        $avaliacoes  = AvaliacaoBem::listar($id);

        $this->view('outros/editar', compact('cli', 'ob', 'd', 'tipo', 'erro', 'ok', 'docs_list', 'manutencoes', 'avaliacoes'));
    }

    /** GET/POST outros/manutencao?bem_id=&id= — revisão/manutenção do bem. */
    public function manutencao(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $bem_id = (int) ($_GET['bem_id'] ?? 0);
        $ob = OutroBem::buscarDoCliente($bem_id, $cli['id']);
        if (!$ob) $this->redirect('outros');

        $id = (int) ($_GET['id'] ?? 0);
        $mt = $id ? BemManutencao::buscar($id) : null;

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $campos = [
                'data'         => ($_POST['data'] ?? '') ?: date('Y-m-d'),
                'tipo'         => $_POST['tipo'] ?? 'revisao',
                'descricao'    => trim($_POST['descricao'] ?? ''),
                'fornecedor'   => $this->str('fornecedor'),
                'horimetro'    => $this->intv('horimetro'),
                'valor'        => $this->dec('valor'),
                'garantia_ate' => ($_POST['garantia_ate'] ?? '') ?: null,
                'proxima_data' => ($_POST['proxima_data'] ?? '') ?: null,
                'observacoes'  => $this->str('observacoes'),
            ];
            if ($campos['descricao'] === '') {
                $erro = 'Descrição é obrigatória.';
            } else {
                if ($mt) BemManutencao::atualizar($id, $campos);
                else     BemManutencao::criar($bem_id, $campos);
                $this->redirect('outros/editar?id=' . $bem_id . '#manutencoes');
            }
        }

        $d = $_POST ?: ($mt ?: []);
        $this->view('outros/manutencao', compact('ob', 'bem_id', 'mt', 'd', 'erro'));
    }

    /** GET/POST outros/avaliacao?bem_id=&id= — avaliação (histórico de valor). */
    public function avaliacao(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $bem_id = (int) ($_GET['bem_id'] ?? 0);
        $ob = OutroBem::buscarDoCliente($bem_id, $cli['id']);
        if (!$ob) $this->redirect('outros');

        $id = (int) ($_GET['id'] ?? 0);
        $av = $id ? AvaliacaoBem::buscar($id) : null;

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $campos = [
                'data'        => ($_POST['data'] ?? '') ?: date('Y-m-d'),
                'valor'       => $this->dec('valor'),
                'fonte'       => $this->str('fonte'),
                'observacoes' => $this->str('observacoes'),
            ];
            if (!$campos['valor']) {
                $erro = 'Informe o valor avaliado.';
            } else {
                if ($av) AvaliacaoBem::atualizar($id, $campos);
                else     AvaliacaoBem::criar($bem_id, $campos);
                // Atualiza o valor de mercado do bem com a avaliação mais recente
                $ultima = AvaliacaoBem::listar($bem_id)[0] ?? null;
                if ($ultima) OutroBem::atualizar($bem_id, ['valor_mercado' => $ultima['valor']]);
                $this->redirect('outros/editar?id=' . $bem_id . '#avaliacoes');
            }
        }

        $d = $_POST ?: ($av ?: []);
        $this->view('outros/avaliacao', compact('ob', 'bem_id', 'av', 'd', 'erro'));
    }
}
