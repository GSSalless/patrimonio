<?php
function base_url(string $path = ''): string {
    // A URL base vem da config (BASE_URL, derivada do .env). Fallback para o
    // ambiente local do XAMPP caso a constante ainda não tenha sido definida.
    $base = defined('BASE_URL') ? BASE_URL : '/cezar/';
    return $base . ltrim($path, '/');
}

function moeda(float $valor): string {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function data_br(?string $data): string {
    if (!$data) return '—';
    return date('d/m/Y', strtotime($data));
}

/** Monta o link wa.me a partir de um telefone. Assume Brasil (+55) se não vier DDI. */
function link_whatsapp(?string $telefone): string {
    $d = preg_replace('/\D/', '', $telefone ?? '');
    if (strlen($d) < 10) return '';
    if (strlen($d) <= 11) $d = '55' . $d;   // 10/11 dígitos = nº nacional sem DDI
    return 'https://wa.me/' . $d;
}

/** Rótulo do estado civil. */
function estado_civil_label(?string $ec): string {
    return [
        'solteiro' => 'Solteiro(a)', 'casado' => 'Casado(a)',
        'divorciado' => 'Divorciado(a)', 'viuvo' => 'Viúvo(a)',
        'uniao_estavel' => 'União estável', 'separado' => 'Separado(a)',
    ][$ec ?? ''] ?? '—';
}

/** Rótulo do grau de parentesco (família/sucessão). */
function parentesco_label(?string $p): string {
    return [
        'conjuge' => 'Cônjuge', 'ex_conjuge' => 'Ex-cônjuge', 'pai' => 'Pai',
        'mae' => 'Mãe', 'filho' => 'Filho(a)', 'dependente' => 'Dependente',
        'herdeiro' => 'Herdeiro(a)', 'irmao' => 'Irmão(ã)', 'neto' => 'Neto(a)',
        'outro' => 'Outro',
    ][$p ?? ''] ?? h((string) $p);
}

function proximo_codigo_imovel(): string {
    $stmt = db()->query("SELECT MAX(CAST(SUBSTRING(codigo, 4) AS UNSIGNED)) AS ultimo FROM imoveis");
    $row = $stmt->fetch();
    $proximo = ($row['ultimo'] ?? 0) + 1;
    return 'IM-' . str_pad($proximo, 4, '0', STR_PAD_LEFT);
}

function proximo_codigo_veiculo(): string {
    $stmt = db()->query("SELECT MAX(CAST(SUBSTRING(codigo, 4) AS UNSIGNED)) AS ultimo FROM veiculos");
    $row = $stmt->fetch();
    $proximo = ($row['ultimo'] ?? 0) + 1;
    return 'VE-' . str_pad($proximo, 4, '0', STR_PAD_LEFT);
}

function proximo_codigo_outro_bem(): string {
    $stmt = db()->query("SELECT MAX(CAST(SUBSTRING(codigo, 4) AS UNSIGNED)) AS ultimo FROM outros_bens");
    $row = $stmt->fetch();
    $proximo = ($row['ultimo'] ?? 0) + 1;
    return 'OB-' . str_pad($proximo, 4, '0', STR_PAD_LEFT);
}

function proximo_codigo_conta(): string {
    $stmt = db()->query("SELECT MAX(CAST(SUBSTRING(codigo, 4) AS UNSIGNED)) AS ultimo FROM contas_financeiras");
    $row = $stmt->fetch();
    $proximo = ($row['ultimo'] ?? 0) + 1;
    return 'CF-' . str_pad($proximo, 4, '0', STR_PAD_LEFT);
}

/**
 * Patrimônio consolidado (Módulo 15 — Dashboard Executivo).
 * Soma o valor de mercado dos bens + saldo em contas (BRL).
 *
 * @param int|null $cliente_id  filtra por cliente; null = todos os clientes ativos
 * @return array {
 *   imoveis_qtd, imoveis_valor, veiculos_qtd, veiculos_valor,
 *   outros_qtd, outros_valor, contas_qtd, contas_saldo, total
 * }  — valores em float, quantidades em int
 */
function patrimonio_consolidado(?int $cliente_id = null): array {
    // Cláusula de filtro por cliente (ou todos). Cada tabela tem cliente_id + ativo.
    $where = 'ativo = 1' . ($cliente_id !== null ? ' AND cliente_id = :cid' : '');
    $bind  = $cliente_id !== null ? [':cid' => $cliente_id] : [];

    $uma = function (string $sql) use ($bind) {
        $stmt = db()->prepare($sql);
        $stmt->execute($bind);
        return $stmt->fetch(PDO::FETCH_NUM);
    };

    // Imóveis: valor de mercado
    [$imoveis_qtd, $imoveis_valor] = $uma(
        "SELECT COUNT(*), COALESCE(SUM(valor_mercado),0) FROM imoveis WHERE $where"
    );
    // Veículos: mercado → FIPE → aquisição
    [$veiculos_qtd, $veiculos_valor] = $uma(
        "SELECT COUNT(*), COALESCE(SUM(COALESCE(valor_mercado, valor_fipe, valor_aquisicao, 0)),0)
           FROM veiculos WHERE $where"
    );
    // Outros bens: mercado → aquisição
    [$outros_qtd, $outros_valor] = $uma(
        "SELECT COUNT(*), COALESCE(SUM(COALESCE(valor_mercado, valor_aquisicao, 0)),0)
           FROM outros_bens WHERE $where"
    );
    // Contas: só BRL entra no consolidado (moedas diferentes não se somam)
    [$contas_qtd, $contas_saldo] = $uma(
        "SELECT COUNT(*), COALESCE(SUM(CASE WHEN moeda = 'BRL' THEN saldo_atual ELSE 0 END),0)
           FROM contas_financeiras WHERE $where"
    );

    $imoveis_valor  = (float) $imoveis_valor;
    $veiculos_valor = (float) $veiculos_valor;
    $outros_valor   = (float) $outros_valor;
    $contas_saldo   = (float) $contas_saldo;

    return [
        'imoveis_qtd'    => (int) $imoveis_qtd,
        'imoveis_valor'  => $imoveis_valor,
        'veiculos_qtd'   => (int) $veiculos_qtd,
        'veiculos_valor' => $veiculos_valor,
        'outros_qtd'     => (int) $outros_qtd,
        'outros_valor'   => $outros_valor,
        'contas_qtd'     => (int) $contas_qtd,
        'contas_saldo'   => $contas_saldo,
        'total'          => $imoveis_valor + $veiculos_valor + $outros_valor + $contas_saldo,
    ];
}

function proximo_codigo_seguro(): string {
    $stmt = db()->query("SELECT MAX(CAST(SUBSTRING(codigo, 4) AS UNSIGNED)) AS ultimo FROM seguros");
    $row = $stmt->fetch();
    $proximo = ($row['ultimo'] ?? 0) + 1;
    return 'SG-' . str_pad($proximo, 4, '0', STR_PAD_LEFT);
}

function proximo_codigo_empresa(): string {
    $stmt = db()->query("SELECT MAX(CAST(SUBSTRING(codigo, 4) AS UNSIGNED)) AS ultimo FROM empresas");
    $row = $stmt->fetch();
    $proximo = ($row['ultimo'] ?? 0) + 1;
    return 'EM-' . str_pad($proximo, 4, '0', STR_PAD_LEFT);
}

/** Rótulos do Módulo 03 — Empresas. */
function empresa_natureza_label(string $n): string {
    return [
        'operacional' => 'Operacional', 'holding_patrimonial' => 'Holding patrimonial',
        'holding_participacao' => 'Holding de participação', 'spe' => 'SPE', 'outro' => 'Outra',
    ][$n] ?? ucfirst($n);
}
function empresa_regime_label(?string $r): string {
    return [
        'simples' => 'Simples Nacional', 'lucro_presumido' => 'Lucro Presumido',
        'lucro_real' => 'Lucro Real', 'mei' => 'MEI', 'imune' => 'Imune/Isenta', 'outro' => 'Outro',
    ][$r] ?? '—';
}
function empresa_socio_funcao_label(string $f): string {
    return [
        'socio' => 'Sócio', 'administrador' => 'Administrador',
        'socio_administrador' => 'Sócio-administrador', 'procurador' => 'Procurador', 'outro' => 'Outro',
    ][$f] ?? ucfirst($f);
}

/** Rótulos de tipo de seguro (Módulo 11). */
function seguro_tipo_label(string $t): string {
    return [
        'vida' => 'Vida', 'saude' => 'Saúde', 'veiculo' => 'Veículo',
        'residencial' => 'Residencial', 'imovel' => 'Imóvel', 'embarcacao' => 'Embarcação',
        'empresarial' => 'Empresarial', 'viagem' => 'Viagem', 'outro' => 'Outro',
    ][$t] ?? ucfirst($t);
}

/**
 * Agenda e Alertas (Módulo 14) — varre todas as datas de vencimento espalhadas
 * pelos módulos e devolve uma lista unificada de compromissos.
 *
 * @param int|null $cliente_id  filtra por cliente; null = todos os clientes ativos
 * @return array  linhas com: cliente_id, cliente_nome, categoria, titulo, bem,
 *                data (Y-m-d), link — ordenadas por data crescente
 */
function alertas_consolidado(?int $cliente_id = null): array {
    // `veiculos` está em utf8mb4_general_ci e as demais tabelas em _unicode_ci; sem
    // forçar uma collation comum, o UNION dá "Illegal mix of collations". Todas as
    // colunas de texto saem em _unicode_ci via COLLATE.
    $C = ' COLLATE utf8mb4_unicode_ci ';
    $sql = "
    SELECT a.* FROM (
        -- IPTU em aberto
        SELECT im.cliente_id, 'iptu'$C AS categoria,
               CONCAT('IPTU ', COALESCE(ip.ano, ''))$C AS titulo,
               im.nome_referencia$C AS bem, ip.data_vencimento_1 AS data,
               CONCAT('imoveis/ficha?id=', im.id)$C AS link
          FROM iptu ip
          JOIN imoveis im ON im.id = ip.imovel_id AND im.ativo = 1
         WHERE ip.pago = 0 AND ip.data_vencimento_1 IS NOT NULL

        UNION ALL
        -- Licenciamento de veículo
        SELECT v.cliente_id, 'licenciamento'$C, 'Licenciamento do veículo'$C,
               TRIM(CONCAT_WS(' ', v.marca, v.modelo, NULLIF(CONCAT('· ', v.placa), '· ')))$C,
               v.vencimento_licenciamento, CONCAT('veiculos/editar?id=', v.id)$C
          FROM veiculos v
         WHERE v.ativo = 1 AND v.vencimento_licenciamento IS NOT NULL

        UNION ALL
        -- Seguro de veículo
        SELECT v.cliente_id, 'seguro'$C, 'Seguro do veículo'$C,
               TRIM(CONCAT_WS(' ', v.marca, v.modelo))$C,
               v.vencimento_seguro, CONCAT('veiculos/editar?id=', v.id)$C
          FROM veiculos v
         WHERE v.ativo = 1 AND v.vencimento_seguro IS NOT NULL

        UNION ALL
        -- Seguro de outro bem (embarcação/joia/obra)
        SELECT o.cliente_id, 'seguro'$C, 'Seguro do bem'$C,
               COALESCE(NULLIF(o.nome, ''), o.tipo)$C,
               o.vencimento_seguro, CONCAT('outros/editar?id=', o.id)$C
          FROM outros_bens o
         WHERE o.ativo = 1 AND o.vencimento_seguro IS NOT NULL

        UNION ALL
        -- Fim de contrato de locação (ativos)
        SELECT im.cliente_id, 'contrato'$C, 'Fim do contrato de locação'$C,
               CONCAT_WS(' · ', im.nome_referencia, cl.locatario_nome)$C,
               cl.data_fim, CONCAT('imoveis/ficha?id=', im.id)$C
          FROM contratos_locacao cl
          JOIN imoveis im ON im.id = cl.imovel_id AND im.ativo = 1
         WHERE cl.status = 'ativo' AND cl.data_fim IS NOT NULL

        UNION ALL
        -- Próxima revisão de veículo (só futuras)
        SELECT v.cliente_id, 'revisao'$C, 'Revisão prevista do veículo'$C,
               TRIM(CONCAT_WS(' ', v.marca, v.modelo))$C,
               vm.proxima_data, CONCAT('veiculos/editar?id=', v.id)$C
          FROM veiculo_manutencoes vm
          JOIN veiculos v ON v.id = vm.veiculo_id AND v.ativo = 1
         WHERE vm.proxima_data IS NOT NULL AND vm.proxima_data >= CURDATE()

        UNION ALL
        -- Próxima manutenção de embarcação (só futuras)
        SELECT o.cliente_id, 'revisao'$C, 'Manutenção prevista do bem'$C,
               COALESCE(NULLIF(o.nome, ''), o.tipo)$C,
               bm.proxima_data, CONCAT('outros/editar?id=', o.id)$C
          FROM bem_manutencoes bm
          JOIN outros_bens o ON o.id = bm.outro_bem_id AND o.ativo = 1
         WHERE bm.proxima_data IS NOT NULL AND bm.proxima_data >= CURDATE()

        UNION ALL
        -- Apólices de seguro (vigência)
        SELECT s.cliente_id, 'seguro'$C, CONCAT('Vigência do seguro · ', s.tipo)$C,
               COALESCE(NULLIF(s.seguradora, ''), CONCAT('Apólice ', COALESCE(s.numero_apolice, s.codigo)))$C,
               s.vigencia_fim, CONCAT('seguros/editar?id=', s.id)$C
          FROM seguros s
         WHERE s.ativo = 1 AND s.status = 'vigente' AND s.vigencia_fim IS NOT NULL

        UNION ALL
        -- Documentos com validade
        SELECT d.cliente_id, 'documento'$C, CONCAT('Documento: ', d.categoria)$C,
               d.nome_arquivo$C, d.data_validade, ''$C
          FROM documentos d
         WHERE d.data_validade IS NOT NULL
    ) AS a
    JOIN clientes c ON c.id = a.cliente_id AND c.ativo = 1
    ";

    $bind = [];
    if ($cliente_id !== null) { $sql .= ' WHERE a.cliente_id = :cid'; $bind[':cid'] = $cliente_id; }
    $sql .= ' ORDER BY a.data ASC';

    $stmt = db()->prepare($sql);
    $stmt->execute($bind);
    $linhas = $stmt->fetchAll();

    // Anexa o nome do cliente (uma consulta leve, evita repetir o JOIN 8x no SELECT).
    if ($linhas) {
        $nomes = db()->query('SELECT id, nome FROM clientes')->fetchAll(PDO::FETCH_KEY_PAIR);
        foreach ($linhas as &$l) { $l['cliente_nome'] = $nomes[$l['cliente_id']] ?? '—'; }
        unset($l);
    }
    return $linhas;
}

/**
 * Dias entre hoje e uma data (Y-m-d). Negativo = já passou; 0 = hoje.
 */
function dias_ate(string $data): int {
    $hoje = new DateTimeImmutable('today');
    $alvo = new DateTimeImmutable($data);
    return (int) $hoje->diff($alvo)->format('%r%a');
}

/**
 * Classe/rótulo do alerta conforme a proximidade (em dias).
 * @return array ['classe', 'cor', 'rotulo']
 */
function alerta_status(int $dias): array {
    if ($dias < 0)   return ['vencido',  '#b91c1c', 'Vencido há ' . abs($dias) . ' dia' . (abs($dias) == 1 ? '' : 's')];
    if ($dias === 0) return ['hoje',     '#c2410c', 'Vence hoje'];
    if ($dias <= 7)  return ['urgente',  '#c2410c', 'Em ' . $dias . ' dia' . ($dias == 1 ? '' : 's')];
    if ($dias <= 30) return ['proximo',  '#b45309', 'Em ' . $dias . ' dias'];
    return ['futuro', '#3fae7a', 'Em ' . $dias . ' dias'];
}

/**
 * Resumo de alertas para badges: quantos vencidos e quantos urgentes (≤ $dias).
 * @return array ['vencidos', 'proximos', 'urgentes' (soma), 'total']
 */
function alertas_resumo(?int $cliente_id = null, int $dias = 30): array {
    $vencidos = $proximos = 0;
    $lista = alertas_consolidado($cliente_id);
    foreach ($lista as $a) {
        $d = dias_ate($a['data']);
        if ($d < 0) $vencidos++;
        elseif ($d <= $dias) $proximos++;
    }
    return [
        'vencidos' => $vencidos,
        'proximos' => $proximos,
        'urgentes' => $vencidos + $proximos,
        'total'    => count($lista),
    ];
}

/**
 * Salva uploads de campos de arquivo único na tabela `documentos`.
 * @param array  $campos    ['nome_campo' => 'categoria', ...]
 * @param string $tipo_ref  tipo_referencia (ex.: 'condominio')
 * @param int    $ref_id    id da entidade
 * @param int    $cliente_id cliente para organização/posse do arquivo
 * @param string $subpasta  subpasta dentro de /uploads (ex.: 'condominios/condominio_5')
 */
function salvar_upload_documentos(array $campos, string $tipo_ref, int $ref_id, int $cliente_id, string $subpasta): void {
    $allowed  = ['pdf','jpg','jpeg','png','webp'];
    $dir_base = dirname(__DIR__) . '/uploads/' . $subpasta . '/';
    if (!is_dir($dir_base)) mkdir($dir_base, 0755, true);

    foreach ($campos as $field => $categoria) {
        $f = $_FILES[$field] ?? null;
        if (!$f || empty($f['name']) || empty($f['tmp_name'])) continue;
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) continue;
        if ($f['size'] > 30 * 1024 * 1024) continue;

        $nome_salvo = $field . '_' . time() . '.' . $ext;
        if (move_uploaded_file($f['tmp_name'], $dir_base . $nome_salvo)) {
            $rel = 'uploads/' . $subpasta . '/' . $nome_salvo;
            db()->prepare('INSERT INTO documentos (cliente_id, tipo_referencia, referencia_id, categoria, nome_arquivo, caminho, mime_type, tamanho_bytes) VALUES (?,?,?,?,?,?,?,?)')
               ->execute([$cliente_id, $tipo_ref, $ref_id, $categoria, $f['name'], $rel, $f['type'], $f['size']]);
        }
    }
}

