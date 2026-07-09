<?php
/**
 * Model de cliente — encapsula o acesso às tabelas `clientes` e (para o login
 * opcional do cliente) `usuarios`.
 */
class Cliente
{
    /** Lista clientes ativos com o e-mail de login (se houver). */
    public static function listarAtivos(): array
    {
        return db()->query(
            'SELECT c.*, u.email
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

    public static function criar(array $d): int
    {
        $s = db()->prepare(
            'INSERT INTO clientes (usuario_id, tipo_pessoa, nome, cpf_cnpj, email, telefone)
             VALUES (?,?,?,?,?,?)'
        );
        $s->execute([
            $d['usuario_id'], $d['tipo_pessoa'], $d['nome'],
            $d['cpf_cnpj'], $d['email'], $d['telefone'],
        ]);
        return (int) db()->lastInsertId();
    }

    public static function atualizar(int $id, array $d): void
    {
        $s = db()->prepare(
            'UPDATE clientes SET tipo_pessoa=?, nome=?, cpf_cnpj=?, email=?, telefone=? WHERE id=?'
        );
        $s->execute([
            $d['tipo_pessoa'], $d['nome'], $d['cpf_cnpj'],
            $d['email'], $d['telefone'], $id,
        ]);
    }

    /** Formata o CPF/CNPJ a partir dos dígitos, conforme o tipo de pessoa. */
    public static function formatarDocumento(string $digitos, string $tipo): string
    {
        return $tipo === 'PF'
            ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digitos)
            : preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digitos);
    }
}
