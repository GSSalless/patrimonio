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

  <?php if (($usuario['nivel'] ?? '') === 'admin'):
    // Mensagens de retorno da integração Notion.
    $nmsgs = [
      'conectado'    => ['ok',  'Notion conectado e tarefas enviadas para o seu board! ✅'],
      'desvinculado' => ['ok',  'Notion desvinculado.'],
      'sem_config'   => ['erro','Integração do Notion ainda não configurada no servidor (falta o Client ID/Secret).'],
      'sem_pagina'   => ['erro','Notion conectado, mas você ainda não compartilhou uma página com a integração. No Notion: abra a página onde quer o board → menu “•••” (canto superior) → Conexões → adicione “CZR Patrimonial”. Depois volte aqui e clique em Sincronizar.'],
      'negado'       => ['erro','Autorização cancelada no Notion.'],
      'state'        => ['erro','Sessão expirada na conexão com o Notion. Tente de novo.'],
      'sem_code'     => ['erro','Não recebemos a autorização do Notion.'],
      'erro'         => ['erro','Não foi possível concluir a ação no Notion. Tente novamente.'],
    ];
    if ($notion_msg === 'sync') {
      $c=(int)($_GET['c']??0); $a=(int)($_GET['a']??0); $e=(int)($_GET['e']??0);
      $nflash = ['ok', "Sincronizado com o Notion: $c criada(s), $a atualizada(s)" . ($e ? ", $e erro(s)" : '') . '.'];
    } else {
      $nflash = $nmsgs[$notion_msg] ?? null;
    }
  ?>
    <?php if ($nflash): ?>
      <div class="ag-notion-flash ag-nf-<?= $nflash[0] ?>"><?= h($nflash[1]) ?></div>
    <?php endif; ?>

    <div class="ag-notion">
      <div class="ag-notion-info">
        <span class="ag-notion-ico"><i class="bi bi-journal-check"></i></span>
        <div>
          <?php if ($notion): ?>
            <div class="ag-notion-tit">Notion conectado<?= $notion['workspace_name'] ? ' · ' . h($notion['workspace_name']) : '' ?></div>
            <div class="ag-notion-sub">
              Suas tarefas/pendências são enviadas para o board no Notion.
              <?php if (!empty($notion['ultimo_sync'])): ?> Último envio: <?= h(data_br($notion['ultimo_sync'])) ?>.<?php endif; ?>
              <?php if (!empty($notion['database_url'])): ?> <a href="<?= h($notion['database_url']) ?>" target="_blank" rel="noopener">Abrir board ↗</a><?php endif; ?>
            </div>
          <?php else: ?>
            <div class="ag-notion-tit">Vincular ao Notion</div>
            <div class="ag-notion-sub">Conecte seu Notion para acompanhar as tarefas e pendências também por lá.</div>
          <?php endif; ?>
        </div>
      </div>
      <div class="ag-notion-acoes">
        <?php if ($notion): ?>
          <form method="post" action="<?= base_url('agenda/notion/sincronizar') ?>" style="display:inline">
            <button type="submit" class="btn btn-primario btn-sm"><i class="bi bi-arrow-repeat"></i> Sincronizar</button>
          </form>
          <form method="post" action="<?= base_url('agenda/notion/desvincular') ?>" style="display:inline"
                onsubmit="return confirm('Desvincular o Notion? As tarefas já enviadas continuam lá, mas paramos de sincronizar.');">
            <button type="submit" class="btn btn-secundario btn-sm">Desvincular</button>
          </form>
        <?php else: ?>
          <a class="btn btn-primario btn-sm" href="<?= base_url('agenda/notion/conectar') ?>">
            <i class="bi bi-journal-check"></i> Vincular Notion
          </a>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

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