/** Comodidades do condomínio: coluna => [rótulo, ícone bootstrap]. */
function condominio_comodidades(): array {
    return [
        'tem_piscina'       => ['Piscina',          'bi-water'],
        'tem_churrasqueira' => ['Churrasqueira',    'bi-fire'],
        'tem_salao_festas'  => ['Salão de festas',  'bi-balloon'],
        'tem_academia'      => ['Academia',         'bi-bicycle'],
        'tem_playground'    => ['Playground',       'bi-dribbble'],
        'tem_quadra'        => ['Quadra esportiva', 'bi-trophy'],
        'tem_portaria_24h'  => ['Portaria 24h',     'bi-shield-lock'],
        'tem_elevador'      => ['Elevador',         'bi-arrow-down-up'],
        'tem_gerador'       => ['Gerador',          'bi-lightning-charge'],
        'tem_salao_jogos'   => ['Salão de jogos',   'bi-controller'],
        'tem_coworking'     => ['Coworking',        'bi-laptop'],
        'tem_pet'           => ['Pet place',        'bi-heart'],
    ];
}

function combustivel_label(string $c): string {
    $labels = [
        'gasolina'=>'Gasolina', 'etanol'=>'Etanol', 'flex'=>'Flex',
        'diesel'=>'Diesel', 'gnv'=>'GNV', 'eletrico'=>'Elétrico', 'hibrido'=>'Híbrido',
    ];
    return $labels[$c] ?? $c;
}

