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

// Usuário CLIENTE: a seleção fica travada no próprio registro em toda requisição
// (segurança — não pode ver outro cliente — e navegação consistente). Um cliente
// nunca troca de contexto via ?cliente_id (o bloco acima é só admin).
if (($uc = usuario_logado()) && $uc['nivel'] === 'cliente') {
    $sc = db()->prepare('SELECT id, nome, cpf_cnpj, tipo_pessoa FROM clientes WHERE usuario_id = ? AND ativo = 1');
    $sc->execute([$uc['id']]);
    $_SESSION['cliente_selecionado'] = $sc->fetch() ?: null;
}
