<?php
/**
 * Controller de imóveis.
 * Reúne lista (com filtros), cadastro (form de 7 blocos + uploads),
 * edição, ficha (hub de modais) e o relatório de pendências em PDF.
 */
class ImoveisController extends Controller
{
    /* ───────────── Helpers de entrada (POST) ───────────── */

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

    /** Cliente em foco: selecionado (admin) ou vinculado (cliente). Redireciona se não houver. */
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

    /** Campos comuns (compartilhados por cadastro e edição), já normalizados. */
    private function camposComuns(): array
    {
        $links = gerar_links_localizacao([
            'logradouro'  => $_POST['logradouro']  ?? '',
            'numero'      => $_POST['numero']       ?? '',
            'complemento' => $_POST['complemento']  ?? '',
            'bairro'      => $_POST['bairro']       ?? '',
            'cidade'      => $_POST['cidade']       ?? '',
            'estado'      => $_POST['estado']       ?? '',
        ]);

        $valorCompra = $this->dec('valor_compra');
        $areaTotal   = $this->dec('area_total');
        $valorM2     = ($valorCompra && $areaTotal && $areaTotal > 0)
            ? round($valorCompra / $areaTotal, 2) : null;

        $cepRaw = preg_replace('/\D/', '', $_POST['cep'] ?? '');
        $cep    = strlen($cepRaw) === 8 ? substr($cepRaw, 0, 5) . '-' . substr($cepRaw, 5) : ($cepRaw ?: null);

        return [
            'nome_referencia'         => $this->str('nome_referencia'),
            'tipo'                    => $_POST['tipo']       ?? 'apartamento',
            'finalidade'              => $_POST['finalidade'] ?? 'residencial',
            'situacao'                => $_POST['situacao']   ?? 'pronto',
            'logradouro'              => $this->str('logradouro'),
            'numero'                  => $this->str('numero'),
            'complemento'             => $this->str('complemento'),
            'cep'                     => $cep,
            'bairro'                  => $this->str('bairro'),
            'cidade'                  => $this->str('cidade'),
            'estado'                  => $this->str('estado'),
            'link_maps'               => $links['maps'],
            'link_street_view'        => $links['street_view'],
            'inscricao_municipal'     => $this->str('inscricao_municipal'),
            'numero_matricula'        => $this->str('numero_matricula'),
            'cartorio'                => $this->str('cartorio'),
            'site_cartorio'           => $this->str('site_cartorio'),
            'comarca'                 => $this->str('comarca'),
            'livro'                   => $this->str('livro'),
            'folha'                   => $this->str('folha'),
            'data_matricula'          => $this->str('data_matricula'),
            'data_aquisicao'          => $this->str('data_aquisicao'),
            'forma_aquisicao'         => $this->str('forma_aquisicao'),
            'percentual_participacao' => $this->dec('percentual_participacao'),
            'outros_proprietarios'    => $this->str('outros_proprietarios'),
            'area_privativa'          => $this->dec('area_privativa'),
            'area_total'              => $this->dec('area_total'),
            'area_construida'         => $this->dec('area_construida'),
            'area_comum'              => $this->dec('area_comum'),
            'area_terreno'            => $this->dec('area_terreno'),
            'quartos'                 => $this->intv('quartos'),
            'suites'                  => $this->intv('suites'),
            'banheiros'               => $this->intv('banheiros'),
            'lavabos'                 => $this->intv('lavabos'),
            'vagas_garagem'           => $this->intv('vagas_garagem'),
            'andar'                   => $this->intv('andar'),
            'face_solar'              => $this->str('face_solar'),
            'vista'                   => $this->str('vista'),
            'posicao_imovel'          => $this->str('posicao_imovel'),
            'numero_unidade'          => $this->str('numero_unidade'),
            'valor_compra'            => $this->dec('valor_compra'),
            'valor_entrada'           => $this->dec('valor_entrada'),
            'valor_financiamento'     => $this->dec('valor_financiamento'),
            'banco_financiador'       => $this->str('banco_financiador'),
            'prazo_financiamento'     => $this->intv('prazo_financiamento'),
            'taxa_juros_anual'        => $this->dec('taxa_juros_anual'),
            'valor_mercado'           => $this->dec('valor_mercado'),
            'data_avaliacao_mercado'  => $this->str('data_avaliacao_mercado'),
            'valor_contabil'          => $this->dec('valor_contabil'),
            'empresa_avaliadora'      => $this->str('empresa_avaliadora'),
            'valor_m2'                => $valorM2,
            'custo_condominio'        => $this->dec('custo_condominio'),
            'custo_seguro'            => $this->dec('custo_seguro'),
            'custo_iptu_mensal'       => $this->dec('custo_iptu_mensal'),
            'custo_energia'           => $this->dec('custo_energia'),
            'custo_agua'              => $this->dec('custo_agua'),
            'custo_internet'          => $this->dec('custo_internet'),
            'custo_outros'            => $this->dec('custo_outros'),
        ];
    }

