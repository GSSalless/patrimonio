<?php
/**
 * Clientes / Pessoas — carteira com abas de status (Todos/Ativos/Inativos).
 * Ativar/desativar NÃO apaga o cliente: só muda `ativo` (sai/entra das
 * consolidações e do contexto de gestão). Reativável a qualquer momento.
 *
 * @var array  $clientes  lista filtrada
 * @var array  $contagem  ['todos','ativos','inativos']
 * @var string $filtro    aba atual
 */
$page_title = 'Clientes';
require APP_ROOT . '/includes/header.php';

$abas = [
    'todos'    => ['Todos',    $contagem['todos']],
    'ativos'   => ['Ativos',   $contagem['ativos']],
    'inativos' => ['Inativos', $contagem['inativos']],
];
?>
<div class="container">
  <div class="card-header cli-head">
    <h2 class="card-titulo">Pessoas / Clientes</h2>
    <a href="<?= base_url('clientes/novo') ?>" class="btn btn-primario">+ Nova pessoa</a>
  </div>

  <!-- Abas de status -->
  <div class="cli-tabs">
    <?php foreach ($abas as $chave => [$rot, $n]): ?>
      <a class="cli-tab<?= $filtro === $chave ? ' ativo' : '' ?>" href="<?= base_url('clientes?status=' . $chave) ?>">
        <?= h($rot) ?> <span class="cli-tab-n"><?= (int) $n ?></span>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if ($clientes): ?>
    <div class="pessoas-grid">
      <?php foreach ($clientes as $c):
        $nome   = $c['nome_completo'] ?: $c['nome'];
        $wa     = link_whatsapp($c['telefone'] ?? '');
        $ini    = mb_strtoupper(mb_substr(trim($c['nome']), 0, 1));
        $ativo  = (int) $c['ativo'] === 1;
      ?>
      <div class="pessoa-card<?= $ativo ? '' : ' inativo' ?>">
        <a class="pessoa-card-corpo" href="<?= base_url('clientes/editar?id=' . $c['id']) ?>">
          <div class="pessoa-avatar"><?= h($ini) ?></div>
          <div class="pessoa-card-info">
            <div class="pessoa-nome-linha">
              <span class="pessoa-nome"><?= h($nome) ?></span>
              <span class="pessoa-status <?= $ativo ? 'st-ativo' : 'st-inativo' ?>"><?= $ativo ? 'Ativo' : 'Inativo' ?></span>
            </div>
            <div class="pessoa-doc"><span class="tag"><?= h($c['tipo_pessoa']) ?></span> <?= h($c['cpf_cnpj']) ?></div>
            <?php if ($c['telefone']): ?><div class="pessoa-linha"><i class="bi bi-telephone"></i> <?= h($c['telefone']) ?></div><?php endif; ?>
            <?php if ($c['email']): ?><div class="pessoa-linha"><i class="bi bi-envelope"></i> <?= h($c['email']) ?></div><?php endif; ?>
            <?php if (!$c['telefone'] && !$c['email']): ?><div class="pessoa-linha pessoa-linha-vazia"><i class="bi bi-dash-circle"></i> sem contato cadastrado</div><?php endif; ?>
          </div>
        </a>
        <div class="pessoa-card-acoes">
          <?php if ($wa): ?>
            <a href="<?= h($wa) ?>" target="_blank" rel="noopener" class="pessoa-wa"
               title="Conversar no WhatsApp" aria-label="Conversar no WhatsApp">
              <i class="bi bi-whatsapp"></i>
            </a>
          <?php endif; ?>

          <div class="pessoa-acoes-dir">
            <form method="post" action="<?= base_url('clientes/status') ?>" class="pessoa-toggle-form">
              <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
              <input type="hidden" name="status" value="<?= h($filtro) ?>">
              <input type="hidden" name="acao" value="<?= $ativo ? 'desativar' : 'ativar' ?>">
              <button type="submit" class="btn btn-sm <?= $ativo ? 'pessoa-btn-desativar' : 'pessoa-btn-ativar' ?>"
                      title="<?= $ativo ? 'Desativar (deixar de gerenciar)' : 'Reativar cliente' ?>"
                      onclick="return confirm('<?= $ativo ? 'Desativar este cliente? Ele sai das consolidações, mas nada é apagado — você pode reativar depois.' : 'Reativar este cliente?' ?>');">
                <i class="bi <?= $ativo ? 'bi-pause-circle' : 'bi-play-circle' ?>"></i>
                <?= $ativo ? 'Desativar' : 'Ativar' ?>
              </button>
            </form>
            <?php if ($ativo): ?>
              <a href="<?= base_url('dashboard?cliente_id=' . $c['id']) ?>" class="btn btn-secundario btn-sm">Patrimônio →</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="card"><p class="cli-vazio">
      <?= $filtro === 'inativos' ? 'Nenhum cliente inativo.' : ($filtro === 'ativos' ? 'Nenhum cliente ativo.' : 'Nenhuma pessoa cadastrada.') ?>
    </p></div>
  <?php endif; ?>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
