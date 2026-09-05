<?php
/**
 * Lista de fornecedores do cliente.
 * @var array  $cli
 * @var array  $fornecedores
 * @var string $filtro_categoria, $filtro_busca
 */
$page_title = 'Fornecedores';
require APP_ROOT . '/includes/header.php';
$cat_label = [
  'contabilidade'=>'Contabilidade','juridico'=>'Jurídico','seguros'=>'Seguros','marina'=>'Marina',
  'saude'=>'Saúde','tecnologia'=>'Tecnologia','rh'=>'RH','imobiliaria'=>'Imobiliária',
  'manutencao'=>'Manutenção','construcao'=>'Construção','financeiro'=>'Financeiro','transporte'=>'Transporte','outro'=>'Outro',
];
$cat_icone = ['contabilidade'=>'🧮','juridico'=>'⚖️','seguros'=>'🛡️','marina'=>'⚓','saude'=>'🩺','tecnologia'=>'💻','rh'=>'👥','imobiliaria'=>'🏠','manutencao'=>'🔧','construcao'=>'🏗️','financeiro'=>'💰','transporte'=>'🚚','outro'=>'🤝'];
?>
<div class="container">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem">
    <div style="display:flex;align-items:center;gap:1rem">
      <a href="<?= base_url('dashboard') ?>" class="btn btn-secundario">← Voltar</a>
      <div>
        <h2 style="font-size:1.2rem;color:var(--cor-primaria)">🤝 Fornecedores — <?= h($cli['nome']) ?></h2>
        <div style="font-size:.85rem;color:var(--cor-secundario)"><?= count($fornecedores) ?> fornecedor(es)</div>
      </div>
    </div>
    <?php if ($usuario['nivel'] === 'admin'): ?>
    <a href="<?= base_url('fornecedores/novo') ?>" class="btn btn-primario">+ Cadastrar fornecedor</a>
    <?php endif; ?>
  </div>

  <div class="card" style="padding:1rem">
    <form method="get" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
      <div class="form-grupo" style="margin:0;flex:1;min-width:170px">
        <label>Categoria</label>
        <select name="categoria">
          <option value="">Todas</option>
          <?php foreach ($cat_label as $v => $l): ?>
          <option value="<?= $v ?>" <?= $filtro_categoria === $v ? 'selected' : '' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-grupo" style="margin:0;flex:2;min-width:200px">
        <label>Buscar</label>
        <input type="text" name="busca" placeholder="Nome, fantasia, CNPJ ou contato…" value="<?= h($filtro_busca) ?>">
      </div>
      <button type="submit" class="btn btn-secundario">Filtrar</button>
      <?php if ($filtro_categoria || $filtro_busca): ?><a href="?" class="btn btn-secundario">Limpar</a><?php endif; ?>
    </form>
  </div>

  <?php if ($fornecedores): ?>
  <div class="imoveis-grid">
    <?php foreach ($fornecedores as $f): ?>
    <a href="<?= base_url('fornecedores/editar?id=' . $f['id']) ?>" class="imovel-card">
      <div class="imovel-card-body">
        <div class="imovel-card-codigo" style="display:flex;align-items:center;gap:.4rem">
          <span style="font-size:1.15rem"><?= $cat_icone[$f['categoria']] ?? '🤝' ?></span>
          <?= h($f['codigo']) ?> · <?= $cat_label[$f['categoria']] ?? $f['categoria'] ?>
          <?php if ($f['avaliacao_nota']): ?><span style="margin-left:auto;color:#d4af37;font-size:.85rem"><?= str_repeat('★', (int)$f['avaliacao_nota']) ?></span><?php endif; ?>
        </div>
        <div class="imovel-card-nome"><?= h($f['nome_fantasia'] ?: $f['nome']) ?></div>
        <div class="imovel-card-local">
          <?php if ($f['contato_nome']): ?>👤 <?= h($f['contato_nome']) ?><?php endif; ?>
          <?php if ($f['telefone']): ?><?= $f['contato_nome'] ? ' · ' : '' ?><?= h($f['telefone']) ?><?php endif; ?>
        </div>
        <div class="imovel-card-rodape">
          <?php if ($f['contrato_valor'] !== null): ?>
          <span class="imovel-card-valor"><?= moeda((float)$f['contrato_valor']) ?></span>
          <span style="font-size:.75rem;color:var(--cor-secundario)">contrato</span>
          <?php elseif ($f['email']): ?>
          <span style="font-size:.82rem;color:var(--cor-secundario)"><?= h($f['email']) ?></span>
          <?php endif; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="card" style="text-align:center;padding:3rem">
    <div style="font-size:3rem;margin-bottom:1rem">🤝</div>
    <p style="color:var(--cor-secundario)">Nenhum fornecedor cadastrado.</p>
    <?php if ($usuario['nivel'] === 'admin'): ?>
    <a href="<?= base_url('fornecedores/novo') ?>" class="btn btn-primario" style="margin-top:1rem">+ Cadastrar primeiro fornecedor</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
