<?php
/**
 * @var array  $im
 * @var int    $imovel_id
 * @var string $erro
 */
$page_title = 'Novo Contrato de Locação';
require APP_ROOT . '/includes/header.php';
?>
<div class="container" style="max-width:680px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <h2 class="card-titulo">Contrato de Locação — <?= h($im['nome_referencia']) ?></h2>
    <a href="<?= base_url('imoveis/ficha?id='.$imovel_id.'&aba=aluguel') ?>" class="btn btn-secundario">← Voltar</a>
  </div>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>
  <div class="card">
    <form method="post" enctype="multipart/form-data">
      <div class="form-secao"><div class="form-secao-titulo">Locatário</div></div>
      <div class="form-grid form-grid-2">
        <div class="form-grupo" style="grid-column:span 2"><label>Nome do locatário *</label><input type="text" name="locatario_nome" required value="<?= h($_POST['locatario_nome'] ?? '') ?>"></div>
        <div class="form-grupo" style="grid-column:span 2"><label>CPF / CNPJ</label><input type="text" name="locatario_cpf_cnpj" oninput="mascara_cpf_cnpj(this)" maxlength="18" value="<?= h($_POST['locatario_cpf_cnpj'] ?? '') ?>"></div>
      </div>
      <div class="form-secao"><div class="form-secao-titulo">Contrato</div></div>
      <div class="form-grid form-grid-2">
        <div class="form-grupo"><label>Data de início *</label><input type="date" name="data_inicio" required value="<?= h($_POST['data_inicio'] ?? date('Y-m-d')) ?>"></div>
        <div class="form-grupo"><label>Data de término</label><input type="date" name="data_fim" value="<?= h($_POST['data_fim'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Valor do aluguel (R$) *</label><input type="text" name="valor_aluguel" required placeholder="0,00" value="<?= h($_POST['valor_aluguel'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Dia do vencimento</label><input type="number" name="dia_vencimento" min="1" max="31" value="<?= h($_POST['dia_vencimento'] ?? '10') ?>"></div>
      </div>
      <div class="form-grupo"><label>Observações</label><textarea name="observacoes"><?= h($_POST['observacoes'] ?? '') ?></textarea></div>
      <div class="form-grupo"><label>Contrato assinado (PDF)</label><input type="file" name="contrato" accept=".pdf,.jpg,.jpeg,.png"></div>
      <div style="display:flex;gap:.75rem;margin-top:1rem">
        <button type="submit" class="btn btn-primario">Salvar contrato</button>
        <a href="<?= base_url('imoveis/ficha?id='.$imovel_id.'&aba=aluguel') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
