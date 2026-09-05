<?php
/**
 * @var array  $ve
 * @var array  $d
 * @var string $erro
 */
$page_title = 'Editar Veículo';
require APP_ROOT . '/includes/header.php';
if (!function_exists('mf_val')) {
    function mf_val(?float $v): string { return $v ? number_format($v, 2, ',', '.') : ''; }
}
$v = fn($k) => h($d[$k] ?? '');
?>
<div class="container" style="max-width:960px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <h2 class="card-titulo">Editar — <?= h(trim(($ve['marca'] ?? '') . ' ' . $ve['modelo'])) ?></h2>
    <a href="<?= base_url('veiculos') ?>" class="btn btn-secundario">← Voltar</a>
  </div>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>

  <div class="card">
    <form method="post">

      <!-- BLOCO 1 -->
      <div class="form-secao"><div class="form-secao-titulo">1. Identificação</div></div>
      <div class="form-grid form-grid-3">
        <div class="form-grupo"><label>Marca</label><input type="text" name="marca" value="<?=$v('marca')?>"></div>
        <div class="form-grupo" style="grid-column:span 2"><label>Modelo *</label><input type="text" name="modelo" required value="<?=$v('modelo')?>"></div>
        <div class="form-grupo"><label>Ano de fabricação</label><input type="number" name="ano_fabricacao" min="1900" max="2100" value="<?=$v('ano_fabricacao')?>"></div>
        <div class="form-grupo"><label>Ano do modelo</label><input type="number" name="ano_modelo" min="1900" max="2100" value="<?=$v('ano_modelo')?>"></div>
        <div class="form-grupo"><label>Cor</label><input type="text" name="cor" value="<?=$v('cor')?>"></div>
        <div class="form-grupo"><label>Combustível</label><select name="combustivel"><option value="">—</option>
          <?php foreach (['gasolina','etanol','flex','diesel','gnv','eletrico','hibrido'] as $c): ?>
          <option value="<?=$c?>" <?=($d['combustivel']??'')===$c?'selected':''?>><?=combustivel_label($c)?></option>
          <?php endforeach; ?></select></div>
        <div class="form-grupo"><label>Placa</label><input type="text" name="placa" maxlength="10" value="<?=$v('placa')?>" style="text-transform:uppercase"></div>
        <div class="form-grupo"><label>Renavam</label><input type="text" name="renavam" value="<?=$v('renavam')?>"></div>
        <div class="form-grupo" style="grid-column:span 2"><label>Chassi</label><input type="text" name="chassi" maxlength="30" value="<?=$v('chassi')?>" style="text-transform:uppercase"></div>
      </div>

      <!-- BLOCO 2 -->
      <div class="form-secao"><div class="form-secao-titulo">2. Documentação</div></div>
      <div class="form-grid form-grid-2">
        <div class="form-grupo"><label>Vencimento do licenciamento</label><input type="date" name="vencimento_licenciamento" value="<?=$v('vencimento_licenciamento')?>"></div>
        <div class="form-grupo"><label>Multas</label><input type="text" name="multas" value="<?=$v('multas')?>"></div>
      </div>

      <!-- BLOCO 3 -->
      <div class="form-secao"><div class="form-secao-titulo">3. Seguro</div></div>
      <div class="form-grid form-grid-4">
        <div class="form-grupo" style="grid-column:span 2"><label>Seguradora</label><input type="text" name="seguradora" value="<?=$v('seguradora')?>"></div>
        <div class="form-grupo"><label>Apólice</label><input type="text" name="apolice" value="<?=$v('apolice')?>"></div>
        <div class="form-grupo"><label>Franquia (R$)</label><input type="text" name="franquia" placeholder="0,00" value="<?=mf_val((float)($d['franquia']??0))?>"></div>
        <div class="form-grupo"><label>Vencimento do seguro</label><input type="date" name="vencimento_seguro" value="<?=$v('vencimento_seguro')?>"></div>
      </div>

      <!-- BLOCO 4 -->
      <div class="form-secao"><div class="form-secao-titulo">4. Financeiro</div></div>
      <div class="form-grid form-grid-4">
        <div class="form-grupo"><label>Data de aquisição</label><input type="date" name="data_aquisicao" value="<?=$v('data_aquisicao')?>"></div>
        <div class="form-grupo"><label>Valor de aquisição (R$)</label><input type="text" name="valor_aquisicao" placeholder="0,00" value="<?=mf_val((float)($d['valor_aquisicao']??0))?>"></div>
        <div class="form-grupo"><label>Valor FIPE (R$)</label><input type="text" name="valor_fipe" placeholder="0,00" value="<?=mf_val((float)($d['valor_fipe']??0))?>"></div>
        <div class="form-grupo"><label>Valor de mercado (R$)</label><input type="text" name="valor_mercado" placeholder="0,00" value="<?=mf_val((float)($d['valor_mercado']??0))?>"></div>
      </div>

      <!-- BLOCO 5 -->
      <div class="form-secao"><div class="form-secao-titulo">5. Controle</div></div>
      <div class="form-grid form-grid-3">
        <div class="form-grupo"><label>KM atual</label><input type="number" name="km_atual" min="0" value="<?=$v('km_atual')?>"></div>
        <div class="form-grupo"><label>Consumo médio (km/l)</label><input type="text" name="consumo_medio" placeholder="0,00" value="<?=mf_val((float)($d['consumo_medio']??0))?>"></div>
        <div class="form-grupo" style="grid-column:1/-1"><label>Observações</label><input type="text" name="observacoes" value="<?=$v('observacoes')?>"></div>
      </div>

      <div style="display:flex;gap:.75rem;margin-top:2rem;padding-top:1.25rem;border-top:1px solid #dde2ea">
        <button type="submit" class="btn btn-primario">Salvar alterações</button>
        <a href="<?= base_url('veiculos') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>

  <?php
  $ve_id = (int) $ve['id'];
  $comb_lbl = fn($c) => $c ? combustivel_label($c) : '—';
  ?>

  <!-- HISTÓRICO: ABASTECIMENTOS -->
  <div class="card" id="abastecimentos" style="margin-top:1.5rem">
    <div class="card-header" style="margin-bottom:1rem">
      <h3 class="card-titulo">⛽ Abastecimentos</h3>
      <a href="<?= base_url('veiculos/abastecimento?veiculo_id='.$ve_id) ?>" class="btn btn-primario btn-sm">+ Abastecimento</a>
    </div>
    <?php if ($abastecimentos): ?>
    <div style="overflow-x:auto">
    <table class="tabela">
      <thead><tr><th>Data</th><th>Combustível</th><th>Litros</th><th>R$/L</th><th>Total</th><th>KM</th><th>Posto</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($abastecimentos as $ab): ?>
        <tr>
          <td><?= data_br($ab['data']) ?></td>
          <td><?= $comb_lbl($ab['combustivel']) ?></td>
          <td><?= $ab['litros'] ? number_format((float)$ab['litros'],2,',','.') : '—' ?></td>
          <td><?= $ab['valor_litro'] ? 'R$ '.number_format((float)$ab['valor_litro'],3,',','.') : '—' ?></td>
          <td><?= $ab['valor_total'] ? moeda((float)$ab['valor_total']) : '—' ?></td>
          <td><?= $ab['km'] ? number_format((int)$ab['km'],0,',','.').' km' : '—' ?></td>
          <td><?= h($ab['posto'] ?? '—') ?></td>
          <td><a href="<?= base_url('veiculos/abastecimento?veiculo_id='.$ve_id.'&id='.$ab['id']) ?>" class="btn btn-secundario btn-sm">Editar</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php else: ?><p style="color:var(--cor-secundario)">Nenhum abastecimento registrado.</p><?php endif; ?>
  </div>

  <!-- HISTÓRICO: MANUTENÇÕES -->
  <?php $vm_tipos = ['revisao'=>'Revisão','troca_oleo'=>'Troca de óleo','pneus'=>'Pneus','freios'=>'Freios','bateria'=>'Bateria','suspensao'=>'Suspensão','eletrica'=>'Elétrica','funilaria'=>'Funilaria/Pintura','peca'=>'Troca de peça','outro'=>'Outro']; ?>
  <div class="card" id="manutencoes" style="margin-top:1.5rem">
    <div class="card-header" style="margin-bottom:1rem">
      <h3 class="card-titulo">🔧 Manutenções</h3>
      <a href="<?= base_url('veiculos/manutencao?veiculo_id='.$ve_id) ?>" class="btn btn-primario btn-sm">+ Manutenção</a>
    </div>
    <?php if ($manutencoes): ?>
    <div style="overflow-x:auto">
    <table class="tabela">
      <thead><tr><th>Data</th><th>Tipo</th><th>Descrição</th><th>Oficina</th><th>KM</th><th>Valor</th><th>Garantia</th><th>Próxima</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($manutencoes as $mt):
          $garante = $mt['garantia_ate'] && $mt['garantia_ate'] >= date('Y-m-d'); ?>
        <tr>
          <td><?= data_br($mt['data']) ?></td>
          <td><span class="tag"><?= $vm_tipos[$mt['tipo']] ?? $mt['tipo'] ?></span></td>
          <td><?= h($mt['descricao']) ?></td>
          <td><?= h($mt['fornecedor'] ?? '—') ?></td>
          <td><?= $mt['km'] ? number_format((int)$mt['km'],0,',','.') : '—' ?></td>
          <td><?= $mt['valor'] ? moeda((float)$mt['valor']) : '—' ?></td>
          <td><?php if ($mt['garantia_ate']): ?><span class="tag <?= $garante?'tag-verde':'tag-vermelho' ?>"><?= data_br($mt['garantia_ate']) ?></span><?php else: ?>—<?php endif; ?></td>
          <td><?= $mt['proxima_data'] ? data_br($mt['proxima_data']) : ($mt['proxima_km'] ? number_format((int)$mt['proxima_km'],0,',','.').' km' : '—') ?></td>
          <td><a href="<?= base_url('veiculos/manutencao?veiculo_id='.$ve_id.'&id='.$mt['id']) ?>" class="btn btn-secundario btn-sm">Editar</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php else: ?><p style="color:var(--cor-secundario)">Nenhuma manutenção registrada.</p><?php endif; ?>
  </div>

  <!-- HISTÓRICO: SINISTROS -->
  <?php
  $si_tipos = ['colisao'=>'Colisão','roubo'=>'Roubo','furto'=>'Furto','avaria'=>'Avaria','vidro'=>'Vidro','terceiros'=>'Terceiros','outro'=>'Outro'];
  $si_status = ['aberto'=>['Aberto','tag-laranja'],'em_analise'=>['Em análise','tag-azul'],'em_reparo'=>['Em reparo','tag-azul'],'concluido'=>['Concluído','tag-verde'],'recusado'=>['Recusado','tag-vermelho']];
  ?>
  <div class="card" id="sinistros" style="margin-top:1.5rem">
    <div class="card-header" style="margin-bottom:1rem">
      <h3 class="card-titulo">⚠️ Sinistros / ocorrências</h3>
      <a href="<?= base_url('veiculos/sinistro?veiculo_id='.$ve_id) ?>" class="btn btn-primario btn-sm">+ Sinistro</a>
    </div>
    <?php if ($sinistros): ?>
    <div style="overflow-x:auto">
    <table class="tabela">
      <thead><tr><th>Data</th><th>Tipo</th><th>Descrição</th><th>Seguro</th><th>Prejuízo</th><th>Franquia</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($sinistros as $si): [$stLbl,$stCls] = $si_status[$si['status']] ?? [$si['status'],'']; ?>
        <tr>
          <td><?= data_br($si['data']) ?></td>
          <td><span class="tag"><?= $si_tipos[$si['tipo']] ?? $si['tipo'] ?></span></td>
          <td><?= h($si['descricao']) ?><?= $si['numero_sinistro'] ? '<br><small style="color:var(--cor-secundario)">nº '.h($si['numero_sinistro']).'</small>' : '' ?></td>
          <td><?= $si['acionou_seguro'] ? 'Acionado' : 'Não' ?></td>
          <td><?= $si['valor_prejuizo'] ? moeda((float)$si['valor_prejuizo']) : '—' ?></td>
          <td><?= $si['valor_franquia'] ? moeda((float)$si['valor_franquia']) : '—' ?></td>
          <td><span class="tag <?= $stCls ?>"><?= $stLbl ?></span></td>
          <td><a href="<?= base_url('veiculos/sinistro?veiculo_id='.$ve_id.'&id='.$si['id']) ?>" class="btn btn-secundario btn-sm">Editar</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php else: ?><p style="color:var(--cor-secundario)">Nenhum sinistro registrado.</p><?php endif; ?>
  </div>

</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
