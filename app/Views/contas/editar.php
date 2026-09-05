<?php
/**
 * Edição de conta financeira + documentos + histórico de saldos.
 * @var array  $cli
 * @var array  $conta
 * @var array  $d
 * @var string $erro
 * @var string $ok
 * @var array  $docs_list
 * @var array  $saldos
 */
$page_title = 'Editar Conta — ' . $conta['apelido'];
require APP_ROOT . '/includes/header.php';
$conta_id = (int) $conta['id'];
?>
<div class="container" style="max-width:960px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <div>
      <div style="font-size:.8rem;color:var(--cor-secundario);font-weight:600"><?= h($conta['codigo']) ?><?= $conta['integracao'] === 'asaas' ? ' · <span style="color:#0030b9">Asaas vinculado</span>' : '' ?></div>
      <h2 class="card-titulo"><?= h($conta['apelido']) ?></h2>
      <div style="font-size:.85rem;color:var(--cor-secundario)">Cliente: <strong><?= h($cli['nome']) ?></strong></div>
    </div>
    <a href="<?= base_url('contas') ?>" class="btn btn-secundario">← Voltar</a>
  </div>

  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>
  <?php if ($ok):   ?><div class="alerta alerta-sucesso"><?= h($ok) ?></div><?php endif; ?>

  <div class="card">
    <form method="post" enctype="multipart/form-data">
      <?php require APP_PATH . '/Views/contas/_campos.php'; ?>

      <!-- DOCUMENTOS -->
      <div class="form-secao"><div class="form-secao-titulo">6. Documentos</div></div>
      <div class="form-grid form-grid-3">
        <div class="form-grupo"><label>📄 Contrato de abertura</label><input type="file" name="doc_contrato" accept=".pdf,.jpg,.jpeg,.png"></div>
        <div class="form-grupo"><label>🧾 Extrato</label><input type="file" name="doc_extrato" accept=".pdf,.jpg,.jpeg,.png"></div>
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

      <div class="form-secao" style="margin-top:1.5rem"><div class="form-secao-titulo">7. Observações</div></div>
      <div class="form-grupo">
        <textarea name="observacoes" rows="3"><?= h($d['observacoes'] ?? '') ?></textarea>
      </div>

      <div style="display:flex;gap:.75rem;margin-top:2rem;padding-top:1.25rem;border-top:1px solid #dde2ea">
        <button type="submit" class="btn btn-primario">Salvar alterações</button>
        <a href="<?= base_url('contas') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>

  <!-- HISTÓRICO: SALDOS -->
  <div class="card" id="saldos" style="margin-top:1.5rem">
    <div class="card-header" style="margin-bottom:1rem">
      <div>
        <h3 class="card-titulo">💰 Saldos (histórico)</h3>
        <?php if ($conta['saldo_atual'] !== null): ?>
        <div style="font-size:.85rem;color:var(--cor-secundario)">Saldo atual: <strong style="color:<?= (float)$conta['saldo_atual'] >= 0 ? '#1a7a45' : '#b82020' ?>"><?= moeda((float)$conta['saldo_atual']) ?></strong> em <?= data_br($conta['saldo_data']) ?></div>
        <?php endif; ?>
      </div>
      <a href="<?= base_url('contas/saldo?conta_id=' . $conta_id) ?>" class="btn btn-primario btn-sm">+ Saldo</a>
    </div>
    <?php if ($saldos): ?>
    <div style="overflow-x:auto">
    <table class="tabela">
      <thead><tr><th>Data</th><th>Saldo</th><th>Variação</th><th>Origem</th><th>Observações</th><th></th></tr></thead>
      <tbody>
        <?php
        // Saldos em ordem decrescente; a variação compara com o imediatamente anterior no tempo.
        foreach ($saldos as $i => $sl):
          $ant = $saldos[$i + 1] ?? null;
          $delta = $ant ? (float)$sl['saldo'] - (float)$ant['saldo'] : null;
        ?>
        <tr>
          <td><?= data_br($sl['data']) ?></td>
          <td><strong style="color:<?= (float)$sl['saldo'] >= 0 ? 'inherit' : '#b82020' ?>"><?= moeda((float)$sl['saldo']) ?></strong></td>
          <td><?php if ($delta === null): ?><span style="color:var(--cor-secundario)">—</span>
              <?php elseif ($delta >= 0): ?><span style="color:#1a7a45">▲ <?= moeda($delta) ?></span>
              <?php else: ?><span style="color:#b82020">▼ <?= moeda(abs($delta)) ?></span><?php endif; ?></td>
          <td><span class="tag <?= $sl['origem'] === 'asaas' ? 'tag-verde' : '' ?>"><?= $sl['origem'] === 'asaas' ? 'Asaas' : 'Manual' ?></span></td>
          <td><?= h($sl['observacoes'] ?? '—') ?></td>
          <td><a href="<?= base_url('contas/saldo?conta_id=' . $conta_id . '&id=' . $sl['id']) ?>" class="btn btn-secundario btn-sm">Editar</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php else: ?><p style="color:var(--cor-secundario)">Nenhum saldo registrado. Registre o primeiro para acompanhar a evolução.</p><?php endif; ?>
  </div>

</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
