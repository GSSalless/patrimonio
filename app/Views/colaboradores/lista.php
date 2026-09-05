<?php
/**
 * Lista de colaboradores do cliente.
 * @var array  $cli
 * @var array  $colaboradores
 * @var string $filtro_status, $filtro_busca
 */
$page_title = 'Colaboradores';
require APP_ROOT . '/includes/header.php';
$status_op  = ['ativo'=>'Ativo','experiencia'=>'Experiência','afastado'=>'Afastado','ferias'=>'Férias','desligado'=>'Desligado'];
$status_cor = ['ativo'=>'#1a7a45','experiencia'=>'#0891b2','afastado'=>'#b45309','ferias'=>'#c9a227','desligado'=>'#64748b'];
// Folha dos ativos.
$total_folha = array_sum(array_map(fn($c) => in_array($c['status'], ['ativo','experiencia','ferias']) ? (float)($c['salario'] ?? 0) : 0, $colaboradores));
$ativos = count(array_filter($colaboradores, fn($c) => $c['status'] !== 'desligado'));
?>
<div class="container">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem">
    <div style="display:flex;align-items:center;gap:1rem">
      <a href="<?= base_url('dashboard') ?>" class="btn btn-secundario">← Voltar</a>
      <div>
        <h2 style="font-size:1.2rem;color:var(--cor-primaria)">👔 Colaboradores — <?= h($cli['nome']) ?></h2>
        <div style="font-size:.85rem;color:var(--cor-secundario)"><?= count($colaboradores) ?> colaborador(es) · <?= $ativos ?> ativo(s)</div>
      </div>
    </div>
    <?php if ($usuario['nivel'] === 'admin'): ?>
    <a href="<?= base_url('colaboradores/novo') ?>" class="btn btn-primario">+ Cadastrar colaborador</a>
    <?php endif; ?>
  </div>

  <div class="card" style="padding:1rem">
    <form method="get" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
      <div class="form-grupo" style="margin:0;flex:1;min-width:150px">
        <label>Situação</label>
        <select name="status">
          <option value="">Todas</option>
          <?php foreach ($status_op as $v => $l): ?>
          <option value="<?= $v ?>" <?= $filtro_status === $v ? 'selected' : '' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-grupo" style="margin:0;flex:2;min-width:200px">
        <label>Buscar</label>
        <input type="text" name="busca" placeholder="Nome, cargo ou departamento…" value="<?= h($filtro_busca) ?>">
      </div>
      <button type="submit" class="btn btn-secundario">Filtrar</button>
      <?php if ($filtro_status || $filtro_busca): ?><a href="?" class="btn btn-secundario">Limpar</a><?php endif; ?>
    </form>
  </div>

  <?php if ($colaboradores): ?>
  <?php if ($total_folha > 0): ?>
  <div class="card" style="padding:1rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem">
    <span style="font-size:.9rem;color:var(--cor-secundario)">Folha mensal (colaboradores ativos)</span>
    <strong style="font-size:1.15rem;color:var(--cor-primaria)"><?= moeda($total_folha) ?></strong>
  </div>
  <?php endif; ?>
  <div class="imoveis-grid">
    <?php foreach ($colaboradores as $c):
      $ini = mb_strtoupper(mb_substr(trim($c['nome']), 0, 1));
    ?>
    <a href="<?= base_url('colaboradores/editar?id=' . $c['id']) ?>" class="imovel-card">
      <div class="imovel-card-body">
        <div class="imovel-card-codigo" style="display:flex;align-items:center;gap:.4rem">
          <span style="font-size:1.15rem">👤</span>
          <?= h($c['codigo']) ?>
          <?php if ($c['tipo_contrato']): ?> · <?= h(colaborador_contrato_label($c['tipo_contrato'])) ?><?php endif; ?>
          <span class="tag" style="margin-left:auto;color:#fff;background:<?= $status_cor[$c['status']] ?? '#64748b' ?>"><?= $status_op[$c['status']] ?? $c['status'] ?></span>
        </div>
        <div class="imovel-card-nome"><?= h($c['nome']) ?></div>
        <div class="imovel-card-local">
          <?= h($c['cargo'] ?: '—') ?>
          <?php if ($c['departamento']): ?> · <?= h($c['departamento']) ?><?php endif; ?>
        </div>
        <div class="imovel-card-rodape">
          <?php if ($c['salario'] !== null): ?>
          <span class="imovel-card-valor"><?= moeda((float)$c['salario']) ?></span>
          <span style="font-size:.75rem;color:var(--cor-secundario)">salário</span>
          <?php elseif ($c['telefone']): ?>
          <span style="font-size:.82rem;color:var(--cor-secundario)">📞 <?= h($c['telefone']) ?></span>
          <?php endif; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="card" style="text-align:center;padding:3rem">
    <div style="font-size:3rem;margin-bottom:1rem">👔</div>
    <p style="color:var(--cor-secundario)">Nenhum colaborador cadastrado.</p>
    <?php if ($usuario['nivel'] === 'admin'): ?>
    <a href="<?= base_url('colaboradores/novo') ?>" class="btn btn-primario" style="margin-top:1rem">+ Cadastrar primeiro colaborador</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
