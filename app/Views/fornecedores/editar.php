<?php
/**
 * Edição de fornecedor + documentos.
 * @var array  $cli
 * @var array  $fornecedor
 * @var array  $d
 * @var string $erro
 * @var string $ok
 * @var array  $docs_list
 */
$page_title = 'Editar Fornecedor — ' . ($fornecedor['nome_fantasia'] ?: $fornecedor['nome']);
require APP_ROOT . '/includes/header.php';
?>
<div class="container" style="max-width:960px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <div>
      <div style="font-size:.8rem;color:var(--cor-secundario);font-weight:600"><?= h($fornecedor['codigo']) ?> · <?= h(fornecedor_categoria_label($fornecedor['categoria'])) ?></div>
      <h2 class="card-titulo"><?= h($fornecedor['nome']) ?></h2>
      <div style="font-size:.85rem;color:var(--cor-secundario)">
        Cliente: <strong><?= h($cli['nome']) ?></strong>
        <?php if ($fornecedor['cpf_cnpj']): ?> · <?= h($fornecedor['cpf_cnpj']) ?><?php endif; ?>
      </div>
    </div>
    <a href="<?= base_url('fornecedores') ?>" class="btn btn-secundario">← Voltar</a>
  </div>

  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>
  <?php if ($ok):   ?><div class="alerta alerta-sucesso"><?= h($ok) ?></div><?php endif; ?>

  <div class="card">
    <form method="post" enctype="multipart/form-data">
      <?php require APP_PATH . '/Views/fornecedores/_campos.php'; ?>

      <!-- DOCUMENTOS -->
      <div class="form-secao"><div class="form-secao-titulo">6. Documentos</div></div>
      <div class="form-grid form-grid-3">
        <div class="form-grupo"><label>📄 Contrato</label><input type="file" name="doc_contrato" accept=".pdf,.jpg,.jpeg,.png"></div>
        <div class="form-grupo"><label>🧾 Nota fiscal</label><input type="file" name="doc_nf" accept=".pdf,.jpg,.jpeg,.png"></div>
        <div class="form-grupo"><label>📎 Certidão</label><input type="file" name="doc_certidao" accept=".pdf,.jpg,.jpeg,.png"></div>
      </div>
      <?php if ($docs_list): ?>
      <div style="display:flex;flex-direction:column;gap:.4rem;margin-top:.5rem">
        <?php foreach ($docs_list as $doc): ?>
        <a href="<?= base_url($doc['caminho']) ?>" target="_blank"
           style="display:flex;align-items:center;gap:.6rem;padding:.5rem .75rem;border:1px solid var(--cor-borda);border-radius:8px;background:#fff;font-size:.85rem;text-decoration:none;color:inherit">
          <span><?= str_starts_with($doc['mime_type'] ?? '', 'image/') ? '🖼️' : '📄' ?></span>
          <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= h($doc['nome_arquivo']) ?></span>
          <span class="tag"><?= h($doc['categoria']) ?></span>
          <span style="color:var(--cor-secundario)"><?= data_br($doc['criado_em']) ?></span>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <div class="form-secao" style="margin-top:1.5rem"><div class="form-secao-titulo">7. Observações</div></div>
      <div class="form-grupo">
        <textarea name="observacoes" rows="3"><?= h($d['observacoes'] ?? '') ?></textarea>
      </div>

      <div style="display:flex;gap:.75rem;margin-top:2rem;padding-top:1.25rem;border-top:1px solid #dde2ea">
        <button type="submit" class="btn btn-primario">Salvar alterações</button>
        <a href="<?= base_url('fornecedores') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
