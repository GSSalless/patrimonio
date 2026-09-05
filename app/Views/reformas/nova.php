<?php
/**
 * @var array  $im
 * @var int    $imovel_id
 * @var string $erro
 */
$page_title = 'Nova Reforma';
require APP_ROOT . '/includes/header.php';
?>
<div class="container" style="max-width:720px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <h2 class="card-titulo">Nova Reforma — <?= h($im['nome_referencia']) ?></h2>
    <a href="<?= base_url('imoveis/ficha?id='.$imovel_id.'&aba=reformas') ?>" class="btn btn-secundario">← Voltar</a>
  </div>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>
  <div class="card">
    <form method="post" enctype="multipart/form-data">
      <div class="form-grupo">
        <label>Descrição *</label>
        <input type="text" name="descricao" required placeholder="Ex.: Reforma da cozinha — revestimento e armários" value="<?= h($_POST['descricao'] ?? '') ?>">
      </div>
      <div class="form-grid form-grid-2">
        <div class="form-grupo"><label>Status</label>
          <select name="status">
            <?php foreach(['planejado'=>'Planejado','em_andamento'=>'Em andamento','concluido'=>'Concluído','cancelado'=>'Cancelado'] as $v=>$l): ?>
            <option value="<?=$v?>" <?= (($_POST['status']??'planejado')===$v)?'selected':'' ?>><?=$l?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo"><label>Fornecedor</label><input type="text" name="fornecedor" placeholder="Nome da empresa ou profissional" value="<?= h($_POST['fornecedor'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Data de início</label><input type="date" name="data_inicio" value="<?= h($_POST['data_inicio'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Data fim prevista</label><input type="date" name="data_fim_prevista" value="<?= h($_POST['data_fim_prevista'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Data fim real</label><input type="date" name="data_fim_real" value="<?= h($_POST['data_fim_real'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Custo previsto (R$)</label><input type="text" name="custo_previsto" placeholder="0,00" value="<?= h($_POST['custo_previsto'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Custo realizado (R$)</label><input type="text" name="custo_realizado" placeholder="0,00" value="<?= h($_POST['custo_realizado'] ?? '') ?>"></div>
      </div>
      <div class="form-grupo"><label>Observações</label><textarea name="observacoes"><?= h($_POST['observacoes'] ?? '') ?></textarea></div>
      <div class="form-grupo">
        <label>Documentos (NF, cupom, contrato — múltiplos)</label>
        <input type="file" name="documentos[]" multiple accept=".pdf,.jpg,.jpeg,.png">
      </div>
      <div style="display:flex;gap:.75rem;margin-top:1rem">
        <button type="submit" class="btn btn-primario">Salvar reforma</button>
        <a href="<?= base_url('imoveis/ficha?id='.$imovel_id.'&aba=reformas') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
