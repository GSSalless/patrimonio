<?php
/**
 * Model de seguros/apólices do cliente (Módulo 11).
 * Tabela `seguros`. INSERT/UPDATE por array coluna=>valor (mesmo padrão dos demais).
 */
class Seguro
{
    public static function listar(int $clienteId, string $tipo = '', string $status = '', string $busca = ''): array
    {
        $sql    = 'SELECT * FROM seguros WHERE cliente_id = ? AND ativo = 1';
        $params = [$clienteId];
        if ($tipo !== '')   { $sql .= ' AND tipo = ?';   $params[] = $tipo; }
        if ($status !== '') { $sql .= ' AND status = ?'; $params[] = $status; }
        if ($busca !== '') {
            $sql .= ' AND (seguradora LIKE ? OR corretora LIKE ? OR numero_apolice LIKE ?)';
            $params[] = "%$busca%"; $params[] = "%$busca%"; $params[] = "%$busca%";
        }
        // Vigentes primeiro; dentro disso, os que vencem antes no topo.
        $sql .= " ORDER BY FIELD(status,'vigente','em_cotacao','vencida','cancelada'), vigencia_fim IS NULL, vigencia_fim";
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public static function buscarDoCliente(int $id, int $clienteId): ?array
    {
        $s = db()->prepare('SELECT * FROM seguros WHERE id = ? AND cliente_id = ? AND ativo = 1');
        $s->execute([$id, $clienteId]);
        return $s->fetch() ?: null;
    }

    public static function criar(array $campos): int
    {
        $cols = array_keys($campos);
        $ph   = implode(',', array_fill(0, count($cols), '?'));
        $sql  = 'INSERT INTO seguros (' . implode(', ', $cols) . ') VALUES (' . $ph . ')';
        db()->prepare($sql)->execute(array_values($campos));
        return (int) db()->lastInsertId();
    }

    public static function atualizar(int $id, array $campos): void
    {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($campos)));
        db()->prepare("UPDATE seguros SET $set WHERE id = ?")
            ->execute([...array_values($campos), $id]);
    }

    /**
     * Itens seguráveis do cliente (imóveis, veículos, outros bens) para o
     * seletor de vínculo do formulário. Devolve grupos [rotulo => [ [valor,label], ... ]].
     * O `valor` codifica "tipo:id" (ex.: "imovel:5").
     */
    public static function itensSeguraveis(int $clienteId): array
    {
        $grupos = [];

        $s = db()->prepare("SELECT id, codigo, nome_referencia FROM imoveis WHERE cliente_id = ? AND ativo = 1 ORDER BY nome_referencia");
        $s->execute([$clienteId]);
        foreach ($s->fetchAll() as $r) {
            $grupos['Imóveis'][] = ['imovel:' . $r['id'], trim(($r['codigo'] ? $r['codigo'] . ' · ' : '') . ($r['nome_referencia'] ?: 'Imóvel'))];
        }

        $s = db()->prepare("SELECT id, codigo, marca, modelo, placa FROM veiculos WHERE cliente_id = ? AND ativo = 1 ORDER BY marca, modelo");
        $s->execute([$clienteId]);
        foreach ($s->fetchAll() as $r) {
            $nome = trim(($r['marca'] ?? '') . ' ' . ($r['modelo'] ?? '')) ?: 'Veículo';
            if (!empty($r['placa'])) $nome .= ' · ' . $r['placa'];
            $grupos['Veículos'][] = ['veiculo:' . $r['id'], trim(($r['codigo'] ? $r['codigo'] . ' · ' : '') . $nome)];
        }

        $s = db()->prepare("SELECT id, codigo, nome, tipo FROM outros_bens WHERE cliente_id = ? AND ativo = 1 ORDER BY nome");
        $s->execute([$clienteId]);
        foreach ($s->fetchAll() as $r) {
            $nome = $r['nome'] ?: ucfirst(str_replace('_', ' ', $r['tipo']));
            $grupos['Outros bens'][] = ['outro_bem:' . $r['id'], trim(($r['codigo'] ? $r['codigo'] . ' · ' : '') . $nome)];
        }

        return $grupos;
    }

    /**
     * Descrição legível do item vinculado a um seguro (para lista/edição).
     */
    public static function descreverVinculo(string $itemTipo, ?int $itemId): string
    {
        if ($itemTipo === 'nenhum' || !$itemId) return '';
        $mapa = [
            'imovel'    => ['imoveis',    "CONCAT_WS(' · ', codigo, nome_referencia)"],
            'veiculo'   => ['veiculos',   "CONCAT_WS(' · ', codigo, TRIM(CONCAT_WS(' ', marca, modelo)))"],
            'outro_bem' => ['outros_bens', "CONCAT_WS(' · ', codigo, nome)"],
        ];
        if (!isset($mapa[$itemTipo])) return '';
        [$tabela, $expr] = $mapa[$itemTipo];
        $s = db()->prepare("SELECT $expr FROM $tabela WHERE id = ?");
        $s->execute([$itemId]);
        return (string) ($s->fetchColumn() ?: '');
    }
}
