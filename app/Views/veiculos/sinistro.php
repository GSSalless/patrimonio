<?php
/**
 * @var array  $ve
 * @var int    $veiculo_id
 * @var array|null $si
 * @var array  $d
 * @var string $erro
 */
$page_title = ($si ? 'Editar' : 'Novo') . ' Sinistro';
require APP_ROOT . '/includes/header.php';
$nomeVe = trim(($ve['marca'] ?? '') . ' ' . ($ve['modelo'] ?? ''));
$tipos = ['colisao'=>'Colisão','roubo'=>'Roubo','furto'=>'Furto','avaria'=>'Avaria','vidro'=>'Vidro','terceiros'=>'Danos a terceiros','outro'=>'Outro'];
$statuses = ['aberto'=>'Aberto','em_analise'=>'Em análise','em_reparo'=>'Em reparo','concluido'=>'Concluído','recusado'=>'Recusado'];
$val = fn($k) => h($d[$k] ?? '');
$mv  = fn($k) => isset($d[$k]) && $d[$k] !== '' && $d[$k] !== null ? number_format((float)$d[$k], 2, ',', '.') : '';
$acao = base_url('veiculos/sinistro?veiculo_id=' . $veiculo_id . ($si ? '&id=' . $si['id'] : ''));
?>
<div class="container" style="max-width:720px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <h2 class="card-titulo"><?= $si ? 'Editar' : 'Novo' ?> Sinistro — <?= h($nomeVe) ?></h2>
    <a href="<?= base_url('veiculos/editar?id='.$veiculo_id.'#sinistros') ?>" class="btn btn-secundario">← Voltar</a>
  </div>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>
  <div class="card">
    <form method="post" action="<?= $acao ?>">
      <div class="form-grupo">
        <label>Descrição *</label>
        <input type="text" name="descricao" required placeholder="Ex.: Colisão traseira no estacionamento" value="<?= $val('descricao') ?>">
      </div>
      <div class="form-grid form-grid-3">
        <div class="form-grupo"><label>Tipo</label>
          <select name="tipo">
            <?php foreach($tipos as $v=>$l): ?>
            <option value="<?=$v?>" <?= (($d['tipo']??'colisao')===$v)?'selected':'' ?>><?=$l?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo"><label>Data *</label><input type="date" name="data" required value="<?= $val('data') ?: date('Y-m-d') ?>"></div>
        <div class="form-grupo"><label>Status</label>
          <select name="status">
            <?php foreach($statuses as $v=>$l): ?>
            <option value="<?=$v?>" <?= (($d['status']??'aberto')===$v)?'selected':'' ?>><?=$l?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo" style="grid-column:span 2"><label>Local</label><input type="text" name="local" value="<?= $val('local') ?>"></div>
        <div class="form-grupo"><label>Boletim de ocorrência</label><input type="text" name="boletim_ocorrencia" value="<?= $val('boletim_ocorrencia') ?>"></div>
        <div class="form-grupo"><label>Acionou o seguro?</label>
          <label style="display:flex;align-items:center;gap:.5rem;margin-top:.4rem;font-weight:400">
            <input type="checkbox" name="acionou_seguro" value="1" <?= !empty($d['acionou_seguro']) ? 'checked' : '' ?> style="width:auto"> Sim
          </label>
        </div>
        <div class="form-grupo"><label>Nº do sinistro</label><input type="text" name="numero_sinistro" value="<?= $val('numero_sinistro') ?>"></div>
        <div class="form-grupo"><label>Valor do prejuízo (R$)</label><input type="text" name="valor_prejuizo" placeholder="0,00" value="<?= $mv('valor_prejuizo') ?>"></div>
        <div class="form-grupo"><label>Franquia paga (R$)</label><input type="text" name="valor_franquia" placeholder="0,00" value="<?= $mv('valor_franquia') ?>"></div>
      </div>
      <div class="form-grupo"><label>Observações</label><textarea name="observacoes"><?= $val('observacoes') ?></textarea></div>
      <div style="display:flex;gap:.75rem;margin-top:1rem">
        <button type="submit" class="btn btn-primario">Salvar sinistro</button>
        <a href="<?= base_url('veiculos/editar?id='.$veiculo_id.'#sinistros') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
