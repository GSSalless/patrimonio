<?php
/**
 * @var array      $cli
 * @var array      $imoveis
 * @var string     $filtro_tipo
 * @var string     $filtro_situacao
 * @var string     $filtro_busca
 * @var array|null $novo_im
 * @var array      $novo_pend
 * @var int        $novo_pend_total
 */
$page_title = 'Imóveis';
require APP_ROOT . '/includes/header.php';
?>
<div class="container">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem">
    <div>
      <h2 style="font-size:1.2rem;color:var(--cor-primaria)">Imóveis — <?= h($cli['nome']) ?></h2>
      <div style="font-size:.85rem;color:var(--cor-secundario)"><?= count($imoveis) ?> imóvel(is) encontrado(s)</div>
    </div>
    <?php if ($usuario['nivel'] === 'admin'): ?>
    <a href="<?= base_url('imoveis/novo') ?>" class="btn btn-primario">+ Cadastrar imóvel</a>
    <?php endif; ?>
  </div>

  <!-- Filtros -->
  <div class="card" style="padding:1rem">
    <form method="get" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
      <div class="form-grupo" style="margin:0;flex:1;min-width:160px">
        <label>Buscar</label>
        <input type="text" name="busca" placeholder="Nome do imóvel…" value="<?= h($filtro_busca) ?>">
      </div>
      <div class="form-grupo" style="margin:0;min-width:150px">
        <label>Tipo</label>
        <select name="tipo">
          <option value="">Todos</option>
          <?php foreach (['apartamento','casa','sala_comercial','terreno','galpao','loja','hotel','outro'] as $t): ?>
          <option value="<?= $t ?>" <?= $filtro_tipo===$t?'selected':'' ?>><?= tipo_imovel_label($t) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-grupo" style="margin:0;min-width:150px">
        <label>Situação</label>
        <select name="situacao">
          <option value="">Todas</option>
          <option value="pronto"         <?= $filtro_situacao==='pronto'?'selected':'' ?>>Pronto</option>
          <option value="em_construcao"  <?= $filtro_situacao==='em_construcao'?'selected':'' ?>>Em Construção</option>
          <option value="na_planta"      <?= $filtro_situacao==='na_planta'?'selected':'' ?>>Na Planta</option>
        </select>
      </div>
      <button type="submit" class="btn btn-secundario">Filtrar</button>
      <?php if ($filtro_tipo || $filtro_situacao || $filtro_busca): ?>
      <a href="?" class="btn btn-secundario">Limpar</a>
      <?php endif; ?>
    </form>
  </div>

  <?php if ($imoveis): ?>
  <?php
    $tipo_visual = [
      'apartamento'    => ['🏢', '#2e7dd1'],
      'casa'           => ['🏠', '#1a7a45'],
      'terreno'        => ['🌳', '#b3700f'],
      'sala_comercial' => ['🏪', '#0d9488'],
      'galpao'         => ['🏭', '#475569'],
      'loja'           => ['🏬', '#9333ea'],
      'hotel'          => ['🏨', '#dc2626'],
      'outro'          => ['🏗️', 'var(--cor-secundario)'],
    ];
  ?>
  <div class="ios-list">
    <?php foreach ($imoveis as $im):
      [$ic, $cor] = $tipo_visual[$im['tipo']] ?? ['🏠', 'var(--cor-secundario)'];
      $local = trim(($im['cidade'] ?? '') . ($im['estado'] ? ', ' . $im['estado'] : ''));
    ?>
    <a href="<?= base_url('imoveis/ficha?id=' . $im['id']) ?>" class="ios-row">
      <span class="ios-icone" style="background:<?= $cor ?>">
        <?php if ($im['foto_principal']): ?><img src="<?= url_arquivo($im['foto_principal']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:10px"><?php else: ?><?= $ic ?><?php endif; ?>
      </span>
      <span class="ios-corpo">
        <span class="ios-titulo"><?= h($im['nome_referencia']) ?></span>
        <span class="ios-sub"><?= h($im['codigo']) ?> · <?= h(tipo_imovel_label($im['tipo'])) ?><?= $local ? ' · ' . h($local) : '' ?></span>
      </span>
      <span class="ios-dir">
        <span class="tag <?= $im['situacao']==='pronto' ? 'tag-verde' : 'tag-laranja' ?>"><?= situacao_label($im['situacao']) ?></span>
        <span class="ios-valor"><?= $im['valor_mercado'] ? moeda((float)$im['valor_mercado']) : '—' ?></span>
        <span class="ios-chevron">›</span>
      </span>
    </a>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="card" style="text-align:center;padding:3rem">
    <div style="font-size:3rem;margin-bottom:1rem">🏠</div>
    <p style="color:var(--cor-secundario)">Nenhum imóvel encontrado.</p>
    <?php if ($usuario['nivel'] === 'admin' && !$filtro_tipo && !$filtro_situacao && !$filtro_busca): ?>
    <a href="<?= base_url('imoveis/novo') ?>" class="btn btn-primario" style="margin-top:1rem">+ Cadastrar primeiro imóvel</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<?php if ($novo_im): ?>
<?php
  $wa_texto = pendencias_texto($novo_pend, $novo_im['codigo'], $novo_im['nome_referencia']);
  $wa_url   = 'https://wa.me/?text=' . rawurlencode($wa_texto);
  $pdf_url  = base_url('imoveis/pendencias-pdf?id=' . $novo_im['id']);
?>
<div class="modal-overlay" id="modal-pendencias">
  <div class="modal-box">
    <div class="modal-head">
      <div>
        <div style="font-size:.8rem;color:var(--cor-secundario);font-weight:600"><?= h($novo_im['codigo']) ?> · Imóvel cadastrado ✅</div>
        <h3 style="font-size:1.15rem;color:var(--cor-primaria);margin-top:.15rem"><?= h($novo_im['nome_referencia']) ?></h3>
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
          ⚠️ <strong><?= $novo_pend_total ?></strong> campo(s) ficaram sem preencher. Você pode completar depois editando o imóvel.
        </div>
        <div class="pendencias-lista">
          <?php foreach ($novo_pend as $grupo => $campos): ?>
          <div class="pendencias-grupo">
            <div class="pendencias-grupo-titulo"><?= h($grupo) ?> <span class="pendencias-grupo-cont"><?= count($campos) ?></span></div>
            <ul>
              <?php foreach ($campos as $c): ?><li><?= h($c) ?></li><?php endforeach; ?>
            </ul>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="modal-rodape">
      <a href="<?= h($pdf_url) ?>" target="_blank" class="btn btn-secundario">📄 Gerar PDF</a>
      <a href="<?= h($wa_url) ?>" target="_blank" class="btn btn-whatsapp">📱 Enviar por WhatsApp</a>
      <a href="<?= base_url('imoveis/ficha?id=' . $novo_im['id']) ?>" class="btn btn-primario" style="margin-left:auto">Ver imóvel →</a>
    </div>
  </div>
</div>
<script>
  function fecharModalPendencias() {
    const m = document.getElementById('modal-pendencias');
    if (m) m.style.display = 'none';
    if (history.replaceState) history.replaceState(null, '', '<?= base_url('imoveis') ?>');
  }
  document.getElementById('modal-pendencias')?.addEventListener('click', function (e) {
    if (e.target === this) fecharModalPendencias();
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') fecharModalPendencias(); });
</script>
<?php endif; ?>
<?php require APP_ROOT . '/includes/footer.php'; ?>
