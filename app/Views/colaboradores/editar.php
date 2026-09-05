<?php
/**
 * Edição de colaborador + documentos + dependentes + histórico de RH.
 * @var array  $cli
 * @var array  $colaborador
 * @var array  $d
 * @var string $erro
 * @var string $ok
 * @var array  $docs_list, $dependentes, $historico
 */
$page_title = 'Editar Colaborador — ' . $colaborador['nome'];
require APP_ROOT . '/includes/header.php';
$co_id = (int) $colaborador['id'];
$parentescos = ['conjuge'=>'Cônjuge','filho'=>'Filho','filha'=>'Filha','pai'=>'Pai','mae'=>'Mãe','outro'=>'Outro'];
$hist_tipos  = ['salario'=>'Salário','promocao'=>'Promoção','avaliacao'=>'Avaliação','ferias'=>'Férias','treinamento'=>'Treinamento','advertencia'=>'Advertência','atestado'=>'Atestado','falta'=>'Falta','beneficio'=>'Benefício','outro'=>'Outro'];
$hist_cor = ['salario'=>'#1a7a45','promocao'=>'#0891b2','avaliacao'=>'#6366f1','ferias'=>'#c9a227','treinamento'=>'#3fae7a','advertencia'=>'#b82020','atestado'=>'#b45309','falta'=>'#b45309','beneficio'=>'#9b6dd6','outro'=>'#64748b'];
?>
<div class="container" style="max-width:960px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <div>
      <div style="font-size:.8rem;color:var(--cor-secundario);font-weight:600"><?= h($colaborador['codigo']) ?> · <?= h(colaborador_status_label($colaborador['status'])) ?></div>
      <h2 class="card-titulo"><?= h($colaborador['nome']) ?></h2>
      <div style="font-size:.85rem;color:var(--cor-secundario)">
        Cliente: <strong><?= h($cli['nome']) ?></strong>
        <?php if ($colaborador['cargo']): ?> · <?= h($colaborador['cargo']) ?><?php endif; ?>
        <?php if ($colaborador['tipo_contrato']): ?> · <?= h(colaborador_contrato_label($colaborador['tipo_contrato'])) ?><?php endif; ?>
      </div>
    </div>
    <a href="<?= base_url('colaboradores') ?>" class="btn btn-secundario">← Voltar</a>
  </div>

  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>
  <?php if ($ok):   ?><div class="alerta alerta-sucesso"><?= h($ok) ?></div><?php endif; ?>

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

      <div class="form-secao" style="margin-top:1.5rem"><div class="form-secao-titulo">7. Observações</div></div>
      <div class="form-grupo"><textarea name="observacoes" rows="3"><?= h($d['observacoes'] ?? '') ?></textarea></div>

      <div style="display:flex;gap:.75rem;margin-top:2rem;padding-top:1.25rem;border-top:1px solid #dde2ea">
        <button type="submit" class="btn btn-primario">Salvar alterações</button>
        <a href="<?= base_url('colaboradores') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>

  <!-- DEPENDENTES -->
  <div class="card" id="dependentes" style="margin-top:1.5rem">
    <div class="card-header" style="margin-bottom:1rem">
      <div><h3 class="card-titulo">👨‍👩‍👧 Dependentes</h3><div style="font-size:.85rem;color:var(--cor-secundario)"><?= count($dependentes) ?> registro(s)</div></div>
    </div>
    <?php if ($dependentes): ?>
    <div style="overflow-x:auto">
    <table class="tabela">
      <thead><tr><th>Nome</th><th>Parentesco</th><th>Nascimento</th><th>CPF</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($dependentes as $dep): ?>
        <tr>
          <td><strong><?= h($dep['nome']) ?></strong></td>
          <td><span class="tag"><?= h($parentescos[$dep['parentesco']] ?? $dep['parentesco']) ?></span></td>
          <td><?= $dep['data_nascimento'] ? data_br($dep['data_nascimento']) : '—' ?></td>
          <td><?= h($dep['cpf'] ?: '—') ?></td>
          <td><a href="<?= base_url('colaboradores/dependente-remover?colaborador_id=' . $co_id . '&id=' . $dep['id']) ?>" onclick="return confirm('Remover dependente?')" class="btn btn-secundario btn-sm">Remover</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php else: ?><p style="color:var(--cor-secundario)">Nenhum dependente cadastrado.</p><?php endif; ?>

    <form method="post" action="<?= base_url('colaboradores/dependente?colaborador_id=' . $co_id) ?>" style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--cor-borda)">
      <div class="form-grid form-grid-4">
        <div class="form-grupo" style="grid-column:span 2"><label>Nome *</label><input type="text" name="nome" required></div>
        <div class="form-grupo">
          <label>Parentesco</label>
          <select name="parentesco"><?php foreach ($parentescos as $v => $l): ?><option value="<?= $v ?>"><?= $l ?></option><?php endforeach; ?></select>
        </div>
        <div class="form-grupo"><label>Nascimento</label><input type="date" name="data_nascimento"></div>
        <div class="form-grupo"><label>CPF</label><input type="text" name="cpf"></div>
        <div class="form-grupo" style="grid-column:span 2"><label>Observações</label><input type="text" name="observacoes"></div>
        <div class="form-grupo" style="align-self:end"><button type="submit" class="btn btn-primario">+ Adicionar</button></div>
      </div>
    </form>
  </div>

  <!-- HISTÓRICO -->
  <div class="card" id="historico" style="margin-top:1.5rem">
    <div class="card-header" style="margin-bottom:1rem">
      <div><h3 class="card-titulo">📋 Histórico</h3><div style="font-size:.85rem;color:var(--cor-secundario)">Salários, promoções, férias, advertências, atestados, treinamentos…</div></div>
    </div>
    <?php if ($historico): ?>
    <div style="overflow-x:auto">
    <table class="tabela">
      <thead><tr><th>Data</th><th>Tipo</th><th>Descrição</th><th style="text-align:right">Valor</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($historico as $hist): ?>
        <tr>
          <td><?= data_br($hist['data']) ?><?php if ($hist['data_fim']): ?> → <?= data_br($hist['data_fim']) ?><?php endif; ?></td>
          <td><span class="tag" style="color:#fff;background:<?= $hist_cor[$hist['tipo']] ?? '#64748b' ?>"><?= h(colaborador_hist_tipo_label($hist['tipo'])) ?></span></td>
          <td><?= h($hist['descricao'] ?: '—') ?></td>
          <td style="text-align:right"><?= $hist['valor'] !== null ? moeda((float)$hist['valor']) : '—' ?></td>
          <td><a href="<?= base_url('colaboradores/historico-remover?colaborador_id=' . $co_id . '&id=' . $hist['id']) ?>" onclick="return confirm('Remover registro?')" class="btn btn-secundario btn-sm">Remover</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php else: ?><p style="color:var(--cor-secundario)">Nenhum registro no histórico.</p><?php endif; ?>

    <form method="post" action="<?= base_url('colaboradores/historico?colaborador_id=' . $co_id) ?>" style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--cor-borda)">
      <div class="form-grid form-grid-4">
        <div class="form-grupo"><label>Data *</label><input type="date" name="data" required value="<?= date('Y-m-d') ?>"></div>
        <div class="form-grupo"><label>Fim (período)</label><input type="date" name="data_fim"></div>
        <div class="form-grupo">
          <label>Tipo</label>
          <select name="tipo"><?php foreach ($hist_tipos as $v => $l): ?><option value="<?= $v ?>"><?= $l ?></option><?php endforeach; ?></select>
        </div>
        <div class="form-grupo"><label>Valor (R$)</label><input type="text" name="valor" placeholder="0,00"></div>
        <div class="form-grupo" style="grid-column:span 3"><label>Descrição</label><input type="text" name="descricao"></div>
        <div class="form-grupo" style="align-self:end"><button type="submit" class="btn btn-primario">+ Adicionar</button></div>
      </div>
    </form>
  </div>

</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
