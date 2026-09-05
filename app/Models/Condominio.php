<?php
/**
 * Model de condomínio — tabela `condominios` e vínculo com imóveis.
 */
class Condominio
{
    public static function buscar(int $id): ?array
    {
        $s = db()->prepare('SELECT * FROM condominios WHERE id = ?');
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    public static function criar(array $campos): int
    {
        $cols = array_keys($campos);
        $ph   = implode(',', array_fill(0, count($cols), '?'));
        db()->prepare('INSERT INTO condominios (' . implode(', ', $cols) . ') VALUES (' . $ph . ')')->execute(array_values($campos));
        return (int) db()->lastInsertId();
    }

    public static function atualizar(int $id, array $campos): void
    {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($campos)));
        db()->prepare("UPDATE condominios SET $set WHERE id = ?")->execute([...array_values($campos), $id]);
    }

    public static function vincular(int $imovelId, int $condId): void
    {
        db()->prepare('UPDATE imoveis SET condominio_id = ? WHERE id = ?')->execute([$condId, $imovelId]);
    }

    public static function desvincular(int $imovelId): void
    {
        db()->prepare('UPDATE imoveis SET condominio_id = NULL WHERE id = ?')->execute([$imovelId]);
    }

    /** Cliente dono dos uploads: do imóvel de origem ou de um imóvel já vinculado. */
    public static function clienteParaDocumentos(int $imovelId, int $condId): int
    {
        if ($imovelId) {
            $r = db()->prepare('SELECT cliente_id FROM imoveis WHERE id = ?');
            $r->execute([$imovelId]);
            $c = (int) ($r->fetchColumn() ?: 0);
            if ($c) return $c;
        }
        $r = db()->prepare('SELECT cliente_id FROM imoveis WHERE condominio_id = ? AND ativo = 1 LIMIT 1');
        $r->execute([$condId]);
        return (int) ($r->fetchColumn() ?: 0);
    }
}
