<?php
/**
 * @var array      $cli
 * @var array      $veiculos
 * @var string     $filtro_busca
 * @var array|null $novo_ve
 * @var array      $novo_pend
 * @var int        $novo_pend_total
 */
$page_title = 'Veículos';
require APP_ROOT . '/includes/header.php';
?>
<div class="container">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem">
    <div style="display:flex;align-items:center;gap:1rem">
      <a href="<?= base_url('patrimonio') ?>" class="btn btn-secundario">← Voltar</a>
      <div>
        <h2 style="font-size:1.2rem;color:var(--cor-primaria)">Veículos — <?= h($cli['nome']) ?></h2>
        <div style="font-size:.85rem;color:var(--cor-secundario)"><?= count($veiculos) ?> veículo(s)</div>
      </div>
    </div>
    <?php if ($usuario['nivel'] === 'admin'): ?>
    <a href="<?= base_url('veiculos/novo') ?>" class="btn btn-primario">+ Cadastrar veículo</a>
    <?php endif; ?>
  </div>

  <div class="card" style="padding:1rem">
    <form method="get" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
      <div class="form-grupo" style="margin:0;flex:1;min-width:200px">
        <label>Buscar</label>
        <input type="text" name="busca" placeholder="Marca, modelo ou placa…" value="<?= h($filtro_busca) ?>">
      </div>
      <button type="submit" class="btn btn-secundario">Filtrar</button>
      <?php if ($filtro_busca): ?><a href="?" class="btn btn-secundario">Limpar</a><?php endif; ?>
    </form>
  </div>

  <?php if ($veiculos): ?>
  <div class="imoveis-grid">
    <?php foreach ($veiculos as $ve): ?>
    <a href="<?= base_url('veiculos/editar?id=' . $ve['id']) ?>" class="imovel-card">
      <div class="imovel-card-foto">
        <?php if ($ve['foto_principal']): ?>
          <img src="<?= url_arquivo($ve['foto_principal']) ?>" alt="">
        <?php else: ?>🚗<?php endif; ?>
      </div>
      <div class="imovel-card-body">
        <div class="imovel-card-codigo"><?= h($ve['codigo']) ?><?= $ve['placa'] ? ' &middot; ' . h($ve['placa']) : '' ?></div>
        <div class="imovel-card-nome"><?= h(trim(($ve['marca'] ?? '') . ' ' . ($ve['modelo'] ?? ''))) ?></div>
        <div class="imovel-card-local">
          <?= h(trim(($ve['ano_fabricacao'] ?? '') . ($ve['ano_modelo'] ? '/' . $ve['ano_modelo'] : ''), '/')) ?>
          <?= $ve['combustivel'] ? ' · ' . h(combustivel_label($ve['combustivel'])) : '' ?>
        </div>
        <div class="imovel-card-rodape">
          <span class="imovel-card-valor"><?= $ve['valor_mercado'] ? moeda((float)$ve['valor_mercado']) : ($ve['valor_fipe'] ? moeda((float)$ve['valor_fipe']) : '—') ?></span>
          <?php $p = pendencias_total(pendencias_veiculo($ve)); ?>
          <span class="tag <?= $p === 0 ? 'tag-verde' : 'tag-laranja' ?>"><?= $p === 0 ? 'Completo' : $p . ' pendência(s)' ?></span>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="card" style="text-align:center;padding:3rem">
    <div style="font-size:3rem;margin-bottom:1rem">🚗</div>
    <p style="color:var(--cor-secundario)">Nenhum veículo cadastrado.</p>
    <?php if ($usuario['nivel'] === 'admin'): ?>
    <a href="<?= base_url('veiculos/novo') ?>" class="btn btn-primario" style="margin-top:1rem">+ Cadastrar primeiro veículo</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<?php if ($novo_ve): ?>
<?php
  $titulo   = $novo_ve['codigo'];
  $subt     = trim(($novo_ve['marca'] ?? '') . ' ' . ($novo_ve['modelo'] ?? ''));
  $wa_texto = pendencias_texto($novo_pend, $titulo, $subt);
  $wa_url   = 'https://wa.me/?text=' . rawurlencode($wa_texto);
  $pdf_url  = base_url('veiculos/pendencias-pdf?id=' . $novo_ve['id']);
?>
<div class="modal-overlay" id="modal-pendencias">
  <div class="modal-box">
    <div class="modal-head">
      <div>
        <div style="font-size:.8rem;color:var(--cor-secundario);font-weight:600"><?= h($novo_ve['codigo']) ?> · Veículo cadastrado ✅</div>
        <h3 style="font-size:1.15rem;color:var(--cor-primaria);margin-top:.15rem"><?= h($subt) ?></h3>
      </div>
      <button type="button" class="modal-fechar" onclick="fecharModalPendencias()" aria-label="Fechar">&times;</button>
    </div>
    <div class="modal-body">
      <?php if ($novo_pend_total === 0): ?>
        <div style="text-align:center;padding:1.5rem 0">
          <div style="font-size:2.5rem">🎉</div>
          <p style="color:#1a7a45;font-weight:600;margin-top:.5rem">Cadastro completo! Nenhum campo pendente.</p>
        </div>
      <?php else: ?>
        <div class="alerta-pendencias">
          ⚠️ <strong><?= $novo_pend_total ?></strong> campo(s) ficaram sem preencher. Você pode completar depois editando o veículo.
        </div>
        <div class="pendencias-lista">
          <?php foreach ($novo_pend as $grupo => $campos): ?>
          <div class="pendencias-grupo">
            <div class="pendencias-grupo-titulo"><?= h($grupo) ?> <span class="pendencias-grupo-cont"><?= count($campos) ?></span></div>
            <ul><?php foreach ($campos as $c): ?><li><?= h($c) ?></li><?php endforeach; ?></ul>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="modal-rodape">
      <a href="<?= h($pdf_url) ?>" target="_blank" class="btn btn-secundario">📄 Gerar PDF</a>
      <a href="<?= h($wa_url) ?>" target="_blank" class="btn btn-whatsapp">📱 Enviar por WhatsApp</a>
      <a href="<?= base_url('veiculos/editar?id=' . $novo_ve['id']) ?>" class="btn btn-primario" style="margin-left:auto">Editar veículo →</a>
    </div>
  </div>
</div>
<script>
  function fecharModalPendencias() {
    const m = document.getElementById('modal-pendencias');
    if (m) m.style.display = 'none';
    if (history.replaceState) history.replaceState(null, '', '<?= base_url('veiculos') ?>');
  }
  document.getElementById('modal-pendencias')?.addEventListener('click', function (e) { if (e.target === this) fecharModalPendencias(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') fecharModalPendencias(); });
</script>
<?php endif; ?>
<?php require APP_ROOT . '/includes/footer.php'; ?>
