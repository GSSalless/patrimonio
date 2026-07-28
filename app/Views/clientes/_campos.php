<?php
/**
 * Campos diretos da pessoa (tabela clientes). Compartilhado por novo.php e
 * pela seção "Dados" de editar.php.
 * @var array|null $cliente  (null no cadastro novo)
 */
$v = fn(string $k) => h((string) ($_POST[$k] ?? $cliente[$k] ?? ''));
$sel = fn(string $k, string $opt) => (($_POST[$k] ?? $cliente[$k] ?? '') === $opt) ? 'selected' : '';
$ehEdicao = isset($cliente);
?>
<div class="form-secao"><div class="form-secao-titulo">Identificação</div></div>
<div class="form-grid form-grid-2">
  <div class="form-grupo">
    <label>Tipo de pessoa *</label>
    <select name="tipo_pessoa">
      <option value="PF" <?= $sel('tipo_pessoa','PF') ?>>Pessoa Física (CPF)</option>
      <option value="PJ" <?= $sel('tipo_pessoa','PJ') ?>>Pessoa Jurídica (CNPJ)</option>
    </select>
  </div>
  <div class="form-grupo">
    <label>CPF / CNPJ *</label>
    <input type="text" name="cpf_cnpj" required oninput="mascara_cpf_cnpj(this)" maxlength="18" value="<?= $v('cpf_cnpj') ?>">
  </div>
  <div class="form-grupo">
    <label>Nome (como é chamado) *</label>
    <input type="text" name="nome" required value="<?= $v('nome') ?>" placeholder="ex.: Marcos Borges">
  </div>
  <div class="form-grupo">
    <label>Apelido</label>
    <input type="text" name="apelido" value="<?= $v('apelido') ?>">
  </div>
  <div class="form-grupo" style="grid-column:1/-1">
    <label>Nome civil completo</label>
    <input type="text" name="nome_completo" value="<?= $v('nome_completo') ?>" placeholder="ex.: Marcos Vinícius Machado Borges">
  </div>
  <div class="form-grupo">
    <label>RG</label>
    <input type="text" name="rg" value="<?= $v('rg') ?>">
  </div>
  <div class="form-grupo">
    <label>Órgão emissor</label>
    <input type="text" name="rg_orgao" value="<?= $v('rg_orgao') ?>" placeholder="SSP">
  </div>
  <div class="form-grupo">
    <label>UF do RG</label>
    <input type="text" name="rg_uf" maxlength="2" value="<?= $v('rg_uf') ?>" style="text-transform:uppercase">
  </div>
</div>

<div class="form-secao"><div class="form-secao-titulo">Dados pessoais</div></div>
<div class="form-grid form-grid-2">
  <div class="form-grupo">
    <label>Data de nascimento</label>
    <input type="date" name="data_nascimento" value="<?= $v('data_nascimento') ?>">
  </div>
  <div class="form-grupo">
    <label>Naturalidade</label>
    <input type="text" name="naturalidade" value="<?= $v('naturalidade') ?>" placeholder="Cidade natal">
  </div>
  <div class="form-grupo">
    <label>Nacionalidade</label>
    <input type="text" name="nacionalidade" value="<?= h((string)($_POST['nacionalidade'] ?? $cliente['nacionalidade'] ?? 'Brasileira')) ?>">
  </div>
  <div class="form-grupo">
    <label>Profissão</label>
    <input type="text" name="profissao" value="<?= $v('profissao') ?>">
  </div>
  <div class="form-grupo">
    <label>Estado civil</label>
    <select name="estado_civil">
      <option value="">—</option>
      <?php foreach (['solteiro'=>'Solteiro(a)','casado'=>'Casado(a)','divorciado'=>'Divorciado(a)','viuvo'=>'Viúvo(a)','uniao_estavel'=>'União estável','separado'=>'Separado(a)'] as $k=>$lbl): ?>
        <option value="<?= $k ?>" <?= $sel('estado_civil',$k) ?>><?= $lbl ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-grupo">
    <label>Regime de bens</label>
    <select name="regime_bens">
      <option value="">—</option>
      <?php foreach (['comunhao_parcial'=>'Comunhão parcial','comunhao_universal'=>'Comunhão universal','separacao_total'=>'Separação total','participacao_final'=>'Participação final nos aquestos','nao_aplicavel'=>'Não se aplica'] as $k=>$lbl): ?>
        <option value="<?= $k ?>" <?= $sel('regime_bens',$k) ?>><?= $lbl ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-grupo">
    <label>Tipo sanguíneo</label>
    <select name="tipo_sanguineo">
      <option value="">—</option>
      <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $ts): ?>
        <option value="<?= $ts ?>" <?= $sel('tipo_sanguineo',$ts) ?>><?= $ts ?></option>
      <?php endforeach; ?>
    </select>
  </div>
