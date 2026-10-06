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
//   ?cliente_id=N  → entra no contexto do cliente N;
//   ?cliente_id=0  → "Sair do cliente": volta ao modo gestor (todos os clientes).
// Os demais parâmetros da URL são preservados (ex.: imoveis/ficha?id=5&cliente_id=2
// → imoveis/ficha?id=5 já no contexto do cliente 2 — usado nas listas do modo gestor).
if (isset($_GET['cliente_id']) && ($u = usuario_logado()) && $u['nivel'] === 'admin') {
    $novo_cli = (int) $_GET['cliente_id'];
    if ($novo_cli > 0) {
        selecionar_cliente($novo_cli);
    } else {
        unset($_SESSION['cliente_selecionado']);
    }
    $partes = parse_url($_SERVER['REQUEST_URI'] ?? '') ?: [];
    parse_str($partes['query'] ?? '', $qs);
    unset($qs['cliente_id'], $qs['url']);
    // '/' + ltrim: impede "//host" (redirect para outro domínio).
    $destino = '/' . ltrim($partes['path'] ?? BASE_URL, '/');
    header('Location: ' . $destino . ($qs ? '?' . http_build_query($qs) : ''));
    exit;
}

// Nota: o cliente selecionado PERMANECE na sessão até ser trocado ou até o
// gestor clicar em "Sair do cliente" (chip do topo → ?cliente_id=0). O menu é
// único: sem cliente as telas mostram todos os clientes; com cliente, só ele
// (ver includes/header.php e Controller::escopoCliente()).

// Usuário CLIENTE: a seleção fica travada no próprio registro em toda requisição
// (segurança — não pode ver outro cliente — e navegação consistente). Um cliente
// nunca troca de contexto via ?cliente_id (o bloco acima é só admin).
if (($uc = usuario_logado()) && $uc['nivel'] === 'cliente') {
    $sc = db()->prepare('SELECT id, nome, cpf_cnpj, tipo_pessoa FROM clientes WHERE usuario_id = ? AND ativo = 1');
    $sc->execute([$uc['id']]);
    $_SESSION['cliente_selecionado'] = $sc->fetch() ?: null;
}
