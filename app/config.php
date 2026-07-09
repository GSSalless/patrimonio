<?php
/**
 * Bootstrap de configuração da camada MVC.
 * Reaproveita a infraestrutura existente (db, functions, auth) e carrega
 * a configuração geral de config/app.php.
 */

define('APP_ROOT', dirname(__DIR__));   // /cezar
define('APP_PATH', __DIR__);            // /cezar/app

// Infraestrutura (db.php também carrega o .env via Env)
require_once APP_ROOT . '/includes/db.php';
require_once APP_ROOT . '/includes/functions.php';
require_once APP_ROOT . '/includes/auth.php';

// Configuração geral da aplicação
$config = require APP_ROOT . '/config/app.php';
define('BASE_URL', $config['base_url']);
