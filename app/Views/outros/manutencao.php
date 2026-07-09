<?php
/**
 * @var array  $ob
 * @var int    $bem_id
 * @var array|null $mt
 * @var array  $d
 * @var string $erro
 */
$page_title = ($mt ? 'Editar' : 'Nova') . ' Manutenção do Bem';
require APP_ROOT . '/includes/header.php';
$tipos = ['revisao'=>'Revisão','motor'=>'Motor','casco'=>'Casco','pintura'=>'Pintura','eletrica'=>'Elétrica','limpeza'=>'Limpeza','peca'=>'Troca de peça','outro'=>'Outro'];
$val = fn($k) => h($d[$k] ?? '');
$mv  = fn($k) => isset($d[$k]) && $d[$k] !== '' && $d[$k] !== null ? number_format((float)$d[$k], 2, ',', '.') : '';
$acao = base_url('outros/manutencao?bem_id=' . $bem_id . ($mt ? '&id=' . $mt['id'] : ''));
?>
<div class="container" style="max-width:720px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <h2 class="card-titulo"><?= $mt ? 'Editar' : 'Nova' ?> Manutenção — <?= h($ob['nome']) ?></h2>
    <a href="<?= base_url('outros/editar?id='.$bem_id.'#manutencoes') ?>" class="btn btn-secundario">← Voltar</a>
  </div>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>
  <div class="card">
    <form method="post" action="<?= $acao ?>">
      <div class="form-grupo">
        <label>Descrição *</label>
        <input type="text" name="descricao" required placeholder="Ex.: Revisão do motor / pintura do casco" value="<?= $val('descricao') ?>">
      </div>
      <div class="form-grid form-grid-3">
        <div class="form-grupo"><label>Tipo</label>
          <select name="tipo">
            <?php foreach($tipos as $v=>$l): ?>
            <option value="<?=$v?>" <?= (($d['tipo']??'revisao')===$v)?'selected':'' ?>><?=$l?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo"><label>Data *</label><input type="date" name="data" required value="<?= $val('data') ?: date('Y-m-d') ?>"></div>
        <div class="form-grupo"><label>Horímetro</label><input type="number" name="horimetro" min="0" value="<?= $val('horimetro') ?>"></div>
        <div class="form-grupo"><label>Fornecedor / estaleiro</label><input type="text" name="fornecedor" value="<?= $val('fornecedor') ?>"></div>
        <div class="form-grupo"><label>Valor (R$)</label><input type="text" name="valor" placeholder="0,00" value="<?= $mv('valor') ?>"></div>
        <div class="form-grupo"><label>Garantia até</label><input type="date" name="garantia_ate" value="<?= $val('garantia_ate') ?>"></div>
        <div class="form-grupo"><label>Próxima revisão</label><input type="date" name="proxima_data" value="<?= $val('proxima_data') ?>"></div>
      </div>
      <div class="form-grupo"><label>Observações</label><textarea name="observacoes"><?= $val('observacoes') ?></textarea></div>
      <div style="display:flex;gap:.75rem;margin-top:1rem">
        <button type="submit" class="btn btn-primario">Salvar manutenção</button>
        <a href="<?= base_url('outros/editar?id='.$bem_id.'#manutencoes') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
