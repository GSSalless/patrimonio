<?php
/**
 * Model da carteira de investimentos (Módulo 10) + histórico de movimentos.
 * Tabelas `investimentos` e `investimento_movimentos`.
 */
class Investimento
{
    public static function listar(int $clienteId, string $classe = '', string $status = '', string $busca = ''): array
    {
        $sql    = 'SELECT * FROM investimentos WHERE cliente_id = ? AND ativo = 1';
        $params = [$clienteId];
        if ($classe !== '') { $sql .= ' AND classe = ?'; $params[] = $classe; }
        if ($status !== '') { $sql .= ' AND status = ?'; $params[] = $status; }
        if ($busca !== '') {
            $sql .= ' AND (nome LIKE ? OR instituicao LIKE ? OR emissor LIKE ?)';
            $params[] = "%$busca%"; $params[] = "%$busca%"; $params[] = "%$busca%";
        }
        // Ativos primeiro; dentro disso, maior valor no topo.
        $sql .= " ORDER BY FIELD(status,'ativo','vencido','resgatado'), valor_atual DESC";
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public static function buscarDoCliente(int $id, int $clienteId): ?array
    {
        $s = db()->prepare('SELECT * FROM investimentos WHERE id = ? AND cliente_id = ? AND ativo = 1');
        $s->execute([$id, $clienteId]);
        return $s->fetch() ?: null;
    }

    public static function criar(array $campos): int
    {
        $cols = array_keys($campos);
        $ph   = implode(',', array_fill(0, count($cols), '?'));
        db()->prepare('INSERT INTO investimentos (' . implode(', ', $cols) . ') VALUES (' . $ph . ')')
            ->execute(array_values($campos));
        return (int) db()->lastInsertId();
    }

    public static function atualizar(int $id, array $campos): void
    {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($campos)));
        db()->prepare("UPDATE investimentos SET $set WHERE id = ?")
            ->execute([...array_values($campos), $id]);
    }

    /** Contas financeiras do cliente (para o select de vínculo). */
    public static function contasDoCliente(int $clienteId): array
    {
        $s = db()->prepare("SELECT id, codigo, apelido, instituicao FROM contas_financeiras WHERE cliente_id = ? AND ativo = 1 ORDER BY apelido");
        $s->execute([$clienteId]);
        return $s->fetchAll();
    }

    // ---- Movimentos (aplicações/resgates/rendimentos) ---------------------

    public static function movimentos(int $investimentoId): array
    {
        $s = db()->prepare('SELECT * FROM investimento_movimentos WHERE investimento_id = ? ORDER BY data DESC, id DESC');
        $s->execute([$investimentoId]);
        return $s->fetchAll();
    }

    public static function movimentoCriar(int $investimentoId, array $campos): int
    {
        $campos = ['investimento_id' => $investimentoId] + $campos;
        $cols = array_keys($campos);
        $ph   = implode(',', array_fill(0, count($cols), '?'));
        db()->prepare('INSERT INTO investimento_movimentos (' . implode(', ', $cols) . ') VALUES (' . $ph . ')')
            ->execute(array_values($campos));
        return (int) db()->lastInsertId();
    }

    /** Remove um movimento garantindo que o investimento é do cliente (sem IDOR). */
    public static function movimentoExcluir(int $movId, int $investimentoId, int $clienteId): void
    {
        db()->prepare(
            'DELETE m FROM investimento_movimentos m
               JOIN investimentos i ON i.id = m.investimento_id
              WHERE m.id = ? AND m.investimento_id = ? AND i.cliente_id = ?'
        )->execute([$movId, $investimentoId, $clienteId]);
    }
}
