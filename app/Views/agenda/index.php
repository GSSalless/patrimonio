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
    'colaborador'   => '👔',
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
<?php require APP_ROOT . '/includes/footer.php'; ?>
