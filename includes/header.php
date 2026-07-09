<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

session_init();
$usuario = usuario_logado();
$cliente_sel = cliente_selecionado();

// Busca lista de clientes (só admin vê todos)
$clientes_lista = [];
if ($usuario && $usuario['nivel'] === 'admin') {
    $clientes_lista = db()->query('SELECT id, nome FROM clientes WHERE ativo = 1 ORDER BY nome')->fetchAll();
}

// Troca de cliente via GET
if (isset($_GET['cliente_id']) && $usuario && $usuario['nivel'] === 'admin') {
    selecionar_cliente((int)$_GET['cliente_id']);
    $cliente_sel = cliente_selecionado();
    // Redireciona sem o param na URL
    $url_limpa = strtok($_SERVER['REQUEST_URI'], '?');
    header("Location: $url_limpa");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($page_title ?? 'Gestão Patrimonial') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <?php $css_ver = @filemtime(__DIR__ . '/../assets/css/style.css') ?: time(); ?>
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>?v=<?= $css_ver ?>">
</head>
<body>

<?php if ($usuario): ?>
<header class="topo">
  <div class="topo-logo">Gestão <span>Patrimonial</span></div>

  <?php if ($usuario['nivel'] === 'admin' && $clientes_lista): ?>
  <div class="topo-seletor">
    <form method="get" id="form-cliente">
      <select name="cliente_id" onchange="document.getElementById('form-cliente').submit()">
        <option value="">— selecione o cliente —</option>
        <?php foreach ($clientes_lista as $c): ?>
          <option value="<?= $c['id'] ?>" <?= ($cliente_sel && $cliente_sel['id'] == $c['id']) ? 'selected' : '' ?>>
            <?= h($c['nome']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>
  <?php endif; ?>

  <nav class="topo-nav">
    <?php if ($usuario['nivel'] === 'admin'): ?>
      <a href="<?= base_url('dashboard') ?>">Dashboard</a>
      <a href="<?= base_url('clientes') ?>">Clientes</a>
      <?php if ($cliente_sel): ?>
        <a href="<?= base_url('imoveis') ?>">Imóveis</a>
      <?php endif; ?>
    <?php else: ?>
      <a href="<?= base_url('imoveis') ?>">Meus Imóveis</a>
    <?php endif; ?>
  </nav>

  <div class="topo-usuario">
    <?= h($usuario['nome']) ?> &nbsp;|&nbsp;
    <a href="<?= base_url('logout') ?>" style="color:rgba(255,255,255,.7)">Sair</a>
  </div>
</header>
<?php endif; ?>