function tipo_imovel_label(string $tipo): string {
    $labels = [
        'apartamento'   => 'Apartamento',
        'casa'          => 'Casa',
        'sala_comercial'=> 'Sala Comercial',
        'terreno'       => 'Terreno',
        'galpao'        => 'Galpão',
        'loja'          => 'Loja',
        'hotel'         => 'Hotel',
        'outro'         => 'Outro',
    ];
    return $labels[$tipo] ?? $tipo;
}

function situacao_label(string $situacao): string {
    $labels = [
        'pronto'          => 'Pronto',
        'em_construcao'   => 'Em Construção',
        'na_planta'       => 'Na Planta',
    ];
    return $labels[$situacao] ?? $situacao;
}

function finalidade_label(string $finalidade): string {
    $labels = [
        'residencial'  => 'Residencial',
        'comercial'    => 'Comercial',
        'mista'        => 'Mista',
        'locacao'      => 'Locação',
        'investimento' => 'Investimento',
    ];
    return $labels[$finalidade] ?? $finalidade;
}

function forma_aquisicao_label(string $forma): string {
    $labels = [
        'compra'          => 'Compra',
        'heranca'         => 'Herança',
        'doacao'          => 'Doação',
        'permuta'         => 'Permuta',
        'integralizacao'  => 'Integralização',
        'outro'           => 'Outro',
    ];
    return $labels[$forma] ?? $forma;
}

