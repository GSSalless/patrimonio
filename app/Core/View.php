<?php
/**
 * Renderizador de views. As variáveis do array $data ficam disponíveis
 * como variáveis locais dentro do arquivo da view.
 */
class View
{
    public static function render(string $view, array $data = []): void
    {
        $file = APP_PATH . '/Views/' . $view . '.php';
        if (!is_file($file)) {
            http_response_code(500);
            echo 'View não encontrada: ' . htmlspecialchars($view);
            return;
        }
        extract($data, EXTR_SKIP);
        require $file;
    }
}
