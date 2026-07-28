<?php
/**
 * Dashboard — visão de UM cliente (hub de módulos).
 * Admin sem cliente selecionado é redirecionado para a Gestão Geral no controller.
 *
 * @var array|null $cli
 * @var int        $qtd_imoveis
 * @var int        $qtd_contas
 */
$page_title = 'Dashboard — Gestão Patrimonial';
require APP_ROOT . '/includes/header.php';
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
