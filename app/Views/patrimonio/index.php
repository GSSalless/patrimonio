<?php
/**
 * @var array $cli
 * @var int   $qtd_imoveis
 * @var int   $qtd_veiculos
 * @var int   $qtd_outros
 */
$page_title = 'Patrimônio';
require APP_ROOT . '/includes/header.php';
?>
<div class="container">

  <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem">
    <a href="<?= base_url('dashboard') ?>" class="btn btn-secundario">← Voltar</a>
    <div>
      <h2 style="font-size:1.2rem;color:var(--cor-primaria)">Patrimônio — <?= h($cli['nome']) ?></h2>
      <div style="font-size:.85rem;color:var(--cor-secundario)">Selecione a categoria de bens</div>
    </div>
  </div>

  <div class="app-grid">
    <a href="<?= base_url('imoveis') ?>" class="app-icon">
      <span class="app-icon-tile app-tile-azul">
        🏠
        <?php if ($qtd_imoveis > 0): ?><span class="app-icon-badge"><?= $qtd_imoveis ?></span><?php endif; ?>
      </span>
      <span class="app-icon-label">Imóveis</span>
    </a>

    <a href="<?= base_url('veiculos') ?>" class="app-icon">
      <span class="app-icon-tile app-tile-verde">
        🚗
        <?php if ($qtd_veiculos > 0): ?><span class="app-icon-badge"><?= $qtd_veiculos ?></span><?php endif; ?>
      </span>
      <span class="app-icon-label">Carros</span>
    </a>

    <a href="<?= base_url('outros') ?>" class="app-icon">
      <span class="app-icon-tile app-tile-roxo">
        🛥️
        <?php if ($qtd_outros > 0): ?><span class="app-icon-badge"><?= $qtd_outros ?></span><?php endif; ?>
      </span>
      <span class="app-icon-label">Outros</span>
    </a>
  </div>

</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
