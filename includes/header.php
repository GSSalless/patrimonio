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

    <?php if ($eh_admin):
      // "Sair do cliente" volta para a mesma tela no modo gestor (todos os
      // clientes) quando ela tem esse modo; senão, para o Dashboard geral.
      $seg = explode('/', $rota_atual)[0];
      $modo_geral = ['patrimonio','imoveis','veiculos','outros','empresas','contas','investimentos',
                     'seguros','contratos','fornecedores','colaboradores','documentos','tarefas','clientes'];
      if ($seg === 'agenda') $seg = 'tarefas';
      $sair_rota = in_array($seg, $modo_geral, true) ? $seg : 'gestao-geral';
    ?>
      <?php if ($cliente_sel): ?>
      <div class="topo-contexto">
        <button type="button" class="topo-cliente js-abre-clientes" data-next="<?= h($rota_atual ?: 'dashboard') ?>" title="Trocar cliente">
          <i class="bi bi-person-circle"></i>
          <span class="topo-cliente-txt"><small>Você está em</small><b><?= h($cliente_sel['nome']) ?></b></span>
          <i class="bi bi-chevron-down topo-cliente-seta"></i>
        </button>
        <a class="topo-cliente-sair" href="<?= base_url($sair_rota . '?cliente_id=0') ?>" title="Sair do cliente (ver todos os clientes)" aria-label="Sair do cliente">
          <i class="bi bi-x-lg"></i>
        </a>
      </div>
      <?php else: ?>
      <button type="button" class="topo-cliente topo-cliente-vazio js-abre-clientes" data-next="<?= h(($rota_atual === 'gestao-geral' || $rota_atual === '') ? 'dashboard' : $rota_atual) ?>" title="Selecionar um cliente">
        <?= icone('clientes') ?>
        <span class="topo-cliente-txt"><small>Exibindo</small><b>Todos os clientes</b></span>
        <i class="bi bi-chevron-down topo-cliente-seta"></i>
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
    // Menu ÚNICO — "mesmos botões, dois modos" (reunião 01/10/2026):
    // sem cliente selecionado cada item mostra os dados de TODOS os clientes;
    // com cliente selecionado, os mesmos itens mostram só os dados dele.
    // Ordem/grupos seguem o mockup do César. Itens: [rota, rótulo, chave do ícone].
    $painel = ($eh_admin && !$cliente_sel) ? 'gestao-geral' : 'dashboard';
    $grupos = [
      ['', array_values(array_filter([
        [$painel,    'Dashboard', 'dashboard'],
        $eh_admin ? ['clientes', 'Clientes', 'clientes'] : null,
        ($eh_admin && $cliente_sel) ? ['clientes/editar?id=' . (int) $cliente_sel['id'], 'Cadastro do cliente', 'cadastro'] : null,
      ]))],
      ['Ativos', [
        ['patrimonio',    'Patrimônio',    'patrimonio'],
        ['investimentos', 'Investimentos', 'investimentos'],
        ['empresas',      'Empresas',      'empresas'],
      ]],
      ['Financeiro', [
        ['contas',    'Contas',    'contas'],
        ['seguros',   'Seguros',   'seguros'],
        ['contratos', 'Contratos', 'contratos'],
      ]],
      ['Gestão', [
        ['documentos',    'Documentos',   'documentos'],
        ['tarefas',       'Tarefas',      'tarefas'],
        ['colaboradores', 'Pessoas (RH)', 'colaboradores'],
        ['fornecedores',  'Fornecedores', 'fornecedores'],
      ]],
    ];
    // Itens do mockup ainda não construídos (aparecem desabilitados).
    $em_breve = $eh_admin ? [
      ['Teia Patrimonial', 'teia'], ['Projetos', 'projetos'], ['Relatórios', 'relatorios'],
      ['IA Assistente', 'ia'], ['Configurações', 'configuracoes'], ['Ajuda', 'ajuda'],
    ] : [];

    // Rotas que também acendem um item (sub-telas).
    $acende = [
      'dashboard'     => ['dashboard', 'gestao-geral'],
      'gestao-geral'  => ['dashboard', 'gestao-geral'],
      'patrimonio'    => ['patrimonio', 'imoveis', 'veiculos', 'outros', 'reformas', 'manutencoes', 'locacao', 'condominios', 'financeiro'],
      'tarefas'       => ['tarefas', 'agenda'],
    ];
    $seg_atual = explode('/', $rota_atual)[0];
    ?>
    <?php if ($eh_admin): ?>
    <div class="menu-modo <?= $cliente_sel ? 'menu-modo-cli' : '' ?>">
      <span>Exibindo</span>
      <b><?= $cliente_sel ? h($cliente_sel['nome']) : 'Todos os clientes' ?></b>
    </div>
    <?php endif; ?>
    <?php foreach ($grupos as [$titulo, $itens]): ?>
      <?php if ($titulo !== ''): ?><div class="menu-grupo-tit"><?= h($titulo) ?></div><?php endif; ?>
      <?php foreach ($itens as [$rota, $rotulo, $ico]):
        $base_rota = strtok($rota, '?');
        if (str_contains($rota, '?')) {
          // Item com parâmetro (Cadastro do cliente): acende só na própria tela.
          $ativo = ($rota_atual === $base_rota && (int) ($_GET['id'] ?? 0) === (int) ($cliente_sel['id'] ?? -1));
        } else {
          $ativo = in_array($seg_atual, $acende[$rota] ?? [$rota], true);
          if ($rota === 'clientes' && $ativo && $cliente_sel && $rota_atual === 'clientes/editar'
              && (int) ($_GET['id'] ?? 0) === (int) $cliente_sel['id']) $ativo = false;
        }
      ?>
        <a class="menu-item<?= $ativo ? ' ativo' : '' ?>" href="<?= base_url($rota) ?>">
          <?= icone($ico) ?><span><?= h($rotulo) ?></span>
        </a>
      <?php endforeach; ?>
    <?php endforeach; ?>
    <?php if ($em_breve): ?>
      <div class="menu-grupo-tit">Em breve</div>
      <?php foreach ($em_breve as [$rotulo, $ico]): ?>
        <span class="menu-item menu-item-off" title="Em construção" aria-disabled="true">
          <?= icone($ico) ?><span><?= h($rotulo) ?></span>
        </span>
      <?php endforeach; ?>
    <?php endif; ?>
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
  // Lista de clientes ativos para o modal de seleção (aberto pelo chip do
  // topo e pelos botões "+ Cadastrar" no modo gestor, que precisam de um dono).
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
