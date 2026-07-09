<?php
/**
 * Model de contrato de locação — tabela `contratos_locacao`.
 */
class ContratoLocacao
{
    /** Encerra contratos ativos anteriores do imóvel. */
    public static function encerrarAtivos(int $imovelId): void
    {
        db()->prepare('UPDATE contratos_locacao SET status = "encerrado" WHERE imovel_id = ? AND status = "ativo"')
            ->execute([$imovelId]);
    }

    public static function criar(int $imovelId, array $campos): int
    {
        $cols = array_merge(['imovel_id'], array_keys($campos));
        $vals = array_merge([$imovelId], array_values($campos));
        $ph   = implode(',', array_fill(0, count($cols), '?'));
        db()->prepare('INSERT INTO contratos_locacao (' . implode(', ', $cols) . ') VALUES (' . $ph . ')')->execute($vals);
        return (int) db()->lastInsertId();
    }
}
