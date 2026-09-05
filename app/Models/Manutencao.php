<?php
/**
 * Model de manutenção de imóvel — tabela `manutencoes`.
 */
class Manutencao
{
    public static function buscar(int $id): ?array
    {
        $s = db()->prepare('SELECT * FROM manutencoes WHERE id = ?');
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    public static function listar(int $imovelId): array
    {
        $s = db()->prepare('SELECT * FROM manutencoes WHERE imovel_id = ? ORDER BY data DESC, id DESC');
        $s->execute([$imovelId]);
        return $s->fetchAll();
    }

    public static function criar(int $imovelId, array $campos): int
    {
        $cols = array_merge(['imovel_id'], array_keys($campos));
        $vals = array_merge([$imovelId], array_values($campos));
        $ph   = implode(',', array_fill(0, count($cols), '?'));
        db()->prepare('INSERT INTO manutencoes (' . implode(', ', $cols) . ') VALUES (' . $ph . ')')->execute($vals);
        return (int) db()->lastInsertId();
    }

    public static function atualizar(int $id, array $campos): void
    {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($campos)));
        db()->prepare("UPDATE manutencoes SET $set WHERE id = ?")->execute([...array_values($campos), $id]);
    }
}
