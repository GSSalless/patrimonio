<?php
/**
 * View do dashboard. Reaproveita o header/footer legados (topo, seletor de
 * cliente e navegação). O header define $usuario, $cliente_sel e $clientes_lista.
 *
 * @var int        $total_clientes
 * @var int        $total_imoveis
 * @var array|null $cli
 * @var int        $qtd_imoveis
 */
$page_title = 'Dashboard — Gestão Patrimonial';
require APP_ROOT . '/includes/header.php';
?>
<div class="container">

  <?php if ($usuario['nivel'] === 'admin' && !$cliente_sel): ?>
  <!-- Visão admin sem cliente selecionado -->
  <div class="card">
    <div class="card-header">
      <span class="card-titulo">Painel Administrativo</span>
    </div>
    <div class="form-grid form-grid-3" style="gap:1rem">
      <div style="background:#faf7ef;padding:1.25rem;border-radius:8px;text-align:center">
        <div style="font-size:2rem;font-weight:800;color:var(--cor-primaria)"><?= $total_clientes ?></div>
        <div style="color:var(--cor-secundario);font-size:.9rem">Clientes ativos</div>
      </div>
      <div style="background:#faf7ef;padding:1.25rem;border-radius:8px;text-align:center">
        <div style="font-size:2rem;font-weight:800;color:var(--cor-primaria)"><?= $total_imoveis ?></div>
        <div style="color:var(--cor-secundario);font-size:.9rem">Imóveis cadastrados</div>
      </div>
    </div>
    <div style="margin-top:1.25rem">
      <p style="color:var(--cor-secundario);font-size:.9rem">
        Selecione um cliente no topo para ver seu patrimônio, ou
        <a href="<?= base_url('clientes') ?>">acesse a lista de clientes</a>.
      </p>
    </div>
  </div>

  <?php else: ?>
  <!-- Visão com cliente selecionado: menu de módulos -->
  <div style="margin-bottom:1.5rem">
    <h2 style="font-size:1.2rem;color:var(--cor-primaria)"><?= h($cli['nome']) ?></h2>
    <div style="font-size:.85rem;color:var(--cor-secundario)"><?= h($cli['tipo_pessoa'] === 'PF' ? 'CPF: ' : 'CNPJ: ') . h($cli['cpf_cnpj']) ?></div>
  </div>

  <div class="app-grid">
    <a href="<?= base_url('patrimonio') ?>" class="app-icon">
      <span class="app-icon-tile app-tile-azul">
        🏛️
        <?php if ($qtd_imoveis > 0): ?><span class="app-icon-badge"><?= $qtd_imoveis ?></span><?php endif; ?>
      </span>
      <span class="app-icon-label">Patrimônios</span>
    </a>

    <span class="app-icon app-icon-off">
      <span class="app-icon-tile app-tile-verde">💰</span>
      <span class="app-icon-label">Caixa</span>
    </span>

    <span class="app-icon app-icon-off">
      <span class="app-icon-tile app-tile-laranja">✅</span>
      <span class="app-icon-label">Tarefas</span>
    </span>

    <span class="app-icon app-icon-off">
      <span class="app-icon-tile app-tile-roxo">📊</span>
      <span class="app-icon-label">Caixa Geral</span>
    </span>
  </div>
  <?php endif; ?>

</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
