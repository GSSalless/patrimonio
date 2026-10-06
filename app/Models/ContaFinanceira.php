<?php
/**
 * Model de contas financeiras (bancárias/digitais) do cliente.
 * Tabela `contas_financeiras`. INSERT/UPDATE por array coluna=>valor.
 */
class ContaFinanceira
{
    public static function listar(?int $clienteId, string $tipo = '', string $busca = ''): array
    {
        // $clienteId null = todos os clientes (modo gestor, reunião 01/10/2026).
        $sql    = 'SELECT t.*, c.nome AS cliente_nome FROM contas_financeiras t
                     JOIN clientes c ON c.id = t.cliente_id
                    WHERE t.ativo = 1 AND c.ativo = 1';
        $params = [];
        if ($clienteId !== null) { $sql .= ' AND t.cliente_id = ?'; $params[] = $clienteId; }
        if ($tipo !== '')  { $sql .= ' AND t.tipo = ?'; $params[] = $tipo; }
        if ($busca !== '') {
            $sql .= ' AND (t.apelido LIKE ? OR t.instituicao LIKE ? OR t.numero_conta LIKE ?)';
            $params[] = "%$busca%"; $params[] = "%$busca%"; $params[] = "%$busca%";
        }
        $sql .= ' ORDER BY ' . ($clienteId === null ? 'c.nome, ' : '') . 't.instituicao, t.apelido';
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
