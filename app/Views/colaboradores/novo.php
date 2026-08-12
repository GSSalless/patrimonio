<?php
/**
 * Cadastro de colaborador.
 * @var array  $cli
 * @var string $erro
 * @var array  $d
 */
$page_title = 'Cadastrar Colaborador';
require APP_ROOT . '/includes/header.php';
?>
<div class="container" style="max-width:960px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <div>
      <h2 class="card-titulo">Cadastrar Colaborador</h2>
      <div style="font-size:.85rem;color:var(--cor-secundario)">Cliente: <strong><?= h($cli['nome']) ?></strong></div>
    </div>
    <a href="<?= base_url('colaboradores') ?>" class="btn btn-secundario">← Voltar</a>
  </div>

  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>

  <div class="card">
    <form method="post" enctype="multipart/form-data">
      <?php require APP_PATH . '/Views/colaboradores/_campos.php'; ?>

      <!-- DOCUMENTOS -->
      <div class="form-secao"><div class="form-secao-titulo">6. Documentos</div></div>
      <div class="form-grid form-grid-3">
        <div class="form-grupo"><label>📄 Contrato de trabalho</label><input type="file" name="doc_contrato" accept=".pdf,.jpg,.jpeg,.png"></div>
        <div class="form-grupo"><label>🪪 Documento de identidade</label><input type="file" name="doc_identidade" accept=".pdf,.jpg,.jpeg,.png"></div>
        <div class="form-grupo"><label>📎 Outros documentos</label><input type="file" name="doc_outros" accept=".pdf,.jpg,.jpeg,.png"></div>
      </div>
      <div style="font-size:.8rem;color:var(--cor-secundario)">Formatos aceitos: PDF, JPG, PNG · Máx. 30 MB por arquivo · Dependentes e histórico após salvar.</div>

      <div class="form-secao" style="margin-top:1.5rem"><div class="form-secao-titulo">7. Observações</div></div>
      <div class="form-grupo">
        <textarea name="observacoes" rows="3" placeholder="Informações adicionais sobre o colaborador…"><?= h($d['observacoes'] ?? '') ?></textarea>
      </div>

      <div style="display:flex;gap:.75rem;margin-top:2rem;padding-top:1.25rem;border-top:1px solid #dde2ea">
        <button type="submit" class="btn btn-primario">Salvar colaborador</button>
        <a href="<?= base_url('colaboradores') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
