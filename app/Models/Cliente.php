<?php
/**
 * Model de cliente/pessoa — tabela `clientes` (âncora do sistema) + `usuarios`
 * para o login opcional do cliente. INSERT/UPDATE genérico por array coluna=>valor.
 */
class Cliente
{
    /** Lista clientes ativos com o e-mail de login (se houver). */
    public static function listarAtivos(): array
    {
        return db()->query(
            'SELECT c.*, u.email AS email_login
               FROM clientes c
               LEFT JOIN usuarios u ON u.id = c.usuario_id
              WHERE c.ativo = 1
              ORDER BY c.nome'
        )->fetchAll();
    }

    public static function buscar(int $id): ?array
    {
        $s = db()->prepare('SELECT * FROM clientes WHERE id = ?');
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    /** Cria um usuário de login (nível cliente) e devolve o id. */
    public static function criarUsuarioLogin(string $nome, string $email, string $senha): int
    {
        $hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);
        $s = db()->prepare('INSERT INTO usuarios (nome, email, senha_hash, nivel) VALUES (?,?,?,?)');
        $s->execute([$nome, $email, $hash, 'cliente']);
        return (int) db()->lastInsertId();
    }

    /** INSERT genérico: recebe [coluna => valor]. */
    public static function criar(array $campos): int
    {
        $cols = array_keys($campos);
        $ph   = implode(',', array_fill(0, count($cols), '?'));
        $sql  = 'INSERT INTO clientes (' . implode(', ', $cols) . ') VALUES (' . $ph . ')';
        db()->prepare($sql)->execute(array_values($campos));
        return (int) db()->lastInsertId();
    }

    /** UPDATE genérico: recebe [coluna => valor]. */
    public static function atualizar(int $id, array $campos): void
    {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($campos)));
        db()->prepare("UPDATE clientes SET $set WHERE id = ?")
            ->execute([...array_values($campos), $id]);
    }

    /** Formata o CPF/CNPJ a partir dos dígitos, conforme o tipo de pessoa. */
    public static function formatarDocumento(string $digitos, string $tipo): string
    {
        return $tipo === 'PF'
            ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digitos)
            : preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digitos);
    }
}