function custo_mensal_total(array $imovel): float {
    return (float)($imovel['custo_condominio']  ?? 0)
         + (float)($imovel['custo_iptu_mensal'] ?? 0)
         + (float)($imovel['custo_energia']     ?? 0)
         + (float)($imovel['custo_agua']        ?? 0)
         + (float)($imovel['custo_internet']    ?? 0)
         + (float)($imovel['custo_outros']      ?? 0);
}

function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function gerar_links_localizacao(array $im): array {
    $partes = array_filter([
        trim($im['logradouro'] ?? ''),
        trim($im['numero'] ?? ''),
        trim($im['complemento'] ?? ''),
        trim($im['bairro'] ?? ''),
        trim($im['cidade'] ?? ''),
        trim($im['estado'] ?? ''),
        'Brasil',
    ]);
    if (count($partes) < 3) return ['maps' => null, 'street_view' => null];

    $query = implode(', ', $partes);
    $q     = urlencode($query);

    return [
        'maps'        => "https://www.google.com/maps/search/?api=1&query={$q}",
        'street_view' => "https://www.google.com/maps?q={$q}&layer=c",
    ];
}

function redirect(string $path): never {
    header('Location: ' . base_url($path));
    exit;
}

/**
 * Retorna os campos do cadastro de imóvel que ficaram vazios (pendências),
 * agrupados por bloco do formulário. Grupos sem pendência são omitidos.
 */
