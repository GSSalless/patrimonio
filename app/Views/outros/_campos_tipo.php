<?php
/**
 * Partial: blocos de campos específicos por tipo de bem.
 * Compartilhado entre novo e editar (mantém os dois espelhados).
 * @var string $tipo
 * @var array  $d
 */
?>
<?php if ($tipo === 'embarcacao'): ?>
<div class="form-secao"><div class="form-secao-titulo">2. Dados da Embarcação</div></div>
<div class="form-grid form-grid-3">
  <div class="form-grupo">
    <label>Tipo de embarcação</label>
    <select name="tipo_embarcacao">
      <option value="">—</option>
      <?php foreach (['jet_ski'=>'Jet Ski','lancha'=>'Lancha','barco'=>'Barco','veleiro'=>'Veleiro','iate'=>'Iate','outro'=>'Outro'] as $v=>$l): ?>
      <option value="<?= $v ?>" <?= (($d['tipo_embarcacao']??'')===$v)?'selected':'' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-grupo"><label>Comprimento (m)</label><input type="text" name="comprimento_m" placeholder="8,5" value="<?= h($d['comprimento_m'] ?? '') ?>"></div>
  <div class="form-grupo"><label>Motor</label><input type="text" name="motor" placeholder="Ex.: 300hp MerCruiser" value="<?= h($d['motor'] ?? '') ?>"></div>
  <div class="form-grupo"><label>Horímetro (h)</label><input type="number" name="horimetro" min="0" placeholder="0" value="<?= h($d['horimetro'] ?? '') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>Registro (Capitania dos Portos)</label><input type="text" name="registro_embarcacao" placeholder="Nº de inscrição" value="<?= h($d['registro_embarcacao'] ?? '') ?>"></div>
  <div class="form-grupo"><label>Marina</label><input type="text" name="marina" placeholder="Ex.: Marina Atlântico" value="<?= h($d['marina'] ?? '') ?>"></div>
  <div class="form-grupo"><label>Vaga / Box</label><input type="text" name="vaga_marina" placeholder="Ex.: B-12" value="<?= h($d['vaga_marina'] ?? '') ?>"></div>
  <div class="form-grupo"><label>Mensalidade da marina (R$)</label><input type="text" name="mensalidade_marina" placeholder="0,00" value="<?= h($d['mensalidade_marina'] ?? '') ?>"></div>
</div>
<?php endif; ?>

<?php if ($tipo === 'joia'): ?>
<div class="form-secao"><div class="form-secao-titulo">2. Dados da Joia</div></div>
<div class="form-grid form-grid-3">
  <div class="form-grupo" style="grid-column:span 2"><label>Material</label><input type="text" name="material" placeholder="Ex.: Ouro 18k, Platina, Diamante" value="<?= h($d['material'] ?? '') ?>"></div>
  <div class="form-grupo"><label>Quilates / Peso (ct ou g)</label><input type="text" name="quilates" placeholder="Ex.: 1,50" value="<?= h($d['quilates'] ?? '') ?>"></div>
  <div class="form-grupo" style="grid-column:1/-1"><label>Certificado de autenticidade</label><input type="text" name="certificado" placeholder="Ex.: GIA 12345678" value="<?= h($d['certificado'] ?? '') ?>"></div>
</div>
<?php endif; ?>

<?php if ($tipo === 'obra_de_arte'): ?>
<div class="form-secao"><div class="form-secao-titulo">2. Dados da Obra de Arte</div></div>
<div class="form-grid form-grid-3">
  <div class="form-grupo" style="grid-column:span 2"><label>Artista / Autor</label><input type="text" name="artista_autor" placeholder="Ex.: Cândido Portinari" value="<?= h($d['artista_autor'] ?? '') ?>"></div>
  <div class="form-grupo"><label>Técnica</label><input type="text" name="tecnica" placeholder="Ex.: Óleo sobre tela" value="<?= h($d['tecnica'] ?? '') ?>"></div>
  <div class="form-grupo"><label>Dimensões</label><input type="text" name="dimensoes" placeholder="Ex.: 80×120 cm" value="<?= h($d['dimensoes'] ?? '') ?>"></div>
  <div class="form-grupo"><label>Material / Suporte</label><input type="text" name="material" placeholder="Ex.: Tela, madeira, papel" value="<?= h($d['material'] ?? '') ?>"></div>
  <div class="form-grupo" style="grid-column:1/-1"><label>Certificado / Proveniência</label><input type="text" name="certificado" placeholder="Ex.: Leilão Sotheby's 2019, certificado nº…" value="<?= h($d['certificado'] ?? '') ?>"></div>
</div>
<?php endif; ?>
