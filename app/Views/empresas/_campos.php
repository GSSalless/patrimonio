<?php
/**
 * Campos do formulário de empresa, compartilhados por novo.php e editar.php.
 * Espera $d (POST ou registro do banco).
 */
$naturezas = [
  'operacional'=>'Operacional','holding_patrimonial'=>'Holding patrimonial',
  'holding_participacao'=>'Holding de participação','spe'=>'SPE','outro'=>'Outra',
];
$regimes = [
  'simples'=>'Simples Nacional','lucro_presumido'=>'Lucro Presumido',
  'lucro_real'=>'Lucro Real','mei'=>'MEI','imune'=>'Imune/Isenta','outro'=>'Outro',
];
$situacoes = ['ativa'=>'Ativa','baixada'=>'Baixada','suspensa'=>'Suspensa','inapta'=>'Inapta'];
$val = fn($k) => h($d[$k] ?? '');
?>
<!-- BLOCO 1 — IDENTIFICAÇÃO -->
<div class="form-secao"><div class="form-secao-titulo">1. Identificação</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo" style="grid-column:span 2"><label>Razão social *</label><input type="text" name="razao_social" required value="<?= $val('razao_social') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>Nome fantasia</label><input type="text" name="nome_fantasia" value="<?= $val('nome_fantasia') ?>"></div>
  <div class="form-grupo"><label>CNPJ</label><input type="text" name="cnpj" placeholder="00.000.000/0001-00" value="<?= $val('cnpj') ?>"></div>
  <div class="form-grupo">
    <label>Natureza</label>
    <select name="natureza">
      <?php foreach ($naturezas as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($d['natureza'] ?? 'operacional') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-grupo"><label>Natureza jurídica</label><input type="text" name="natureza_juridica" placeholder="LTDA, S/A, EIRELI, MEI…" value="<?= $val('natureza_juridica') ?>"></div>
  <div class="form-grupo">
    <label>Situação cadastral</label>
    <select name="situacao">
      <?php foreach ($situacoes as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($d['situacao'] ?? 'ativa') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
</div>

<!-- BLOCO 2 — FISCAL -->
<div class="form-secao"><div class="form-secao-titulo">2. Dados fiscais</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo">
    <label>Regime tributário</label>
    <select name="regime_tributario">
      <option value="">—</option>
      <?php foreach ($regimes as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($d['regime_tributario'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-grupo"><label>Inscrição estadual</label><input type="text" name="inscricao_estadual" value="<?= $val('inscricao_estadual') ?>"></div>
  <div class="form-grupo"><label>Inscrição municipal</label><input type="text" name="inscricao_municipal" value="<?= $val('inscricao_municipal') ?>"></div>
  <div class="form-grupo"><label>Data de abertura</label><input type="date" name="data_abertura" value="<?= $val('data_abertura') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>CNAE principal</label><input type="text" name="cnae_principal" placeholder="0000-0/00 — descrição" value="<?= $val('cnae_principal') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>Capital social (R$)</label><input type="text" name="capital_social" placeholder="0,00" value="<?= $val('capital_social') ?>"></div>
  <div class="form-grupo" style="grid-column:1/-1"><label>CNAEs secundários</label><textarea name="cnaes_secundarios" rows="2"><?= $val('cnaes_secundarios') ?></textarea></div>
</div>

<!-- BLOCO 3 — ENDEREÇO -->
<div class="form-secao"><div class="form-secao-titulo">3. Endereço</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo"><label>CEP</label><input type="text" name="cep" value="<?= $val('cep') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>Logradouro</label><input type="text" name="logradouro" value="<?= $val('logradouro') ?>"></div>
  <div class="form-grupo"><label>Número</label><input type="text" name="numero" value="<?= $val('numero') ?>"></div>
  <div class="form-grupo"><label>Complemento</label><input type="text" name="complemento" value="<?= $val('complemento') ?>"></div>
  <div class="form-grupo"><label>Bairro</label><input type="text" name="bairro" value="<?= $val('bairro') ?>"></div>
  <div class="form-grupo"><label>Cidade</label><input type="text" name="cidade" value="<?= $val('cidade') ?>"></div>
  <div class="form-grupo"><label>UF</label><input type="text" name="estado" maxlength="2" style="text-transform:uppercase" value="<?= $val('estado') ?>"></div>
</div>

<!-- BLOCO 4 — CONTATO E CONTABILIDADE -->
<div class="form-secao"><div class="form-secao-titulo">4. Contato e Contabilidade</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo"><label>Telefone</label><input type="text" name="telefone" value="<?= $val('telefone') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>E-mail</label><input type="text" name="email" value="<?= $val('email') ?>"></div>
  <div class="form-grupo"><label>Site</label><input type="text" name="site" value="<?= $val('site') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>Contador / Escritório contábil</label><input type="text" name="contador_nome" value="<?= $val('contador_nome') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>Contato do contador</label><input type="text" name="contador_contato" placeholder="Telefone ou e-mail" value="<?= $val('contador_contato') ?>"></div>
</div>
