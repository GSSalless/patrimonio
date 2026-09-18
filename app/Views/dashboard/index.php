<?php
/**
 * Dashboard — visão de UM cliente (hub de módulos).
 * Admin sem cliente selecionado é redirecionado para a Gestão Geral no controller.
 *
 * @var array|null $cli
 * @var array      $pat  patrimônio consolidado do cliente (patrimonio_consolidado())
 */
$page_title = 'Dashboard — Gestão Patrimonial';
require APP_ROOT . '/includes/header.php';

$qtd_imoveis = $pat['imoveis_qtd'] ?? 0;
$qtd_contas  = $pat['contas_qtd'] ?? 0;
$total_pat   = (float) ($pat['total'] ?? 0);
$linhas = [
    ['Imóveis',  '🏛️', $pat['imoveis_valor']  ?? 0, $pat['imoveis_qtd']  ?? 0, '#c9a227'],
    ['Veículos', '🚗', $pat['veiculos_valor'] ?? 0, $pat['veiculos_qtd'] ?? 0, '#5b8def'],
    ['Outros bens', '💎', $pat['outros_valor'] ?? 0, $pat['outros_qtd'] ?? 0, '#9b6dd6'],
    ['Investimentos', '📈', $pat['invest_valor'] ?? 0, $pat['invest_qtd'] ?? 0, '#e0669e'],
    ['Contas',   '🏦', $pat['contas_saldo']   ?? 0, $pat['contas_qtd']   ?? 0, '#3fae7a'],
];
?>
<div class="container">

  <?php if (!$cli): ?>
  <div class="card">
    <p style="color:var(--cor-secundario);text-align:center;padding:2rem">
      Nenhum cliente selecionado.
      <?php if (($usuario['nivel'] ?? '') === 'admin'): ?>
        Vá para <a href="<?= base_url('gestao-geral') ?>">Gestão Geral</a> e escolha um cliente.
      <?php endif; ?>
    </p>
  </div>

  <?php else: ?>
  <!-- Visão com cliente selecionado: menu de módulos -->
  <div style="margin-bottom:1.5rem">
    <h2 style="font-size:1.2rem;color:var(--cor-primaria)"><?= h($cli['nome']) ?></h2>
    <div style="font-size:.85rem;color:var(--cor-secundario)"><?= h($cli['tipo_pessoa'] === 'PF' ? 'CPF: ' : 'CNPJ: ') . h($cli['cpf_cnpj']) ?></div>
  </div>

  <!-- Patrimônio consolidado do cliente (Módulo 15) -->
  <div class="db-pat">
    <div class="db-pat-head">
      <span class="db-pat-l">Patrimônio total</span>
      <span class="db-pat-n"><?= moeda($total_pat) ?></span>
    </div>
    <div class="db-pat-rows">
      <?php foreach ($linhas as [$label, $emoji, $valor, $qtd, $cor]):
        $pct = $total_pat > 0 ? round($valor / $total_pat * 100) : 0; ?>
      <div class="db-pat-row">
        <span class="db-pat-ico" style="background:<?= $cor ?>18;border-color:<?= $cor ?>55"><?= $emoji ?></span>
        <span class="db-pat-cat"><?= h($label) ?> <span class="db-pat-q"><?= $qtd ?></span></span>
        <span class="db-pat-track"><span class="db-pat-fill" style="width:<?= $pct ?>%;background:<?= $cor ?>"></span></span>
        <span class="db-pat-v"><?= moeda((float)$valor) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Indicadores executivos do cliente (Módulo 15) -->
  <?php if (!empty($ind)): ?>
  <div class="db-kpis">
    <a class="db-kpi" href="<?= base_url('contas') ?>">
      <div class="db-kpi-top">💰 Financeiro</div>
      <div class="db-kpi-n"><?= moeda((float) ($ind['financeiro']['contas_saldo'] + $ind['financeiro']['invest_valor'])) ?></div>
      <div class="db-kpi-sub">🏦 <?= (int) $ind['financeiro']['contas_qtd'] ?> · 📈 <?= (int) $ind['financeiro']['invest_qtd'] ?></div>
    </a>
    <a class="db-kpi" href="<?= base_url('colaboradores') ?>">
      <div class="db-kpi-top">👔 RH</div>
      <div class="db-kpi-n"><?= (int) $ind['rh']['colaboradores'] ?></div>
      <div class="db-kpi-sub"><?php if ($ind['rh']['ferias'] || $ind['rh']['treinamentos']): ?>🏖️ <?= (int) $ind['rh']['ferias'] ?> · 🎓 <?= (int) $ind['rh']['treinamentos'] ?><?php else: ?>ativos<?php endif; ?></div>
    </a>
    <a class="db-kpi" href="<?= base_url('contratos') ?>">
      <div class="db-kpi-top">📜 Contratos</div>
      <div class="db-kpi-n"><?= (int) $ind['contratos']['ativos'] ?></div>
      <div class="db-kpi-sub<?= $ind['contratos']['vencendo'] ? ' db-kpi-warn' : '' ?>"><?= $ind['contratos']['vencendo'] ? '⏱ ' . (int) $ind['contratos']['vencendo'] . ' vencendo' : 'ativos' ?></div>
    </a>
    <a class="db-kpi" href="<?= base_url('seguros') ?>">
      <div class="db-kpi-top">🛡️ Seguros</div>
      <div class="db-kpi-n"><?= (int) $ind['seguros']['vigentes'] ?></div>
      <div class="db-kpi-sub<?= $ind['seguros']['vencendo'] ? ' db-kpi-warn' : '' ?>"><?= $ind['seguros']['vencendo'] ? '⏱ ' . (int) $ind['seguros']['vencendo'] . ' vencendo' : 'vigentes' ?></div>
    </a>
  </div>
  <?php endif; ?>

  <div class="app-grid">
    <a href="<?= base_url('patrimonio') ?>" class="app-icon">
      <span class="app-icon-tile app-tile-azul">
        🏛️
        <?php if ($qtd_imoveis > 0): ?><span class="app-icon-badge"><?= $qtd_imoveis ?></span><?php endif; ?>
      </span>
      <span class="app-icon-label">Patrimônios</span>
    </a>

    <a href="<?= base_url('contas') ?>" class="app-icon">
      <span class="app-icon-tile app-tile-verde">
        🏦
        <?php if (($qtd_contas ?? 0) > 0): ?><span class="app-icon-badge"><?= $qtd_contas ?></span><?php endif; ?>
      </span>
      <span class="app-icon-label">Contas</span>
    </a>

    <a href="<?= base_url('empresas') ?>" class="app-icon">
      <span class="app-icon-tile app-tile-azul">
        🏢
        <?php if (($qtd_empresas ?? 0) > 0): ?><span class="app-icon-badge"><?= $qtd_empresas ?></span><?php endif; ?>
      </span>
      <span class="app-icon-label">Empresas</span>
    </a>

    <a href="<?= base_url('investimentos') ?>" class="app-icon">
      <span class="app-icon-tile app-tile-verde">
        📈
        <?php if (($qtd_investimentos ?? 0) > 0): ?><span class="app-icon-badge"><?= $qtd_investimentos ?></span><?php endif; ?>
      </span>
      <span class="app-icon-label">Investimentos</span>
    </a>

    <a href="<?= base_url('seguros') ?>" class="app-icon">
      <span class="app-icon-tile app-tile-azul">
        🛡️
        <?php if (($qtd_seguros ?? 0) > 0): ?><span class="app-icon-badge"><?= $qtd_seguros ?></span><?php endif; ?>
      </span>
      <span class="app-icon-label">Seguros</span>
    </a>

    <a href="<?= base_url('contratos') ?>" class="app-icon">
      <span class="app-icon-tile app-tile-roxo">
        📜
        <?php if (($qtd_contratos ?? 0) > 0): ?><span class="app-icon-badge"><?= $qtd_contratos ?></span><?php endif; ?>
      </span>
      <span class="app-icon-label">Contratos</span>
    </a>

    <a href="<?= base_url('fornecedores') ?>" class="app-icon">
      <span class="app-icon-tile app-tile-laranja">
        🤝
        <?php if (($qtd_fornecedores ?? 0) > 0): ?><span class="app-icon-badge"><?= $qtd_fornecedores ?></span><?php endif; ?>
      </span>
      <span class="app-icon-label">Fornecedores</span>
    </a>

    <a href="<?= base_url('colaboradores') ?>" class="app-icon">
      <span class="app-icon-tile app-tile-laranja">
        👔
        <?php if (($qtd_colaboradores ?? 0) > 0): ?><span class="app-icon-badge"><?= $qtd_colaboradores ?></span><?php endif; ?>
      </span>
      <span class="app-icon-label">Colaboradores</span>
    </a>

    <a href="<?= base_url('documentos') ?>" class="app-icon">
      <span class="app-icon-tile app-tile-azul">
        📁
        <?php if (($qtd_documentos ?? 0) > 0): ?><span class="app-icon-badge"><?= $qtd_documentos ?></span><?php endif; ?>
      </span>
      <span class="app-icon-label">Documentos</span>
    </a>

    <?php $ag_urg = $alertas['urgentes'] ?? 0; ?>
    <a href="<?= base_url('agenda') ?>" class="app-icon">
      <span class="app-icon-tile app-tile-laranja">
        📅
        <?php if ($ag_urg > 0): ?><span class="app-icon-badge"><?= $ag_urg ?></span><?php endif; ?>
      </span>
      <span class="app-icon-label">Agenda</span>
    </a>

    <span class="app-icon app-icon-off">
      <span class="app-icon-tile app-tile-verde">💰</span>
      <span class="app-icon-label">Caixa</span>
    </span>

    <span class="app-icon app-icon-off">
      <span class="app-icon-tile app-tile-laranja">✅</span>
      <span class="app-icon-label">Tarefas</span>
    </span>
  </div>
  <?php endif; ?>

</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
