<?php
/**
 * Relatório de pendências do veículo (standalone para impressão / PDF).
 * @var array      $ve
 * @var array|null $cliente
 * @var array      $pend
 * @var int        $total
 * @var string     $hoje
 * @var string     $nome
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Pendências — <?= h($ve['codigo']) ?></title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: -apple-system, "Segoe UI", Arial, sans-serif; color: #1e2530; margin: 0; padding: 2.5rem; background: #fff; }
    .cabecalho { border-bottom: 3px solid #16141a; padding-bottom: 1rem; margin-bottom: 1.5rem; }
    .cabecalho h1 { font-size: 1.4rem; color: #16141a; margin: 0 0 .25rem; }
    .cabecalho .sub { color: #5a6a7e; font-size: .9rem; }
    .meta { display: flex; justify-content: space-between; flex-wrap: wrap; gap: .5rem; font-size: .85rem; color: #5a6a7e; margin-bottom: 1.5rem; }
    .resumo { background: #fff7e6; border-left: 4px solid #ff9f1a; color: #8a5a00; padding: .85rem 1rem; border-radius: 6px; font-size: .95rem; margin-bottom: 1.5rem; }
    .resumo.ok { background: #f0faf4; border-color: #1a7a45; color: #1a7a45; }
    .grupo { margin-bottom: 1.25rem; page-break-inside: avoid; }
    .grupo h2 { font-size: .9rem; text-transform: uppercase; letter-spacing: .03em; color: #16141a; border-bottom: 1px solid #e7e0d0; padding-bottom: .35rem; margin: 0 0 .6rem; }
    .grupo ul { margin: 0; padding-left: 1.2rem; }
    .grupo li { font-size: .9rem; color: #3a4656; margin-bottom: .25rem; }
    .rodape { margin-top: 2.5rem; padding-top: 1rem; border-top: 1px solid #e7e0d0; font-size: .75rem; color: #9aa6b4; text-align: center; }
    .barra-print { position: sticky; top: 0; background: #16141a; color: #fff; padding: .75rem 1rem; margin: -2.5rem -2.5rem 1.5rem; display: flex; gap: .75rem; align-items: center; justify-content: center; }
    .barra-print button { background: #b8912f; color: #fff; border: none; padding: .5rem 1.25rem; border-radius: 6px; font-size: .9rem; cursor: pointer; font-weight: 600; }
    @media print { .barra-print { display: none; } body { padding: 0; } }
  </style>
</head>
<body>
  <div class="barra-print">
    <span>Use o botão para salvar como PDF</span>
    <button onclick="window.print()">🖨️ Imprimir / Salvar PDF</button>
  </div>

  <div class="cabecalho">
    <h1>Relatório de Pendências</h1>
    <div class="sub"><?= h($ve['codigo']) ?> · <?= h($nome) ?><?= $ve['placa'] ? ' · ' . h($ve['placa']) : '' ?></div>
  </div>

  <div class="meta">
    <span><strong>Cliente:</strong> <?= h($cliente['nome'] ?? '—') ?><?= $cliente ? ' (' . h($cliente['tipo_pessoa'] === 'PF' ? 'CPF ' : 'CNPJ ') . h($cliente['cpf_cnpj']) . ')' : '' ?></span>
    <span><strong>Emitido em:</strong> <?= $hoje ?></span>
  </div>

  <?php if ($total === 0): ?>
    <div class="resumo ok">✅ Cadastro completo — nenhum campo pendente.</div>
  <?php else: ?>
    <div class="resumo">⚠️ <strong><?= $total ?></strong> campo(s) ainda não preenchido(s) neste cadastro.</div>
    <?php foreach ($pend as $grupo => $campos): ?>
    <div class="grupo">
      <h2><?= h($grupo) ?> (<?= count($campos) ?>)</h2>
      <ul><?php foreach ($campos as $c): ?><li><?= h($c) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <div class="rodape">Gestão Patrimonial — César Cordeiro · Documento gerado automaticamente</div>

  <script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 400); });</script>
</body>
</html>
