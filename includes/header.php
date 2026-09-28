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
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <?php
    // Camadas de CSS (organizadores). tokens.css primeiro — é o tema.
    $css_dir = __DIR__ . '/../assets/css/';
    $css_layers = ['tokens.css', 'layout.css', 'style.css', 'paginas.css'];
    foreach ($css_layers as $layer):
      if (!is_file($css_dir . $layer)) continue;
      $ver = @filemtime($css_dir . $layer) ?: time();
  ?>
  <link rel="stylesheet" href="<?= base_url('assets/css/' . $layer) ?>?v=<?= $ver ?>">
  <?php endforeach; ?>
</head>
<body>

<?php
if ($usuario):
  // Rota atual (para marcar o item ativo no menu). Vem do front controller.
  $rota_atual = trim($_GET['url'] ?? '', '/');
  $eh_admin   = $usuario['nivel'] === 'admin';

  // Data/hora para o topo (pt-BR, sem depender de locale do servidor)
  $dias_semana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
  $meses_abrev = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];
  $ts = time();
  $data_fmt = $dias_semana[(int)date('w', $ts)] . ', ' . date('d', $ts)
            . ' de ' . $meses_abrev[(int)date('n', $ts) - 1] . ' de ' . date('Y', $ts);
  $hora_fmt = date('H:i', $ts);

  // Inicial do usuário para o avatar
  $inicial = function_exists('mb_substr')
    ? mb_strtoupper(mb_substr($usuario['nome'], 0, 1))
    : strtoupper(substr($usuario['nome'], 0, 1));
?>
<header class="topo">
  <button type="button" class="menu-toggle" id="menu-toggle"
          aria-label="Abrir menu" aria-controls="menu-lateral" aria-expanded="false">
    <i class="bi bi-list"></i>
  </button>

  <label class="topo-busca">
    <i class="bi bi-search"></i>
    <input type="search" placeholder="Buscar clientes, ativos, documentos..." aria-label="Buscar">
  </label>

  <div class="topo-dir">
    <button type="button" class="topo-icone" aria-label="Notificações" title="Notificações">
      <i class="bi bi-bell"></i><span class="ponto ambar"></span>
    </button>

    <?php if ($eh_admin): ?>
      <?php if ($cliente_sel): ?>
      <button type="button" class="topo-cliente js-abre-clientes" data-next="<?= h($rota_atual ?: 'dashboard') ?>" title="Trocar cliente">
        <i class="bi bi-person-circle"></i><span><?= h($cliente_sel['nome']) ?></span>
        <i class="bi bi-chevron-down topo-cliente-seta"></i>
      </button>
      <?php else: ?>
      <button type="button" class="topo-cliente topo-cliente-vazio js-abre-clientes" data-next="<?= h($rota_atual ?: 'dashboard') ?>" title="Selecionar cliente">
        <i class="bi bi-person-plus"></i><span>Selecionar cliente</span>
      </button>
      <?php endif; ?>
    <?php endif; ?>

    <div class="topo-data">
      <div class="d"><?= h($data_fmt) ?></div>
      <div class="t" id="relogio"><?= h($hora_fmt) ?></div>
    </div>
  </div>
</header>

