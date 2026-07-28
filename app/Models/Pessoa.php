<?php
/**
 * Model das sub-tabelas 1:N da pessoa (Módulo 01).
 * Tabelas: pessoa_telefones, pessoa_emails, pessoa_enderecos,
 * pessoa_familiares, pessoa_contatos_emergencia.
 *
 * Tabelas permitidas ficam numa allow-list para o nome nunca vir do request.
 */
class Pessoa
{
    private const TABELAS = [
        'telefones'  => 'pessoa_telefones',
        'emails'     => 'pessoa_emails',
        'enderecos'  => 'pessoa_enderecos',
        'familiares' => 'pessoa_familiares',
        'emergencia' => 'pessoa_contatos_emergencia',
    ];

    private static function tabela(string $chave): string
    {
        if (!isset(self::TABELAS[$chave])) {
            throw new InvalidArgumentException("Sub-tabela de pessoa inválida: $chave");
        }
        return self::TABELAS[$chave];
    }

    /** Lista os registros de uma sub-tabela para o cliente. */
    public static function listar(string $chave, int $clienteId): array
    {
        $t = self::tabela($chave);
        $s = db()->prepare("SELECT * FROM $t WHERE cliente_id = ? ORDER BY id");
        $s->execute([$clienteId]);
        return $s->fetchAll();
    }

    /** Devolve todas as sub-listas de uma vez (para a tela de edição). */
    public static function subListas(int $clienteId): array
    {
        $out = [];
        foreach (array_keys(self::TABELAS) as $chave) {
            $out[$chave] = self::listar($chave, $clienteId);
        }
        return $out;
    }

    /** INSERT genérico numa sub-tabela. $campos NÃO deve conter cliente_id. */
    public static function criar(string $chave, int $clienteId, array $campos): int
    {
        $t = self::tabela($chave);
        $campos = ['cliente_id' => $clienteId] + $campos;
        $cols   = array_keys($campos);
        $ph     = implode(',', array_fill(0, count($cols), '?'));
        db()->prepare("INSERT INTO $t (" . implode(', ', $cols) . ") VALUES ($ph)")
            ->execute(array_values($campos));
        return (int) db()->lastInsertId();
    }

    /** DELETE de um item da sub-tabela, restrito ao cliente (evita IDOR). */
    public static function excluir(string $chave, int $clienteId, int $id): void
    {
        $t = self::tabela($chave);
        db()->prepare("DELETE FROM $t WHERE id = ? AND cliente_id = ?")
            ->execute([$id, $clienteId]);
    }
}
