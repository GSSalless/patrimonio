<?php
/**
 * Model de fornecedores/parceiros do cliente (Módulo 04).
 * Tabela `fornecedores`. INSERT/UPDATE por array coluna=>valor.
 */
class Fornecedor
{
    public static function listar(int $clienteId, string $categoria = '', string $busca = ''): array
    {
        $sql    = 'SELECT * FROM fornecedores WHERE cliente_id = ? AND ativo = 1';
        $params = [$clienteId];
        if ($categoria !== '') { $sql .= ' AND categoria = ?'; $params[] = $categoria; }
        if ($busca !== '') {
            $sql .= ' AND (nome LIKE ? OR nome_fantasia LIKE ? OR cpf_cnpj LIKE ? OR contato_nome LIKE ?)';
            $params[] = "%$busca%"; $params[] = "%$busca%"; $params[] = "%$busca%"; $params[] = "%$busca%";
        }
        $sql .= ' ORDER BY categoria, nome';
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public static function buscarDoCliente(int $id, int $clienteId): ?array
    {
        $s = db()->prepare('SELECT * FROM fornecedores WHERE id = ? AND cliente_id = ? AND ativo = 1');
        $s->execute([$id, $clienteId]);
        return $s->fetch() ?: null;
    }

    public static function criar(array $campos): int
    {
        $cols = array_keys($campos);
        $ph   = implode(',', array_fill(0, count($cols), '?'));
        db()->prepare('INSERT INTO fornecedores (' . implode(', ', $cols) . ') VALUES (' . $ph . ')')
            ->execute(array_values($campos));
        return (int) db()->lastInsertId();
    }

    public static function atualizar(int $id, array $campos): void
    {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($campos)));
        db()->prepare("UPDATE fornecedores SET $set WHERE id = ?")
            ->execute([...array_values($campos), $id]);
    }
}