<!-- Menu lateral esquerdo (navegação principal · drawer no mobile, fixo no desktop) -->
<div class="menu-overlay" id="menu-overlay" hidden></div>
<aside class="menu-lateral" id="menu-lateral" aria-hidden="true">
  <div class="menu-lateral-topo">
    <div class="marca-cz">
      <div class="marca-cz-mark">CZR</div>
      <div>
        <div class="marca-cz-nome">CZR</div>
        <div class="marca-cz-sub">Soluções</div>
      </div>
    </div>
    <button type="button" class="menu-fechar" id="menu-fechar" aria-label="Fechar menu">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>

  <nav class="menu-nav">
    <?php
    // Grupos de navegação: [título, [ [rota, rótulo, ícone, mostrar?], ... ], precisa_cliente]
    // precisa_cliente = itens que dependem de um cliente selecionado. Para o admin,
    // o menu aparece SEMPRE completo; se nenhum cliente estiver setado, esses itens
    // abrem o modal de seleção (em vez de navegar direto).
    $itens_cliente = [
      ['dashboard',  'Dashboard',   'bi-speedometer2', true],
      ['patrimonio', 'Patrimônios', 'bi-buildings',    true],
      ['empresas',   'Empresas',    'bi-briefcase',    true],
      ['contas',     'Contas',      'bi-bank',         true],
      ['investimentos','Investimentos','bi-graph-up-arrow', true],
      ['seguros',    'Seguros',     'bi-shield-check', true],
      ['contratos',  'Contratos',   'bi-file-earmark-text', true],
      ['fornecedores','Fornecedores','bi-people-fill',  true],
      ['colaboradores','Colaboradores','bi-person-badge', true],
      ['documentos',  'Documentos',  'bi-folder2-open', true],
    ];
    if ($eh_admin) {
      $grupos = [
        ['Gestão', [
          ['gestao-geral', 'Gestão Geral', 'bi-columns-gap',    true],
          ['clientes',     'Clientes',     'bi-people',         true],
          ['agenda',       'Agenda',       'bi-calendar-check', true],
        ], false],
        [($cliente_sel['nome'] ?? 'Cliente'), $itens_cliente, true],
      ];
    } else {
      // Cliente final: sempre no próprio contexto (nunca precisa selecionar).
      $grupos = [
        ['Meu patrimônio', array_merge($itens_cliente, [
          ['agenda', 'Agenda', 'bi-calendar-check', true],
        ]), false],
      ];
    }
    foreach ($grupos as [$titulo, $itens, $precisa_cliente]):
    ?>
      <div class="menu-grupo-tit"><?= h($titulo) ?></div>
      <?php foreach ($itens as [$rota, $rotulo, $icone, $mostrar]):
        if (!$mostrar) continue;
        $ativo = ($rota_atual === $rota || str_starts_with($rota_atual, $rota . '/')) ? ' ativo' : '';
        // Patrimônios fica ativo também nas telas de imóveis/veículos/outros
        if ($rota === 'patrimonio' && preg_match('#^(imoveis|veiculos|outros)#', $rota_atual)) $ativo = ' ativo';

        // Item do cliente SEM cliente setado → botão que abre o modal de seleção.
        if ($precisa_cliente && !$cliente_sel):
      ?>
        <button type="button" class="menu-item js-abre-clientes" data-next="<?= h($rota) ?>">
          <i class="bi <?= $icone ?>"></i><span><?= h($rotulo) ?></span>
        </button>
      <?php else: ?>
        <a class="menu-item<?= $ativo ?>" href="<?= base_url($rota) ?>">
          <i class="bi <?= $icone ?>"></i><span><?= h($rotulo) ?></span>
        </a>
      <?php endif; ?>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>

  <div class="menu-lateral-rodape">
    <div class="menu-user">
      <div class="menu-user-avatar"><?= h($inicial) ?></div>
      <div>
        <div class="menu-user-nome"><?= h($usuario['nome']) ?></div>
        <div class="menu-user-nivel"><?= $eh_admin ? 'Administrador' : 'Cliente' ?></div>
      </div>
    </div>
    <a class="menu-sair" href="<?= base_url('logout') ?>">
      <i class="bi bi-box-arrow-right"></i> Sair
    </a>
  </div>
</aside>

<?php if ($eh_admin):
  // Lista de clientes ativos para o modal de seleção (aberto pelos itens do
  // menu do cliente quando nenhum está setado, ou pelo chip do topo).
  $mc_clientes = db()->query(
    'SELECT id, nome, nome_completo, cpf_cnpj, tipo_pessoa FROM clientes WHERE ativo = 1 ORDER BY nome'
  )->fetchAll();
?>
<div class="modal-cli" id="modal-cliente" data-base="<?= h(BASE_URL) ?>" hidden>
  <div class="modal-cli-box" role="dialog" aria-modal="true" aria-label="Selecionar cliente">
    <div class="modal-cli-head">
      <div>
        <h3>Selecione um cliente</h3>
        <p>Escolha de quem você quer ver os dados.</p>
      </div>
      <button type="button" class="mc-fechar" aria-label="Fechar">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
    <label class="mc-busca-wrap">
      <i class="bi bi-search"></i>
      <input type="search" class="mc-busca" placeholder="Buscar cliente..." aria-label="Buscar cliente">
    </label>
    <div class="modal-cli-lista">
      <?php if ($mc_clientes): foreach ($mc_clientes as $mc):
        $mc_nome = $mc['nome_completo'] ?: $mc['nome'];
        $mc_ini  = mb_strtoupper(mb_substr(trim($mc['nome']), 0, 1));
        $mc_atual = $cliente_sel && (int) $cliente_sel['id'] === (int) $mc['id'];
      ?>
        <button type="button" class="mc-item<?= $mc_atual ? ' mc-item-atual' : '' ?>" data-id="<?= (int) $mc['id'] ?>">
          <span class="mc-ava"><?= h($mc_ini) ?></span>
          <span class="mc-info">
            <span class="mc-nome"><?= h($mc_nome) ?></span>
            <span class="mc-doc"><span class="tag"><?= h($mc['tipo_pessoa']) ?></span> <?= h($mc['cpf_cnpj']) ?></span>
          </span>
          <?php if ($mc_atual): ?><i class="bi bi-check-circle-fill mc-check"></i><?php else: ?><i class="bi bi-chevron-right"></i><?php endif; ?>
        </button>
      <?php endforeach; else: ?>
        <p class="mc-vazio">Nenhum cliente ativo. <a href="<?= base_url('clientes/novo') ?>">Cadastrar o primeiro</a>.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>
<?php endif; ?>