function pendencias_imovel(array $im): array {
    // Mapa: bloco => [coluna => rótulo]
    $grupos_def = [
        '1. Identificação' => [
            'inscricao_municipal' => 'Nº Inscrição Municipal / IPTU',
        ],
        '2. Localização' => [
            'cep' => 'CEP', 'logradouro' => 'Logradouro', 'numero' => 'Número',
            'complemento' => 'Complemento', 'bairro' => 'Bairro', 'cidade' => 'Cidade', 'estado' => 'Estado',
        ],
        '3. Dados da Matrícula' => [
            'numero_matricula' => 'Número da matrícula', 'data_matricula' => 'Data da matrícula',
            'comarca' => 'Comarca', 'cartorio' => 'Cartório de Registro de Imóveis',
            'site_cartorio' => 'Site do cartório', 'livro' => 'Livro', 'folha' => 'Folha',
        ],
        '4. Titularidade' => [
            'data_aquisicao' => 'Data de aquisição', 'forma_aquisicao' => 'Forma de aquisição',
            'percentual_participacao' => '% de participação',
        ],
        '5. Características Físicas' => [
            'area_privativa' => 'Área privativa', 'area_total' => 'Área total',
            'area_construida' => 'Área construída', 'area_comum' => 'Área comum', 'area_terreno' => 'Área do terreno',
            'quartos' => 'Quartos', 'suites' => 'Suítes', 'banheiros' => 'Banheiros',
            'lavabos' => 'Lavabos', 'vagas_garagem' => 'Vagas de garagem',
            'andar' => 'Andar', 'numero_unidade' => 'Nº da unidade',
            'face_solar' => 'Face solar', 'posicao_imovel' => 'Posição do imóvel', 'vista' => 'Vista',
        ],
        '6. Dados Financeiros' => [
            'valor_compra' => 'Valor de compra', 'valor_entrada' => 'Valor de entrada',
            'valor_financiamento' => 'Valor financiado', 'banco_financiador' => 'Banco financiador',
            'prazo_financiamento' => 'Prazo do financiamento', 'taxa_juros_anual' => 'Taxa de juros',
            'valor_mercado' => 'Valor de mercado', 'data_avaliacao_mercado' => 'Data da avaliação',
            'valor_contabil' => 'Valor contábil', 'empresa_avaliadora' => 'Empresa avaliadora',
            'valor_m2' => 'Valor do m²',
            'custo_condominio' => 'Custo condomínio', 'custo_iptu_mensal' => 'Custo IPTU mensal',
            'custo_seguro' => 'Custo seguro', 'custo_energia' => 'Custo energia',
            'custo_agua' => 'Custo água', 'custo_internet' => 'Custo internet', 'custo_outros' => 'Outros custos',
        ],
    ];

    $vazio = fn($v) => $v === null || trim((string)$v) === '';

    $pend = [];
    foreach ($grupos_def as $grupo => $campos) {
        foreach ($campos as $col => $rotulo) {
            if (!array_key_exists($col, $im)) continue;
            if ($vazio($im[$col])) $pend[$grupo][] = $rotulo;
        }
    }

    // Coproprietários: só é pendência quando participação < 100%
    $perc = $im['percentual_participacao'] ?? null;
    if ($perc !== null && trim((string)$perc) !== '' && (float)$perc < 100 && $vazio($im['outros_proprietarios'] ?? null)) {
        $pend['4. Titularidade'][] = 'Outro(s) proprietário(s)';
    }

    return $pend;
}

