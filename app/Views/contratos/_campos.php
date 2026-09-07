<?php
/**
 * Campos do formulário de contrato, compartilhados por novo.php e editar.php.
 * Espera $d (POST ou registro) e $itens_vinc (grupos de itens vinculáveis).
 */
$tipos = [
  'locacao'=>'Locação','prestacao_servico'=>'Prestação de serviço','fornecimento'=>'Fornecimento',
  'compra_venda'=>'Compra e venda','sociedade'=>'Sociedade','emprestimo'=>'Empréstimo',
  'financiamento'=>'Financiamento','seguro'=>'Seguro','trabalho'=>'Trabalho','outro'=>'Outro',
];
$status_op = ['ativo'=>'Ativo','em_negociacao'=>'Em negociação','suspenso'=>'Suspenso','encerrado'=>'Encerrado','rescindido'=>'Rescindido'];
$periodos = ['unico'=>'Único','mensal'=>'Mensal','trimestral'=>'Trimestral','semestral'=>'Semestral','anual'=>'Anual','outro'=>'Outro'];
$val = fn($k) => h($d[$k] ?? '');

// Valor atual do vínculo (edição traz vinculo_tipo/vinculo_id; POST traz "vinculo").
$vinc_atual = $d['vinculo'] ?? (
  (!empty($d['vinculo_tipo']) && $d['vinculo_tipo'] !== 'nenhum' && !empty($d['vinculo_id']))
    ? $d['vinculo_tipo'] . ':' . $d['vinculo_id'] : ''
);
?>
<!-- BLOCO 1 — IDENTIFICAÇÃO -->
<div class="form-secao"><div class="form-secao-titulo">1. Identificação</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo">
    <label>Tipo de contrato</label>
    <select name="tipo">
      <?php foreach ($tipos as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($d['tipo'] ?? 'outro') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-grupo"><label>Nº do contrato</label><input type="text" name="numero" value="<?= $val('numero') ?>"></div>
  <div class="form-grupo">
    <label>Situação</label>
    <select name="status">
      <?php foreach ($status_op as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($d['status'] ?? 'ativo') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-grupo" style="grid-column:span 4"><label>Objeto do contrato</label><input type="text" name="objeto" placeholder="Descreva o que o contrato trata" value="<?= $val('objeto') ?>"></div>
</div>

<!-- BLOCO 2 — PARTES E VÍNCULO -->
<div class="form-secao"><div class="form-secao-titulo">2. Partes e Vínculo</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo" style="grid-column:span 2"><label>Contraparte (com quem é o contrato)</label><input type="text" name="contraparte_nome" placeholder="Nome / razão social" value="<?= $val('contraparte_nome') ?>"></div>
  <div class="form-grupo"><label>CPF/CNPJ da contraparte</label><input type="text" name="contraparte_doc" value="<?= $val('contraparte_doc') ?>"></div>
  <div class="form-grupo">
    <label>Relacionado a <span style="font-weight:400;color:var(--cor-secundario)">(opcional)</span></label>
    <select name="vinculo">
      <option value="">— Nenhum</option>
      <?php foreach (($itens_vinc ?? []) as $grupo => $itens): ?>
        <optgroup label="<?= h($grupo) ?>">
        <?php foreach ($itens as [$v, $l]): ?>
          <option value="<?= h($v) ?>" <?= $vinc_atual === $v ? 'selected' : '' ?>><?= h($l) ?></option>
        <?php endforeach; ?>
        </optgroup>
      <?php endforeach; ?>
    </select>
  </div>
</div>

<!-- BLOCO 3 — VIGÊNCIA E RENOVAÇÃO -->
<div class="form-secao"><div class="form-secao-titulo">3. Vigência e Renovação</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo"><label>Início</label><input type="date" name="data_inicio" value="<?= $val('data_inicio') ?>"></div>
  <div class="form-grupo"><label>Término / vencimento</label><input type="date" name="data_fim" value="<?= $val('data_fim') ?>"></div>
  <div class="form-grupo"><label>Prazo de renovação</label><input type="text" name="prazo_renovacao" placeholder="Ex.: 12 meses" value="<?= $val('prazo_renovacao') ?>"></div>
  <div class="form-grupo" style="display:flex;align-items:center;padding-top:1.3rem">
    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-weight:400">
      <input type="checkbox" name="renovacao_automatica" value="1" style="width:auto" <?= !empty($d['renovacao_automatica']) ? 'checked' : '' ?>>
      Renovação automática
    </label>
  </div>
</div>

<!-- BLOCO 4 — VALORES -->
<div class="form-secao"><div class="form-secao-titulo">4. Valores</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo" style="grid-column:span 2"><label>Valor (R$)</label><input type="text" name="valor" placeholder="0,00" value="<?= $val('valor') ?>"></div>
  <div class="form-grupo">
    <label>Periodicidade</label>
    <select name="periodicidade">
      <option value="">—</option>
      <?php foreach ($periodos as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($d['periodicidade'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-grupo"><label>Índice de reajuste</label><input type="text" name="indice_reajuste" placeholder="IGP-M, IPCA…" value="<?= $val('indice_reajuste') ?>"></div>
</div>
