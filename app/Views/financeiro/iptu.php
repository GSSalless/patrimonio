<?php
/**
 * @var array  $im
 * @var int    $imovel_id
 * @var string $erro
 */
$page_title = 'Registrar IPTU';
require APP_ROOT . '/includes/header.php';
?>
<div class="container" style="max-width:600px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <h2 class="card-titulo">Registrar IPTU — <?= h($im['nome_referencia']) ?></h2>
    <a href="<?= base_url('imoveis/ficha?id='.$imovel_id.'&aba=financeiro') ?>" class="btn btn-secundario">← Voltar</a>
  </div>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>
  <div class="card">
    <form method="post">
      <div class="form-grid form-grid-2">
        <div class="form-grupo"><label>Ano *</label><input type="number" name="ano" required min="2000" max="2099" value="<?= h($_POST['ano'] ?? date('Y')) ?>"></div>
        <div class="form-grupo"><label>Inscrição IPTU</label><input type="text" name="inscricao_iptu" value="<?= h($_POST['inscricao_iptu'] ?? $im['inscricao_municipal'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Valor total (R$) *</label><input type="text" name="valor_total" required placeholder="0,00" value="<?= h($_POST['valor_total'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Nº de parcelas</label><input type="number" name="parcelas" min="1" max="12" value="<?= h($_POST['parcelas'] ?? '1') ?>"></div>
        <div class="form-grupo" style="grid-column:span 2"><label>Vencimento da 1ª parcela</label><input type="date" name="data_vencimento_1" value="<?= h($_POST['data_vencimento_1'] ?? '') ?>"></div>
      </div>
      <div style="display:flex;gap:.75rem;margin-top:1rem">
        <button type="submit" class="btn btn-primario">Salvar IPTU</button>
        <a href="<?= base_url('imoveis/ficha?id='.$imovel_id.'&aba=financeiro') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
