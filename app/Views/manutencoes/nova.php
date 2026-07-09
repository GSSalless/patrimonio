<?php
/**
 * @var array  $im
 * @var int    $imovel_id
 * @var string $erro
 */
$page_title = 'Nova Manutenção';
require APP_ROOT . '/includes/header.php';
$tipos = ['eletrica'=>'Elétrica','hidraulica'=>'Hidráulica','pintura'=>'Pintura','ar_condicionado'=>'Ar-condicionado','estrutural'=>'Estrutural','jardim'=>'Jardim/Área externa','elevador'=>'Elevador','seguranca'=>'Segurança','eletrodomestico'=>'Eletrodoméstico','outro'=>'Outro'];
?>
<div class="container" style="max-width:720px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <h2 class="card-titulo">Nova Manutenção — <?= h($im['nome_referencia']) ?></h2>
    <a href="<?= base_url('imoveis/ficha?id='.$imovel_id.'&aba=manutencoes') ?>" class="btn btn-secundario">← Voltar</a>
  </div>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>
  <div class="card">
    <form method="post" enctype="multipart/form-data">
      <div class="form-grupo">
        <label>Descrição *</label>
        <input type="text" name="descricao" required placeholder="Ex.: Troca do motor do portão / conserto de infiltração" value="<?= h($_POST['descricao'] ?? '') ?>">
      </div>
      <div class="form-grid form-grid-2">
        <div class="form-grupo"><label>Tipo</label>
          <select name="tipo">
            <?php foreach($tipos as $v=>$l): ?>
            <option value="<?=$v?>" <?= (($_POST['tipo']??'outro')===$v)?'selected':'' ?>><?=$l?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo"><label>Data *</label><input type="date" name="data" required value="<?= h($_POST['data'] ?? date('Y-m-d')) ?>"></div>
        <div class="form-grupo"><label>Fornecedor / prestador</label><input type="text" name="fornecedor" placeholder="Empresa ou profissional" value="<?= h($_POST['fornecedor'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Valor (R$)</label><input type="text" name="valor" placeholder="0,00" value="<?= h($_POST['valor'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Garantia até</label><input type="date" name="garantia_ate" value="<?= h($_POST['garantia_ate'] ?? '') ?>"></div>
      </div>
      <div class="form-grupo"><label>Observações</label><textarea name="observacoes"><?= h($_POST['observacoes'] ?? '') ?></textarea></div>
      <div class="form-grupo">
        <label>Documentos (NF, garantia, fotos — múltiplos)</label>
        <input type="file" name="documentos[]" multiple accept=".pdf,.jpg,.jpeg,.png">
      </div>
      <div style="display:flex;gap:.75rem;margin-top:1rem">
        <button type="submit" class="btn btn-primario">Salvar manutenção</button>
        <a href="<?= base_url('imoveis/ficha?id='.$imovel_id.'&aba=manutencoes') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
