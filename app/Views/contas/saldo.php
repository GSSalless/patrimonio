<?php
/**
 * Registro/edição de saldo no histórico da conta.
 * @var array      $conta
 * @var int        $conta_id
 * @var array|null $sl
 * @var array      $d
 * @var string     $erro
 */
$page_title = ($sl ? 'Editar' : 'Novo') . ' Saldo';
require APP_ROOT . '/includes/header.php';
$val = fn($k) => h($d[$k] ?? '');
$mv  = fn($k) => isset($d[$k]) && $d[$k] !== '' && $d[$k] !== null && is_numeric($d[$k]) ? number_format((float)$d[$k], 2, ',', '.') : h($d[$k] ?? '');
$acao = base_url('contas/saldo?conta_id=' . $conta_id . ($sl ? '&id=' . $sl['id'] : ''));
?>
<div class="container" style="max-width:600px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <h2 class="card-titulo"><?= $sl ? 'Editar' : 'Novo' ?> Saldo — <?= h($conta['apelido']) ?></h2>
    <a href="<?= base_url('contas/editar?id=' . $conta_id . '#saldos') ?>" class="btn btn-secundario">← Voltar</a>
  </div>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>
  <div class="card">
    <p style="color:var(--cor-secundario);font-size:.88rem;margin:-.25rem 0 1rem">O saldo mais recente atualiza automaticamente o <strong>saldo atual</strong> da conta. Use valor negativo (ex.: -500,00) para conta no vermelho.</p>
    <form method="post" action="<?= $acao ?>">
      <div class="form-grid form-grid-2">
        <div class="form-grupo"><label>Data *</label><input type="date" name="data" required value="<?= $val('data') ?: date('Y-m-d') ?>"></div>
        <div class="form-grupo"><label>Saldo (R$) *</label><input type="text" name="saldo" required placeholder="0,00" value="<?= $mv('saldo') ?>"></div>
      </div>
      <div class="form-grupo"><label>Observações</label><textarea name="observacoes" placeholder="Ex.: extrato de fechamento do mês"><?= $val('observacoes') ?></textarea></div>
      <div style="display:flex;gap:.75rem;margin-top:1rem">
        <button type="submit" class="btn btn-primario">Salvar saldo</button>
        <a href="<?= base_url('contas/editar?id=' . $conta_id . '#saldos') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
