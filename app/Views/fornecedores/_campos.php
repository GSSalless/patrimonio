<?php
/**
 * Campos do formulário de fornecedor, compartilhados por novo.php e editar.php.
 * Espera $d (POST ou registro do banco).
 */
$categorias = [
  'contabilidade'=>'Contabilidade','juridico'=>'Jurídico','seguros'=>'Seguros','marina'=>'Marina',
  'saude'=>'Saúde','tecnologia'=>'Tecnologia','rh'=>'RH','imobiliaria'=>'Imobiliária',
  'manutencao'=>'Manutenção','construcao'=>'Construção','financeiro'=>'Financeiro','transporte'=>'Transporte','outro'=>'Outro',
];
$pix_tipos = ['cpf'=>'CPF','cnpj'=>'CNPJ','email'=>'E-mail','telefone'=>'Telefone','aleatoria'=>'Chave aleatória'];
$val = fn($k) => h($d[$k] ?? '');
?>
<!-- BLOCO 1 — IDENTIFICAÇÃO -->
<div class="form-secao"><div class="form-secao-titulo">1. Identificação</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo" style="grid-column:span 2"><label>Nome / Razão social *</label><input type="text" name="nome" required value="<?= $val('nome') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>Nome fantasia</label><input type="text" name="nome_fantasia" value="<?= $val('nome_fantasia') ?>"></div>
  <div class="form-grupo">
    <label>Tipo</label>
    <select name="tipo_pessoa">
      <option value="PJ" <?= ($d['tipo_pessoa'] ?? 'PJ') === 'PJ' ? 'selected' : '' ?>>PJ</option>
      <option value="PF" <?= ($d['tipo_pessoa'] ?? '') === 'PF' ? 'selected' : '' ?>>PF</option>
    </select>
  </div>
  <div class="form-grupo"><label>CPF/CNPJ</label><input type="text" name="cpf_cnpj" value="<?= $val('cpf_cnpj') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2">
    <label>Categoria</label>
    <select name="categoria">
      <?php foreach ($categorias as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($d['categoria'] ?? 'outro') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
</div>

<!-- BLOCO 2 — CONTATO -->
<div class="form-secao"><div class="form-secao-titulo">2. Contato</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo" style="grid-column:span 2"><label>Pessoa de contato</label><input type="text" name="contato_nome" value="<?= $val('contato_nome') ?>"></div>
  <div class="form-grupo"><label>Telefone</label><input type="text" name="telefone" value="<?= $val('telefone') ?>"></div>
  <div class="form-grupo"><label>Site</label><input type="text" name="site" value="<?= $val('site') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>E-mail</label><input type="text" name="email" value="<?= $val('email') ?>"></div>
  <div class="form-grupo"><label>CEP</label><input type="text" name="cep" value="<?= $val('cep') ?>"></div>
  <div class="form-grupo"><label>Cidade</label><input type="text" name="cidade" value="<?= $val('cidade') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>Logradouro</label><input type="text" name="logradouro" value="<?= $val('logradouro') ?>"></div>
  <div class="form-grupo"><label>Número</label><input type="text" name="numero" value="<?= $val('numero') ?>"></div>
  <div class="form-grupo"><label>Bairro</label><input type="text" name="bairro" value="<?= $val('bairro') ?>"></div>
  <div class="form-grupo"><label>Complemento</label><input type="text" name="complemento" value="<?= $val('complemento') ?>"></div>
  <div class="form-grupo"><label>UF</label><input type="text" name="estado" maxlength="2" style="text-transform:uppercase" value="<?= $val('estado') ?>"></div>
</div>

<!-- BLOCO 3 — CONTRATO -->
<div class="form-secao"><div class="form-secao-titulo">3. Contrato</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo"><label>Início</label><input type="date" name="contrato_inicio" value="<?= $val('contrato_inicio') ?>"></div>
  <div class="form-grupo"><label>Fim / renovação</label><input type="date" name="contrato_fim" value="<?= $val('contrato_fim') ?>"></div>
  <div class="form-grupo"><label>Valor (R$)</label><input type="text" name="contrato_valor" placeholder="0,00" value="<?= $val('contrato_valor') ?>"></div>
  <div class="form-grupo"><label>Forma de pagamento</label><input type="text" name="forma_pagamento" placeholder="Mensal, por serviço…" value="<?= $val('forma_pagamento') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>Reajuste</label><input type="text" name="contrato_reajuste" placeholder="Ex.: IPCA anual" value="<?= $val('contrato_reajuste') ?>"></div>
  <div style="align-self:end;font-size:.8rem;color:var(--cor-secundario);padding-bottom:.6rem;grid-column:span 2">O fim do contrato aparece na Agenda.</div>
</div>

<!-- BLOCO 4 — PAGAMENTO -->
<div class="form-secao"><div class="form-secao-titulo">4. Dados de pagamento</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo" style="grid-column:span 2"><label>Banco</label><input type="text" name="banco" value="<?= $val('banco') ?>"></div>
  <div class="form-grupo"><label>Agência</label><input type="text" name="agencia" value="<?= $val('agencia') ?>"></div>
  <div class="form-grupo"><label>Conta</label><input type="text" name="conta" value="<?= $val('conta') ?>"></div>
  <div class="form-grupo">
    <label>Tipo de chave PIX</label>
    <select name="pix_tipo">
      <option value="">—</option>
      <?php foreach ($pix_tipos as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($d['pix_tipo'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-grupo" style="grid-column:span 3"><label>Chave PIX</label><input type="text" name="pix_chave" value="<?= $val('pix_chave') ?>"></div>
</div>

<!-- BLOCO 5 — AVALIAÇÃO -->
<div class="form-secao"><div class="form-secao-titulo">5. Avaliação</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo">
    <label>Nota (1 a 5)</label>
    <select name="avaliacao_nota">
      <option value="">—</option>
      <?php for ($n = 5; $n >= 1; $n--): ?>
      <option value="<?= $n ?>" <?= (int)($d['avaliacao_nota'] ?? 0) === $n ? 'selected' : '' ?>><?= str_repeat('★', $n) . str_repeat('☆', 5 - $n) ?></option>
      <?php endfor; ?>
    </select>
  </div>
  <div class="form-grupo" style="grid-column:span 3"><label>SLA / acordo de nível de serviço</label><input type="text" name="sla" placeholder="Ex.: atendimento em até 24h" value="<?= $val('sla') ?>"></div>
  <div class="form-grupo" style="grid-column:1/-1"><label>Observações da avaliação</label><textarea name="avaliacao_obs" rows="2"><?= $val('avaliacao_obs') ?></textarea></div>
</div>
