<?php
/**
 * @var array  $ob
 * @var int    $bem_id
 * @var array|null $av
 * @var array  $d
 * @var string $erro
 */
$page_title = ($av ? 'Editar' : 'Nova') . ' Avaliação';
require APP_ROOT . '/includes/header.php';
$val = fn($k) => h($d[$k] ?? '');
$mv  = fn($k) => isset($d[$k]) && $d[$k] !== '' && $d[$k] !== null ? number_format((float)$d[$k], 2, ',', '.') : '';
$acao = base_url('outros/avaliacao?bem_id=' . $bem_id . ($av ? '&id=' . $av['id'] : ''));
?>
<div class="container" style="max-width:600px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <h2 class="card-titulo"><?= $av ? 'Editar' : 'Nova' ?> Avaliação — <?= h($ob['nome']) ?></h2>
    <a href="<?= base_url('outros/editar?id='.$bem_id.'#avaliacoes') ?>" class="btn btn-secundario">← Voltar</a>
  </div>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>
  <div class="card">
    <p style="color:var(--cor-secundario);font-size:.88rem;margin:-.25rem 0 1rem">A avaliação mais recente atualiza automaticamente o <strong>valor de mercado</strong> do bem.</p>
    <form method="post" action="<?= $acao ?>">
      <div class="form-grid form-grid-2">
        <div class="form-grupo"><label>Data *</label><input type="date" name="data" required value="<?= $val('data') ?: date('Y-m-d') ?>"></div>
        <div class="form-grupo"><label>Valor avaliado (R$) *</label><input type="text" name="valor" required placeholder="0,00" value="<?= $mv('valor') ?>"></div>
        <div class="form-grupo" style="grid-column:1/-1"><label>Fonte</label><input type="text" name="fonte" placeholder="Ex.: Laudo perito, corretor, leilão de referência" value="<?= $val('fonte') ?>"></div>
      </div>
      <div class="form-grupo"><label>Observações</label><textarea name="observacoes"><?= $val('observacoes') ?></textarea></div>
      <div style="display:flex;gap:.75rem;margin-top:1rem">
        <button type="submit" class="btn btn-primario">Salvar avaliação</button>
        <a href="<?= base_url('outros/editar?id='.$bem_id.'#avaliacoes') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
