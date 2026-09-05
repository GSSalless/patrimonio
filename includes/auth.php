<?php
require_once __DIR__ . '/db.php';

function session_init(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function usuario_logado(): ?array {
    session_init();
    return $_SESSION['usuario'] ?? null;
}

function exige_login(): void {
    if (!usuario_logado()) {
        header('Location: ' . base_url('index.php'));
        exit;
    }
}

function exige_admin(): void {
    exige_login();
    if (usuario_logado()['nivel'] !== 'admin') {
        header('Location: ' . base_url('dashboard'));
        exit;
    }
}

function login(string $email, string $senha): bool {
    $stmt = db()->prepare('SELECT * FROM usuarios WHERE email = ? AND ativo = 1 LIMIT 1');
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();
    if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) {
        return false;
    }
    session_init();
    session_regenerate_id(true);
    $_SESSION['usuario'] = [
        'id'    => $usuario['id'],
        'nome'  => $usuario['nome'],
        'email' => $usuario['email'],
        'nivel' => $usuario['nivel'],
    ];
    return true;
}

function logout(): void {
    session_init();
    session_destroy();
    header('Location: /cezar/index.php');
    exit;
}

function cliente_selecionado(): ?array {
    return $_SESSION['cliente_selecionado'] ?? null;
}

function selecionar_cliente(int $id): void {
    $stmt = db()->prepare('SELECT id, nome, cpf_cnpj, tipo_pessoa FROM clientes WHERE id = ? AND ativo = 1');
    $stmt->execute([$id]);
    $cliente = $stmt->fetch();
    if ($cliente) {
        $_SESSION['cliente_selecionado'] = $cliente;
    }
}
