<?php
/**
 * Model de avaliação de "outro bem" (histórico de valor) — tabela `avaliacoes_bem`.
 */
class AvaliacaoBem
{
    public static function buscar(int $id): ?array
    {
        $s = db()->prepare('SELECT * FROM avaliacoes_bem WHERE id = ?');
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    public static function listar(int $bemId): array
    {
        $s = db()->prepare('SELECT * FROM avaliacoes_bem WHERE outro_bem_id = ? ORDER BY data DESC, id DESC');
        $s->execute([$bemId]);
        return $s->fetchAll();
    }

    public static function criar(int $bemId, array $campos): int
    {
        $cols = array_merge(['outro_bem_id'], array_keys($campos));
        $vals = array_merge([$bemId], array_values($campos));
        $ph   = implode(',', array_fill(0, count($cols), '?'));
        db()->prepare('INSERT INTO avaliacoes_bem (' . implode(', ', $cols) . ') VALUES (' . $ph . ')')->execute($vals);
        return (int) db()->lastInsertId();
    }

    public static function atualizar(int $id, array $campos): void
    {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($campos)));
        db()->prepare("UPDATE avaliacoes_bem SET $set WHERE id = ?")->execute([...array_values($campos), $id]);
    }
}
