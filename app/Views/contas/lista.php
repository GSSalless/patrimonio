<?php
/**
 * Lista de contas financeiras do cliente.
 * @var array  $cli
 * @var array  $contas
 * @var string $filtro_tipo
 * @var string $filtro_busca
 */
$page_title = 'Contas Financeiras';
require APP_ROOT . '/includes/header.php';
$tipos_label = [
  'corrente'      => 'Conta Corrente',
  'poupanca'      => 'Poupança',
  'pagamento'     => 'Conta de Pagamento',
  'investimento'  => 'Investimento',
  'internacional' => 'Internacional',
  'outro'         => 'Outra',
];
$tipo_icone = ['corrente'=>'🏦','poupanca'=>'🐷','pagamento'=>'💳','investimento'=>'📈','internacional'=>'🌎','outro'=>'💼'];
?>
<div class="container">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem">
    <div style="display:flex;align-items:center;gap:1rem">
      <a href="<?= base_url('dashboard') ?>" class="btn btn-secundario">← Voltar</a>
      <div>
        <h2 style="font-size:1.2rem;color:var(--cor-primaria)">Contas Financeiras — <?= h($cli['nome']) ?></h2>
        <div style="font-size:.85rem;color:var(--cor-secundario)"><?= count($contas) ?> conta(s)</div>
      </div>
    </div>
    <?php if ($usuario['nivel'] === 'admin'): ?>
    <a href="<?= base_url('contas/novo') ?>" class="btn btn-primario">+ Cadastrar conta</a>
    <?php endif; ?>
  </div>

  <div class="card" style="padding:1rem">
    <form method="get" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
      <div class="form-grupo" style="margin:0;flex:1;min-width:160px">
        <label>Tipo</label>
        <select name="tipo">
          <option value="">Todos</option>
          <?php foreach ($tipos_label as $v => $l): ?>
          <option value="<?= $v ?>" <?= $filtro_tipo === $v ? 'selected' : '' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-grupo" style="margin:0;flex:2;min-width:200px">
        <label>Buscar</label>
        <input type="text" name="busca" placeholder="Apelido, instituição ou nº da conta…" value="<?= h($filtro_busca) ?>">
      </div>
      <button type="submit" class="btn btn-secundario">Filtrar</button>
      <?php if ($filtro_tipo || $filtro_busca): ?><a href="?" class="btn btn-secundario">Limpar</a><?php endif; ?>
    </form>
  </div>

  <?php if ($contas): ?>
  <?php $total = array_sum(array_map(fn($c) => $c['moeda'] === 'BRL' ? (float)($c['saldo_atual'] ?? 0) : 0, $contas)); ?>
  <div class="card" style="padding:1rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem">
    <span style="font-size:.9rem;color:var(--cor-secundario)">Saldo consolidado (contas em BRL)</span>
    <strong style="font-size:1.15rem;color:<?= $total >= 0 ? '#1a7a45' : '#b82020' ?>"><?= moeda($total) ?></strong>
  </div>
  <div class="imoveis-grid">
    <?php foreach ($contas as $c): ?>
    <a href="<?= base_url('contas/editar?id=' . $c['id']) ?>" class="imovel-card">
      <div class="imovel-card-body">
        <div class="imovel-card-codigo" style="display:flex;align-items:center;gap:.4rem">
          <span style="font-size:1.15rem"><?= $tipo_icone[$c['tipo']] ?? '🏦' ?></span>
          <?= h($c['codigo']) ?> · <?= $tipos_label[$c['tipo']] ?? $c['tipo'] ?>
          <?php if ($c['integracao'] === 'asaas'): ?><span class="tag tag-verde" style="margin-left:auto">Asaas</span><?php endif; ?>
        </div>
        <div class="imovel-card-nome"><?= h($c['apelido']) ?></div>
        <div class="imovel-card-local">
          <?= h($c['instituicao'] ?? '—') ?>
          <?php if ($c['agencia'] || $c['numero_conta']): ?> · Ag. <?= h($c['agencia'] ?? '—') ?> · Cc. <?= h($c['numero_conta'] ?? '—') ?><?php endif; ?>
          <?php if ($c['moeda'] !== 'BRL'): ?> · <?= h($c['moeda']) ?><?php endif; ?>
        </div>
        <div class="imovel-card-rodape">
          <?php if ($c['saldo_atual'] !== null): ?>
          <span class="imovel-card-valor" style="color:<?= (float)$c['saldo_atual'] >= 0 ? 'inherit' : '#b82020' ?>"><?= moeda((float)$c['saldo_atual']) ?></span>
          <span style="font-size:.78rem;color:var(--cor-secundario)">em <?= data_br($c['saldo_data']) ?></span>
          <?php else: ?>
          <span style="font-size:.85rem;color:var(--cor-secundario)">Sem saldo registrado</span>
          <?php endif; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="card" style="text-align:center;padding:3rem">
    <div style="font-size:3rem;margin-bottom:1rem">🏦</div>
    <p style="color:var(--cor-secundario)">Nenhuma conta cadastrada.</p>
    <?php if ($usuario['nivel'] === 'admin'): ?>
    <a href="<?= base_url('contas/novo') ?>" class="btn btn-primario" style="margin-top:1rem">+ Cadastrar primeira conta</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
