<?php
/**
 * Gestão Geral — visão consolidada do gestor (todos os clientes).
 * @var int   $total_clientes, $total_imoveis, $total_contas
 * @var float $valor_imoveis, $saldo_contas
 * @var array $clientes
 */
$page_title = 'Gestão Geral';
require APP_ROOT . '/includes/header.php';
?>
<div class="container">

  <div style="margin-bottom:1.25rem">
    <h2 style="font-size:1.4rem;color:var(--cor-primaria);font-family:var(--fonte-titulo)">Gestão Geral</h2>
    <div style="font-size:.88rem;color:var(--cor-secundario)">Visão consolidada de todos os clientes</div>
  </div>

  <!-- KPIs -->
  <div class="gg-kpis">
    <div class="gg-kpi">
      <div class="gg-kpi-n"><?= $total_clientes ?></div>
      <div class="gg-kpi-l">Clientes ativos</div>
    </div>
    <div class="gg-kpi">
      <div class="gg-kpi-n"><?= $total_imoveis ?></div>
      <div class="gg-kpi-l">Imóveis cadastrados</div>
    </div>
    <div class="gg-kpi">
      <div class="gg-kpi-n gg-kpi-money"><?= moeda($valor_imoveis) ?></div>
      <div class="gg-kpi-l">Patrimônio imobiliário (valor de mercado)</div>
    </div>
    <div class="gg-kpi">
      <div class="gg-kpi-n gg-kpi-money"><?= moeda($saldo_contas) ?></div>
      <div class="gg-kpi-l">Saldo consolidado em contas (<?= $total_contas ?>)</div>
    </div>
  </div>

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
          <div class="gg-cli-meta">🏛️ <?= (int)$c['qtd_imoveis'] ?> imóvel(is)</div>
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
  .gg-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:1rem}
  .gg-kpi{background:var(--cor-branco,#fff);border:1px solid var(--cor-borda,#e3e8ef);border-radius:14px;
    padding:1.15rem 1.25rem;box-shadow:0 3px 10px rgba(0,0,0,.05)}
  .gg-kpi-n{font-family:var(--fonte-titulo);font-size:1.9rem;font-weight:800;color:var(--cor-primaria);line-height:1.1}
  .gg-kpi-money{font-size:1.35rem}
  .gg-kpi-l{font-size:.8rem;color:var(--cor-secundario);margin-top:.3rem;line-height:1.25}

  .gg-clientes{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:.9rem}
  .gg-cli-card{display:flex;align-items:center;gap:.85rem;padding:1rem 1.1rem;text-decoration:none;color:inherit;
    background:var(--cor-branco,#fff);border:1px solid var(--cor-borda,#e3e8ef);border-radius:14px;
    box-shadow:0 3px 10px rgba(0,0,0,.05);transition:box-shadow .2s,transform .2s}
  .gg-cli-card:hover{box-shadow:0 8px 22px rgba(0,0,0,.1);transform:translateY(-2px);text-decoration:none}
  .gg-cli-avatar{width:46px;height:46px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;
    font-weight:700;font-size:1.25rem;color:#fff;background:linear-gradient(135deg,#1a1a1e,#3a3a42);border:1px solid rgba(212,175,55,.4)}
  .gg-cli-nome{font-weight:700;font-size:1.02rem;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .gg-cli-sub{color:var(--cor-secundario);font-size:.82rem;margin-top:.2rem}
  .gg-cli-meta{color:var(--cor-secundario);font-size:.8rem;margin-top:.25rem}
</style>
<?php require APP_ROOT . '/includes/footer.php'; ?>
