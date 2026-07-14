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
