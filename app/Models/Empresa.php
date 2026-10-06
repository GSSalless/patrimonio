<?php
/**
 * Model de empresas/holdings do cliente (Módulo 03) + quadro societário.
 * Tabelas `empresas` e `empresa_socios`. INSERT/UPDATE por array coluna=>valor.
 */
class Empresa
{
    public static function listar(?int $clienteId, string $natureza = '', string $busca = ''): array
    {
        // $clienteId null = todos os clientes (modo gestor, reunião 01/10/2026).
        $sql    = 'SELECT t.*, c.nome AS cliente_nome FROM empresas t
                     JOIN clientes c ON c.id = t.cliente_id
                    WHERE t.ativo = 1 AND c.ativo = 1';
        $params = [];
        if ($clienteId !== null) { $sql .= ' AND t.cliente_id = ?'; $params[] = $clienteId; }
        if ($natureza !== '') { $sql .= ' AND t.natureza = ?'; $params[] = $natureza; }
        if ($busca !== '') {
            $sql .= ' AND (t.razao_social LIKE ? OR t.nome_fantasia LIKE ? OR t.cnpj LIKE ?)';
            $params[] = "%$busca%"; $params[] = "%$busca%"; $params[] = "%$busca%";
        }
        $sql .= ' ORDER BY ' . ($clienteId === null ? 'c.nome, ' : '') . 't.razao_social';
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public static function buscarDoCliente(int $id, int $clienteId): ?array
    {
        $s = db()->prepare('SELECT * FROM empresas WHERE id = ? AND cliente_id = ? AND ativo = 1');
        $s->execute([$id, $clienteId]);
        return $s->fetch() ?: null;
    }

    public static function criar(array $campos): int
    {
        $cols = array_keys($campos);
        $ph   = implode(',', array_fill(0, count($cols), '?'));
        db()->prepare('INSERT INTO empresas (' . implode(', ', $cols) . ') VALUES (' . $ph . ')')
            ->execute(array_values($campos));
        return (int) db()->lastInsertId();
    }

    public static function atualizar(int $id, array $campos): void
    {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($campos)));
        db()->prepare("UPDATE empresas SET $set WHERE id = ?")
            ->execute([...array_values($campos), $id]);
    }

    // ---- Quadro societário (empresa_socios) -------------------------------
    // Todas as operações validam empresa_id contra o cliente (sem IDOR).

    public static function socios(int $empresaId): array
    {
        $s = db()->prepare('SELECT * FROM empresa_socios WHERE empresa_id = ? ORDER BY participacao DESC, id');
        $s->execute([$empresaId]);
        return $s->fetchAll();
    }

    public static function socioCriar(int $empresaId, array $campos): int
    {
        $campos = ['empresa_id' => $empresaId] + $campos;
        $cols = array_keys($campos);
        $ph   = implode(',', array_fill(0, count($cols), '?'));
        db()->prepare('INSERT INTO empresa_socios (' . implode(', ', $cols) . ') VALUES (' . $ph . ')')
            ->execute(array_values($campos));
        return (int) db()->lastInsertId();
    }

    /** Remove um sócio garantindo que a empresa pertence ao cliente (evita IDOR). */
    public static function socioExcluir(int $socioId, int $empresaId, int $clienteId): void
    {
        db()->prepare(
            'DELETE es FROM empresa_socios es
               JOIN empresas e ON e.id = es.empresa_id
              WHERE es.id = ? AND es.empresa_id = ? AND e.cliente_id = ?'
        )->execute([$socioId, $empresaId, $clienteId]);
    }

    /** Soma da participação lançada (para conferência do 100%). */
    public static function participacaoTotal(int $empresaId): float
    {
        $s = db()->prepare('SELECT COALESCE(SUM(participacao),0) FROM empresa_socios WHERE empresa_id = ?');
        $s->execute([$empresaId]);
        return (float) $s->fetchColumn();
    }
}
