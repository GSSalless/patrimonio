<?php
/**
 * Repositório central de documentos (Módulo 13).
 * Lista todos os arquivos do cliente (ou de todos, admin sem seleção),
 * com filtros por categoria, módulo, validade e busca por nome.
 *
 * @var array       $docs        linhas de documentos + cliente_nome
 * @var array       $vinculos    mapa "tipo:id" => ['label','nome','link']
 * @var array       $filtros     ['categoria','tipo','q','validade']
 * @var array       $categorias  categoria => rótulo
 * @var array       $tipos       tipo_referencia => rótulo
 * @var string|null $escopo_nome nome do cliente (null = todos)
 * @var bool        $is_admin
 */
$page_title = 'Documentos';
require APP_ROOT . '/includes/header.php';
$geral = ($escopo_nome === null);

// Formata tamanho de arquivo (bytes → KB/MB).
$fmt_tam = function (?int $b): string {
    if (!$b) return '—';
    if ($b >= 1048576) return number_format($b / 1048576, 1, ',', '.') . ' MB';
    if ($b >= 1024)    return number_format($b / 1024, 0, ',', '.') . ' KB';
    return $b . ' B';
};
// Ícone por extensão do arquivo.
$ico_ext = function (string $nome): string {
    $e = strtolower(pathinfo($nome, PATHINFO_EXTENSION));
    if ($e === 'pdf') return '📄';
    if (in_array($e, ['jpg','jpeg','png','webp','gif'])) return '🖼️';
    return '📎';
};

// Resumo rápido (para os chips do topo).
$total     = count($docs);
$com_venc  = 0; $vencidos = 0;
foreach ($docs as $d) {
    if (!empty($d['data_validade'])) {
        $com_venc++;
        if (dias_ate($d['data_validade']) < 0) $vencidos++;
    }
}
?>
<div class="container">

  <div class="doc-head">
    <div>
      <h2 style="font-size:1.4rem;color:var(--cor-primaria);font-family:var(--fonte-titulo)">Documentos</h2>
      <div style="font-size:.88rem;color:var(--cor-secundario)">
        <?= $geral ? 'Todos os clientes' : h($escopo_nome) ?> · repositório central de arquivos
      </div>
    </div>
    <div class="doc-chips">
      <span class="doc-chip"><?= $total ?> arquivo<?= $total == 1 ? '' : 's' ?></span>
      <?php if ($com_venc): ?><span class="doc-chip"><?= $com_venc ?> com validade</span><?php endif; ?>
      <?php if ($vencidos): ?><span class="doc-chip doc-chip-venc"><?= $vencidos ?> vencido<?= $vencidos == 1 ? '' : 's' ?></span><?php endif; ?>
    </div>
  </div>

  <!-- Filtros -->
  <form method="get" action="<?= base_url('documentos') ?>" class="doc-filtros">
    <input type="search" name="q" value="<?= h($filtros['q']) ?>" placeholder="🔎 Buscar por nome ou descrição…" class="doc-busca">
    <select name="categoria" class="doc-select">
      <option value="">Todas as categorias</option>
      <?php foreach ($categorias as $val => $rot): ?>
        <option value="<?= h($val) ?>" <?= $filtros['categoria'] === $val ? 'selected' : '' ?>><?= h($rot) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="tipo" class="doc-select">
      <option value="">Todos os módulos</option>
      <?php foreach ($tipos as $val => $rot): ?>
        <option value="<?= h($val) ?>" <?= $filtros['tipo'] === $val ? 'selected' : '' ?>><?= h($rot) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="validade" class="doc-select">
      <option value="">Validade: todas</option>
      <option value="vencidos" <?= $filtros['validade'] === 'vencidos' ? 'selected' : '' ?>>Vencidos</option>
      <option value="30d"      <?= $filtros['validade'] === '30d' ? 'selected' : '' ?>>Vencem em 30 dias</option>
      <option value="vigentes" <?= $filtros['validade'] === 'vigentes' ? 'selected' : '' ?>>Vigentes</option>
    </select>
    <button type="submit" class="btn btn-primario btn-sm">Filtrar</button>
    <?php if ($filtros['q'] || $filtros['categoria'] || $filtros['tipo'] || $filtros['validade']): ?>
      <a href="<?= base_url('documentos') ?>" class="doc-limpar">limpar</a>
    <?php endif; ?>
  </form>

  <?php if (!$docs): ?>
    <div class="card"><p style="color:var(--cor-secundario);text-align:center;padding:2.5rem">
      Nenhum documento encontrado. Os arquivos anexados em cada módulo (imóveis, veículos,
      seguros, contratos…) aparecem aqui automaticamente.
    </p></div>
  <?php else: ?>
    <div class="doc-lista">
      <?php foreach ($docs as $d):
        $chave = $d['tipo_referencia'] . ':' . (int) $d['referencia_id'];
        $vin   = $vinculos[$chave] ?? ['label' => ($tipos[$d['tipo_referencia']] ?? $d['tipo_referencia']), 'nome' => '', 'link' => ''];
        $cat   = $categorias[$d['categoria']] ?? $d['categoria'];
        // Situação de validade.
        $venc_badge = '';
        if (!empty($d['data_validade'])) {
          $dias = dias_ate($d['data_validade']);
          [$vcls, $vcor, $vrot] = alerta_status($dias);
          $venc_badge = '<span class="doc-venc" style="color:' . $vcor . ';border-color:' . $vcor . '55">'
                      . '⏱ ' . h(data_br($d['data_validade'])) . ' · ' . h($vrot) . '</span>';
        }
      ?>
      <div class="doc-item">
        <span class="doc-ico"><?= $ico_ext($d['nome_arquivo']) ?></span>
        <div class="doc-info">
          <div class="doc-nome"><?= h($d['nome_arquivo']) ?></div>
          <div class="doc-meta">
            <span class="doc-tag doc-tag-cat"><?= h($cat) ?></span>
            <span class="doc-tag"><?= h($vin['label']) ?><?php if ($vin['nome'] !== ''): ?>: <?= h($vin['nome']) ?><?php endif; ?></span>
            <?php if ($geral): ?><span class="doc-tag doc-tag-cli">👤 <?= h($d['cliente_nome']) ?></span><?php endif; ?>
            <span class="doc-sz"><?= $fmt_tam($d['tamanho_bytes'] !== null ? (int) $d['tamanho_bytes'] : null) ?></span>
            <?php if (!empty($d['data_emissao'])): ?><span class="doc-sz">emissão <?= h(data_br($d['data_emissao'])) ?></span><?php endif; ?>
          </div>
          <?php if (!empty($d['descricao'])): ?><div class="doc-desc"><?= h($d['descricao']) ?></div><?php endif; ?>
          <?= $venc_badge ?>
        </div>
        <div class="doc-acoes">
          <a class="doc-btn" href="<?= url_documento($d) ?>" target="_blank" rel="noopener" title="Abrir/baixar">⤓ Abrir</a>
          <?php if ($vin['link'] !== ''): ?>
            <a class="doc-btn doc-btn-ghost" href="<?= base_url($vin['link']) ?>" title="Ir ao cadastro">↗ Cadastro</a>
          <?php endif; ?>
          <?php if ($is_admin): ?>
            <form method="post" action="<?= base_url('documentos/excluir?id=' . (int) $d['id']) ?>"
                  onsubmit="return confirm('Excluir este documento? O arquivo será removido.');" style="display:inline">
              <button type="submit" class="doc-btn doc-btn-del" title="Excluir">🗑</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
