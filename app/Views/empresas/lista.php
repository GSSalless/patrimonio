<?php
/**
 * Lista de empresas do cliente.
 * @var array  $cli
 * @var array  $empresas
 * @var string $filtro_natureza, $filtro_busca
 */
$page_title = 'Empresas';
require APP_ROOT . '/includes/header.php';
$nat_label = [
  'operacional'=>'Operacional','holding_patrimonial'=>'Holding patrimonial',
  'holding_participacao'=>'Holding de participação','spe'=>'SPE','outro'=>'Outra',
];
$nat_icone = ['operacional'=>'🏢','holding_patrimonial'=>'🏛️','holding_participacao'=>'🧬','spe'=>'🏗️','outro'=>'🏢'];
$sit_cor   = ['ativa'=>'#1a7a45','baixada'=>'#b82020','suspensa'=>'#b45309','inapta'=>'#64748b'];
$total_capital = array_sum(array_map(fn($e) => (float)($e['capital_social'] ?? 0), $empresas));
?>
<div class="container">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem">
    <div style="display:flex;align-items:center;gap:1rem">
      <a href="<?= base_url('dashboard') ?>" class="btn btn-secundario">← Voltar</a>
      <div>
        <h2 style="font-size:1.2rem;color:var(--cor-primaria)">🏢 Empresas — <?= h($cli['nome']) ?></h2>
        <div style="font-size:.85rem;color:var(--cor-secundario)"><?= count($empresas) ?> empresa(s)</div>
      </div>
    </div>
    <?php if ($usuario['nivel'] === 'admin'): ?>
    <a href="<?= base_url('empresas/novo') ?>" class="btn btn-primario">+ Cadastrar empresa</a>
    <?php endif; ?>
  </div>

  <div class="card" style="padding:1rem">
    <form method="get" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
      <div class="form-grupo" style="margin:0;flex:1;min-width:170px">
        <label>Natureza</label>
        <select name="natureza">
          <option value="">Todas</option>
          <?php foreach ($nat_label as $v => $l): ?>
          <option value="<?= $v ?>" <?= $filtro_natureza === $v ? 'selected' : '' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-grupo" style="margin:0;flex:2;min-width:200px">
        <label>Buscar</label>
        <input type="text" name="busca" placeholder="Razão social, fantasia ou CNPJ…" value="<?= h($filtro_busca) ?>">
      </div>
      <button type="submit" class="btn btn-secundario">Filtrar</button>
      <?php if ($filtro_natureza || $filtro_busca): ?><a href="?" class="btn btn-secundario">Limpar</a><?php endif; ?>
    </form>
  </div>

  <?php if ($empresas): ?>
  <?php if ($total_capital > 0): ?>
  <div class="card" style="padding:1rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem">
    <span style="font-size:.9rem;color:var(--cor-secundario)">Capital social somado</span>
    <strong style="font-size:1.15rem;color:var(--cor-primaria)"><?= moeda($total_capital) ?></strong>
  </div>
  <?php endif; ?>
  <div class="imoveis-grid">
    <?php foreach ($empresas as $e): ?>
    <a href="<?= base_url('empresas/editar?id=' . $e['id']) ?>" class="imovel-card">
      <div class="imovel-card-body">
        <div class="imovel-card-codigo" style="display:flex;align-items:center;gap:.4rem">
          <span style="font-size:1.15rem"><?= $nat_icone[$e['natureza']] ?? '🏢' ?></span>
          <?= h($e['codigo']) ?> · <?= $nat_label[$e['natureza']] ?? $e['natureza'] ?>
          <span class="tag" style="margin-left:auto;color:#fff;background:<?= $sit_cor[$e['situacao']] ?? '#64748b' ?>"><?= ucfirst($e['situacao']) ?></span>
        </div>
        <div class="imovel-card-nome"><?= h($e['nome_fantasia'] ?: $e['razao_social']) ?></div>
        <div class="imovel-card-local">
          <?php if ($e['nome_fantasia']): ?><?= h($e['razao_social']) ?><?php endif; ?>
          <?php if ($e['cnpj']): ?><?= $e['nome_fantasia'] ? ' · ' : '' ?>CNPJ <?= h($e['cnpj']) ?><?php endif; ?>
        </div>
        <div class="imovel-card-rodape">
          <?php if ($e['capital_social'] !== null): ?>
          <span class="imovel-card-valor"><?= moeda((float)$e['capital_social']) ?></span>
          <span style="font-size:.75rem;color:var(--cor-secundario)">capital social</span>
          <?php else: ?>
          <span style="font-size:.85rem;color:var(--cor-secundario)"><?= h(empresa_regime_label($e['regime_tributario'])) ?></span>
          <?php endif; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="card" style="text-align:center;padding:3rem">
    <div style="font-size:3rem;margin-bottom:1rem">🏢</div>
    <p style="color:var(--cor-secundario)">Nenhuma empresa cadastrada.</p>
    <?php if ($usuario['nivel'] === 'admin'): ?>
    <a href="<?= base_url('empresas/novo') ?>" class="btn btn-primario" style="margin-top:1rem">+ Cadastrar primeira empresa</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
