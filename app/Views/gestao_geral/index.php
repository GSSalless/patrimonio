<?php
/**
 * Gestão Geral — visão consolidada do gestor (todos os clientes).
 * @var int   $total_clientes
 * @var array $pat        patrimônio consolidado (patrimonio_consolidado())
 * @var array $clientes   cada um com ['patrimonio'] embutido
 */
$page_title = 'Gestão Geral';
require APP_ROOT . '/includes/header.php';

// Composição do patrimônio para a barra empilhada + legenda.
$comp = [
    ['label' => 'Imóveis',  'emoji' => '🏛️', 'valor' => $pat['imoveis_valor'],  'qtd' => $pat['imoveis_qtd'],  'cor' => '#c9a227'],
    ['label' => 'Veículos', 'emoji' => '🚗', 'valor' => $pat['veiculos_valor'], 'qtd' => $pat['veiculos_qtd'], 'cor' => '#5b8def'],
    ['label' => 'Outros bens', 'emoji' => '💎', 'valor' => $pat['outros_valor'], 'qtd' => $pat['outros_qtd'], 'cor' => '#9b6dd6'],
    ['label' => 'Investimentos', 'emoji' => '📈', 'valor' => $pat['invest_valor'], 'qtd' => $pat['invest_qtd'], 'cor' => '#e0669e'],
    ['label' => 'Contas',   'emoji' => '🏦', 'valor' => $pat['contas_saldo'],  'qtd' => $pat['contas_qtd'],  'cor' => '#3fae7a'],
];
$total_pat = (float) $pat['total'];
?>
<div class="container">

  <div style="margin-bottom:1.25rem">
    <h2 style="font-size:1.4rem;color:var(--cor-primaria);font-family:var(--fonte-titulo)">Gestão Geral</h2>
    <div style="font-size:.88rem;color:var(--cor-secundario)">Visão consolidada de todos os clientes</div>
  </div>

  <!-- Patrimônio total consolidado -->
  <div class="gg-hero">
    <div class="gg-hero-top">
      <div>
        <div class="gg-hero-l">Patrimônio total sob gestão</div>
        <div class="gg-hero-n"><?= moeda($total_pat) ?></div>
      </div>
      <div class="gg-hero-badge"><?= $total_clientes ?> cliente<?= $total_clientes == 1 ? '' : 's' ?></div>
    </div>

    <?php if ($total_pat > 0): ?>
    <div class="gg-bar">
      <?php foreach ($comp as $seg): if ($seg['valor'] <= 0) continue;
        $pct = round($seg['valor'] / $total_pat * 100, 1); ?>
        <div class="gg-bar-seg" style="width:<?= $pct ?>%;background:<?= $seg['cor'] ?>"
             title="<?= h($seg['label']) ?>: <?= moeda($seg['valor']) ?> (<?= $pct ?>%)"></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="gg-legend">
      <?php foreach ($comp as $seg):
        $pct = $total_pat > 0 ? round($seg['valor'] / $total_pat * 100) : 0; ?>
        <div class="gg-leg">
          <span class="gg-leg-dot" style="background:<?= $seg['cor'] ?>"></span>
          <div>
            <div class="gg-leg-top"><?= $seg['emoji'] ?> <?= h($seg['label']) ?> <span class="gg-leg-qtd">· <?= $seg['qtd'] ?></span></div>
            <div class="gg-leg-val"><?= moeda($seg['valor']) ?> <span class="gg-leg-pct"><?= $pct ?>%</span></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Indicadores executivos (Módulo 15): RH, Contratos, Seguros, Financeiro -->
  <div class="gg-kpis">
    <a class="gg-kpi" href="<?= base_url('agenda') ?>">
      <div class="gg-kpi-top"><span class="gg-kpi-ico">💰</span> Financeiro</div>
      <div class="gg-kpi-n"><?= moeda((float) ($ind['financeiro']['contas_saldo'] + $ind['financeiro']['invest_valor'])) ?></div>
      <div class="gg-kpi-sub">
        🏦 <?= (int) $ind['financeiro']['contas_qtd'] ?> conta<?= $ind['financeiro']['contas_qtd'] == 1 ? '' : 's' ?>
        · 📈 <?= (int) $ind['financeiro']['invest_qtd'] ?> aplicaç<?= $ind['financeiro']['invest_qtd'] == 1 ? 'ão' : 'ões' ?>
      </div>
    </a>

    <a class="gg-kpi" href="<?= base_url('colaboradores') ?>">
      <div class="gg-kpi-top"><span class="gg-kpi-ico">👔</span> RH</div>
      <div class="gg-kpi-n"><?= (int) $ind['rh']['colaboradores'] ?></div>
      <div class="gg-kpi-sub">
        colaborador<?= $ind['rh']['colaboradores'] == 1 ? '' : 'es' ?> ativo<?= $ind['rh']['colaboradores'] == 1 ? '' : 's' ?>
        <?php if ($ind['rh']['ferias'] || $ind['rh']['treinamentos']): ?>
          <br><span class="gg-kpi-flag">🏖️ <?= (int) $ind['rh']['ferias'] ?> férias · 🎓 <?= (int) $ind['rh']['treinamentos'] ?> treino</span>
        <?php endif; ?>
      </div>
    </a>

    <a class="gg-kpi" href="<?= base_url('contratos') ?>">
      <div class="gg-kpi-top"><span class="gg-kpi-ico">📜</span> Contratos</div>
      <div class="gg-kpi-n"><?= (int) $ind['contratos']['ativos'] ?></div>
      <div class="gg-kpi-sub">
        ativo<?= $ind['contratos']['ativos'] == 1 ? '' : 's' ?>
        <?php if ($ind['contratos']['vencendo']): ?>
          <br><span class="gg-kpi-flag gg-kpi-warn">⏱ <?= (int) $ind['contratos']['vencendo'] ?> vencendo em 30d</span>
        <?php endif; ?>
      </div>
    </a>

    <a class="gg-kpi" href="<?= base_url('seguros') ?>">
      <div class="gg-kpi-top"><span class="gg-kpi-ico">🛡️</span> Seguros</div>
      <div class="gg-kpi-n"><?= (int) $ind['seguros']['vigentes'] ?></div>
      <div class="gg-kpi-sub">
        vigente<?= $ind['seguros']['vigentes'] == 1 ? '' : 's' ?>
        <?php if ($ind['seguros']['vencendo']): ?>
          <br><span class="gg-kpi-flag gg-kpi-warn">⏱ <?= (int) $ind['seguros']['vencendo'] ?> vencendo em 30d</span>
        <?php endif; ?>
      </div>
    </a>
  </div>

  <?php if (($alertas['urgentes'] ?? 0) > 0): ?>
  <a class="gg-alertas" href="<?= base_url('agenda') ?>">
    <span class="gg-alertas-ico">🔔</span>
    <div style="flex:1;min-width:0">
      <div class="gg-alertas-tit">
        <?= $alertas['urgentes'] ?> vencimento<?= $alertas['urgentes'] == 1 ? '' : 's' ?> exige<?= $alertas['urgentes'] == 1 ? '' : 'm' ?> atenção
      </div>
      <div class="gg-alertas-sub">
        <?php if ($alertas['vencidos'] > 0): ?><b><?= $alertas['vencidos'] ?> vencido<?= $alertas['vencidos'] == 1 ? '' : 's' ?></b><?php endif; ?>
        <?php if ($alertas['vencidos'] > 0 && $alertas['proximos'] > 0): ?> · <?php endif; ?>
        <?php if ($alertas['proximos'] > 0): ?><?= $alertas['proximos'] ?> nos próximos 30 dias<?php endif; ?>
      </div>
    </div>
    <span class="gg-alertas-cta">Ver agenda →</span>
  </a>
  <?php endif; ?>

  <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;margin:2rem 0 1rem">
    <h3 style="font-size:1.1rem;color:var(--cor-primaria)">Clientes</h3>
    <a href="<?= base_url('clientes/novo') ?>" class="btn btn-primario btn-sm">+ Nova pessoa</a>
  </div>

  <?php if ($clientes): ?>
    <div class="gg-clientes">
      <?php foreach ($clientes as $c):
        $nome = $c['nome_completo'] ?: $c['nome'];
        $ini  = mb_strtoupper(mb_substr(trim($c['nome']), 0, 1));
      ?>
      <a class="gg-cli-card" href="<?= base_url('dashboard?cliente_id=' . $c['id']) ?>">
        <div class="gg-cli-avatar"><?= h($ini) ?></div>
        <div style="min-width:0;flex:1">
          <div class="gg-cli-nome"><?= h($nome) ?></div>
          <div class="gg-cli-sub"><span class="tag"><?= $c['tipo_pessoa'] ?></span> <?= h($c['cpf_cnpj']) ?></div>
          <div class="gg-cli-pat"><?= moeda($c['patrimonio']['total']) ?></div>
          <div class="gg-cli-meta">🏛️ <?= (int)$c['patrimonio']['imoveis_qtd'] ?> · 🚗 <?= (int)$c['patrimonio']['veiculos_qtd'] ?> · 💎 <?= (int)$c['patrimonio']['outros_qtd'] ?> · 📈 <?= (int)$c['patrimonio']['invest_qtd'] ?> · 🏦 <?= (int)$c['patrimonio']['contas_qtd'] ?></div>
        </div>
        <i class="bi bi-chevron-right" style="color:var(--cor-secundario)"></i>
      </a>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="card"><p style="color:var(--cor-secundario);text-align:center;padding:2rem">Nenhum cliente cadastrado. <a href="<?= base_url('clientes/novo') ?>">Cadastrar o primeiro</a>.</p></div>
  <?php endif; ?>

</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
