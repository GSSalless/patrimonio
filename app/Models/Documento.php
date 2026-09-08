<?php
/**
 * Model de documento — tabela `documentos` (polimórfica).
 *
 * Além do INSERT usado pelos módulos, concentra a leitura do
 * **repositório central** (Módulo 13): lista todos os documentos de um
 * cliente (ou de todos, para o admin), resolve a que entidade cada arquivo
 * está vinculado e aplica os filtros da tela.
 */
class Documento
{
    /**
     * Mapa de tipo_referencia → como exibir/rotear o vínculo.
     * [rótulo, tabela, expressão SQL do nome, rota de edição (ou '')].
     */
    private const MAPA = [
        'imovel'           => ['Imóvel',        'imoveis',            "COALESCE(NULLIF(nome_referencia,''), codigo)",           'imoveis/ficha?id='],
        'veiculo'          => ['Veículo',       'veiculos',           "COALESCE(NULLIF(CONCAT_WS(' ',marca,modelo),''), codigo)", 'veiculos/editar?id='],
        'outro_bem'        => ['Outro bem',     'outros_bens',        "COALESCE(NULLIF(nome,''), tipo, codigo)",                'outros/editar?id='],
        'seguro'           => ['Seguro',        'seguros',            "COALESCE(NULLIF(seguradora,''), codigo)",                'seguros/editar?id='],
        'contrato'         => ['Contrato',      'contratos',          "COALESCE(NULLIF(contraparte_nome,''), NULLIF(numero,''), codigo)", 'contratos/editar?id='],
        'empresa'          => ['Empresa',       'empresas',           "COALESCE(NULLIF(razao_social,''), codigo)",              'empresas/editar?id='],
        'colaborador'      => ['Colaborador',   'colaboradores',      "COALESCE(NULLIF(nome,''), codigo)",                      'colaboradores/editar?id='],
        'fornecedor'       => ['Fornecedor',    'fornecedores',       "COALESCE(NULLIF(nome,''), codigo)",                      'fornecedores/editar?id='],
        'conta_financeira' => ['Conta',         'contas_financeiras', "COALESCE(NULLIF(apelido,''), NULLIF(instituicao,''), codigo)", 'contas/editar?id='],
        'investimento'     => ['Investimento',  'investimentos',      "COALESCE(NULLIF(nome,''), codigo)",                      'investimentos/editar?id='],
        'condominio'       => ['Condomínio',    'condominios',        "nome",                                                   ''],
        'cliente'          => ['Pessoa',        'clientes',           "nome",                                                   'clientes/editar?id='],
        'pessoa'           => ['Pessoa',        'clientes',           "nome",                                                   'clientes/editar?id='],
    ];

    /** tipo_referencia → rótulo amigável (para o filtro e as etiquetas). */
    public static function tiposLabel(): array
    {
        $out = [];
        foreach (self::MAPA as $tipo => $def) $out[$tipo] = $def[0];
        return $out;
    }

    /** categoria → rótulo amigável (ordem de exibição no filtro). */
    public static function categorias(): array
    {
        return [
            'escritura'              => 'Escritura',
            'matricula'              => 'Matrícula',
            'iptu'                   => 'IPTU',
            'contrato_compra'        => 'Contrato de compra',
            'habite_se'              => 'Habite-se',
            'laudo'                  => 'Laudo',
            'foto'                   => 'Foto',
            'boleto'                 => 'Boleto',
            'nf'                     => 'Nota fiscal',
            'crlv'                   => 'CRLV',
            'apolice'                => 'Apólice',
            'manutencao'             => 'Manutenção',
            'cnpj'                   => 'Cartão CNPJ',
            'contrato'               => 'Contrato',
            'conta_financeira'       => 'Conta financeira',
            'extrato'                => 'Extrato',
            'testamento'             => 'Testamento',
            'identidade'             => 'Identidade',
            'comprovante_residencia' => 'Comprovante de residência',
            'certidao'               => 'Certidão',
            'procuracao'             => 'Procuração',
            'outro'                  => 'Outro',
        ];
    }

