<?php
/**
 * Roteador mínimo. Mapeia (método HTTP + caminho) para [Controller, ação].
 * O caminho vem do parâmetro `url` que o .htaccess (ou o front controller)
 * preenche. Ex.: /cezar/login  ->  url = "login".
 */
class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][trim($path, '/')] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][trim($path, '/')] = $handler;
    }

    public function dispatch(string $url, string $method): void
    {
        $url    = trim($url, '/');
        $method = strtoupper($method) === 'POST' ? 'POST' : 'GET';
        $handler = $this->routes[$method][$url] ?? null;

        if ($handler === null) {
            http_response_code(404);
            echo '404 — rota não encontrada: ' . htmlspecialchars($url);
            return;
        }

        [$class, $action] = $handler;
        (new $class())->$action();
    }
}