</div>

<div class="form-secao"><div class="form-secao-titulo">Filiação</div></div>
<div class="form-grid form-grid-2">
  <div class="form-grupo">
    <label>Nome do pai</label>
    <input type="text" name="nome_pai" value="<?= $v('nome_pai') ?>">
  </div>
  <div class="form-grupo">
    <label>Nome da mãe</label>
    <input type="text" name="nome_mae" value="<?= $v('nome_mae') ?>">
  </div>
</div>

<div class="form-secao"><div class="form-secao-titulo">Endereço principal</div></div>
<div class="form-grid form-grid-2">
  <div class="form-grupo">
    <label>CEP</label>
    <input type="text" name="cep" maxlength="9" value="<?= $v('cep') ?>"
           oninput="mascara_cep(this)" onblur="busca_cep(this)">
  </div>
  <div class="form-grupo" style="grid-column:2/-1">
    <label>Logradouro</label>
    <input type="text" name="logradouro" id="logradouro" value="<?= $v('logradouro') ?>">
  </div>
  <div class="form-grupo">
    <label>Número</label>
    <input type="text" name="numero" value="<?= $v('numero') ?>">
  </div>
  <div class="form-grupo">
    <label>Complemento</label>
    <input type="text" name="complemento" value="<?= $v('complemento') ?>">
  </div>
  <div class="form-grupo">
    <label>Bairro</label>
    <input type="text" name="bairro" id="bairro" value="<?= $v('bairro') ?>">
  </div>
  <div class="form-grupo">
    <label>Cidade</label>
    <input type="text" name="cidade" id="cidade" value="<?= $v('cidade') ?>">
  </div>
  <div class="form-grupo">
    <label>UF</label>
    <input type="text" name="uf" id="estado" maxlength="2" value="<?= $v('uf') ?>" style="text-transform:uppercase">
  </div>
</div>

<div class="form-secao"><div class="form-secao-titulo">Contato principal</div></div>
<div class="form-grid form-grid-2">
  <div class="form-grupo">
    <label>Telefone principal (WhatsApp)</label>
    <input type="text" name="telefone" value="<?= $v('telefone') ?>" placeholder="(11) 90000-0000">
  </div>
  <div class="form-grupo">
    <label>E-mail principal</label>
    <input type="email" name="email" value="<?= $v('email') ?>">
  </div>
</div>

<div class="form-secao"><div class="form-secao-titulo">Testamento e sucessão</div></div>
<div class="form-grid form-grid-2">
  <div class="form-grupo" style="grid-column:1/-1">
    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
      <input type="checkbox" name="tem_testamento" value="1" style="width:auto"
             <?= (($_POST['tem_testamento'] ?? $cliente['tem_testamento'] ?? 0)) ? 'checked' : '' ?>>
      Possui testamento
    </label>
  </div>
  <div class="form-grupo" style="grid-column:1/-1">
    <label>Dados do testamento (cartório, livro, data, observações)</label>
    <textarea name="testamento_obs" rows="2"><?= $v('testamento_obs') ?></textarea>
  </div>
  <?php if ($ehEdicao): ?>
  <div class="form-grupo" style="grid-column:1/-1">
    <label>Anexar documento do testamento (PDF/imagem)</label>
    <input type="file" name="testamento_arquivo" accept=".pdf,image/*">
  </div>
  <?php endif; ?>
</div>

<div class="form-secao"><div class="form-secao-titulo">Observações gerais</div></div>
<div class="form-grid">
  <div class="form-grupo" style="grid-column:1/-1">
    <textarea name="observacoes" rows="3"><?= $v('observacoes') ?></textarea>
  </div>
</div>
