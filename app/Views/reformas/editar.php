<?php
/**
 * @var array  $r
 * @var array  $d
 * @var string $erro
 */
$page_title = 'Editar Reforma';
require APP_ROOT . '/includes/header.php';
if (!function_exists('mf')) {
    function mf(float $v): string { return $v ? number_format($v, 2, ',', '.') : ''; }
}
?>
<div class="container" style="max-width:720px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <h2 class="card-titulo">Editar Reforma</h2>
    <a href="<?= base_url('imoveis/ficha?id='.$r['imovel_id'].'&aba=reformas') ?>" class="btn btn-secundario">← Voltar</a>
  </div>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>
  <div class="card">
    <form method="post">
      <div class="form-grupo"><label>Descrição *</label><input type="text" name="descricao" required value="<?= h($d['descricao']) ?>"></div>
      <div class="form-grid form-grid-2">
        <div class="form-grupo"><label>Status</label>
          <select name="status">
            <?php foreach(['planejado'=>'Planejado','em_andamento'=>'Em andamento','concluido'=>'Concluído','cancelado'=>'Cancelado'] as $v=>$l): ?>
            <option value="<?=$v?>" <?=$d['status']===$v?'selected':''?>><?=$l?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo"><label>Fornecedor</label><input type="text" name="fornecedor" value="<?= h($d['fornecedor']??'') ?>"></div>
        <div class="form-grupo"><label>Data de início</label><input type="date" name="data_inicio" value="<?= h($d['data_inicio']??'') ?>"></div>
        <div class="form-grupo"><label>Data fim prevista</label><input type="date" name="data_fim_prevista" value="<?= h($d['data_fim_prevista']??'') ?>"></div>
        <div class="form-grupo"><label>Data fim real</label><input type="date" name="data_fim_real" value="<?= h($d['data_fim_real']??'') ?>"></div>
        <div class="form-grupo"><label>Custo previsto (R$)</label><input type="text" name="custo_previsto" value="<?= mf((float)($d['custo_previsto']??0)) ?>"></div>
        <div class="form-grupo"><label>Custo realizado (R$)</label><input type="text" name="custo_realizado" value="<?= mf((float)($d['custo_realizado']??0)) ?>"></div>
      </div>
      <div class="form-grupo"><label>Observações</label><textarea name="observacoes"><?= h($d['observacoes']??'') ?></textarea></div>
      <div style="display:flex;gap:.75rem;margin-top:1rem">
        <button type="submit" class="btn btn-primario">Salvar alterações</button>
        <a href="<?= base_url('imoveis/ficha?id='.$r['imovel_id'].'&aba=reformas') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
