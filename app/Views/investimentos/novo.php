<?php
/**
 * Cadastro de investimento.
 * @var array  $cli
 * @var string $erro
 * @var array  $d
 * @var array  $contas
 */
$page_title = 'Cadastrar Investimento';
require APP_ROOT . '/includes/header.php';
?>
<div class="container" style="max-width:960px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <div>
      <h2 class="card-titulo">Cadastrar Investimento</h2>
      <div style="font-size:.85rem;color:var(--cor-secundario)">Cliente: <strong><?= h($cli['nome']) ?></strong></div>
    </div>
    <a href="<?= base_url('investimentos') ?>" class="btn btn-secundario">← Voltar</a>
  </div>

  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>

  <div class="card">
    <form method="post" enctype="multipart/form-data">
      <?php require APP_PATH . '/Views/investimentos/_campos.php'; ?>

      <!-- DOCUMENTOS -->
      <div class="form-secao"><div class="form-secao-titulo">5. Documentos</div></div>
      <div class="form-grid form-grid-3">
        <div class="form-grupo"><label>📄 Proposta / boletim</label><input type="file" name="doc_proposta" accept=".pdf,.jpg,.jpeg,.png"></div>
        <div class="form-grupo"><label>📘 Regulamento</label><input type="file" name="doc_regulamento" accept=".pdf,.jpg,.jpeg,.png"></div>
        <div class="form-grupo"><label>🧾 Extrato</label><input type="file" name="doc_extrato" accept=".pdf,.jpg,.jpeg,.png"></div>
      </div>
      <div style="font-size:.8rem;color:var(--cor-secundario)">Formatos aceitos: PDF, JPG, PNG · Máx. 30 MB por arquivo · Aplicações/resgates após salvar.</div>

      <div class="form-secao" style="margin-top:1.5rem"><div class="form-secao-titulo">6. Observações</div></div>
      <div class="form-grupo">
        <textarea name="observacoes" rows="3" placeholder="Informações adicionais sobre o investimento…"><?= h($d['observacoes'] ?? '') ?></textarea>
      </div>

      <div style="display:flex;gap:.75rem;margin-top:2rem;padding-top:1.25rem;border-top:1px solid #dde2ea">
        <button type="submit" class="btn btn-primario">Salvar investimento</button>
        <a href="<?= base_url('investimentos') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
