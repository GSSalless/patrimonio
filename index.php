<?php
/**
 * Front controller — raiz web da aplicação.
 *
 * A pasta do projeto (ex.: /cezar local, ou a pasta do domínio em produção)
 * é a própria raiz web: não há subpasta /public. As pastas de código
 * (app, config, includes, routes, sql) e o .env ficam protegidas por
 * .htaccess próprio em cada uma.
 *
 * Toda requisição que não seja um arquivo/pasta real é encaminhada para cá
 * pelo .htaccess, que preenche ?url= com o caminho solicitado.
 */
require_once __DIR__ . '/app/bootstrap.php';

$router = new Router();
require_once APP_ROOT . '/routes/web.php';

$router->dispatch($_GET['url'] ?? '', $_SERVER['REQUEST_METHOD']);
