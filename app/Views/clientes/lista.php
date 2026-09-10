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

<style>
  .pessoas-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1rem}
  .pessoa-card{background:#fff;border:1px solid var(--cor-borda,#e3e8ef);border-radius:14px;overflow:hidden;
    display:flex;flex-direction:column;box-shadow:0 3px 10px rgba(0,0,0,.05);transition:box-shadow .2s,transform .2s}
  .pessoa-card:hover{box-shadow:0 8px 22px rgba(0,0,0,.1);transform:translateY(-2px)}
  .pessoa-card-corpo{display:flex;gap:.85rem;padding:1.1rem;text-decoration:none;color:inherit;align-items:flex-start}
  .pessoa-avatar{width:48px;height:48px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;
    font-weight:700;font-size:1.3rem;color:#fff;background:linear-gradient(135deg,#1a3a5c,#2e7dd1)}
  .pessoa-nome{font-weight:700;font-size:1.05rem;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .pessoa-doc{color:var(--cor-secundario);font-size:.85rem;margin-top:.2rem}
  .pessoa-linha{color:var(--cor-secundario);font-size:.82rem;margin-top:.25rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .pessoa-card-acoes{display:flex;justify-content:space-between;align-items:center;gap:.5rem;
    padding:.6rem 1.1rem;border-top:1px solid var(--cor-borda,#eef1f5);background:#fafbfc}
  /* Botão WhatsApp: só o ícone, redondo e verde */
  .pessoa-wa{display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;flex-shrink:0;
    border-radius:50%;background:#25d366;color:#fff;font-size:1.15rem;text-decoration:none;line-height:1;
    box-shadow:0 2px 6px rgba(37,211,102,.35);transition:background .18s,transform .18s,box-shadow .18s}
  .pessoa-wa:hover{background:#1ebe5b;transform:translateY(-1px);box-shadow:0 5px 14px rgba(37,211,102,.45);text-decoration:none;color:#fff}
  .pessoa-wa:active{transform:translateY(0)}
  .pessoa-wa i{display:block}
</style>
<?php require APP_ROOT . '/includes/footer.php'; ?>
