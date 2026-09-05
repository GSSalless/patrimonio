<?php
/**
 * Edição de empresa + documentos + quadro societário.
 * @var array  $cli
 * @var array  $empresa
 * @var array  $d
 * @var string $erro
 * @var string $ok
 * @var array  $docs_list
 * @var array  $socios
 * @var float  $participacao_total
 */
$page_title = 'Editar Empresa — ' . ($empresa['nome_fantasia'] ?: $empresa['razao_social']);
require APP_ROOT . '/includes/header.php';
$empresa_id = (int) $empresa['id'];
$funcoes = ['socio'=>'Sócio','administrador'=>'Administrador','socio_administrador'=>'Sócio-administrador','procurador'=>'Procurador','outro'=>'Outro'];
?>
<div class="container" style="max-width:960px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <div>
      <div style="font-size:.8rem;color:var(--cor-secundario);font-weight:600"><?= h($empresa['codigo']) ?> · <?= h(empresa_natureza_label($empresa['natureza'])) ?></div>
      <h2 class="card-titulo"><?= h($empresa['razao_social']) ?></h2>
      <div style="font-size:.85rem;color:var(--cor-secundario)">
        Cliente: <strong><?= h($cli['nome']) ?></strong>
        <?php if ($empresa['cnpj']): ?> · CNPJ: <?= h($empresa['cnpj']) ?><?php endif; ?>
      </div>
    </div>
    <a href="<?= base_url('empresas') ?>" class="btn btn-secundario">← Voltar</a>
  </div>

  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>
  <?php if ($ok):   ?><div class="alerta alerta-sucesso"><?= h($ok) ?></div><?php endif; ?>

  <div class="card">
    <form method="post" enctype="multipart/form-data">
      <?php require APP_PATH . '/Views/empresas/_campos.php'; ?>

      <!-- DOCUMENTOS -->
      <div class="form-secao"><div class="form-secao-titulo">5. Documentos</div></div>
      <div class="form-grid form-grid-3">
        <div class="form-grupo"><label>📄 Contrato social</label><input type="file" name="doc_contrato_social" accept=".pdf,.jpg,.jpeg,.png"></div>
        <div class="form-grupo"><label>🧾 Cartão CNPJ</label><input type="file" name="doc_cnpj" accept=".pdf,.jpg,.jpeg,.png"></div>
        <div class="form-grupo"><label>📎 Outros documentos</label><input type="file" name="doc_outros" accept=".pdf,.jpg,.jpeg,.png"></div>
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

      <div class="form-secao" style="margin-top:1.5rem"><div class="form-secao-titulo">6. Observações</div></div>
      <div class="form-grupo">
        <textarea name="observacoes" rows="3"><?= h($d['observacoes'] ?? '') ?></textarea>
      </div>

      <div style="display:flex;gap:.75rem;margin-top:2rem;padding-top:1.25rem;border-top:1px solid #dde2ea">
        <button type="submit" class="btn btn-primario">Salvar alterações</button>
        <a href="<?= base_url('empresas') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>

  <!-- QUADRO SOCIETÁRIO -->
  <div class="card" id="socios" style="margin-top:1.5rem">
    <div class="card-header" style="margin-bottom:1rem">
      <div>
        <h3 class="card-titulo">👥 Quadro societário</h3>
        <div style="font-size:.85rem;color:var(--cor-secundario)">
          <?= count($socios) ?> registro(s)
          <?php if ($participacao_total > 0): ?>
            · participação somada:
            <strong style="color:<?= abs($participacao_total - 100) < 0.001 ? '#1a7a45' : '#b45309' ?>"><?= number_format($participacao_total, 2, ',', '.') ?>%</strong>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php if ($socios): ?>
    <div style="overflow-x:auto">
    <table class="tabela">
      <thead><tr><th>Nome</th><th>CPF/CNPJ</th><th>Função</th><th style="text-align:right">Part.</th><th>Observações</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($socios as $s): ?>
        <tr>
          <td><strong><?= h($s['nome']) ?></strong></td>
          <td><?= h($s['cpf_cnpj'] ?: '—') ?></td>
          <td><span class="tag"><?= h(empresa_socio_funcao_label($s['funcao'])) ?></span></td>
          <td style="text-align:right"><?= $s['participacao'] !== null ? number_format((float)$s['participacao'], 2, ',', '.') . '%' : '—' ?></td>
          <td><?= h($s['observacoes'] ?: '—') ?></td>
          <td><a href="<?= base_url('empresas/socio-remover?empresa_id=' . $empresa_id . '&id=' . $s['id']) ?>"
                 onclick="return confirm('Remover <?= h(addslashes($s['nome'])) ?> do quadro societário?')"
                 class="btn btn-secundario btn-sm">Remover</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php else: ?>
    <p style="color:var(--cor-secundario)">Nenhum sócio/administrador cadastrado.</p>
    <?php endif; ?>

    <!-- Adicionar sócio -->
    <form method="post" action="<?= base_url('empresas/socio?empresa_id=' . $empresa_id) ?>" style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--cor-borda)">
      <div class="form-grid form-grid-4">
        <div class="form-grupo" style="grid-column:span 2"><label>Nome *</label><input type="text" name="nome" required></div>
        <div class="form-grupo"><label>CPF/CNPJ</label><input type="text" name="cpf_cnpj"></div>
        <div class="form-grupo">
          <label>Função</label>
          <select name="funcao">
            <?php foreach ($funcoes as $v => $l): ?><option value="<?= $v ?>"><?= $l ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo"><label>Participação (%)</label><input type="text" name="participacao" placeholder="0,00"></div>
        <div class="form-grupo" style="grid-column:span 2"><label>Observações</label><input type="text" name="observacoes"></div>
        <div class="form-grupo" style="align-self:end"><button type="submit" class="btn btn-primario">+ Adicionar</button></div>
      </div>
    </form>
  </div>

</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
