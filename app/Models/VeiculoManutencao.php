<?php
/**
 * Model de manutenção de veículo — tabela `veiculo_manutencoes`.
 */
class VeiculoManutencao
{
    public static function buscar(int $id): ?array
    {
        $s = db()->prepare('SELECT * FROM veiculo_manutencoes WHERE id = ?');
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    public static function listar(int $veiculoId): array
    {
        $s = db()->prepare('SELECT * FROM veiculo_manutencoes WHERE veiculo_id = ? ORDER BY data DESC, id DESC');
        $s->execute([$veiculoId]);
        return $s->fetchAll();
    }

    public static function criar(int $veiculoId, array $campos): int
    {
        $cols = array_merge(['veiculo_id'], array_keys($campos));
        $vals = array_merge([$veiculoId], array_values($campos));
        $ph   = implode(',', array_fill(0, count($cols), '?'));
        db()->prepare('INSERT INTO veiculo_manutencoes (' . implode(', ', $cols) . ') VALUES (' . $ph . ')')->execute($vals);
        return (int) db()->lastInsertId();
    }

    public static function atualizar(int $id, array $campos): void
    {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($campos)));
        db()->prepare("UPDATE veiculo_manutencoes SET $set WHERE id = ?")->execute([...array_values($campos), $id]);
    }
}
