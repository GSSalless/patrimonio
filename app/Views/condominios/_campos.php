<?php
/**
 * Partial: seções de campos do condomínio (identificação, síndico, valores,
 * comodidades). Compartilhado por novo e editar.
 * @var array    $d
 * @var callable $val    Formata texto: fn($k) => valor
 * @var callable $money  Formata dinheiro: fn($k) => valor
 */
$estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
?>
<div class="form-secao"><div class="form-secao-titulo">1. Identificação</div></div>
<div class="form-grid form-grid-3">
  <div class="form-grupo" style="grid-column:span 2"><label>Nome do condomínio *</label><input type="text" name="nome" required value="<?= $val('nome') ?>"></div>
  <div class="form-grupo"><label>CNPJ</label><input type="text" name="cnpj" value="<?= $val('cnpj') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>Endereço</label><input type="text" name="endereco" value="<?= $val('endereco') ?>"></div>
  <div class="form-grupo"><label>CEP</label><input type="text" name="cep" maxlength="9" value="<?= $val('cep') ?>"></div>
  <div class="form-grupo"><label>Bairro</label><input type="text" name="bairro" value="<?= $val('bairro') ?>"></div>
  <div class="form-grupo"><label>Cidade</label><input type="text" name="cidade" value="<?= $val('cidade') ?>"></div>
  <div class="form-grupo"><label>Estado</label><select name="estado"><option value="">—</option>
    <?php foreach ($estados as $uf): ?><option value="<?= $uf ?>" <?= (($d['estado']??'')===$uf)?'selected':'' ?>><?= $uf ?></option><?php endforeach; ?>
  </select></div>
  <div class="form-grupo"><label>Nº de blocos</label><input type="number" name="numero_blocos" min="0" value="<?= $val('numero_blocos') ?>"></div>
  <div class="form-grupo"><label>Nº de unidades</label><input type="number" name="numero_unidades" min="0" value="<?= $val('numero_unidades') ?>"></div>
</div>

<div class="form-secao"><div class="form-secao-titulo">2. Síndico e Administradora</div></div>
<div class="form-grid form-grid-3">
  <div class="form-grupo"><label>Síndico</label><input type="text" name="sindico_nome" value="<?= $val('sindico_nome') ?>"></div>
  <div class="form-grupo"><label>Telefone do síndico</label><input type="text" name="sindico_telefone" value="<?= $val('sindico_telefone') ?>"></div>
  <div class="form-grupo"><label>E-mail do síndico</label><input type="email" name="sindico_email" value="<?= $val('sindico_email') ?>"></div>
  <div class="form-grupo"><label>Fim do mandato</label><input type="date" name="mandato_fim" value="<?= $val('mandato_fim') ?>"></div>
  <div class="form-grupo"><label>Administradora</label><input type="text" name="administradora" value="<?= $val('administradora') ?>"></div>
  <div class="form-grupo"><label>Telefone da administradora</label><input type="text" name="administradora_telefone" value="<?= $val('administradora_telefone') ?>"></div>
</div>

<div class="form-secao"><div class="form-secao-titulo">3. Valores</div></div>
<div class="form-grid form-grid-3">
  <div class="form-grupo"><label>Taxa condominial (R$/mês)</label><input type="text" name="valor_taxa" placeholder="0,00" value="<?= $money('valor_taxa') ?>"></div>
  <div class="form-grupo"><label>Fundo de reserva (R$/mês)</label><input type="text" name="valor_fundo_reserva" placeholder="0,00" value="<?= $money('valor_fundo_reserva') ?>"></div>
  <div class="form-grupo"><label>Dia de vencimento</label><input type="number" name="dia_vencimento" min="1" max="31" value="<?= $val('dia_vencimento') ?>"></div>
</div>

<div class="form-secao"><div class="form-secao-titulo">4. Comodidades / Estrutura</div></div>
<div class="form-grid form-grid-4">
  <?php foreach (condominio_comodidades() as $col => [$lbl, $ic]): ?>
  <label class="form-grupo" style="flex-direction:row;align-items:center;gap:.5rem;cursor:pointer;margin:0">
    <input type="checkbox" name="<?= $col ?>" value="1" style="width:18px;height:18px;accent-color:#1a7a45" <?= !empty($d[$col])?'checked':'' ?>>
    <span style="font-size:.9rem"><i class="bi <?= $ic ?>" style="margin-right:.3rem;color:var(--cor-primaria)"></i><?= $lbl ?></span>
  </label>
  <?php endforeach; ?>
</div>
