<?php
/**
 * Bootstrap da camada MVC: config + autoload + sessão.
 * Incluído tanto pelo front controller (index.php) quanto pelos shims legados.
 */
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
