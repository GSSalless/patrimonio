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

    <a href="<?= base_url('seguros') ?>" class="app-icon">
      <span class="app-icon-tile app-tile-azul">
        🛡️
        <?php if (($qtd_seguros ?? 0) > 0): ?><span class="app-icon-badge"><?= $qtd_seguros ?></span><?php endif; ?>
      </span>
      <span class="app-icon-label">Seguros</span>
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
</style>
<?php require APP_ROOT . '/includes/footer.php'; ?>
