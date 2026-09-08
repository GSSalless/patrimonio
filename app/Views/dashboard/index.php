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

<style>
  .db-pat{background:var(--cor-branco,#fff);border:1px solid var(--cor-borda,#e3e8ef);border-radius:16px;
    padding:1.2rem 1.35rem;box-shadow:0 3px 12px rgba(0,0,0,.05);margin-bottom:1.6rem}
  .db-pat-head{display:flex;align-items:baseline;justify-content:space-between;gap:1rem;
    padding-bottom:.9rem;margin-bottom:.9rem;border-bottom:1px solid var(--cor-borda,#e3e8ef)}
  .db-pat-l{font-size:.8rem;letter-spacing:.04em;text-transform:uppercase;color:var(--cor-secundario)}
  .db-pat-n{font-family:var(--fonte-titulo);font-size:1.7rem;font-weight:800;color:var(--cor-primaria)}
  .db-pat-rows{display:flex;flex-direction:column;gap:.7rem}
  .db-pat-row{display:grid;grid-template-columns:auto minmax(90px,1fr) 2fr auto;align-items:center;gap:.7rem}
  .db-pat-ico{width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;
    font-size:1rem;border:1px solid}
  .db-pat-cat{font-size:.88rem;color:var(--cor-primaria);font-weight:600}
  .db-pat-q{display:inline-block;min-width:18px;text-align:center;font-size:.72rem;font-weight:600;
    color:var(--cor-secundario);background:var(--cor-fundo,#f4f1ea);border-radius:999px;padding:0 .4rem;margin-left:.15rem}
  .db-pat-track{height:8px;border-radius:6px;background:var(--cor-fundo,#f0ece3);overflow:hidden}
  .db-pat-fill{display:block;height:100%;border-radius:6px;transition:width .5s ease}
  .db-pat-v{font-weight:700;font-size:.92rem;color:var(--cor-primaria);white-space:nowrap;text-align:right}
  @media (max-width:560px){
    .db-pat-row{grid-template-columns:auto 1fr auto;grid-template-areas:"ico cat v" "track track track"}
    .db-pat-ico{grid-area:ico}.db-pat-cat{grid-area:cat}.db-pat-v{grid-area:v}
    .db-pat-track{grid-area:track;height:7px}
  }

  /* Indicadores executivos do cliente (Módulo 15) */
  .db-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.7rem;margin-bottom:1.6rem}
  .db-kpi{display:block;text-decoration:none;color:inherit;background:var(--cor-branco,#fff);
    border:1px solid var(--cor-borda,#e3e8ef);border-radius:14px;padding:.85rem 1rem;
    box-shadow:0 2px 8px rgba(0,0,0,.04);transition:box-shadow .2s,transform .2s}
  .db-kpi:hover{box-shadow:0 8px 18px rgba(0,0,0,.09);transform:translateY(-2px);text-decoration:none}
  .db-kpi-top{font-size:.76rem;font-weight:700;letter-spacing:.02em;text-transform:uppercase;color:var(--cor-secundario)}
  .db-kpi-n{font-family:var(--fonte-titulo);font-size:1.35rem;font-weight:800;color:var(--cor-primaria);
    line-height:1.1;margin:.3rem 0 .15rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .db-kpi-sub{font-size:.78rem;color:var(--cor-secundario)}
  .db-kpi-warn{color:#b45309;font-weight:600}
</style>
<?php require APP_ROOT . '/includes/footer.php'; ?>
