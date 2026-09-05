<?php
/**
 * Model do histórico de saldos de uma conta financeira.
 * Tabela `conta_saldos` — o registro mais recente (por data) espelha
 * saldo_atual/saldo_data em `contas_financeiras`.
 */
class ContaSaldo
{
    public static function listar(int $contaId): array
    {
        $s = db()->prepare('SELECT * FROM conta_saldos WHERE conta_id = ? ORDER BY data DESC, id DESC');
        $s->execute([$contaId]);
        return $s->fetchAll();
    }

    public static function buscar(int $id): ?array
    {
        $s = db()->prepare('SELECT * FROM conta_saldos WHERE id = ?');
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    public static function criar(int $contaId, array $campos): int
    {
        $campos = ['conta_id' => $contaId] + $campos;
        $cols = array_keys($campos);
        $ph   = implode(',', array_fill(0, count($cols), '?'));
        db()->prepare('INSERT INTO conta_saldos (' . implode(', ', $cols) . ') VALUES (' . $ph . ')')
            ->execute(array_values($campos));
        return (int) db()->lastInsertId();
    }

    public static function atualizar(int $id, array $campos): void
    {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($campos)));
        db()->prepare("UPDATE conta_saldos SET $set WHERE id = ?")
            ->execute([...array_values($campos), $id]);
    }
}
