<?php
/**
 * @var string     $erro
 * @var array|null $cliente
 */
$page_title = 'Nova Pessoa';
require APP_ROOT . '/includes/header.php';
?>
<div class="container" style="max-width:860px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <h2 class="card-titulo">Nova Pessoa</h2>
    <a href="<?= base_url('clientes') ?>" class="btn btn-secundario">← Voltar</a>
  </div>

  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>

  <div class="card">
    <form method="post">
      <?php require __DIR__ . '/_campos.php'; ?>

      <div class="form-secao"><div class="form-secao-titulo">Acesso ao sistema (opcional)</div></div>
      <p style="font-size:.85rem;color:var(--cor-secundario);margin-bottom:1rem">Preencha apenas se a pessoa precisar de login próprio para ver o patrimônio dela (usa o e-mail principal acima).</p>
      <div class="form-grid form-grid-2">
        <div class="form-grupo">
          <label>Senha de acesso</label>
          <input type="password" name="senha" autocomplete="new-password">
        </div>
      </div>

      <div style="display:flex;gap:.75rem;margin-top:1.5rem">
        <button type="submit" class="btn btn-primario">Salvar e continuar cadastro</button>
        <a href="<?= base_url('clientes') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
      <p style="font-size:.8rem;color:var(--cor-secundario);margin-top:.75rem">
        Após salvar você poderá adicionar telefones, e-mails e endereços extras, família/sucessão e contatos de emergência.
      </p>
    </form>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
