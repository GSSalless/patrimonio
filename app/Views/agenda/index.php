<?php
/**
 * Agenda e Alertas (Módulo 14) — linha do tempo de vencimentos.
 * @var array       $baldes       baldes agrupados por proximidade
 * @var array       $resumo       ['vencidos','proximos','total']
 * @var string|null $escopo_nome  nome do cliente, ou null = agenda geral
 * @var array|null  $cli
 */
$page_title = 'Agenda e Alertas';
require APP_ROOT . '/includes/header.php';

// Emoji por categoria de vencimento.
$icones = [
    'iptu'          => '🧾',
    'licenciamento' => '🚗',
    'seguro'        => '🛡️',
    'contrato'      => '📜',
    'revisao'       => '🛠️',
    'investimento'  => '📈',
    'documento'     => '🗂️',
];
$geral = ($escopo_nome === null);
?>
<div class="container">

  <div class="ag-head">
    <div>
      <h2 style="font-size:1.4rem;color:var(--cor-primaria);font-family:var(--fonte-titulo)">Agenda e Alertas</h2>
      <div style="font-size:.88rem;color:var(--cor-secundario)">
        <?= $geral ? 'Todos os clientes' : h($escopo_nome) ?> · vencimentos de IPTU, seguros, licenciamento, contratos e documentos
      </div>
    </div>
    <div class="ag-chips">
      <span class="ag-chip ag-chip-venc"><?= $resumo['vencidos'] ?> vencido<?= $resumo['vencidos'] == 1 ? '' : 's' ?></span>
      <span class="ag-chip ag-chip-prox"><?= $resumo['proximos'] ?> em 30 dias</span>
      <span class="ag-chip ag-chip-tot"><?= $resumo['total'] ?> no total</span>
    </div>
  </div>

  <?php if ($resumo['total'] === 0): ?>
    <div class="card"><p style="color:var(--cor-secundario);text-align:center;padding:2.5rem">
      🎉 Nenhum vencimento cadastrado. Conforme você preencher datas de IPTU, seguros,
      licenciamento e contratos, os alertas aparecem aqui automaticamente.
    </p></div>
  <?php endif; ?>

  <?php foreach ($baldes as $chave => $balde):
    if (!$balde['itens']) continue;
    $venc = ($chave === 'vencido');
  ?>
    <section class="ag-secao">
      <h3 class="ag-secao-tit <?= $venc ? 'ag-secao-venc' : '' ?>">
        <?= h($balde['titulo']) ?>
        <span class="ag-secao-n"><?= count($balde['itens']) ?></span>
      </h3>

      <div class="ag-lista">
        <?php foreach ($balde['itens'] as $a):
          [$classe, $cor, $rotulo] = alerta_status($a['dias']);
          $emoji = $icones[$a['categoria']] ?? '📌';
          $tag = 'div'; $href = '';
          if (!empty($a['link'])) { $tag = 'a'; $href = 'href="' . base_url($a['link']) . '"'; }
        ?>
        <<?= $tag ?> class="ag-item" <?= $href ?> style="--st:<?= $cor ?>">
          <span class="ag-ico"><?= $emoji ?></span>
          <div class="ag-info">
            <div class="ag-tit"><?= h($a['titulo']) ?></div>
            <div class="ag-sub">
              <?= h($a['bem'] ?: '—') ?>
              <?php if ($geral): ?><span class="ag-cli">· <?= h($a['cliente_nome']) ?></span><?php endif; ?>
            </div>
          </div>
          <div class="ag-quando">
            <div class="ag-data"><?= data_br($a['data']) ?></div>
            <div class="ag-rel" style="color:<?= $cor ?>"><?= h($rotulo) ?></div>
          </div>
        </<?= $tag ?>>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>

</div>

<style>
  .ag-head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1.4rem}
  .ag-chips{display:flex;gap:.5rem;flex-wrap:wrap}
  .ag-chip{font-size:.8rem;font-weight:600;padding:.35rem .7rem;border-radius:999px;white-space:nowrap;border:1px solid}
  .ag-chip-venc{color:#b91c1c;background:#fef2f2;border-color:#fca5a5}
  .ag-chip-prox{color:#b45309;background:#fffbeb;border-color:#fcd34d}
  .ag-chip-tot{color:var(--cor-secundario);background:var(--cor-fundo,#f4f1ea);border-color:var(--cor-borda,#e3e8ef)}

  .ag-secao{margin-bottom:1.6rem}
  .ag-secao-tit{display:flex;align-items:center;gap:.55rem;font-size:1rem;color:var(--cor-primaria);
    font-weight:700;margin:0 0 .7rem;padding-bottom:.4rem;border-bottom:1px solid var(--cor-borda,#e3e8ef)}
  .ag-secao-venc{color:#b91c1c}
  .ag-secao-n{font-size:.75rem;font-weight:700;color:var(--cor-secundario);background:var(--cor-fundo,#f4f1ea);
    border-radius:999px;padding:.05rem .5rem}

  .ag-lista{display:flex;flex-direction:column;gap:.6rem}
  .ag-item{display:flex;align-items:center;gap:.85rem;padding:.85rem 1rem;text-decoration:none;color:inherit;
    background:var(--cor-branco,#fff);border:1px solid var(--cor-borda,#e3e8ef);border-left:4px solid var(--st);
    border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.04);transition:box-shadow .18s,transform .18s}
  a.ag-item:hover{box-shadow:0 8px 20px rgba(0,0,0,.09);transform:translateY(-1px);text-decoration:none}
  .ag-ico{width:40px;height:40px;flex-shrink:0;border-radius:10px;display:flex;align-items:center;justify-content:center;
    font-size:1.2rem;background:color-mix(in srgb, var(--st) 12%, #fff);border:1px solid color-mix(in srgb, var(--st) 35%, #fff)}
  .ag-info{min-width:0;flex:1}
  .ag-tit{font-weight:600;font-size:.96rem;color:var(--cor-primaria);line-height:1.2}
  .ag-sub{font-size:.82rem;color:var(--cor-secundario);margin-top:.15rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .ag-cli{color:var(--cor-primaria);font-weight:600}
  .ag-quando{text-align:right;flex-shrink:0}
  .ag-data{font-size:.9rem;font-weight:700;color:var(--cor-primaria)}
  .ag-rel{font-size:.76rem;font-weight:600;margin-top:.12rem}

  @media (max-width:520px){
    .ag-sub{white-space:normal}
    .ag-ico{width:34px;height:34px;font-size:1.05rem}
  }
</style>
<?php require APP_ROOT . '/includes/footer.php'; ?>
