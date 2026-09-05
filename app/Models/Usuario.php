<?php
/**
 * Model de usuário. Encapsula o acesso à tabela `usuarios`.
 * A autenticação em si continua em includes/auth.php (função login())
 * para manter uma única fonte de verdade compartilhada com o legado.
 */
class Usuario
{
    public static function buscarPorEmail(string $email): ?array
    {
        $stmt = db()->prepare('SELECT * FROM usuarios WHERE email = ? AND ativo = 1 LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }
}
