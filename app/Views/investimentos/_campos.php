<?php
/**
 * Campos do formulário de investimento, compartilhados por novo.php e editar.php.
 * Espera $d (POST ou registro) e $contas (contas financeiras do cliente).
 */
$classes = [
  'renda_fixa'=>'Renda fixa','tesouro'=>'Tesouro Direto','fundo'=>'Fundo','multimercado'=>'Multimercado',
  'acoes'=>'Ações','previdencia'=>'Previdência','offshore'=>'Offshore','cripto'=>'Cripto','outro'=>'Outro',
];
$indexadores = ['pre'=>'Prefixado','cdi'=>'CDI','ipca'=>'IPCA','selic'=>'Selic','cambio'=>'Câmbio','misto'=>'Misto','na'=>'N/A'];
$liquidezes = ['D0'=>'D+0','D1'=>'D+1','D2'=>'D+2','D30'=>'D+30','D90'=>'D+90','vencimento'=>'No vencimento','outro'=>'Outra'];
$status_op = ['ativo'=>'Ativo','resgatado'=>'Resgatado','vencido'=>'Vencido'];
$val = fn($k) => h($d[$k] ?? '');
?>
<!-- BLOCO 1 — IDENTIFICAÇÃO -->
<div class="form-secao"><div class="form-secao-titulo">1. Identificação</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo" style="grid-column:span 2"><label>Nome do investimento *</label><input type="text" name="nome" required placeholder="Ex.: CDB Banco X 2027, Tesouro IPCA+ 2035" value="<?= $val('nome') ?>"></div>
  <div class="form-grupo">
    <label>Classe</label>
    <select name="classe">
      <?php foreach ($classes as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($d['classe'] ?? 'renda_fixa') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-grupo">
    <label>Situação</label>
    <select name="status">
      <?php foreach ($status_op as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($d['status'] ?? 'ativo') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-grupo" style="grid-column:span 2"><label>Instituição / Corretora</label><input type="text" name="instituicao" placeholder="Ex.: XP, BTG, Itaú" value="<?= $val('instituicao') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>Emissor <span style="font-weight:400;color:var(--cor-secundario)">(renda fixa)</span></label><input type="text" name="emissor" value="<?= $val('emissor') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2">
    <label>Conta financeira vinculada</label>
    <select name="conta_id">
      <option value="">— Nenhuma</option>
      <?php foreach (($contas ?? []) as $c): ?>
      <option value="<?= (int)$c['id'] ?>" <?= (int)($d['conta_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>>
        <?= h($c['codigo']) ?> · <?= h($c['apelido']) ?><?= $c['instituicao'] ? ' (' . h($c['instituicao']) . ')' : '' ?>
      </option>
      <?php endforeach; ?>
    </select>
  </div>
</div>

<!-- BLOCO 2 — RENTABILIDADE E VALORES -->
<div class="form-secao"><div class="form-secao-titulo">2. Rentabilidade e Valores</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo">
    <label>Indexador</label>
    <select name="indexador">
      <option value="">—</option>
      <?php foreach ($indexadores as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($d['indexador'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-grupo" style="grid-column:span 3"><label>Rentabilidade contratada</label><input type="text" name="rentabilidade_contratada" placeholder="Ex.: 110% do CDI · IPCA + 5,5% · 12% a.a." value="<?= $val('rentabilidade_contratada') ?>"></div>
  <div class="form-grupo"><label>Valor aplicado (R$)</label><input type="text" name="valor_aplicado" placeholder="0,00" value="<?= $val('valor_aplicado') ?>"></div>
  <div class="form-grupo"><label>Valor atual (R$)</label><input type="text" name="valor_atual" placeholder="0,00" value="<?= $val('valor_atual') ?>"></div>
  <div class="form-grupo"><label>Quantidade <span style="font-weight:400;color:var(--cor-secundario)">(cotas/ações)</span></label><input type="text" name="quantidade" value="<?= $val('quantidade') ?>"></div>
  <div class="form-grupo"><label>Data da aplicação</label><input type="date" name="data_aplicacao" value="<?= $val('data_aplicacao') ?>"></div>
</div>

<!-- BLOCO 3 — LIQUIDEZ E VENCIMENTO -->
<div class="form-secao"><div class="form-secao-titulo">3. Liquidez e Vencimento</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo">
    <label>Liquidez</label>
    <select name="liquidez">
      <option value="">—</option>
      <?php foreach ($liquidezes as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($d['liquidez'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-grupo"><label>Carência até</label><input type="date" name="carencia_ate" value="<?= $val('carencia_ate') ?>"></div>
  <div class="form-grupo"><label>Vencimento</label><input type="date" name="data_vencimento" value="<?= $val('data_vencimento') ?>"></div>
  <div style="align-self:end;font-size:.8rem;color:var(--cor-secundario);padding-bottom:.6rem">O vencimento aparece na Agenda.</div>
</div>

<!-- BLOCO 4 — TRIBUTAÇÃO E CUSTOS -->
<div class="form-secao"><div class="form-secao-titulo">4. Tributação e Custos</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo"><label>IR (%)</label><input type="text" name="ir_aliquota" placeholder="Ex.: 15" value="<?= $val('ir_aliquota') ?>"></div>
  <div class="form-grupo"><label>Taxa de administração (% a.a.)</label><input type="text" name="taxa_administracao" placeholder="Ex.: 1,00" value="<?= $val('taxa_administracao') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>Taxa de performance</label><input type="text" name="taxa_performance" placeholder="Ex.: 20% sobre o que exceder o CDI" value="<?= $val('taxa_performance') ?>"></div>
  <div class="form-grupo" style="display:flex;align-items:center;gap:.5rem;padding-top:1.6rem">
    <input type="checkbox" name="tem_iof" id="tem_iof" value="1" <?= !empty($d['tem_iof']) ? 'checked' : '' ?> style="width:auto">
    <label for="tem_iof" style="margin:0">Incide IOF</label>
  </div>
  <div class="form-grupo" style="display:flex;align-items:center;gap:.5rem;padding-top:1.6rem">
    <input type="checkbox" name="come_cotas" id="come_cotas" value="1" <?= !empty($d['come_cotas']) ? 'checked' : '' ?> style="width:auto">
    <label for="come_cotas" style="margin:0">Come-cotas</label>
  </div>
</div>
