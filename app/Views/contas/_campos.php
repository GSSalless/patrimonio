<?php
/**
 * Campos do formulário de conta financeira, compartilhados por novo.php e
 * editar.php. Espera $d (dados: POST ou registro do banco).
 */
$tipos_conta = [
  'corrente'      => 'Conta Corrente',
  'poupanca'      => 'Poupança',
  'pagamento'     => 'Conta de Pagamento',
  'investimento'  => 'Investimento',
  'internacional' => 'Internacional',
  'outro'         => 'Outra',
];
$pix_tipos = ['cpf'=>'CPF','cnpj'=>'CNPJ','email'=>'E-mail','telefone'=>'Telefone','aleatoria'=>'Chave aleatória'];
$val = fn($k) => h($d[$k] ?? '');
?>
<!-- BLOCO 1 — IDENTIFICAÇÃO -->
<div class="form-secao"><div class="form-secao-titulo">1. Identificação</div></div>
<div class="form-grid form-grid-3">
  <div class="form-grupo" style="grid-column:span 2">
    <label>Apelido / Nome da conta *</label>
    <input type="text" name="apelido" required placeholder="Ex.: Conta principal Itaú · Conta digital Asaas" value="<?= $val('apelido') ?>">
  </div>
  <div class="form-grupo">
    <label>Tipo</label>
    <select name="tipo" id="campo-tipo-conta">
      <?php foreach ($tipos_conta as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($d['tipo'] ?? 'corrente') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
</div>

<!-- BLOCO 2 — INSTITUIÇÃO -->
<div class="form-secao"><div class="form-secao-titulo">2. Instituição e Conta</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo" style="grid-column:span 2"><label>Instituição</label><input type="text" name="instituicao" placeholder="Ex.: Itaú, XP, Asaas, BTG" value="<?= $val('instituicao') ?>"></div>
  <div class="form-grupo"><label>Código do banco</label><input type="text" name="banco_codigo" placeholder="Ex.: 341" value="<?= $val('banco_codigo') ?>"></div>
  <div class="form-grupo"><label>Moeda</label><input type="text" name="moeda" maxlength="3" placeholder="BRL" value="<?= $val('moeda') ?: 'BRL' ?>" style="text-transform:uppercase"></div>
  <div class="form-grupo"><label>Agência</label><input type="text" name="agencia" placeholder="0000" value="<?= $val('agencia') ?>"></div>
  <div class="form-grupo"><label>Nº da conta (com dígito)</label><input type="text" name="numero_conta" placeholder="00000-0" value="<?= $val('numero_conta') ?>"></div>
  <div class="form-grupo">
    <label>Tipo de chave PIX</label>
    <select name="pix_tipo">
      <option value="">—</option>
      <?php foreach ($pix_tipos as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($d['pix_tipo'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-grupo"><label>Chave PIX</label><input type="text" name="pix_chave" value="<?= $val('pix_chave') ?>"></div>
</div>

<!-- BLOCO 3 — INTERNACIONAL -->
<div class="form-secao" id="secao-internacional"><div class="form-secao-titulo">3. Dados internacionais <span style="font-weight:400;color:var(--cor-secundario)">(se aplicável)</span></div></div>
<div class="form-grid form-grid-3" id="grid-internacional">
  <div class="form-grupo"><label>SWIFT / BIC</label><input type="text" name="swift" value="<?= $val('swift') ?>"></div>
  <div class="form-grupo"><label>IBAN</label><input type="text" name="iban" value="<?= $val('iban') ?>"></div>
  <div class="form-grupo"><label>Routing / ABA</label><input type="text" name="routing" value="<?= $val('routing') ?>"></div>
</div>

<!-- BLOCO 4 — TITULARIDADE E RELACIONAMENTO -->
<div class="form-secao"><div class="form-secao-titulo">4. Titularidade e Relacionamento</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo" style="grid-column:span 2"><label>Titular <span style="font-weight:400;color:var(--cor-secundario)">(se diferente do cliente)</span></label><input type="text" name="titular_nome" placeholder="Ex.: Road Empreendimentos Ltda" value="<?= $val('titular_nome') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>CPF/CNPJ do titular</label><input type="text" name="titular_cpf_cnpj" value="<?= $val('titular_cpf_cnpj') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>Gerente / Assessor</label><input type="text" name="gerente_nome" value="<?= $val('gerente_nome') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>Contato do gerente</label><input type="text" name="gerente_contato" placeholder="Telefone ou e-mail" value="<?= $val('gerente_contato') ?>"></div>
</div>

<!-- BLOCO 5 — INTEGRAÇÃO ASAAS -->
<div class="form-secao"><div class="form-secao-titulo">5. Integração Asaas</div></div>
<div class="form-grid form-grid-3">
  <div class="form-grupo">
    <label>Integração</label>
    <select name="integracao" id="campo-integracao">
      <option value="nenhuma" <?= ($d['integracao'] ?? 'nenhuma') !== 'asaas' ? 'selected' : '' ?>>Nenhuma</option>
      <option value="asaas"   <?= ($d['integracao'] ?? '') === 'asaas' ? 'selected' : '' ?>>Asaas</option>
    </select>
  </div>
</div>
<div class="form-grid form-grid-3" id="grid-asaas">
  <div class="form-grupo"><label>ID da conta no Asaas</label><input type="text" name="asaas_account_id" placeholder="id da conta/subconta" value="<?= $val('asaas_account_id') ?>"></div>
  <div class="form-grupo"><label>Wallet ID</label><input type="text" name="asaas_wallet_id" placeholder="walletId (split/transferências)" value="<?= $val('asaas_wallet_id') ?>"></div>
  <div class="form-grupo"><label>Chave de API</label><input type="password" name="asaas_api_key" autocomplete="new-password" placeholder="<?= !empty($d['asaas_api_key']) ? '•••••••• (preenchida)' : '$aact_…' ?>" value="<?= $val('asaas_api_key') ?>"></div>
  <div style="grid-column:1/-1;font-size:.8rem;color:var(--cor-secundario);margin-top:-.5rem">
    Campos usados pela futura sincronização automática (saldo, extrato e cobranças) via
    <a href="https://docs.asaas.com" target="_blank" rel="noopener">API do Asaas</a>.
  </div>
</div>
<script>
(function(){
  const sel=document.getElementById('campo-integracao');
  const grid=document.getElementById('grid-asaas');
  function toggleAsaas(){ if(grid) grid.style.display = sel.value==='asaas' ? '' : 'none'; }
  if(sel){ sel.addEventListener('change',toggleAsaas); toggleAsaas(); }
})();
</script>
