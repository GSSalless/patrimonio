<?php
/**
 * Controller de veículos: lista (com busca), cadastro (+ uploads),
 * edição e relatório de pendências em PDF.
 */
class VeiculosController extends Controller
{
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

    /** Campos compartilhados por cadastro e edição. */
    private function camposComuns(): array
    {
        return [
            'marca'                    => $this->str('marca'),
            'modelo'                   => $this->str('modelo'),
            'ano_fabricacao'           => $this->intv('ano_fabricacao'),
            'ano_modelo'               => $this->intv('ano_modelo'),
            'cor'                      => $this->str('cor'),
            'combustivel'              => $this->str('combustivel'),
            'placa'                    => $this->str('placa'),
            'renavam'                  => $this->str('renavam'),
            'chassi'                   => $this->str('chassi'),
            'vencimento_licenciamento' => $this->str('vencimento_licenciamento'),
            'multas'                   => $this->str('multas'),
            'seguradora'               => $this->str('seguradora'),
            'apolice'                  => $this->str('apolice'),
            'franquia'                 => $this->dec('franquia'),
            'vencimento_seguro'        => $this->str('vencimento_seguro'),
            'data_aquisicao'           => $this->str('data_aquisicao'),
            'valor_aquisicao'          => $this->dec('valor_aquisicao'),
            'valor_fipe'               => $this->dec('valor_fipe'),
            'valor_mercado'            => $this->dec('valor_mercado'),
            'km_atual'                 => $this->intv('km_atual'),
            'consumo_medio'            => $this->dec('consumo_medio'),
            'observacoes'              => $this->str('observacoes'),
        ];
    }

