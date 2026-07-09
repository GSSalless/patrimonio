<?php
/**
 * @var string $erro
 */
$page_title = 'Novo Cliente';
require APP_ROOT . '/includes/header.php';
?>
<div class="container" style="max-width:720px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <h2 class="card-titulo">Novo Cliente</h2>
    <a href="<?= base_url('clientes') ?>" class="btn btn-secundario">← Voltar</a>
  </div>

  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>

  <div class="card">
    <form method="post">
      <div class="form-secao"><div class="form-secao-titulo">Dados pessoais</div></div>
      <div class="form-grid form-grid-2">
        <div class="form-grupo" style="grid-column:1/-1">
          <label>Nome completo *</label>
          <input type="text" name="nome" required value="<?= h($_POST['nome'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label>Tipo de pessoa *</label>
          <select name="tipo_pessoa" id="tipo_pessoa" onchange="update_mask()">
            <option value="PF" <?= (($_POST['tipo_pessoa']??'PF')==='PF')?'selected':'' ?>>Pessoa Física (CPF)</option>
            <option value="PJ" <?= (($_POST['tipo_pessoa']??'')==='PJ')?'selected':'' ?>>Pessoa Jurídica (CNPJ)</option>
          </select>
        </div>
        <div class="form-grupo">
          <label>CPF / CNPJ *</label>
          <input type="text" name="cpf_cnpj" id="cpf_cnpj" required
                 oninput="mascara_cpf_cnpj(this)" maxlength="18"
                 value="<?= h($_POST['cpf_cnpj'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label>E-mail</label>
          <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label>Telefone</label>
          <input type="text" name="telefone" value="<?= h($_POST['telefone'] ?? '') ?>">
        </div>
      </div>

      <div class="form-secao"><div class="form-secao-titulo">Acesso ao sistema (opcional)</div></div>
      <p style="font-size:.85rem;color:var(--cor-secundario);margin-bottom:1rem">Preencha apenas se o cliente precisar de login próprio para ver o patrimônio dele.</p>
      <div class="form-grid form-grid-2">
        <div class="form-grupo">
          <label>Senha de acesso</label>
          <input type="password" name="senha" autocomplete="new-password">
        </div>
      </div>

      <div style="display:flex;gap:.75rem;margin-top:1.5rem">
        <button type="submit" class="btn btn-primario">Salvar cliente</button>
        <a href="<?= base_url('clientes') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<script>
function update_mask() {
  document.getElementById('cpf_cnpj').value = '';
}
</script>
<?php require APP_ROOT . '/includes/footer.php'; ?>
