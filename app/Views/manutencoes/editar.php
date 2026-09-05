<?php
/**
 * @var array  $m
 * @var array  $d
 * @var string $erro
 */
$page_title = 'Editar Manutenção';
require APP_ROOT . '/includes/header.php';
$imovel_id = (int) $m['imovel_id'];
$tipos = ['eletrica'=>'Elétrica','hidraulica'=>'Hidráulica','pintura'=>'Pintura','ar_condicionado'=>'Ar-condicionado','estrutural'=>'Estrutural','jardim'=>'Jardim/Área externa','elevador'=>'Elevador','seguranca'=>'Segurança','eletrodomestico'=>'Eletrodoméstico','outro'=>'Outro'];
$val = fn($k) => h($d[$k] ?? '');
?>
<div class="container" style="max-width:720px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <h2 class="card-titulo">Editar Manutenção</h2>
    <a href="<?= base_url('imoveis/ficha?id='.$imovel_id.'&aba=manutencoes') ?>" class="btn btn-secundario">← Voltar</a>
  </div>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>
  <div class="card">
    <form method="post">
      <div class="form-grupo">
        <label>Descrição *</label>
        <input type="text" name="descricao" required value="<?= $val('descricao') ?>">
      </div>
      <div class="form-grid form-grid-2">
        <div class="form-grupo"><label>Tipo</label>
          <select name="tipo">
            <?php foreach($tipos as $v=>$l): ?>
            <option value="<?=$v?>" <?= (($d['tipo']??'outro')===$v)?'selected':'' ?>><?=$l?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo"><label>Data *</label><input type="date" name="data" required value="<?= $val('data') ?>"></div>
        <div class="form-grupo"><label>Fornecedor / prestador</label><input type="text" name="fornecedor" value="<?= $val('fornecedor') ?>"></div>
        <div class="form-grupo"><label>Valor (R$)</label><input type="text" name="valor" placeholder="0,00" value="<?= $d['valor'] ? number_format((float)$d['valor'],2,',','.') : '' ?>"></div>
        <div class="form-grupo"><label>Garantia até</label><input type="date" name="garantia_ate" value="<?= $val('garantia_ate') ?>"></div>
      </div>
      <div class="form-grupo"><label>Observações</label><textarea name="observacoes"><?= $val('observacoes') ?></textarea></div>
      <div style="display:flex;gap:.75rem;margin-top:1rem">
        <button type="submit" class="btn btn-primario">Salvar alterações</button>
        <a href="<?= base_url('imoveis/ficha?id='.$imovel_id.'&aba=manutencoes') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