    private function processarUploads(int $clienteId, int $veiculoId): void
    {
        $def = [
            'crlv' => 'crlv', 'apolice_doc' => 'apolice', 'nf_servico' => 'nf',
            'nf_peca' => 'nf', 'fotos' => 'foto',
        ];
        $dirBase = APP_ROOT . '/uploads/' . $clienteId . '/veiculo_' . $veiculoId . '/';
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
                    $rel = 'uploads/' . $clienteId . '/veiculo_' . $veiculoId . '/' . $nomeSalvo;
                    db()->prepare('INSERT INTO documentos (cliente_id, tipo_referencia, referencia_id, categoria, nome_arquivo, caminho, mime_type, tamanho_bytes) VALUES (?,?,?,?,?,?,?,?)')
                        ->execute([$clienteId, 'veiculo', $veiculoId, $cat, $orig, $rel, $types[$i], $sizes[$i]]);
                }
            }
        }
    }

    /** GET veiculos — lista com busca. */
    public function index(): void
    {
        exige_login();
        $usuario = usuario_logado();
        $cli     = $this->clienteEmContexto($usuario);

        $filtro_busca = trim($_GET['busca'] ?? '');
        $veiculos     = Veiculo::listar($cli['id'], $filtro_busca);

        $novo_ve = null; $novo_pend = []; $novo_pend_total = 0;
        $novo_id = (int) ($_GET['novo'] ?? 0);
        if ($novo_id) {
            $novo_ve = Veiculo::buscarDoCliente($novo_id, $cli['id']);
            if ($novo_ve) {
                $novo_pend       = pendencias_veiculo($novo_ve);
                $novo_pend_total = pendencias_total($novo_pend);
            }
        }

        $this->view('veiculos/lista', compact(
            'cli', 'veiculos', 'filtro_busca', 'novo_ve', 'novo_pend', 'novo_pend_total'
        ));
    }

    /** GET/POST veiculos/novo — cadastro. */
    public function novo(): void
    {
        exige_admin();
        $cli = cliente_selecionado();
        if (!$cli) $this->redirect('dashboard');

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->str('modelo')) {
                $erro = 'Modelo é obrigatório.';
            } else {
                $campos = array_merge(
                    ['cliente_id' => $cli['id'], 'codigo' => proximo_codigo_veiculo()],
                    $this->camposComuns()
                );
                $veiculoId = Veiculo::criar($campos);
                $this->processarUploads($cli['id'], $veiculoId);
                $this->redirect('veiculos?novo=' . $veiculoId);
            }
        }

        $d = $_POST;
        $this->view('veiculos/novo', compact('cli', 'erro', 'd'));
    }

    /** GET/POST veiculos/editar?id= — edição. */
    public function editar(): void
    {
        exige_admin();
        $id = (int) ($_GET['id'] ?? 0);
        $ve = Veiculo::buscar($id);
        if (!$ve) $this->redirect('veiculos');

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->str('modelo')) {
                $erro = 'Modelo é obrigatório.';
            } else {
                Veiculo::atualizar($id, $this->camposComuns());
                $this->redirect('veiculos');
            }
        }

        $d = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $ve;

        $abastecimentos = Abastecimento::listar($id);
        $manutencoes    = VeiculoManutencao::listar($id);
        $sinistros      = Sinistro::listar($id);

        $this->view('veiculos/editar', compact('ve', 'erro', 'd', 'abastecimentos', 'manutencoes', 'sinistros'));
    }

    /** GET veiculos/pendencias-pdf?id= — relatório para impressão/PDF. */
    public function pendenciasPdf(): void
    {
        exige_login();
        $usuario = usuario_logado();
        $id = (int) ($_GET['id'] ?? 0);
        $ve = Veiculo::buscar($id);
        if (!$ve) { http_response_code(404); exit('Veículo não encontrado.'); }

        if ($usuario['nivel'] === 'cliente') {
            $sc = db()->prepare('SELECT id FROM clientes WHERE usuario_id = ? AND ativo = 1');
            $sc->execute([$usuario['id']]);
            $meu = $sc->fetch();
            if (!$meu || (int) $meu['id'] !== (int) $ve['cliente_id']) { http_response_code(403); exit('Acesso negado.'); }
        }

        $cl = db()->prepare('SELECT nome, tipo_pessoa, cpf_cnpj FROM clientes WHERE id = ?');
        $cl->execute([$ve['cliente_id']]);
        $cliente = $cl->fetch();

        $pend  = pendencias_veiculo($ve);
        $total = pendencias_total($pend);
        $hoje  = date('d/m/Y H:i');
        $nome  = trim(($ve['marca'] ?? '') . ' ' . ($ve['modelo'] ?? ''));

        $this->view('veiculos/pendencias_pdf', compact('ve', 'cliente', 'pend', 'total', 'hoje', 'nome'));
    }

    // ------------------------------------------------------------------
    // Históricos do veículo (abastecimentos, manutenções, sinistros)
    // ------------------------------------------------------------------

    private function boolv(string $k): int
    {
        return !empty($_POST[$k]) ? 1 : 0;
    }

    /** GET/POST veiculos/abastecimento?veiculo_id=&id= */
    public function abastecimento(): void
    {
        exige_admin();
        $veiculo_id = (int) ($_GET['veiculo_id'] ?? 0);
        $ve = Veiculo::buscar($veiculo_id);
        if (!$ve) $this->redirect('veiculos');

        $id = (int) ($_GET['id'] ?? 0);
        $ab = $id ? Abastecimento::buscar($id) : null;

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $campos = [
                'data'         => ($_POST['data'] ?? '') ?: date('Y-m-d'),
                'combustivel'  => $this->str('combustivel'),
                'litros'       => $this->dec('litros'),
                'valor_litro'  => $this->dec('valor_litro'),
                'valor_total'  => $this->dec('valor_total'),
                'km'           => $this->intv('km'),
                'posto'        => $this->str('posto'),
                'tanque_cheio' => $this->boolv('tanque_cheio'),
                'observacoes'  => $this->str('observacoes'),
            ];
            if ($ab) Abastecimento::atualizar($id, $campos);
            else     Abastecimento::criar($veiculo_id, $campos);
            $this->redirect('veiculos/editar?id=' . $veiculo_id . '#abastecimentos');
        }

        $d = $_POST ?: ($ab ?: []);
        $this->view('veiculos/abastecimento', compact('ve', 'veiculo_id', 'ab', 'd', 'erro'));
    }

    /** GET/POST veiculos/manutencao?veiculo_id=&id= */
    public function manutencao(): void
    {
        exige_admin();
        $veiculo_id = (int) ($_GET['veiculo_id'] ?? 0);
        $ve = Veiculo::buscar($veiculo_id);
        if (!$ve) $this->redirect('veiculos');

        $id = (int) ($_GET['id'] ?? 0);
        $mt = $id ? VeiculoManutencao::buscar($id) : null;

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $campos = [
                'data'         => ($_POST['data'] ?? '') ?: date('Y-m-d'),
                'tipo'         => $_POST['tipo'] ?? 'revisao',
                'descricao'    => trim($_POST['descricao'] ?? ''),
                'fornecedor'   => $this->str('fornecedor'),
                'km'           => $this->intv('km'),
                'valor'        => $this->dec('valor'),
                'garantia_ate' => ($_POST['garantia_ate'] ?? '') ?: null,
                'proxima_data' => ($_POST['proxima_data'] ?? '') ?: null,
                'proxima_km'   => $this->intv('proxima_km'),
                'observacoes'  => $this->str('observacoes'),
            ];
            if ($campos['descricao'] === '') {
                $erro = 'Descrição é obrigatória.';
            } else {
                if ($mt) VeiculoManutencao::atualizar($id, $campos);
                else     VeiculoManutencao::criar($veiculo_id, $campos);
                $this->redirect('veiculos/editar?id=' . $veiculo_id . '#manutencoes');
            }
        }

        $d = $_POST ?: ($mt ?: []);
        $this->view('veiculos/manutencao', compact('ve', 'veiculo_id', 'mt', 'd', 'erro'));
    }

    /** GET/POST veiculos/sinistro?veiculo_id=&id= */
    public function sinistro(): void
    {
        exige_admin();
        $veiculo_id = (int) ($_GET['veiculo_id'] ?? 0);
        $ve = Veiculo::buscar($veiculo_id);
        if (!$ve) $this->redirect('veiculos');

        $id = (int) ($_GET['id'] ?? 0);
        $si = $id ? Sinistro::buscar($id) : null;

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $campos = [
                'data'               => ($_POST['data'] ?? '') ?: date('Y-m-d'),
                'tipo'               => $_POST['tipo'] ?? 'colisao',
                'descricao'          => trim($_POST['descricao'] ?? ''),
                'local'              => $this->str('local'),
                'boletim_ocorrencia' => $this->str('boletim_ocorrencia'),
                'acionou_seguro'     => $this->boolv('acionou_seguro'),
                'numero_sinistro'    => $this->str('numero_sinistro'),
                'valor_prejuizo'     => $this->dec('valor_prejuizo'),
                'valor_franquia'     => $this->dec('valor_franquia'),
                'status'             => $_POST['status'] ?? 'aberto',
                'observacoes'        => $this->str('observacoes'),
            ];
            if ($campos['descricao'] === '') {
                $erro = 'Descrição é obrigatória.';
            } else {
                if ($si) Sinistro::atualizar($id, $campos);
                else     Sinistro::criar($veiculo_id, $campos);
                $this->redirect('veiculos/editar?id=' . $veiculo_id . '#sinistros');
            }
        }

        $d = $_POST ?: ($si ?: []);
        $this->view('veiculos/sinistro', compact('ve', 'veiculo_id', 'si', 'd', 'erro'));
    }
}
