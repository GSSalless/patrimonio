<?php
/**
 * @var array $clientes
 */
$page_title = 'Clientes';
require APP_ROOT . '/includes/header.php';
?>
<div class="container">
  <div class="card-header" style="margin-bottom:1.25rem">
    <h2 class="card-titulo" style="font-size:1.3rem">Pessoas / Clientes</h2>
    <a href="<?= base_url('clientes/novo') ?>" class="btn btn-primario">+ Nova pessoa</a>
  </div>

  <?php if ($clientes): ?>
    <div class="pessoas-grid">
      <?php foreach ($clientes as $c):
        $nome = $c['nome_completo'] ?: $c['nome'];
        $wa   = link_whatsapp($c['telefone'] ?? '');
        $ini  = mb_strtoupper(mb_substr(trim($c['nome']), 0, 1));
      ?>
      <div class="pessoa-card">
        <a class="pessoa-card-corpo" href="<?= base_url('clientes/editar?id=' . $c['id']) ?>">
          <div class="pessoa-avatar"><?= h($ini) ?></div>
          <div style="min-width:0;flex:1">
            <div class="pessoa-nome"><?= h($nome) ?></div>
            <div class="pessoa-doc"><span class="tag"><?= $c['tipo_pessoa'] ?></span> <?= h($c['cpf_cnpj']) ?></div>
            <?php if ($c['telefone']): ?><div class="pessoa-linha"><i class="bi bi-telephone"></i> <?= h($c['telefone']) ?></div><?php endif; ?>
            <?php if ($c['email']): ?><div class="pessoa-linha"><i class="bi bi-envelope"></i> <?= h($c['email']) ?></div><?php endif; ?>
          </div>
        </a>
        <div class="pessoa-card-acoes">
          <?php if ($wa): ?>
            <a href="<?= h($wa) ?>" target="_blank" rel="noopener" class="pessoa-wa"
               title="Conversar no WhatsApp" aria-label="Conversar no WhatsApp">
              <i class="bi bi-whatsapp"></i>
            </a>
          <?php else: ?><span></span><?php endif; ?>
          <a href="<?= base_url('dashboard?cliente_id=' . $c['id']) ?>" class="btn btn-secundario btn-sm">Patrimônio →</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="card"><p style="color:var(--cor-secundario);text-align:center;padding:2rem">Nenhuma pessoa cadastrada.</p></div>
  <?php endif; ?>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
