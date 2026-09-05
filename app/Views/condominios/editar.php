<?php
/**
 * @var array  $co
 * @var int    $id
 * @var int    $imovel_id
 * @var string $erro
 * @var array  $d
 * @var array  $docs
 */
$page_title = 'Editar Condomínio';
require APP_ROOT . '/includes/header.php';
$val   = fn($k) => h($d[$k] ?? '');
$money = fn($k) => $d[$k] ? number_format((float)$d[$k], 2, ',', '.') : '';
?>
<div class="container" style="max-width:960px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <h2 class="card-titulo">Editar — <?= h($co['nome']) ?></h2>
    <a href="<?= $imovel_id ? base_url('imoveis/ficha?id='.$imovel_id) : base_url('imoveis') ?>" class="btn btn-secundario">← Voltar</a>
  </div>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>

  <div class="card">
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="imovel_id" value="<?= $imovel_id ?>">

      <?php require APP_PATH . '/Views/condominios/_campos.php'; ?>

      <!-- DOCUMENTOS -->
      <div class="form-secao"><div class="form-secao-titulo">5. Documentos</div></div>
      <?php if ($docs): ?>
      <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:.75rem">
        <?php foreach ($docs as $doc): ?>
        <a href="<?= url_documento($doc) ?>" target="_blank" class="btn btn-secundario btn-sm">
          <i class="bi <?= $doc['categoria']==='cnpj' ? 'bi-card-text' : 'bi-file-earmark-text' ?>"></i>
          <?= h($doc['categoria']==='cnpj' ? 'Cartão CNPJ' : ($doc['categoria']==='contrato' ? 'Contrato' : $doc['categoria'])) ?> — <?= h($doc['nome_arquivo']) ?>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <div class="form-grid form-grid-2">
        <div class="form-grupo"><label><i class="bi bi-card-text"></i> Cartão CNPJ <?= $docs ? '(enviar novo)' : '' ?></label><input type="file" name="cartao_cnpj" accept=".pdf,.jpg,.jpeg,.png"></div>
        <div class="form-grupo"><label><i class="bi bi-file-earmark-text"></i> Contrato / Convenção <?= $docs ? '(enviar novo)' : '' ?></label><input type="file" name="contrato_doc" accept=".pdf,.jpg,.jpeg,.png"></div>
      </div>
      <div style="font-size:.78rem;color:var(--cor-secundario);margin-top:.25rem">Formatos: PDF, JPG, PNG · máx. 30 MB por arquivo</div>

      <div class="form-secao"><div class="form-secao-titulo">6. Observações</div></div>
      <div class="form-grupo"><textarea name="observacoes" rows="3" style="width:100%"><?= $val('observacoes') ?></textarea></div>

      <div style="display:flex;gap:.75rem;margin-top:2rem;padding-top:1.25rem;border-top:1px solid #dde2ea">
        <button type="submit" class="btn btn-primario">Salvar alterações</button>
        <a href="<?= $imovel_id ? base_url('imoveis/ficha?id='.$imovel_id) : base_url('imoveis') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
