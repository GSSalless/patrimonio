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
