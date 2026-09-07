<?php
/**
 * Lista de contratos do cliente.
 * @var array $cli · @var array $contratos · @var string $filtro_tipo, $filtro_status, $filtro_busca
 */
$page_title = 'Contratos';
require APP_ROOT . '/includes/header.php';
$tipos_label = [
  'locacao'=>'Locação','prestacao_servico'=>'Prestação de serviço','fornecimento'=>'Fornecimento',
  'compra_venda'=>'Compra e venda','sociedade'=>'Sociedade','emprestimo'=>'Empréstimo',
  'financiamento'=>'Financiamento','seguro'=>'Seguro','trabalho'=>'Trabalho','outro'=>'Outro',
];
$tipo_icone = ['locacao'=>'🔑','prestacao_servico'=>'🛠️','fornecimento'=>'📦','compra_venda'=>'🤝','sociedade'=>'🏢','emprestimo'=>'💰','financiamento'=>'🏦','seguro'=>'🛡️','trabalho'=>'👔','outro'=>'📜'];
$status_op  = ['ativo'=>'Ativo','em_negociacao'=>'Em negociação','suspenso'=>'Suspenso','encerrado'=>'Encerrado','rescindido'=>'Rescindido'];
$status_cor = ['ativo'=>'#1a7a45','em_negociacao'=>'#b45309','suspenso'=>'#64748b','encerrado'=>'#64748b','rescindido'=>'#b82020'];
$total_ativos = count(array_filter($contratos, fn($c) => $c['status'] === 'ativo'));
?>
<div class="container">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem">
    <div style="display:flex;align-items:center;gap:1rem">
      <a href="<?= base_url('dashboard') ?>" class="btn btn-secundario">← Voltar</a>
      <div>
        <h2 style="font-size:1.2rem;color:var(--cor-primaria)">📜 Contratos — <?= h($cli['nome']) ?></h2>
        <div style="font-size:.85rem;color:var(--cor-secundario)"><?= count($contratos) ?> contrato(s) · <?= $total_ativos ?> ativo(s)</div>
      </div>
    </div>
    <?php if ($usuario['nivel'] === 'admin'): ?>
    <a href="<?= base_url('contratos/novo') ?>" class="btn btn-primario">+ Cadastrar contrato</a>
    <?php endif; ?>
  </div>

  <div class="card" style="padding:1rem">
    <form method="get" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
      <div class="form-grupo" style="margin:0;flex:1;min-width:150px">
        <label>Tipo</label>
        <select name="tipo">
          <option value="">Todos</option>
          <?php foreach ($tipos_label as $v => $l): ?>
          <option value="<?= $v ?>" <?= $filtro_tipo === $v ? 'selected' : '' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-grupo" style="margin:0;flex:1;min-width:150px">
        <label>Situação</label>
        <select name="status">
          <option value="">Todas</option>
          <?php foreach ($status_op as $v => $l): ?>
          <option value="<?= $v ?>" <?= $filtro_status === $v ? 'selected' : '' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-grupo" style="margin:0;flex:2;min-width:180px">
        <label>Buscar</label>
        <input type="text" name="busca" placeholder="Número, objeto ou contraparte…" value="<?= h($filtro_busca) ?>">
      </div>
      <button type="submit" class="btn btn-secundario">Filtrar</button>
      <?php if ($filtro_tipo || $filtro_status || $filtro_busca): ?><a href="?" class="btn btn-secundario">Limpar</a><?php endif; ?>
    </form>
  </div>

  <?php if ($contratos): ?>
  <div class="imoveis-grid">
    <?php foreach ($contratos as $c):
      $vinc = Contrato::descreverVinculo($c['vinculo_tipo'], $c['vinculo_id'] ? (int)$c['vinculo_id'] : null);
      $rel = '';
      if ($c['status'] === 'ativo' && $c['data_fim']) {
        [$cl, $cor, $rel] = alerta_status(dias_ate($c['data_fim']));
      }
    ?>
    <a href="<?= base_url('contratos/editar?id=' . $c['id']) ?>" class="imovel-card">
      <div class="imovel-card-body">
        <div class="imovel-card-codigo" style="display:flex;align-items:center;gap:.4rem">
          <span style="font-size:1.15rem"><?= $tipo_icone[$c['tipo']] ?? '📜' ?></span>
          <?= h($c['codigo']) ?> · <?= $tipos_label[$c['tipo']] ?? $c['tipo'] ?>
          <span class="tag" style="margin-left:auto;color:#fff;background:<?= $status_cor[$c['status']] ?? '#64748b' ?>"><?= $status_op[$c['status']] ?? $c['status'] ?></span>
        </div>
        <div class="imovel-card-nome"><?= h($c['objeto'] ?: ($c['contraparte_nome'] ?: 'Contrato ' . ($c['numero'] ?: $c['codigo']))) ?></div>
        <div class="imovel-card-local">
          <?php if ($c['contraparte_nome']): ?><?= h($c['contraparte_nome']) ?><?php endif; ?>
          <?php if ($vinc): ?><?= $c['contraparte_nome'] ? ' · ' : '' ?><?= h($vinc) ?><?php endif; ?>
        </div>
        <div class="imovel-card-rodape" style="display:flex;justify-content:space-between;align-items:center;gap:.5rem">
          <div>
            <?php if ($c['valor'] !== null): ?>
            <span class="imovel-card-valor"><?= moeda((float)$c['valor']) ?></span>
            <?php if ($c['periodicidade'] && $c['periodicidade'] !== 'unico'): ?><span style="font-size:.75rem;color:var(--cor-secundario)">/<?= h($c['periodicidade']) ?></span><?php endif; ?>
            <?php endif; ?>
          </div>
          <?php if ($c['data_fim']): ?>
          <div style="text-align:right">
            <div style="font-size:.78rem;color:var(--cor-secundario)">até <?= data_br($c['data_fim']) ?></div>
            <?php if ($rel): ?><div style="font-size:.75rem;font-weight:600;color:<?= $cor ?>"><?= h($rel) ?></div><?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="card" style="text-align:center;padding:3rem">
    <div style="font-size:3rem;margin-bottom:1rem">📜</div>
    <p style="color:var(--cor-secundario)">Nenhum contrato cadastrado.</p>
    <?php if ($usuario['nivel'] === 'admin'): ?>
    <a href="<?= base_url('contratos/novo') ?>" class="btn btn-primario" style="margin-top:1rem">+ Cadastrar primeiro contrato</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
