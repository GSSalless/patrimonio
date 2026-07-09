<?php
/**
 * @var array  $ve
 * @var int    $veiculo_id
 * @var array|null $ab
 * @var array  $d
 * @var string $erro
 */
$page_title = ($ab ? 'Editar' : 'Novo') . ' Abastecimento';
require APP_ROOT . '/includes/header.php';
$nomeVe = trim(($ve['marca'] ?? '') . ' ' . ($ve['modelo'] ?? ''));
$val = fn($k) => h($d[$k] ?? '');
$mv  = fn($k) => isset($d[$k]) && $d[$k] !== '' && $d[$k] !== null ? number_format((float)$d[$k], 2, ',', '.') : '';
$acao = base_url('veiculos/abastecimento?veiculo_id=' . $veiculo_id . ($ab ? '&id=' . $ab['id'] : ''));
?>
<div class="container" style="max-width:720px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <h2 class="card-titulo"><?= $ab ? 'Editar' : 'Novo' ?> Abastecimento — <?= h($nomeVe) ?></h2>
    <a href="<?= base_url('veiculos/editar?id='.$veiculo_id.'#abastecimentos') ?>" class="btn btn-secundario">← Voltar</a>
  </div>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>
  <div class="card">
    <form method="post" action="<?= $acao ?>">
      <div class="form-grid form-grid-3">
        <div class="form-grupo"><label>Data *</label><input type="date" name="data" required value="<?= $val('data') ?: date('Y-m-d') ?>"></div>
        <div class="form-grupo"><label>Combustível</label>
          <select name="combustivel"><option value="">—</option>
            <?php foreach (['gasolina','etanol','flex','diesel','gnv','eletrico'] as $c): ?>
            <option value="<?=$c?>" <?= (($d['combustivel']??'')===$c)?'selected':'' ?>><?= combustivel_label($c) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo"><label>KM no abastecimento</label><input type="number" name="km" min="0" value="<?= $val('km') ?>"></div>
        <div class="form-grupo"><label>Litros</label><input type="text" name="litros" placeholder="0,00" value="<?= $mv('litros') ?>"></div>
        <div class="form-grupo"><label>Valor / litro (R$)</label><input type="text" name="valor_litro" placeholder="0,000" value="<?= isset($d['valor_litro'])&&$d['valor_litro']!==''&&$d['valor_litro']!==null ? number_format((float)$d['valor_litro'],3,',','.') : '' ?>"></div>
        <div class="form-grupo"><label>Valor total (R$)</label><input type="text" name="valor_total" placeholder="0,00" value="<?= $mv('valor_total') ?>"></div>
        <div class="form-grupo" style="grid-column:span 2"><label>Posto</label><input type="text" name="posto" value="<?= $val('posto') ?>"></div>
        <div class="form-grupo"><label>Tanque cheio?</label>
          <label style="display:flex;align-items:center;gap:.5rem;margin-top:.4rem;font-weight:400">
            <input type="checkbox" name="tanque_cheio" value="1" <?= (!$ab || !empty($d['tanque_cheio'])) ? 'checked' : '' ?> style="width:auto"> Sim
          </label>
        </div>
      </div>
      <div class="form-grupo"><label>Observações</label><input type="text" name="observacoes" value="<?= $val('observacoes') ?>"></div>
      <div style="display:flex;gap:.75rem;margin-top:1rem">
        <button type="submit" class="btn btn-primario">Salvar abastecimento</button>
        <a href="<?= base_url('veiculos/editar?id='.$veiculo_id.'#abastecimentos') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
