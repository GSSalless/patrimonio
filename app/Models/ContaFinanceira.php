<?php
/**
 * Model de contas financeiras (bancárias/digitais) do cliente.
 * Tabela `contas_financeiras`. INSERT/UPDATE por array coluna=>valor.
 */
class ContaFinanceira
{
    public static function listar(int $clienteId, string $tipo = '', string $busca = ''): array
    {
        $sql    = 'SELECT * FROM contas_financeiras WHERE cliente_id = ? AND ativo = 1';
        $params = [$clienteId];
        if ($tipo !== '')  { $sql .= ' AND tipo = ?'; $params[] = $tipo; }
        if ($busca !== '') {
            $sql .= ' AND (apelido LIKE ? OR instituicao LIKE ? OR numero_conta LIKE ?)';
            $params[] = "%$busca%"; $params[] = "%$busca%"; $params[] = "%$busca%";
        }
        $sql .= ' ORDER BY instituicao, apelido';
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public static function buscarDoCliente(int $id, int $clienteId): ?array
    {
        $s = db()->prepare('SELECT * FROM contas_financeiras WHERE id = ? AND cliente_id = ? AND ativo = 1');
        $s->execute([$id, $clienteId]);
        return $s->fetch() ?: null;
    }

    public static function criar(array $campos): int
    {
        $cols = array_keys($campos);
        $ph   = implode(',', array_fill(0, count($cols), '?'));
        $sql  = 'INSERT INTO contas_financeiras (' . implode(', ', $cols) . ') VALUES (' . $ph . ')';
        db()->prepare($sql)->execute(array_values($campos));
        return (int) db()->lastInsertId();
    }

    public static function atualizar(int $id, array $campos): void
    {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($campos)));
        db()->prepare("UPDATE contas_financeiras SET $set WHERE id = ?")
            ->execute([...array_values($campos), $id]);
    }
}
