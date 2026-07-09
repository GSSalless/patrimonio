<?php
/**
 * @var array  $im
 * @var int    $imovel_id
 * @var string $erro
 */
$page_title = 'Registrar Fatura de Condomínio';
require APP_ROOT . '/includes/header.php';
?>
<div class="container" style="max-width:600px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <h2 class="card-titulo">Fatura de Condomínio — <?= h($im['nome_referencia']) ?></h2>
    <a href="<?= base_url('imoveis/ficha?id='.$imovel_id.'&aba=financeiro') ?>" class="btn btn-secundario">← Voltar</a>
  </div>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>
  <div class="card">
    <form method="post" enctype="multipart/form-data">
      <div class="form-grid form-grid-2">
        <div class="form-grupo"><label>Competência (mês/ano) *</label><input type="month" name="competencia" required value="<?= h($_POST['competencia'] ?? date('Y-m')) ?>"></div>
        <div class="form-grupo"><label>Valor (R$) *</label><input type="text" name="valor" required placeholder="0,00" value="<?= h($_POST['valor'] ?? '') ?>"></div>
        <div class="form-grupo" style="grid-column:span 2"><label>Descrição extra</label><input type="text" name="descricao_extra" placeholder="Ex.: taxa churrasqueira" value="<?= h($_POST['descricao_extra'] ?? '') ?>"></div>
        <div class="form-grupo" style="grid-column:span 2"><label>Boleto (PDF ou imagem)</label><input type="file" name="boleto" accept=".pdf,.jpg,.jpeg,.png"></div>
        <div class="form-grupo">
          <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
            <input type="checkbox" name="pago" value="1" <?= isset($_POST['pago'])?'checked':'' ?>> Já pago
          </label>
        </div>
        <div class="form-grupo"><label>Data do pagamento</label><input type="date" name="data_pagamento" value="<?= h($_POST['data_pagamento'] ?? '') ?>"></div>
      </div>
      <div style="display:flex;gap:.75rem;margin-top:1rem">
        <button type="submit" class="btn btn-primario">Salvar fatura</button>
        <a href="<?= base_url('imoveis/ficha?id='.$imovel_id.'&aba=financeiro') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
