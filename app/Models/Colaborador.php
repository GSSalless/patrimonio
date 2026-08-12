<?php
/**
 * Model de colaboradores do cliente (Módulo 02) + dependentes + histórico de RH.
 * Tabelas `colaboradores`, `colaborador_dependentes`, `colaborador_historico`.
 */
class Colaborador
{
    public static function listar(int $clienteId, string $status = '', string $busca = ''): array
    {
        $sql    = 'SELECT * FROM colaboradores WHERE cliente_id = ? AND ativo = 1';
        $params = [$clienteId];
        if ($status !== '') { $sql .= ' AND status = ?'; $params[] = $status; }
        if ($busca !== '') {
            $sql .= ' AND (nome LIKE ? OR cargo LIKE ? OR departamento LIKE ?)';
            $params[] = "%$busca%"; $params[] = "%$busca%"; $params[] = "%$busca%";
        }
        $sql .= " ORDER BY FIELD(status,'ativo','experiencia','ferias','afastado','desligado'), nome";
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public static function buscarDoCliente(int $id, int $clienteId): ?array
    {
        $s = db()->prepare('SELECT * FROM colaboradores WHERE id = ? AND cliente_id = ? AND ativo = 1');
        $s->execute([$id, $clienteId]);
        return $s->fetch() ?: null;
    }

    public static function criar(array $campos): int
    {
        $cols = array_keys($campos);
        $ph   = implode(',', array_fill(0, count($cols), '?'));
        db()->prepare('INSERT INTO colaboradores (' . implode(', ', $cols) . ') VALUES (' . $ph . ')')
            ->execute(array_values($campos));
        return (int) db()->lastInsertId();
    }

    public static function atualizar(int $id, array $campos): void
    {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($campos)));
        db()->prepare("UPDATE colaboradores SET $set WHERE id = ?")
            ->execute([...array_values($campos), $id]);
    }

    // ---- Dependentes ------------------------------------------------------

    public static function dependentes(int $colaboradorId): array
    {
        $s = db()->prepare('SELECT * FROM colaborador_dependentes WHERE colaborador_id = ? ORDER BY data_nascimento, id');
        $s->execute([$colaboradorId]);
        return $s->fetchAll();
    }

    public static function dependenteCriar(int $colaboradorId, array $campos): int
    {
        return self::subCriar('colaborador_dependentes', $colaboradorId, $campos);
    }

    public static function dependenteExcluir(int $depId, int $colaboradorId, int $clienteId): void
    {
        self::subExcluir('colaborador_dependentes', $depId, $colaboradorId, $clienteId);
    }

    // ---- Histórico de RH --------------------------------------------------

    public static function historico(int $colaboradorId): array
    {
        $s = db()->prepare('SELECT * FROM colaborador_historico WHERE colaborador_id = ? ORDER BY data DESC, id DESC');
        $s->execute([$colaboradorId]);
        return $s->fetchAll();
    }

    public static function historicoCriar(int $colaboradorId, array $campos): int
    {
        return self::subCriar('colaborador_historico', $colaboradorId, $campos);
    }

    public static function historicoExcluir(int $histId, int $colaboradorId, int $clienteId): void
    {
        self::subExcluir('colaborador_historico', $histId, $colaboradorId, $clienteId);
    }

    // ---- Helpers privados das sub-tabelas (nome de tabela nunca vem do request) ----

    private const SUBTABELAS = ['colaborador_dependentes', 'colaborador_historico'];

    private static function subCriar(string $tabela, int $colaboradorId, array $campos): int
    {
        if (!in_array($tabela, self::SUBTABELAS, true)) throw new InvalidArgumentException('Sub-tabela inválida.');
        $campos = ['colaborador_id' => $colaboradorId] + $campos;
        $cols = array_keys($campos);
        $ph   = implode(',', array_fill(0, count($cols), '?'));
        db()->prepare("INSERT INTO $tabela (" . implode(', ', $cols) . ") VALUES ($ph)")
            ->execute(array_values($campos));
        return (int) db()->lastInsertId();
    }

    /** DELETE garantindo que o colaborador pertence ao cliente (evita IDOR). */
    private static function subExcluir(string $tabela, int $id, int $colaboradorId, int $clienteId): void
    {
        if (!in_array($tabela, self::SUBTABELAS, true)) throw new InvalidArgumentException('Sub-tabela inválida.');
        db()->prepare(
            "DELETE s FROM $tabela s
               JOIN colaboradores c ON c.id = s.colaborador_id
              WHERE s.id = ? AND s.colaborador_id = ? AND c.cliente_id = ?"
        )->execute([$id, $colaboradorId, $clienteId]);
    }
}
