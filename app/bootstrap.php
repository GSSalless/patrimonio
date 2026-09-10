<?php
/**
 * Bootstrap da camada MVC: config + autoload + sessão.
 * Incluído tanto pelo front controller (index.php) quanto pelos shims legados.
 */
// Tratamento global de erros ANTES de tudo — inclusive antes da conexão com o
// banco — para que qualquer falha vire uma página amigável (e vá pro log) em vez
// do "HTTP ERROR 500" cru da hospedagem.
require_once __DIR__ . '/Core/ErrorHandler.php';
ErrorHandler::register();

require_once __DIR__ . '/config.php';

spl_autoload_register(function (string $class): void {
    foreach (['Core', 'Controllers', 'Models'] as $dir) {
        $file = APP_PATH . '/' . $dir . '/' . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

// Migrações automáticas: aplica os .sql pendentes de sql/migrations/ (barato
// quando não há nada novo). Roda no servidor via .env — sem phpMyAdmin.
Migrator::maybeRun();

session_init();

// Seleção de cliente (admin) via ?cliente_id — tratada aqui, ANTES dos
// controllers, para funcionar mesmo em ações que redirecionam antes de
// renderizar a view (ex.: Dashboard → Gestão Geral). Depois redireciona para
// a URL limpa (sem o parâmetro).
if (isset($_GET['cliente_id']) && ($u = usuario_logado()) && $u['nivel'] === 'admin') {
    selecionar_cliente((int) $_GET['cliente_id']);
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

// Voltar às telas "gerais" (grupo Geral do menu: Gestão Geral, Clientes,
// Agenda) significa que o admin SAIU do contexto de um cliente. Limpamos a
// seleção para o menu lateral voltar a mostrar só o grupo Geral — desmarcar
// um cliente é justamente clicar num desses botões. Selecionar de novo é
// sempre via ?cliente_id (bloco acima).
if (($ua = usuario_logado()) && $ua['nivel'] === 'admin') {
    $rota_bs = trim($_GET['url'] ?? '', '/');
    $eh_geral = $rota_bs === 'gestao-geral'
             || $rota_bs === 'agenda'
             || $rota_bs === 'clientes'
             || str_starts_with($rota_bs, 'clientes/');
    if ($eh_geral) {
        unset($_SESSION['cliente_selecionado']);
    }
}

// Usuário CLIENTE: a seleção fica travada no próprio registro em toda requisição
// (segurança — não pode ver outro cliente — e navegação consistente). Um cliente
// nunca troca de contexto via ?cliente_id (o bloco acima é só admin).
if (($uc = usuario_logado()) && $uc['nivel'] === 'cliente') {
    $sc = db()->prepare('SELECT id, nome, cpf_cnpj, tipo_pessoa FROM clientes WHERE usuario_id = ? AND ativo = 1');
    $sc->execute([$uc['id']]);
    $_SESSION['cliente_selecionado'] = $sc->fetch() ?: null;
}
