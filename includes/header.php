<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

session_init();
$usuario = usuario_logado();
$cliente_sel = cliente_selecionado();
// A troca de cliente via ?cliente_id é tratada globalmente em app/bootstrap.php
// (antes dos controllers), pois algumas ações redirecionam antes desta view.
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

  <button type="button" class="topo-voltar"
          onclick="history.length > 1 ? history.back() : location.href='<?= base_url($eh_admin ? 'gestao-geral' : 'dashboard') ?>'"
          aria-label="Voltar">
    <i class="bi bi-arrow-left"></i><span>Voltar</span>
  </button>

  <div class="topo-logo">Gestão <span>Patrimonial</span></div>

  <?php if ($eh_admin && $cliente_sel): ?>
  <a class="topo-cliente" href="<?= base_url('clientes') ?>" title="Trocar cliente">
    <i class="bi bi-person-circle"></i><span><?= h($cliente_sel['nome']) ?></span>
  </a>
  <?php endif; ?>

  <div class="topo-usuario">
    <?= h($usuario['nome']) ?>
    <span class="topo-sep">|</span>
    <a href="<?= base_url('logout') ?>">Sair</a>
  </div>
</header>

<!-- Menu lateral esquerdo (navegação principal · drawer no mobile, fixo no desktop) -->
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
    // Grupos de navegação: [título, [ [rota, rótulo, ícone, mostrar?], ... ]]
    if ($eh_admin) {
      $grupos = [
        ['Geral', [
          ['gestao-geral', 'Gestão Geral', 'bi-columns-gap',    true],
          ['clientes',     'Clientes',     'bi-people',         true],
          ['agenda',       'Agenda',       'bi-calendar-check', true],
        ]],
      ];
      if ($cliente_sel) {
        $grupos[] = [$cliente_sel['nome'], [
          ['dashboard',  'Dashboard',   'bi-speedometer2', true],
          ['patrimonio', 'Patrimônios', 'bi-buildings',    true],
          ['contas',     'Contas',      'bi-bank',         true],
          ['seguros',    'Seguros',     'bi-shield-check', true],
        ]];
      }
    } else {
      $grupos = [
        ['Meu patrimônio', [
          ['dashboard',  'Dashboard',   'bi-speedometer2',   true],
          ['patrimonio', 'Patrimônios', 'bi-buildings',      true],
          ['contas',     'Contas',      'bi-bank',           true],
          ['seguros',    'Seguros',     'bi-shield-check',   true],
          ['agenda',     'Agenda',      'bi-calendar-check', true],
        ]],
      ];
    }
    foreach ($grupos as [$titulo, $itens]):
    ?>
      <div class="menu-grupo-tit"><?= h($titulo) ?></div>
      <?php foreach ($itens as [$rota, $rotulo, $icone, $mostrar]):
        if (!$mostrar) continue;
        $ativo = ($rota_atual === $rota || str_starts_with($rota_atual, $rota . '/')) ? ' ativo' : '';
        // Patrimônios fica ativo também nas telas de imóveis/veículos/outros
        if ($rota === 'patrimonio' && preg_match('#^(imoveis|veiculos|outros)#', $rota_atual)) $ativo = ' ativo';
      ?>
        <a class="menu-item<?= $ativo ?>" href="<?= base_url($rota) ?>">
          <i class="bi <?= $icone ?>"></i><span><?= h($rotulo) ?></span>
        </a>
      <?php endforeach; ?>
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
