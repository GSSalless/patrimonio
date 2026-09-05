<?php
/**
 * Campos do formulário de colaborador, compartilhados por novo.php e editar.php.
 * Espera $d (POST ou registro do banco).
 */
$status_op = ['ativo'=>'Ativo','experiencia'=>'Experiência','afastado'=>'Afastado','ferias'=>'Férias','desligado'=>'Desligado'];
$contratos = ['clt'=>'CLT','pj'=>'PJ','autonomo'=>'Autônomo','diarista'=>'Diarista','temporario'=>'Temporário','estagio'=>'Estágio','outro'=>'Outro'];
$civis = ['solteiro'=>'Solteiro(a)','casado'=>'Casado(a)','divorciado'=>'Divorciado(a)','viuvo'=>'Viúvo(a)','uniao_estavel'=>'União estável','separado'=>'Separado(a)'];
$sangue = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
$escol = ['fundamental'=>'Fundamental','medio'=>'Médio','tecnico'=>'Técnico','superior'=>'Superior','pos'=>'Pós-graduação','mestrado'=>'Mestrado','doutorado'=>'Doutorado','outro'=>'Outro'];
$val = fn($k) => h($d[$k] ?? '');
$sel = fn($k, $v) => ($d[$k] ?? '') === $v ? 'selected' : '';
?>
<!-- BLOCO 1 — DADOS PESSOAIS -->
<div class="form-secao"><div class="form-secao-titulo">1. Dados pessoais</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo" style="grid-column:span 2"><label>Nome completo *</label><input type="text" name="nome" required value="<?= $val('nome') ?>"></div>
  <div class="form-grupo"><label>CPF</label><input type="text" name="cpf" value="<?= $val('cpf') ?>"></div>
  <div class="form-grupo"><label>RG</label><input type="text" name="rg" value="<?= $val('rg') ?>"></div>
  <div class="form-grupo"><label>Nascimento</label><input type="date" name="data_nascimento" value="<?= $val('data_nascimento') ?>"></div>
  <div class="form-grupo">
    <label>Estado civil</label>
    <select name="estado_civil"><option value="">—</option>
      <?php foreach ($civis as $v => $l): ?><option value="<?= $v ?>" <?= $sel('estado_civil', $v) ?>><?= $l ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="form-grupo">
    <label>Tipo sanguíneo</label>
    <select name="tipo_sanguineo"><option value="">—</option>
      <?php foreach ($sangue as $v): ?><option value="<?= $v ?>" <?= $sel('tipo_sanguineo', $v) ?>><?= $v ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="form-grupo"><label>Telefone</label><input type="text" name="telefone" value="<?= $val('telefone') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>E-mail</label><input type="text" name="email" value="<?= $val('email') ?>"></div>
  <div class="form-grupo"><label>CEP</label><input type="text" name="cep" value="<?= $val('cep') ?>"></div>
  <div class="form-grupo"><label>Cidade</label><input type="text" name="cidade" value="<?= $val('cidade') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>Logradouro</label><input type="text" name="logradouro" value="<?= $val('logradouro') ?>"></div>
  <div class="form-grupo"><label>Número</label><input type="text" name="numero" value="<?= $val('numero') ?>"></div>
  <div class="form-grupo"><label>Bairro</label><input type="text" name="bairro" value="<?= $val('bairro') ?>"></div>
  <div class="form-grupo"><label>Complemento</label><input type="text" name="complemento" value="<?= $val('complemento') ?>"></div>
  <div class="form-grupo"><label>UF</label><input type="text" name="estado" maxlength="2" style="text-transform:uppercase" value="<?= $val('estado') ?>"></div>
</div>

