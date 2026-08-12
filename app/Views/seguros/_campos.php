<?php
/**
 * Campos do formulário de seguro, compartilhados por novo.php e editar.php.
 * Espera $d (POST ou registro) e $itens_vinc (grupos de itens seguráveis).
 */
$tipos = [
  'vida'=>'Vida','saude'=>'Saúde','veiculo'=>'Veículo','residencial'=>'Residencial',
  'imovel'=>'Imóvel','embarcacao'=>'Embarcação','empresarial'=>'Empresarial','viagem'=>'Viagem','outro'=>'Outro',
];
$status_op = ['vigente'=>'Vigente','em_cotacao'=>'Em cotação','vencida'=>'Vencida','cancelada'=>'Cancelada'];
$pgtos = ['anual'=>'Anual','semestral'=>'Semestral','mensal'=>'Mensal','parcelado'=>'Parcelado','unico'=>'Único','outro'=>'Outro'];
$val = fn($k) => h($d[$k] ?? '');

// Valor atual do vínculo (edição traz item_tipo/item_id; POST traz "vinculo").
$vinc_atual = $d['vinculo'] ?? (
  (!empty($d['item_tipo']) && $d['item_tipo'] !== 'nenhum' && !empty($d['item_id']))
    ? $d['item_tipo'] . ':' . $d['item_id'] : ''
);
?>
<!-- BLOCO 1 — IDENTIFICAÇÃO -->
<div class="form-secao"><div class="form-secao-titulo">1. Identificação</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo">
    <label>Tipo de seguro</label>
    <select name="tipo">
      <?php foreach ($tipos as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($d['tipo'] ?? 'outro') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-grupo" style="grid-column:span 2">
    <label>Bem / pessoa segurado <span style="font-weight:400;color:var(--cor-secundario)">(opcional)</span></label>
    <select name="vinculo">
      <option value="">— Nenhum (avulso: vida, saúde…)</option>
      <?php foreach (($itens_vinc ?? []) as $grupo => $itens): ?>
        <optgroup label="<?= h($grupo) ?>">
        <?php foreach ($itens as [$v, $l]): ?>
          <option value="<?= h($v) ?>" <?= $vinc_atual === $v ? 'selected' : '' ?>><?= h($l) ?></option>
        <?php endforeach; ?>
        </optgroup>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-grupo">
    <label>Situação</label>
    <select name="status">
      <?php foreach ($status_op as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($d['status'] ?? 'vigente') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
</div>

<!-- BLOCO 2 — SEGURADORA E APÓLICE -->
<div class="form-secao"><div class="form-secao-titulo">2. Seguradora e Apólice</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo" style="grid-column:span 2"><label>Seguradora</label><input type="text" name="seguradora" placeholder="Ex.: Porto Seguro, Bradesco Seguros" value="<?= $val('seguradora') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>Nº da apólice</label><input type="text" name="numero_apolice" value="<?= $val('numero_apolice') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>Corretora</label><input type="text" name="corretora" value="<?= $val('corretora') ?>"></div>
  <div class="form-grupo"><label>Corretor</label><input type="text" name="corretor_nome" value="<?= $val('corretor_nome') ?>"></div>
  <div class="form-grupo"><label>Contato do corretor</label><input type="text" name="corretor_contato" placeholder="Telefone ou e-mail" value="<?= $val('corretor_contato') ?>"></div>
</div>

<!-- BLOCO 3 — VIGÊNCIA E VALORES -->
<div class="form-secao"><div class="form-secao-titulo">3. Vigência e Valores</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo"><label>Início da vigência</label><input type="date" name="vigencia_inicio" value="<?= $val('vigencia_inicio') ?>"></div>
  <div class="form-grupo"><label>Fim da vigência</label><input type="date" name="vigencia_fim" value="<?= $val('vigencia_fim') ?>"></div>
  <div class="form-grupo">
    <label>Forma de pagamento</label>
    <select name="forma_pagamento">
      <option value="">—</option>
      <?php foreach ($pgtos as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($d['forma_pagamento'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-grupo"><label>Prêmio (R$) <span style="font-weight:400;color:var(--cor-secundario)">custo</span></label><input type="text" name="premio" placeholder="0,00" value="<?= $val('premio') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>Valor segurado (R$)</label><input type="text" name="valor_segurado" placeholder="0,00" value="<?= $val('valor_segurado') ?>"></div>
  <div class="form-grupo"><label>Franquia (R$)</label><input type="text" name="franquia" placeholder="0,00" value="<?= $val('franquia') ?>"></div>
</div>

<!-- BLOCO 4 — COBERTURA -->
<div class="form-secao"><div class="form-secao-titulo">4. Cobertura</div></div>
<div class="form-grid form-grid-2">
  <div class="form-grupo"><label>Coberturas</label><textarea name="cobertura" rows="3" placeholder="O que a apólice cobre…"><?= $val('cobertura') ?></textarea></div>
  <div class="form-grupo"><label>Beneficiários <span style="font-weight:400;color:var(--cor-secundario)">(seguro de vida)</span></label><textarea name="beneficiarios" rows="3"><?= $val('beneficiarios') ?></textarea></div>
</div>