/** Conta o total de campos pendentes. */
function pendencias_total(array $pend): int {
    return array_sum(array_map('count', $pend));
}

/** Versão em texto plano das pendências (para mensagem de WhatsApp). */
function pendencias_texto(array $pend, string $titulo, string $subtitulo = ''): string {
    $linhas = [];
    $linhas[] = '*Relatório de pendências — ' . $titulo . '*';
    if ($subtitulo !== '') $linhas[] = $subtitulo;
    $linhas[] = '';
    if (!$pend) {
        $linhas[] = '✅ Cadastro completo, nenhuma pendência.';
    } else {
        $linhas[] = '⚠️ ' . pendencias_total($pend) . ' campo(s) por preencher:';
        $linhas[] = '';
        foreach ($pend as $grupo => $campos) {
            $linhas[] = $grupo . ':';
            foreach ($campos as $c) $linhas[] = '• ' . $c;
            $linhas[] = '';
        }
    }
    return trim(implode("\n", $linhas));
}

/**
 * Campos do cadastro de veículo que ficaram vazios (pendências), agrupados por bloco.
 */
function pendencias_veiculo(array $ve): array {
    $grupos_def = [
        '1. Identificação' => [
            'marca' => 'Marca', 'modelo' => 'Modelo', 'ano_fabricacao' => 'Ano de fabricação',
            'ano_modelo' => 'Ano do modelo', 'cor' => 'Cor', 'combustivel' => 'Combustível',
            'placa' => 'Placa', 'renavam' => 'Renavam', 'chassi' => 'Chassi',
        ],
        '2. Documentação' => [
            'vencimento_licenciamento' => 'Vencimento do licenciamento', 'multas' => 'Multas',
        ],
        '3. Seguro' => [
            'seguradora' => 'Seguradora', 'apolice' => 'Apólice',
            'franquia' => 'Franquia', 'vencimento_seguro' => 'Vencimento do seguro',
        ],
        '4. Financeiro' => [
            'data_aquisicao' => 'Data de aquisição', 'valor_aquisicao' => 'Valor de aquisição',
            'valor_fipe' => 'Valor FIPE', 'valor_mercado' => 'Valor de mercado',
        ],
        '5. Controle' => [
            'km_atual' => 'KM atual', 'consumo_medio' => 'Consumo médio (km/l)', 'observacoes' => 'Observações',
        ],
    ];

    $vazio = fn($v) => $v === null || trim((string)$v) === '';

    $pend = [];
    foreach ($grupos_def as $grupo => $campos) {
        foreach ($campos as $col => $rotulo) {
            if (!array_key_exists($col, $ve)) continue;
            if ($vazio($ve[$col])) $pend[$grupo][] = $rotulo;
        }
    }
    return $pend;
}

