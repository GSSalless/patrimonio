<?php
/**
 * Edição de investimento + documentos + histórico de movimentos.
 * @var array  $cli
 * @var array  $inv
 * @var array  $d
 * @var string $erro
 * @var string $ok
 * @var array  $docs_list
 * @var array  $contas
 * @var array  $movimentos
 */
$page_title = 'Editar Investimento — ' . $inv['nome'];
require APP_ROOT . '/includes/header.php';
$inv_id = (int) $inv['id'];
$mov_label = ['aplicacao'=>'Aplicação','resgate'=>'Resgate','rendimento'=>'Rendimento','ajuste'=>'Ajuste'];
$mov_cor   = ['aplicacao'=>'#1a7a45','resgate'=>'#b82020','rendimento'=>'#0891b2','ajuste'=>'#64748b'];

// Ganho/perda = valor atual − valor aplicado.
$aplicado = (float) ($inv['valor_aplicado'] ?? 0);
$atual    = (float) ($inv['valor_atual'] ?? 0);
$ganho    = ($aplicado > 0 && $atual > 0) ? $atual - $aplicado : null;
$ganho_pct = ($ganho !== null && $aplicado > 0) ? ($ganho / $aplicado * 100) : null;
?>
<div class="container" style="max-width:960px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <div>
      <div style="font-size:.8rem;color:var(--cor-secundario);font-weight:600"><?= h($inv['codigo']) ?> · <?= h(investimento_classe_label($inv['classe'])) ?></div>
      <h2 class="card-titulo"><?= h($inv['nome']) ?></h2>
      <div style="font-size:.85rem;color:var(--cor-secundario)">
        Cliente: <strong><?= h($cli['nome']) ?></strong>
        <?php if ($inv['instituicao']): ?> · <?= h($inv['instituicao']) ?><?php endif; ?>
      </div>
    </div>
    <a href="<?= base_url('investimentos') ?>" class="btn btn-secundario">← Voltar</a>
  </div>

  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>
  <?php if ($ok):   ?><div class="alerta alerta-sucesso"><?= h($ok) ?></div><?php endif; ?>

  <!-- Resumo de posição -->
  <?php if ($atual > 0 || $aplicado > 0): ?>
  <div class="card" style="display:flex;flex-wrap:wrap;gap:1.5rem;align-items:center;justify-content:space-between">
    <div><div style="font-size:.78rem;color:var(--cor-secundario)">Aplicado</div><strong style="font-size:1.1rem"><?= moeda($aplicado) ?></strong></div>
    <div><div style="font-size:.78rem;color:var(--cor-secundario)">Valor atual</div><strong style="font-size:1.1rem;color:var(--cor-primaria)"><?= moeda($atual) ?></strong></div>
    <?php if ($ganho !== null): ?>
    <div><div style="font-size:.78rem;color:var(--cor-secundario)">Ganho/perda</div>
      <strong style="font-size:1.1rem;color:<?= $ganho >= 0 ? '#1a7a45' : '#b82020' ?>">
        <?= $ganho >= 0 ? '▲' : '▼' ?> <?= moeda(abs($ganho)) ?>
        <?php if ($ganho_pct !== null): ?><span style="font-size:.82rem">(<?= number_format($ganho_pct, 2, ',', '.') ?>%)</span><?php endif; ?>
      </strong>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

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
      <?php if ($docs_list): ?>
      <div style="display:flex;flex-direction:column;gap:.4rem;margin-top:.5rem">
        <?php foreach ($docs_list as $doc): ?>
        <a href="<?= url_documento($doc) ?>" target="_blank"
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
        <a href="<?= base_url('investimentos') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>

  <!-- MOVIMENTOS -->
  <div class="card" id="movimentos" style="margin-top:1.5rem">
    <div class="card-header" style="margin-bottom:1rem">
      <div>
        <h3 class="card-titulo">💸 Movimentos</h3>
        <div style="font-size:.85rem;color:var(--cor-secundario)"><?= count($movimentos) ?> registro(s) — aplicações, resgates e rendimentos</div>
      </div>
    </div>

    <?php if ($movimentos): ?>
    <div style="overflow-x:auto">
    <table class="tabela">
      <thead><tr><th>Data</th><th>Tipo</th><th style="text-align:right">Valor</th><th>Observações</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($movimentos as $m): ?>
        <tr>
          <td><?= data_br($m['data']) ?></td>
          <td><span class="tag" style="color:#fff;background:<?= $mov_cor[$m['tipo']] ?? '#64748b' ?>"><?= $mov_label[$m['tipo']] ?? $m['tipo'] ?></span></td>
          <td style="text-align:right"><strong><?= moeda((float)$m['valor']) ?></strong></td>
          <td><?= h($m['observacoes'] ?: '—') ?></td>
          <td><a href="<?= base_url('investimentos/movimento-remover?investimento_id=' . $inv_id . '&id=' . $m['id']) ?>"
                 onclick="return confirm('Remover este movimento?')" class="btn btn-secundario btn-sm">Remover</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php else: ?>
    <p style="color:var(--cor-secundario)">Nenhum movimento registrado.</p>
    <?php endif; ?>

    <!-- Adicionar movimento -->
    <form method="post" action="<?= base_url('investimentos/movimento?investimento_id=' . $inv_id) ?>" style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--cor-borda)">
      <div class="form-grid form-grid-4">
        <div class="form-grupo"><label>Data</label><input type="date" name="data" value="<?= date('Y-m-d') ?>"></div>
        <div class="form-grupo">
          <label>Tipo</label>
          <select name="tipo">
            <?php foreach ($mov_label as $v => $l): ?><option value="<?= $v ?>"><?= $l ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo"><label>Valor (R$) *</label><input type="text" name="valor" required placeholder="0,00"></div>
        <div class="form-grupo" style="align-self:end"><button type="submit" class="btn btn-primario">+ Adicionar</button></div>
        <div class="form-grupo" style="grid-column:1/-1"><label>Observações</label><input type="text" name="observacoes"></div>
      </div>
    </form>
  </div>

</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