    /**
     * Lista documentos do repositório central com filtros.
     * @param int|null $cliente_id  null = todos os clientes (admin sem seleção)
     * @param array    $f           ['categoria','tipo','q','validade'=>vencidos|vigentes|30d]
     * @return array   linhas de `documentos` + cliente_nome
     */
    public static function listar(?int $cliente_id, array $f = []): array
    {
        $sql  = 'SELECT d.*, c.nome AS cliente_nome
                   FROM documentos d
                   JOIN clientes c ON c.id = d.cliente_id AND c.ativo = 1
                  WHERE 1=1';
        $bind = [];

        if ($cliente_id !== null) { $sql .= ' AND d.cliente_id = :cid'; $bind[':cid'] = $cliente_id; }

        if (!empty($f['categoria'])) { $sql .= ' AND d.categoria = :cat'; $bind[':cat'] = $f['categoria']; }
        if (!empty($f['tipo']))      { $sql .= ' AND d.tipo_referencia = :tipo'; $bind[':tipo'] = $f['tipo']; }
        if (!empty($f['q'])) {
            // Placeholder repetido não funciona com prepares nativos → usa dois.
            $sql .= ' AND (d.nome_arquivo LIKE :q1 OR d.descricao LIKE :q2)';
            $bind[':q1'] = '%' . $f['q'] . '%';
            $bind[':q2'] = '%' . $f['q'] . '%';
        }
        switch ($f['validade'] ?? '') {
            case 'vencidos':  $sql .= ' AND d.data_validade IS NOT NULL AND d.data_validade < CURDATE()'; break;
            case 'vigentes':  $sql .= ' AND d.data_validade IS NOT NULL AND d.data_validade >= CURDATE()'; break;
            case '30d':       $sql .= ' AND d.data_validade IS NOT NULL AND d.data_validade BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)'; break;
        }

        $sql .= ' ORDER BY d.criado_em DESC, d.id DESC';

        $stmt = db()->prepare($sql);
        $stmt->execute($bind);
        return $stmt->fetchAll();
    }

    /**
     * Resolve o nome/rota de cada vínculo em lote (evita N+1).
     * @param array $docs linhas de listar()
     * @return array mapa "tipo:id" => ['label','nome','link']
     */
    public static function resolverVinculos(array $docs): array
    {
        // Agrupa os ids por tipo.
        $porTipo = [];
        foreach ($docs as $d) {
            $tipo = $d['tipo_referencia'];
            $rid  = (int) $d['referencia_id'];
            if ($rid > 0 && isset(self::MAPA[$tipo])) $porTipo[$tipo][$rid] = true;
        }

        $mapa = [];
        foreach ($porTipo as $tipo => $ids) {
            [$label, $tabela, $exprNome, $rota] = self::MAPA[$tipo];
            $idlist = array_keys($ids);
            $ph = implode(',', array_fill(0, count($idlist), '?'));
            try {
                $stmt = db()->prepare("SELECT id, $exprNome AS nome FROM $tabela WHERE id IN ($ph)");
                $stmt->execute($idlist);
                foreach ($stmt->fetchAll() as $r) {
                    $mapa["$tipo:{$r['id']}"] = [
                        'label' => $label,
                        'nome'  => $r['nome'] ?: ($label . ' #' . $r['id']),
                        'link'  => $rota !== '' ? $rota . (int) $r['id'] : '',
                    ];
                }
            } catch (\Throwable $e) {
                // tabela/coluna ausente: cai no rótulo genérico abaixo.
            }
        }
        return $mapa;
    }

    /** Uma linha do documento (sem escopo — o controller autoriza). */
    public static function buscar(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM documentos WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Remove o registro. O arquivo em /uploads é apagado no controller. */
    public static function excluir(int $id): void
    {
        db()->prepare('DELETE FROM documentos WHERE id = ?')->execute([$id]);
    }

    /** Total de documentos do cliente (badge do app no dashboard). */
    public static function contar(?int $cliente_id): int
    {
        if ($cliente_id === null) {
            return (int) db()->query('SELECT COUNT(*) FROM documentos')->fetchColumn();
        }
        $stmt = db()->prepare('SELECT COUNT(*) FROM documentos WHERE cliente_id = ?');
        $stmt->execute([$cliente_id]);
        return (int) $stmt->fetchColumn();
    }

    public static function criar(array $c): void
    {
        db()->prepare('INSERT INTO documentos (cliente_id, tipo_referencia, referencia_id, categoria, nome_arquivo, caminho, mime_type, tamanho_bytes, data_emissao, data_validade, descricao) VALUES (?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([
                $c['cliente_id'], $c['tipo_referencia'], $c['referencia_id'], $c['categoria'],
                $c['nome_arquivo'], $c['caminho'], $c['mime_type'], $c['tamanho_bytes'],
                $c['data_emissao'], $c['data_validade'], $c['descricao'],
            ]);
    }
}