    /** Flags de status documental (só no cadastro). */
    private function docStatus(): array
    {
        $out = [];
        foreach ([
            'doc_status_matricula_atualizada', 'doc_status_certidao_negativa', 'doc_status_habite_se',
            'doc_status_convencao_condominio', 'doc_status_planta_aprovada', 'doc_status_alvara',
        ] as $k) {
            $out[$k] = isset($_POST[$k]) ? 1 : 0;
        }
        return $out;
    }

    /** Move uploads do Bloco 7 e registra em `documentos`. */
    private function processarUploads(int $clienteId, int $imovelId): void
    {
        $def = [
            'escritura' => 'escritura', 'matricula' => 'matricula', 'certidao_negativa' => 'certidao_negativa',
            'doc_iptu' => 'iptu', 'contrato_compra' => 'contrato_compra', 'laudo' => 'laudo',
            'habite_se' => 'habite_se', 'convencao_condominio' => 'convencao_condominio',
            'planta' => 'planta', 'alvara' => 'alvara', 'fotos' => 'foto',
        ];
        $dirBase = APP_ROOT . '/uploads/' . $clienteId . '/imovel_' . $imovelId . '/';
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
                    $rel = 'uploads/' . $clienteId . '/imovel_' . $imovelId . '/' . $nomeSalvo;
                    db()->prepare('INSERT INTO documentos (cliente_id, tipo_referencia, referencia_id, categoria, nome_arquivo, caminho, mime_type, tamanho_bytes) VALUES (?,?,?,?,?,?,?,?)')
                        ->execute([$clienteId, 'imovel', $imovelId, $cat, $orig, $rel, $types[$i], $sizes[$i]]);
                }
            }
        }
    }

    /* ───────────── Ações ───────────── */

    /** GET imoveis — lista com filtros. */
    public function index(): void
    {
        exige_login();
        $usuario = usuario_logado();
        $cli     = $this->clienteEmContexto($usuario);

        $filtro_tipo     = $_GET['tipo']     ?? '';
        $filtro_situacao = $_GET['situacao'] ?? '';
        $filtro_busca    = trim($_GET['busca'] ?? '');

        $imoveis = Imovel::listar($cli['id'], [
            'tipo' => $filtro_tipo, 'situacao' => $filtro_situacao, 'busca' => $filtro_busca,
        ]);

        // Modal de pendências após cadastro (?novo=ID)
        $novo_im = null; $novo_pend = []; $novo_pend_total = 0;
        $novo_id = (int) ($_GET['novo'] ?? 0);
        if ($novo_id) {
            $novo_im = Imovel::buscarDoCliente($novo_id, $cli['id']);
            if ($novo_im) {
                $novo_pend       = pendencias_imovel($novo_im);
                $novo_pend_total = pendencias_total($novo_pend);
            }
        }

        $this->view('imoveis/lista', compact(
            'cli', 'imoveis', 'filtro_tipo', 'filtro_situacao', 'filtro_busca',
            'novo_im', 'novo_pend', 'novo_pend_total'
        ));
    }

    /** GET/POST imoveis/novo — cadastro. */
    public function novo(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->str('nome_referencia')) {
                $erro = 'Nome de referência é obrigatório.';
            } else {
                $campos = array_merge(
                    ['cliente_id' => $cli['id'], 'codigo' => proximo_codigo_imovel()],
                    $this->camposComuns(),
                    $this->docStatus()
                );
                $imovelId = Imovel::criar($campos);

                $vm = $this->dec('valor_mercado');
                $dav = $this->str('data_avaliacao_mercado');
                if ($vm && $dav) {
                    Imovel::registrarAvaliacao($imovelId, $dav, $vm, 'Avaliação inicial no cadastro');
                }
                $this->processarUploads($cli['id'], $imovelId);
                $this->redirect('imoveis?novo=' . $imovelId);
            }
        }

        $d = $_POST;
        $this->view('imoveis/novo', compact('cli', 'erro', 'd'));
    }

    /** GET/POST imoveis/editar?id= — edição. */
    public function editar(): void
    {
        exige_admin();
        $id = (int) ($_GET['id'] ?? 0);
        $im = Imovel::buscar($id);
        if (!$im) $this->redirect('imoveis');

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->str('nome_referencia')) {
                $erro = 'Nome de referência é obrigatório.';
            } else {
                Imovel::atualizar($id, $this->camposComuns());
                $this->redirect('imoveis/ficha?id=' . $id);
            }
        }

        $d = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $im;
        $this->view('imoveis/editar', compact('im', 'erro', 'd', 'id'));
    }

    /** GET imoveis/ficha?id= — ficha com hub de modais. */
    public function ficha(): void
    {
        exige_login();
        $usuario = usuario_logado();
        $id = (int) ($_GET['id'] ?? 0);
        $im = Imovel::buscarComCliente($id);
        if (!$im) $this->redirect('imoveis');

        if ($usuario['nivel'] === 'cliente') {
            $c = db()->prepare('SELECT id FROM clientes WHERE usuario_id = ?');
            $c->execute([$usuario['id']]);
            $row = $c->fetch();
            if (!$row || $row['id'] != $im['cliente_id']) $this->redirect('imoveis');
        }

        $novo      = isset($_GET['novo']);
        $aba_abrir = $_GET['aba'] ?? '';
        $is_admin  = $usuario['nivel'] === 'admin';

        $iptu_list = db()->prepare('SELECT * FROM iptu WHERE imovel_id = ? ORDER BY ano DESC');
        $iptu_list->execute([$id]); $iptu_list = $iptu_list->fetchAll();

        $cond_fat = db()->prepare('SELECT * FROM condominio_faturas WHERE imovel_id = ? ORDER BY competencia DESC LIMIT 24');
        $cond_fat->execute([$id]); $cond_fat = $cond_fat->fetchAll();

        $reformas = db()->prepare('SELECT * FROM reformas WHERE imovel_id = ? ORDER BY data_inicio DESC');
        $reformas->execute([$id]); $reformas = $reformas->fetchAll();

        $manutencoes = Manutencao::listar($id);

        $contrato = db()->prepare('SELECT * FROM contratos_locacao WHERE imovel_id = ? AND status = "ativo" ORDER BY data_inicio DESC LIMIT 1');
        $contrato->execute([$id]); $contrato = $contrato->fetch();

        $lancamentos = db()->prepare('SELECT * FROM lancamentos_financeiros WHERE imovel_id = ? ORDER BY data_competencia DESC LIMIT 30');
        $lancamentos->execute([$id]); $lancamentos = $lancamentos->fetchAll();

        $documentos = db()->prepare('SELECT * FROM documentos WHERE tipo_referencia = "imovel" AND referencia_id = ? ORDER BY criado_em DESC');
        $documentos->execute([$id]); $documentos = $documentos->fetchAll();

        $condominio = null; $cond_docs = [];
        if (!empty($im['condominio_id'])) {
            $c = db()->prepare('SELECT * FROM condominios WHERE id = ?');
            $c->execute([$im['condominio_id']]);
            $condominio = $c->fetch();
            if ($condominio) {
                $cd = db()->prepare('SELECT * FROM documentos WHERE tipo_referencia = "condominio" AND referencia_id = ? ORDER BY criado_em DESC');
                $cd->execute([$condominio['id']]); $cond_docs = $cd->fetchAll();
            }
        }
        $condominios_lista = db()->query('SELECT id, nome, cidade FROM condominios ORDER BY nome')->fetchAll();

        $this->view('imoveis/ficha', compact(
            'im', 'id', 'novo', 'aba_abrir', 'is_admin',
            'iptu_list', 'cond_fat', 'reformas', 'manutencoes', 'contrato', 'lancamentos',
            'documentos', 'condominio', 'cond_docs', 'condominios_lista'
        ));
    }

    /** GET imoveis/pendencias-pdf?id= — relatório para impressão/PDF. */
    public function pendenciasPdf(): void
    {
        exige_login();
        $usuario = usuario_logado();
        $id = (int) ($_GET['id'] ?? 0);
        $im = Imovel::buscar($id);
        if (!$im) { http_response_code(404); exit('Imóvel não encontrado.'); }

        if ($usuario['nivel'] === 'cliente') {
            $sc = db()->prepare('SELECT id FROM clientes WHERE usuario_id = ? AND ativo = 1');
            $sc->execute([$usuario['id']]);
            $meu = $sc->fetch();
            if (!$meu || (int) $meu['id'] !== (int) $im['cliente_id']) { http_response_code(403); exit('Acesso negado.'); }
        }

        $cl = db()->prepare('SELECT nome, tipo_pessoa, cpf_cnpj FROM clientes WHERE id = ?');
        $cl->execute([$im['cliente_id']]);
        $cliente = $cl->fetch();

        $pend  = pendencias_imovel($im);
        $total = pendencias_total($pend);
        $hoje  = date('d/m/Y H:i');

        $this->view('imoveis/pendencias_pdf', compact('im', 'cliente', 'pend', 'total', 'hoje'));
    }
}
