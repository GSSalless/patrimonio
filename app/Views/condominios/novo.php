<?php
/**
 * @var array|null $imv
 * @var int        $imovel_id
 * @var string     $erro
 * @var array      $d
 */
$page_title = 'Cadastrar Condomínio';
require APP_ROOT . '/includes/header.php';
$val   = fn($k) => h($d[$k] ?? '');
$money = $val; // no cadastro o valor vem do POST como digitado
?>
<div class="container" style="max-width:960px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <div>
      <h2 class="card-titulo">Cadastrar Condomínio</h2>
      <?php if ($imv): ?><div style="font-size:.85rem;color:var(--cor-secundario)">Vincular ao imóvel: <strong><?= h($imv['nome_referencia']) ?></strong></div><?php endif; ?>
    </div>
    <a href="<?= $imv ? base_url('imoveis/ficha?id='.$imovel_id) : base_url('imoveis') ?>" class="btn btn-secundario">← Voltar</a>
  </div>

  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>

  <div class="card">
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="imovel_id" value="<?= $imovel_id ?>">

      <?php require APP_PATH . '/Views/condominios/_campos.php'; ?>

      <!-- DOCUMENTOS -->
      <div class="form-secao"><div class="form-secao-titulo">5. Documentos</div></div>
      <div class="form-grid form-grid-2">
        <div class="form-grupo"><label><i class="bi bi-card-text"></i> Cartão CNPJ</label><input type="file" name="cartao_cnpj" accept=".pdf,.jpg,.jpeg,.png"></div>
        <div class="form-grupo"><label><i class="bi bi-file-earmark-text"></i> Contrato / Convenção</label><input type="file" name="contrato_doc" accept=".pdf,.jpg,.jpeg,.png"></div>
      </div>
      <div style="font-size:.78rem;color:var(--cor-secundario);margin-top:.25rem">Formatos: PDF, JPG, PNG · máx. 30 MB por arquivo</div>

      <!-- OBSERVAÇÕES -->
      <div class="form-secao"><div class="form-secao-titulo">6. Observações</div></div>
      <div class="form-grupo"><textarea name="observacoes" rows="3" style="width:100%"><?= h($d['observacoes'] ?? '') ?></textarea></div>

      <div style="display:flex;gap:.75rem;margin-top:2rem;padding-top:1.25rem;border-top:1px solid #dde2ea">
        <button type="submit" class="btn btn-primario">Salvar e vincular</button>
        <a href="<?= $imv ? base_url('imoveis/ficha?id='.$imovel_id) : base_url('imoveis') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
