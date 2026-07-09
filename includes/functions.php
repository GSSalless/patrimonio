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
