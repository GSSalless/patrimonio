<?php
/**
 * Controller base. Oferece helpers de renderização de view e redirecionamento.
 */
abstract class Controller
{
    /** Renderiza uma view de app/Views/{view}.php com os dados fornecidos. */
    protected function view(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    /** Redireciona para um caminho relativo à BASE_URL. */
    protected function redirect(string $path): never
    {
        header('Location: ' . BASE_URL . ltrim($path, '/'));
        exit;
    }
}
