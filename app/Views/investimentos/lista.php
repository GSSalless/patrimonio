<?php
/**
 * Lista de investimentos do cliente.
 * @var array  $cli
 * @var array  $investimentos
 * @var string $filtro_classe, $filtro_status, $filtro_busca
 */
$page_title = 'Investimentos';
require APP_ROOT . '/includes/header.php';
$classe_label = [
  'renda_fixa'=>'Renda fixa','tesouro'=>'Tesouro Direto','fundo'=>'Fundo','multimercado'=>'Multimercado',
  'acoes'=>'Ações','previdencia'=>'Previdência','offshore'=>'Offshore','cripto'=>'Cripto','outro'=>'Outro',
];
$classe_icone = ['renda_fixa'=>'🏦','tesouro'=>'🏛️','fundo'=>'📊','multimercado'=>'📊','acoes'=>'📈','previdencia'=>'👴','offshore'=>'🌎','cripto'=>'₿','outro'=>'📈'];
$status_op  = ['ativo'=>'Ativo','resgatado'=>'Resgatado','vencido'=>'Vencido'];
$status_cor = ['ativo'=>'#1a7a45','resgatado'=>'#64748b','vencido'=>'#b45309'];
// Totais dos ativos.
$total_atual    = array_sum(array_map(fn($i) => $i['status'] === 'ativo' ? (float)($i['valor_atual'] ?? 0) : 0, $investimentos));
$total_aplicado = array_sum(array_map(fn($i) => $i['status'] === 'ativo' ? (float)($i['valor_aplicado'] ?? 0) : 0, $investimentos));
$total_ganho    = ($total_atual > 0 && $total_aplicado > 0) ? $total_atual - $total_aplicado : null;
?>
<div class="container">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem">
    <div style="display:flex;align-items:center;gap:1rem">
      <a href="<?= base_url('dashboard') ?>" class="btn btn-secundario">← Voltar</a>
      <div>
        <h2 style="font-size:1.2rem;color:var(--cor-primaria)">📈 Investimentos — <?= h($cli['nome']) ?></h2>
        <div style="font-size:.85rem;color:var(--cor-secundario)"><?= count($investimentos) ?> aplicação(ões)</div>
      </div>
    </div>
    <?php if ($usuario['nivel'] === 'admin'): ?>
    <a href="<?= base_url('investimentos/novo') ?>" class="btn btn-primario">+ Cadastrar investimento</a>
    <?php endif; ?>
  </div>

  <div class="card" style="padding:1rem">
    <form method="get" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
      <div class="form-grupo" style="margin:0;flex:1;min-width:150px">
        <label>Classe</label>
        <select name="classe">
          <option value="">Todas</option>
          <?php foreach ($classe_label as $v => $l): ?>
          <option value="<?= $v ?>" <?= $filtro_classe === $v ? 'selected' : '' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-grupo" style="margin:0;flex:1;min-width:140px">
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
        <input type="text" name="busca" placeholder="Nome, instituição ou emissor…" value="<?= h($filtro_busca) ?>">
      </div>
      <button type="submit" class="btn btn-secundario">Filtrar</button>
      <?php if ($filtro_classe || $filtro_status || $filtro_busca): ?><a href="?" class="btn btn-secundario">Limpar</a><?php endif; ?>
    </form>
  </div>

  <?php if ($investimentos): ?>
  <?php if ($total_atual > 0): ?>
  <div class="card" style="padding:1rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem">
    <span style="font-size:.9rem;color:var(--cor-secundario)">Carteira (ativos)</span>
    <div style="display:flex;gap:1.5rem;flex-wrap:wrap;align-items:baseline">
      <span>Aplicado: <strong><?= moeda($total_aplicado) ?></strong></span>
      <span>Atual: <strong style="color:var(--cor-primaria);font-size:1.1rem"><?= moeda($total_atual) ?></strong></span>
      <?php if ($total_ganho !== null): ?>
      <span style="color:<?= $total_ganho >= 0 ? '#1a7a45' : '#b82020' ?>"><?= $total_ganho >= 0 ? '▲' : '▼' ?> <?= moeda(abs($total_ganho)) ?></span>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
  <div class="imoveis-grid">
    <?php foreach ($investimentos as $i):
      $ap = (float)($i['valor_aplicado'] ?? 0); $at = (float)($i['valor_atual'] ?? 0);
      $g = ($ap > 0 && $at > 0) ? $at - $ap : null;
    ?>
    <a href="<?= base_url('investimentos/editar?id=' . $i['id']) ?>" class="imovel-card">
      <div class="imovel-card-body">
        <div class="imovel-card-codigo" style="display:flex;align-items:center;gap:.4rem">
          <span style="font-size:1.15rem"><?= $classe_icone[$i['classe']] ?? '📈' ?></span>
          <?= h($i['codigo']) ?> · <?= $classe_label[$i['classe']] ?? $i['classe'] ?>
          <span class="tag" style="margin-left:auto;color:#fff;background:<?= $status_cor[$i['status']] ?? '#64748b' ?>"><?= $status_op[$i['status']] ?? $i['status'] ?></span>
        </div>
        <div class="imovel-card-nome"><?= h($i['nome']) ?></div>
        <div class="imovel-card-local">
          <?= h($i['instituicao'] ?: '—') ?>
          <?php if ($i['rentabilidade_contratada']): ?> · <?= h($i['rentabilidade_contratada']) ?><?php endif; ?>
        </div>
        <div class="imovel-card-rodape" style="display:flex;justify-content:space-between;align-items:center;gap:.5rem">
          <div>
            <?php if ($at > 0): ?>
            <span class="imovel-card-valor"><?= moeda($at) ?></span>
            <?php if ($g !== null): ?><span style="font-size:.78rem;font-weight:600;color:<?= $g >= 0 ? '#1a7a45' : '#b82020' ?>"><?= $g >= 0 ? '▲' : '▼' ?> <?= moeda(abs($g)) ?></span><?php endif; ?>
            <?php endif; ?>
          </div>
          <?php if ($i['data_vencimento']): ?>
          <div style="font-size:.78rem;color:var(--cor-secundario)">vence <?= data_br($i['data_vencimento']) ?></div>
          <?php endif; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="card" style="text-align:center;padding:3rem">
    <div style="font-size:3rem;margin-bottom:1rem">📈</div>
    <p style="color:var(--cor-secundario)">Nenhum investimento cadastrado.</p>
    <?php if ($usuario['nivel'] === 'admin'): ?>
    <a href="<?= base_url('investimentos/novo') ?>" class="btn btn-primario" style="margin-top:1rem">+ Cadastrar primeiro investimento</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
