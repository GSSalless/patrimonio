<?php
/**
 * @var array  $cliente
 * @var string $erro
 */
$page_title = 'Editar Cliente';
require APP_ROOT . '/includes/header.php';
?>
<div class="container" style="max-width:720px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <h2 class="card-titulo">Editar Cliente</h2>
    <a href="<?= base_url('clientes') ?>" class="btn btn-secundario">← Voltar</a>
  </div>

  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>

  <div class="card">
    <form method="post">
      <div class="form-grid form-grid-2">
        <div class="form-grupo" style="grid-column:1/-1">
          <label>Nome completo *</label>
          <input type="text" name="nome" required value="<?= h($_POST['nome'] ?? $cliente['nome']) ?>">
        </div>
        <div class="form-grupo">
          <label>Tipo de pessoa *</label>
          <select name="tipo_pessoa">
            <option value="PF" <?= (($_POST['tipo_pessoa'] ?? $cliente['tipo_pessoa'])==='PF')?'selected':'' ?>>Pessoa Física (CPF)</option>
            <option value="PJ" <?= (($_POST['tipo_pessoa'] ?? $cliente['tipo_pessoa'])==='PJ')?'selected':'' ?>>Pessoa Jurídica (CNPJ)</option>
          </select>
        </div>
        <div class="form-grupo">
          <label>CPF / CNPJ *</label>
          <input type="text" name="cpf_cnpj" required oninput="mascara_cpf_cnpj(this)" maxlength="18"
                 value="<?= h($_POST['cpf_cnpj'] ?? $cliente['cpf_cnpj']) ?>">
        </div>
        <div class="form-grupo">
          <label>E-mail</label>
          <input type="email" name="email" value="<?= h($_POST['email'] ?? $cliente['email'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label>Telefone</label>
          <input type="text" name="telefone" value="<?= h($_POST['telefone'] ?? $cliente['telefone'] ?? '') ?>">
        </div>
      </div>
      <div style="display:flex;gap:.75rem;margin-top:1.5rem">
        <button type="submit" class="btn btn-primario">Salvar alterações</button>
        <a href="<?= base_url('clientes') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