<!-- BLOCO 2 — RH / CONTRATO -->
<div class="form-secao"><div class="form-secao-titulo">2. Cargo e Contrato</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo" style="grid-column:span 2"><label>Cargo</label><input type="text" name="cargo" placeholder="Ex.: Motorista, Caseiro, Secretária" value="<?= $val('cargo') ?>"></div>
  <div class="form-grupo"><label>Departamento</label><input type="text" name="departamento" value="<?= $val('departamento') ?>"></div>
  <div class="form-grupo">
    <label>Situação</label>
    <select name="status">
      <?php foreach ($status_op as $v => $l): ?><option value="<?= $v ?>" <?= ($d['status'] ?? 'ativo') === $v ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="form-grupo" style="grid-column:span 2"><label>Gestor</label><input type="text" name="gestor_nome" value="<?= $val('gestor_nome') ?>"></div>
  <div class="form-grupo">
    <label>Tipo de contrato</label>
    <select name="tipo_contrato"><option value="">—</option>
      <?php foreach ($contratos as $v => $l): ?><option value="<?= $v ?>" <?= $sel('tipo_contrato', $v) ?>><?= $l ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="form-grupo"><label>Jornada</label><input type="text" name="jornada" placeholder="Ex.: 44h semanais" value="<?= $val('jornada') ?>"></div>
  <div class="form-grupo"><label>Admissão</label><input type="date" name="data_admissao" value="<?= $val('data_admissao') ?>"></div>
  <div class="form-grupo"><label>Demissão</label><input type="date" name="data_demissao" value="<?= $val('data_demissao') ?>"></div>
  <div class="form-grupo"><label>Salário (R$)</label><input type="text" name="salario" placeholder="0,00" value="<?= $val('salario') ?>"></div>
</div>

<!-- BLOCO 3 — ESCOLARIDADE E SAÚDE -->
<div class="form-secao"><div class="form-secao-titulo">3. Escolaridade e Saúde</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo">
    <label>Escolaridade</label>
    <select name="escolaridade"><option value="">—</option>
      <?php foreach ($escol as $v => $l): ?><option value="<?= $v ?>" <?= $sel('escolaridade', $v) ?>><?= $l ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="form-grupo" style="grid-column:span 3"><label>Formação / cursos</label><input type="text" name="formacao" value="<?= $val('formacao') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>Convênio médico</label><input type="text" name="convenio_medico" value="<?= $val('convenio_medico') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>Alergias / restrições</label><input type="text" name="alergias" value="<?= $val('alergias') ?>"></div>
</div>

<!-- BLOCO 4 — UNIFORMES -->
<div class="form-secao"><div class="form-secao-titulo">4. Uniformes (tamanhos)</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo"><label>Camiseta</label><input type="text" name="uniforme_camiseta" placeholder="Ex.: M" value="<?= $val('uniforme_camiseta') ?>"></div>
  <div class="form-grupo"><label>Camisa</label><input type="text" name="uniforme_camisa" placeholder="Ex.: 2" value="<?= $val('uniforme_camisa') ?>"></div>
  <div class="form-grupo"><label>Calça</label><input type="text" name="uniforme_calca" placeholder="Ex.: 42" value="<?= $val('uniforme_calca') ?>"></div>
  <div class="form-grupo"><label>Calçado</label><input type="text" name="uniforme_calcado" placeholder="Ex.: 41" value="<?= $val('uniforme_calcado') ?>"></div>
</div>

<!-- BLOCO 5 — BENEFÍCIOS -->
<div class="form-secao"><div class="form-secao-titulo">5. Benefícios</div></div>
<div class="form-grid form-grid-4">
  <div class="form-grupo"><label>Vale alimentação (R$)</label><input type="text" name="vale_alimentacao" placeholder="0,00" value="<?= $val('vale_alimentacao') ?>"></div>
  <div class="form-grupo" style="grid-column:span 2"><label>Plano de saúde</label><input type="text" name="plano_saude" value="<?= $val('plano_saude') ?>"></div>
  <div class="form-grupo"><label>Seguro de vida</label><input type="text" name="seguro_vida" value="<?= $val('seguro_vida') ?>"></div>
  <div class="form-grupo" style="grid-column:1/-1"><label>Outros benefícios</label><textarea name="outros_beneficios" rows="2"><?= $val('outros_beneficios') ?></textarea></div>
</div>
