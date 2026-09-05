<?php
/**
 * @var array  $im
 * @var string $tipo_ref
 * @var int    $ref_id
 * @var string $back_url
 * @var string $erro
 */
$page_title = 'Anexar Documento';
require APP_ROOT . '/includes/header.php';
?>
<div class="container" style="max-width:600px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <h2 class="card-titulo">Anexar Documento — <?= h($im['nome_referencia']) ?></h2>
    <a href="<?= $back_url ?>" class="btn btn-secundario">← Voltar</a>
  </div>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>
  <div class="card">
    <form method="post" enctype="multipart/form-data">
      <div class="form-grupo">
        <label>Arquivo * (PDF, JPG, PNG — máx. 20 MB)</label>
        <input type="file" name="arquivo" required accept=".pdf,.jpg,.jpeg,.png,.webp">
      </div>
      <div class="form-grid form-grid-2">
        <div class="form-grupo"><label>Categoria</label>
          <select name="categoria">
            <?php foreach(['escritura'=>'Escritura','matricula'=>'Matrícula','iptu'=>'IPTU','contrato_compra'=>'Contrato de Compra','habite_se'=>'Habite-se','laudo'=>'Laudo','foto'=>'Foto','boleto'=>'Boleto','nf'=>'Nota Fiscal','outro'=>'Outro'] as $v=>$l): ?>
            <option value="<?=$v?>"><?=$l?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo"><label>Descrição</label><input type="text" name="descricao" placeholder="Opcional"></div>
        <div class="form-grupo"><label>Data de emissão</label><input type="date" name="data_emissao"></div>
        <div class="form-grupo"><label>Data de validade</label><input type="date" name="data_validade"></div>
      </div>
      <div style="display:flex;gap:.75rem;margin-top:1rem">
        <button type="submit" class="btn btn-primario">Fazer upload</button>
        <a href="<?= $back_url ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
