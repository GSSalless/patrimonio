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

<?php
if ($usuario):
  // Rota atual (para marcar o item ativo no menu). Vem do front controller.
  $rota_atual = trim($_GET['url'] ?? '', '/');
  $eh_admin   = $usuario['nivel'] === 'admin';
?>
<header class="topo">
  <button type="button" class="menu-toggle" id="menu-toggle"
          aria-label="Abrir menu" aria-controls="menu-lateral" aria-expanded="false">
    <i class="bi bi-list"></i>
  </button>

  <div class="topo-logo">Gestão <span>Patrimonial</span></div>

  <?php if ($eh_admin && $clientes_lista): ?>
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

  <div class="topo-usuario">
    <?= h($usuario['nome']) ?>
    <span class="topo-sep">|</span>
    <a href="<?= base_url('logout') ?>">Sair</a>
  </div>
</header>

<!-- Menu lateral esquerdo (drawer) -->
<div class="menu-overlay" id="menu-overlay" hidden></div>
<aside class="menu-lateral" id="menu-lateral" aria-hidden="true">
  <div class="menu-lateral-topo">
    <span class="menu-lateral-marca">Gestão <span>Patrimonial</span></span>
    <button type="button" class="menu-fechar" id="menu-fechar" aria-label="Fechar menu">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>

  <nav class="menu-nav">
    <?php
    // [rota, rótulo, ícone, mostrar?]
    $itens = $eh_admin
      ? [
          ['dashboard', 'Dashboard', 'bi-speedometer2', true],
          ['clientes',  'Clientes',  'bi-people',       true],
          ['imoveis',   'Imóveis',   'bi-buildings',    (bool) $cliente_sel],
        ]
      : [
          ['imoveis',   'Meus Imóveis', 'bi-buildings', true],
        ];
    foreach ($itens as [$rota, $rotulo, $icone, $mostrar]):
      if (!$mostrar) continue;
      $ativo = ($rota_atual === $rota || str_starts_with($rota_atual, $rota . '/')) ? ' ativo' : '';
    ?>
      <a class="menu-item<?= $ativo ?>" href="<?= base_url($rota) ?>">
        <i class="bi <?= $icone ?>"></i><span><?= h($rotulo) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="menu-lateral-rodape">
    <div class="menu-user">
      <div class="menu-user-nome"><?= h($usuario['nome']) ?></div>
      <div class="menu-user-nivel"><?= $eh_admin ? 'Administrador' : 'Cliente' ?></div>
    </div>
    <a class="menu-sair" href="<?= base_url('logout') ?>">
      <i class="bi bi-box-arrow-right"></i> Sair
    </a>
  </div>
</aside>
<?php endif; ?>
