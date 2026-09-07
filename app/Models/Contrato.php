<?php
/**
 * Model de Contratos (Módulo 12). Cadastro central de contratos do cliente,
 * com vínculo polimórfico opcional (imóvel, veículo, outro bem, fornecedor,
 * colaborador, empresa). INSERT/UPDATE por array coluna=>valor.
 */
class Contrato
{
    public static function listar(int $clienteId, string $tipo = '', string $status = '', string $busca = ''): array
    {
        $sql    = 'SELECT * FROM contratos WHERE cliente_id = ? AND ativo = 1';
        $params = [$clienteId];
        if ($tipo !== '')   { $sql .= ' AND tipo = ?';   $params[] = $tipo; }
        if ($status !== '') { $sql .= ' AND status = ?'; $params[] = $status; }
        if ($busca !== '') {
            $sql .= ' AND (numero LIKE ? OR objeto LIKE ? OR contraparte_nome LIKE ?)';
            $params[] = "%$busca%"; $params[] = "%$busca%"; $params[] = "%$busca%";
        }
        // Ativos primeiro; dentro disso, os que vencem antes no topo.
        $sql .= " ORDER BY FIELD(status,'ativo','em_negociacao','suspenso','encerrado','rescindido'), data_fim IS NULL, data_fim";
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public static function buscarDoCliente(int $id, int $clienteId): ?array
    {
        $s = db()->prepare('SELECT * FROM contratos WHERE id = ? AND cliente_id = ? AND ativo = 1');
        $s->execute([$id, $clienteId]);
        return $s->fetch() ?: null;
    }

    public static function criar(array $campos): int
    {
        $cols = array_keys($campos);
        $ph   = implode(',', array_fill(0, count($cols), '?'));
        $sql  = 'INSERT INTO contratos (' . implode(', ', $cols) . ') VALUES (' . $ph . ')';
        db()->prepare($sql)->execute(array_values($campos));
        return (int) db()->lastInsertId();
    }

    public static function atualizar(int $id, array $campos): void
    {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($campos)));
        db()->prepare("UPDATE contratos SET $set WHERE id = ?")
            ->execute([...array_values($campos), $id]);
    }

    /**
     * Itens vinculáveis do cliente para o seletor do formulário.
     * Grupos [rótulo => [ [valor "tipo:id", label], ... ]].
     */
    public static function itensVinculaveis(int $clienteId): array
    {
        $grupos = [];

        $add = function (string $chave, string $rotulo, string $sql, callable $fmt) use (&$grupos, $clienteId) {
            $s = db()->prepare($sql);
            $s->execute([$clienteId]);
            foreach ($s->fetchAll() as $r) {
                $grupos[$rotulo][] = [$chave . ':' . $r['id'], $fmt($r)];
            }
        };

        $cod = fn($r, $nome) => trim(($r['codigo'] ?? '' ? $r['codigo'] . ' · ' : '') . $nome);

        $add('imovel', 'Imóveis',
            "SELECT id, codigo, nome_referencia FROM imoveis WHERE cliente_id = ? AND ativo = 1 ORDER BY nome_referencia",
            fn($r) => $cod($r, $r['nome_referencia'] ?: 'Imóvel'));

        $add('veiculo', 'Veículos',
            "SELECT id, codigo, marca, modelo, placa FROM veiculos WHERE cliente_id = ? AND ativo = 1 ORDER BY marca, modelo",
            fn($r) => $cod($r, trim(($r['marca'] ?? '') . ' ' . ($r['modelo'] ?? '')) . ($r['placa'] ? ' · ' . $r['placa'] : '')) ?: 'Veículo');

        $add('outro_bem', 'Outros bens',
            "SELECT id, codigo, nome, tipo FROM outros_bens WHERE cliente_id = ? AND ativo = 1 ORDER BY nome",
            fn($r) => $cod($r, $r['nome'] ?: ucfirst(str_replace('_', ' ', $r['tipo']))));

        $add('fornecedor', 'Fornecedores',
            "SELECT id, codigo, nome FROM fornecedores WHERE cliente_id = ? AND ativo = 1 ORDER BY nome",
            fn($r) => $cod($r, $r['nome'] ?: 'Fornecedor'));

        $add('colaborador', 'Colaboradores',
            "SELECT id, codigo, nome FROM colaboradores WHERE cliente_id = ? AND ativo = 1 ORDER BY nome",
            fn($r) => $cod($r, $r['nome'] ?: 'Colaborador'));

        $add('empresa', 'Empresas',
            "SELECT id, codigo, razao_social, nome_fantasia FROM empresas WHERE cliente_id = ? AND ativo = 1 ORDER BY razao_social",
            fn($r) => $cod($r, $r['nome_fantasia'] ?: $r['razao_social'] ?: 'Empresa'));

        return $grupos;
    }

    /** Descrição legível do item vinculado (para lista/edição). */
    public static function descreverVinculo(?string $tipo, ?int $id): string
    {
        if (!$tipo || $tipo === 'nenhum' || !$id) return '';
        $mapa = [
            'imovel'      => ['imoveis',     "CONCAT_WS(' · ', codigo, nome_referencia)"],
            'veiculo'     => ['veiculos',    "CONCAT_WS(' · ', codigo, TRIM(CONCAT_WS(' ', marca, modelo)))"],
            'outro_bem'   => ['outros_bens', "CONCAT_WS(' · ', codigo, nome)"],
            'fornecedor'  => ['fornecedores',"CONCAT_WS(' · ', codigo, nome)"],
            'colaborador' => ['colaboradores',"CONCAT_WS(' · ', codigo, nome)"],
            'empresa'     => ['empresas',    "CONCAT_WS(' · ', codigo, COALESCE(NULLIF(nome_fantasia,''), razao_social))"],
        ];
        if (!isset($mapa[$tipo])) return '';
        [$tabela, $expr] = $mapa[$tipo];
        $s = db()->prepare("SELECT $expr FROM $tabela WHERE id = ?");
        $s->execute([$id]);
        return (string) ($s->fetchColumn() ?: '');
    }
}
