<?php
/**
 * Model de imóvel — acesso à tabela `imoveis` (e avaliações no cadastro).
 * INSERT/UPDATE são montados a partir de um array associativo coluna=>valor,
 * garantindo que nº de colunas = nº de placeholders (regra do CLAUDE.md).
 */
class Imovel
{
    public static function listar(int $clienteId, array $f = []): array
    {
        $sql    = 'SELECT * FROM imoveis WHERE cliente_id = ? AND ativo = 1';
        $params = [$clienteId];
        if (!empty($f['tipo']))     { $sql .= ' AND tipo = ?';              $params[] = $f['tipo']; }
        if (!empty($f['situacao'])) { $sql .= ' AND situacao = ?';          $params[] = $f['situacao']; }
        if (!empty($f['busca']))    { $sql .= ' AND nome_referencia LIKE ?'; $params[] = '%' . $f['busca'] . '%'; }
        $sql .= ' ORDER BY nome_referencia';

        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public static function buscar(int $id): ?array
    {
        $s = db()->prepare('SELECT * FROM imoveis WHERE id = ? AND ativo = 1');
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    /** Imóvel + nome do cliente (para a ficha). */
    public static function buscarComCliente(int $id): ?array
    {
        $s = db()->prepare(
            'SELECT i.*, c.nome AS cliente_nome
               FROM imoveis i JOIN clientes c ON c.id = i.cliente_id
              WHERE i.id = ? AND i.ativo = 1'
        );
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    public static function buscarDoCliente(int $id, int $clienteId): ?array
    {
        $s = db()->prepare('SELECT * FROM imoveis WHERE id = ? AND cliente_id = ? AND ativo = 1');
        $s->execute([$id, $clienteId]);
        return $s->fetch() ?: null;
    }

    public static function criar(array $campos): int
    {
        $cols = array_keys($campos);
        $ph   = implode(',', array_fill(0, count($cols), '?'));
        $sql  = 'INSERT INTO imoveis (' . implode(', ', $cols) . ') VALUES (' . $ph . ')';
        db()->prepare($sql)->execute(array_values($campos));
        return (int) db()->lastInsertId();
    }

    public static function atualizar(int $id, array $campos): void
    {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($campos)));
        db()->prepare("UPDATE imoveis SET $set WHERE id = ?")
            ->execute([...array_values($campos), $id]);
    }

    public static function registrarAvaliacao(int $imovelId, string $data, float $valor, string $fonte): void
    {
        db()->prepare('INSERT INTO avaliacoes (imovel_id, data, valor, fonte) VALUES (?,?,?,?)')
            ->execute([$imovelId, $data, $valor, $fonte]);
    }
}
