<?php
/**
 * Lista de seguros do cliente.
 * @var array  $cli
 * @var array  $seguros
 * @var string $filtro_tipo, $filtro_status, $filtro_busca
 */
$page_title = 'Seguros';
require APP_ROOT . '/includes/header.php';
$tipos_label = [
  'vida'=>'Vida','saude'=>'Saúde','veiculo'=>'Veículo','residencial'=>'Residencial',
  'imovel'=>'Imóvel','embarcacao'=>'Embarcação','empresarial'=>'Empresarial','viagem'=>'Viagem','outro'=>'Outro',
];
$tipo_icone = ['vida'=>'❤️','saude'=>'🩺','veiculo'=>'🚗','residencial'=>'🏠','imovel'=>'🏛️','embarcacao'=>'🛥️','empresarial'=>'🏢','viagem'=>'✈️','outro'=>'🛡️'];
$status_op  = ['vigente'=>'Vigente','em_cotacao'=>'Em cotação','vencida'=>'Vencida','cancelada'=>'Cancelada'];
$status_cor = ['vigente'=>'#1a7a45','em_cotacao'=>'#b45309','vencida'=>'#b82020','cancelada'=>'#64748b'];
// Prêmio anual somado (só vigentes) para o resumo.
$total_premio = array_sum(array_map(fn($s) => $s['status'] === 'vigente' ? (float)($s['premio'] ?? 0) : 0, $seguros));
?>
<div class="container">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem">
    <div style="display:flex;align-items:center;gap:1rem">
      <a href="<?= base_url('dashboard') ?>" class="btn btn-secundario">← Voltar</a>
      <div>
        <h2 style="font-size:1.2rem;color:var(--cor-primaria)">🛡️ Seguros — <?= h($cli['nome']) ?></h2>
        <div style="font-size:.85rem;color:var(--cor-secundario)"><?= count($seguros) ?> apólice(s)</div>
      </div>
    </div>
    <?php if ($usuario['nivel'] === 'admin'): ?>
    <a href="<?= base_url('seguros/novo') ?>" class="btn btn-primario">+ Cadastrar seguro</a>
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
        <input type="text" name="busca" placeholder="Seguradora, corretora ou apólice…" value="<?= h($filtro_busca) ?>">
      </div>
      <button type="submit" class="btn btn-secundario">Filtrar</button>
      <?php if ($filtro_tipo || $filtro_status || $filtro_busca): ?><a href="?" class="btn btn-secundario">Limpar</a><?php endif; ?>
    </form>
  </div>

  <?php if ($seguros): ?>
  <?php if ($total_premio > 0): ?>
  <div class="card" style="padding:1rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem">
    <span style="font-size:.9rem;color:var(--cor-secundario)">Prêmio total (apólices vigentes)</span>
    <strong style="font-size:1.15rem;color:var(--cor-primaria)"><?= moeda($total_premio) ?></strong>
  </div>
  <?php endif; ?>
  <div class="imoveis-grid">
    <?php foreach ($seguros as $s):
      $vinc = Seguro::descreverVinculo($s['item_tipo'], $s['item_id'] ? (int)$s['item_id'] : null);
      $rel = '';
      if ($s['status'] === 'vigente' && $s['vigencia_fim']) {
        [$cl, $cor, $rel] = alerta_status(dias_ate($s['vigencia_fim']));
      }
    ?>
    <a href="<?= base_url('seguros/editar?id=' . $s['id']) ?>" class="imovel-card">
      <div class="imovel-card-body">
        <div class="imovel-card-codigo" style="display:flex;align-items:center;gap:.4rem">
          <span style="font-size:1.15rem"><?= $tipo_icone[$s['tipo']] ?? '🛡️' ?></span>
          <?= h($s['codigo']) ?> · <?= $tipos_label[$s['tipo']] ?? $s['tipo'] ?>
          <span class="tag" style="margin-left:auto;color:#fff;background:<?= $status_cor[$s['status']] ?? '#64748b' ?>"><?= $status_op[$s['status']] ?? $s['status'] ?></span>
        </div>
        <div class="imovel-card-nome"><?= h($s['seguradora'] ?: 'Apólice ' . ($s['numero_apolice'] ?: $s['codigo'])) ?></div>
        <div class="imovel-card-local">
          <?php if ($s['numero_apolice']): ?>Apólice <?= h($s['numero_apolice']) ?><?php endif; ?>
          <?php if ($vinc): ?><?= $s['numero_apolice'] ? ' · ' : '' ?><?= h($vinc) ?><?php endif; ?>
        </div>
        <div class="imovel-card-rodape" style="display:flex;justify-content:space-between;align-items:center;gap:.5rem">
          <div>
            <?php if ($s['valor_segurado'] !== null): ?>
            <span class="imovel-card-valor"><?= moeda((float)$s['valor_segurado']) ?></span>
            <span style="font-size:.75rem;color:var(--cor-secundario)">segurado</span>
            <?php endif; ?>
          </div>
          <?php if ($s['vigencia_fim']): ?>
          <div style="text-align:right">
            <div style="font-size:.78rem;color:var(--cor-secundario)">até <?= data_br($s['vigencia_fim']) ?></div>
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
    <div style="font-size:3rem;margin-bottom:1rem">🛡️</div>
    <p style="color:var(--cor-secundario)">Nenhum seguro cadastrado.</p>
    <?php if ($usuario['nivel'] === 'admin'): ?>
    <a href="<?= base_url('seguros/novo') ?>" class="btn btn-primario" style="margin-top:1rem">+ Cadastrar primeiro seguro</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
