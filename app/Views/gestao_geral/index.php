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

<style>
  /* Herói — patrimônio consolidado (preto & dourado) */
  .gg-hero{background:linear-gradient(135deg,#1a1a1e,#2a2a30);border:1px solid rgba(212,175,55,.35);
    border-radius:18px;padding:1.4rem 1.5rem;box-shadow:0 8px 26px rgba(0,0,0,.18);color:#f3f0e6}
  .gg-hero-top{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap}
  .gg-hero-l{font-size:.82rem;letter-spacing:.04em;text-transform:uppercase;color:#c9a227}
  .gg-hero-n{font-family:var(--fonte-titulo);font-size:2.3rem;font-weight:800;line-height:1.1;margin-top:.15rem;
    color:#fff}
  .gg-hero-badge{background:rgba(212,175,55,.15);border:1px solid rgba(212,175,55,.4);color:#e6cf7a;
    font-size:.8rem;font-weight:600;padding:.35rem .7rem;border-radius:999px;white-space:nowrap}
  .gg-bar{display:flex;height:14px;border-radius:8px;overflow:hidden;margin:1.15rem 0 .1rem;
    background:rgba(255,255,255,.08)}
  .gg-bar-seg{height:100%}
  .gg-bar-seg + .gg-bar-seg{box-shadow:inset 1px 0 0 rgba(0,0,0,.25)}
  .gg-legend{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.65rem 1.2rem;margin-top:1.1rem}
  .gg-leg{display:flex;align-items:flex-start;gap:.5rem}
  .gg-leg-dot{width:11px;height:11px;border-radius:3px;flex-shrink:0;margin-top:.32rem}
  .gg-leg-top{font-size:.82rem;color:#cfcbbe}
  .gg-leg-qtd{color:#8f8b80}
  .gg-leg-val{font-size:.98rem;font-weight:700;color:#fff;margin-top:.1rem}
  .gg-leg-pct{font-size:.78rem;font-weight:500;color:#c9a227;margin-left:.2rem}

  /* Faixa de alertas */
  .gg-alertas{display:flex;align-items:center;gap:.9rem;margin-top:1.4rem;padding:.9rem 1.1rem;
    text-decoration:none;color:inherit;background:#fffbeb;border:1px solid #fcd34d;border-left:4px solid #d97706;
    border-radius:12px;transition:box-shadow .18s,transform .18s}
  .gg-alertas:hover{box-shadow:0 8px 20px rgba(217,119,6,.15);transform:translateY(-1px);text-decoration:none}
  .gg-alertas-ico{font-size:1.5rem;flex-shrink:0}
  .gg-alertas-tit{font-weight:700;color:#92400e;font-size:.98rem}
  .gg-alertas-sub{font-size:.83rem;color:#b45309;margin-top:.1rem}
  .gg-alertas-cta{flex-shrink:0;font-weight:600;font-size:.85rem;color:#b45309;white-space:nowrap}

  .gg-clientes{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:.9rem}
  .gg-cli-card{display:flex;align-items:center;gap:.85rem;padding:1rem 1.1rem;text-decoration:none;color:inherit;
    background:var(--cor-branco,#fff);border:1px solid var(--cor-borda,#e3e8ef);border-radius:14px;
    box-shadow:0 3px 10px rgba(0,0,0,.05);transition:box-shadow .2s,transform .2s}
  .gg-cli-card:hover{box-shadow:0 8px 22px rgba(0,0,0,.1);transform:translateY(-2px);text-decoration:none}
  .gg-cli-avatar{width:46px;height:46px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;
    font-weight:700;font-size:1.25rem;color:#fff;background:linear-gradient(135deg,#1a1a1e,#3a3a42);border:1px solid rgba(212,175,55,.4)}
  .gg-cli-nome{font-weight:700;font-size:1.02rem;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .gg-cli-sub{color:var(--cor-secundario);font-size:.82rem;margin-top:.2rem}
  .gg-cli-pat{font-family:var(--fonte-titulo);font-weight:800;font-size:1.05rem;color:var(--cor-primaria);margin-top:.4rem}
  .gg-cli-meta{color:var(--cor-secundario);font-size:.8rem;margin-top:.15rem}
</style>
<?php require APP_ROOT . '/includes/footer.php'; ?>
