<?php
/**
 * @var array  $im
 * @var int    $imovel_id
 * @var string $erro
 */
$page_title = 'Novo Lançamento';
require APP_ROOT . '/includes/header.php';
?>
<div class="container" style="max-width:600px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <h2 class="card-titulo">Novo Lançamento — <?= h($im['nome_referencia']) ?></h2>
    <a href="<?= base_url('imoveis/ficha?id='.$imovel_id.'&aba=financeiro') ?>" class="btn btn-secundario">← Voltar</a>
  </div>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>
  <div class="card">
    <form method="post">
      <div class="form-grid form-grid-2">
        <div class="form-grupo"><label>Tipo *</label>
          <select name="tipo">
            <option value="despesa" <?= (($_POST['tipo']??'')==='despesa')?'selected':'' ?>>Despesa</option>
            <option value="receita" <?= (($_POST['tipo']??'')==='receita')?'selected':'' ?>>Receita</option>
          </select></div>
        <div class="form-grupo"><label>Categoria *</label>
          <select name="categoria">
            <?php foreach(['iptu','condominio','energia','agua','internet','aluguel_recebido','reforma','manutencao','seguro','financiamento','outro'] as $cat): ?>
            <option value="<?=$cat?>" <?= (($_POST['categoria']??'')===$cat)?'selected':'' ?>><?=ucfirst(str_replace('_',' ',$cat))?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="form-grupo" style="grid-column:span 2"><label>Descrição *</label><input type="text" name="descricao" required placeholder="Ex.: Energia elétrica Junho/2026" value="<?= h($_POST['descricao'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Valor (R$) *</label><input type="text" name="valor" required placeholder="0,00" value="<?= h($_POST['valor'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Competência *</label><input type="date" name="data_competencia" required value="<?= h($_POST['data_competencia'] ?? date('Y-m-d')) ?>"></div>
        <div class="form-grupo">
          <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
            <input type="checkbox" name="pago" value="1" <?= isset($_POST['pago'])?'checked':'' ?>> Já pago
          </label>
        </div>
        <div class="form-grupo"><label>Data do pagamento</label><input type="date" name="data_pagamento" value="<?= h($_POST['data_pagamento'] ?? '') ?>"></div>
      </div>
      <div style="display:flex;gap:.75rem;margin-top:1rem">
        <button type="submit" class="btn btn-primario">Salvar lançamento</button>
        <a href="<?= base_url('imoveis/ficha?id='.$imovel_id.'&aba=financeiro') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
