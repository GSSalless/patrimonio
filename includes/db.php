<?php
/**
 * Conexão PDO com o MySQL. As credenciais vêm do arquivo .env (na raiz do
 * projeto). Se o .env não existir, usa os defaults locais do XAMPP — assim o
 * ambiente de desenvolvimento continua funcionando sem configuração extra.
 */
require_once __DIR__ . '/../app/Core/Env.php';
Env::load(dirname(__DIR__) . '/.env');

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $host    = Env::get('DB_HOST', '127.0.0.1');
        $name    = Env::get('DB_NAME', 'gestao_patrimonial');
        $user    = Env::get('DB_USER', 'root');
        $pass    = Env::get('DB_PASS', '');
        $charset = Env::get('DB_CHARSET', 'utf8mb4');

        $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}