function pendencias_outro_bem(array $ob): array {
    $tipo = $ob['tipo'] ?? 'outro';

    $geral = [
        '1. Identificação' => [
            'nome'  => 'Nome / Descrição',
            'marca' => 'Marca / Fabricante',
            'modelo'=> 'Modelo / Referência',
            'ano'   => 'Ano',
            'cor'   => 'Cor / Acabamento',
        ],
    ];

    $especifico = [];
    if ($tipo === 'embarcacao') {
        $especifico['2. Embarcação'] = [
            'tipo_embarcacao'     => 'Tipo de embarcação',
            'comprimento_m'       => 'Comprimento (m)',
            'motor'               => 'Motor',
            'horimetro'           => 'Horímetro',
            'registro_embarcacao' => 'Registro (Capitania)',
            'marina'              => 'Marina',
            'vaga_marina'         => 'Vaga / Box',
            'mensalidade_marina'  => 'Mensalidade da marina',
        ];
    } elseif ($tipo === 'joia') {
        $especifico['2. Joia'] = [
            'material'    => 'Material',
            'quilates'    => 'Quilates / Peso',
            'certificado' => 'Certificado de autenticidade',
        ];
    } elseif ($tipo === 'obra_de_arte') {
        $especifico['2. Obra de Arte'] = [
            'artista_autor' => 'Artista / Autor',
            'tecnica'       => 'Técnica',
            'dimensoes'     => 'Dimensões',
            'material'      => 'Material / Suporte',
            'certificado'   => 'Certificado / Proveniência',
        ];
    }

    $comuns = [
        '3. Seguro' => [
            'seguradora'       => 'Seguradora',
            'apolice'          => 'Apólice',
            'franquia'         => 'Franquia',
            'vencimento_seguro'=> 'Vencimento do seguro',
        ],
        '4. Financeiro' => [
            'data_aquisicao'  => 'Data de aquisição',
            'valor_aquisicao' => 'Valor de aquisição',
            'valor_mercado'   => 'Valor de mercado',
        ],
    ];

    $grupos_def = array_merge($geral, $especifico, $comuns);
    $vazio = fn($v) => $v === null || trim((string)$v) === '';

    $pend = [];
    foreach ($grupos_def as $grupo => $campos) {
        foreach ($campos as $col => $rotulo) {
            if (!array_key_exists($col, $ob)) continue;
            if ($vazio($ob[$col])) $pend[$grupo][] = $rotulo;
        }
    }
    return $pend;
}
