<?php
/**
 * @var array  $im    Imóvel (do banco)
 * @var array  $d      Repopulação: $_POST (após erro) ou $im
 * @var string $erro
 * @var int    $id
 */
$page_title = 'Editar Imóvel';
require APP_ROOT . '/includes/header.php';

if (!function_exists('mf_val')) {
    function mf_val(?float $v): string { return $v ? number_format($v, 2, ',', '.') : ''; }
}
$v = fn($k, $dec = false) => $dec ? mf_val((float)($d[$k] ?? 0)) : h($d[$k] ?? '');
$estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
?>
<div class="container" style="max-width:960px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <h2 class="card-titulo">Editar — <?= h($im['nome_referencia']) ?></h2>
    <a href="<?= base_url('imoveis/ficha?id='.$id) ?>" class="btn btn-secundario">← Voltar</a>
  </div>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>

  <div class="card">
    <form method="post">

      <!-- BLOCO 1 -->
      <div class="form-secao"><div class="form-secao-titulo">1. Identificação</div></div>
      <div class="form-grid form-grid-2">
        <div class="form-grupo" style="grid-column:1/-1">
          <label>Nome de referência *</label>
          <input type="text" name="nome_referencia" required value="<?= $v('nome_referencia') ?>">
        </div>
        <div class="form-grupo"><label>Tipo</label><select name="tipo">
          <?php foreach (['apartamento','casa','sala_comercial','terreno','galpao','loja','hotel','outro'] as $t): ?>
          <option value="<?=$t?>" <?=($d['tipo']===$t)?'selected':''?>><?=tipo_imovel_label($t)?></option>
          <?php endforeach; ?></select></div>
        <div class="form-grupo"><label>Finalidade</label><select name="finalidade">
          <?php foreach (['residencial','comercial','mista','locacao','investimento'] as $f): ?>
          <option value="<?=$f?>" <?=($d['finalidade']===$f)?'selected':''?>><?=finalidade_label($f)?></option>
          <?php endforeach; ?></select></div>
        <div class="form-grupo"><label>Situação</label><select name="situacao">
          <?php foreach (['pronto'=>'Pronto','em_construcao'=>'Em Construção','na_planta'=>'Na Planta'] as $sv=>$sl): ?>
          <option value="<?=$sv?>" <?=($d['situacao']===$sv)?'selected':''?>><?=$sl?></option>
          <?php endforeach; ?></select></div>
        <div class="form-grupo"><label>Nº Inscrição Municipal / IPTU</label><input type="text" name="inscricao_municipal" value="<?=$v('inscricao_municipal')?>"></div>
      </div>

      <!-- BLOCO 2 -->
      <div class="form-secao"><div class="form-secao-titulo">2. Localização</div></div>
      <div class="form-grid form-grid-4">
        <div class="form-grupo"><label>CEP</label><input type="text" name="cep" id="cep" maxlength="9" oninput="mascara_cep(this)" onblur="busca_cep(this)" value="<?=$v('cep')?>"></div>
        <div class="form-grupo" style="grid-column:span 3"><label>Logradouro</label><input type="text" name="logradouro" id="logradouro" value="<?=$v('logradouro')?>"></div>
        <div class="form-grupo"><label>Número</label><input type="text" name="numero" value="<?=$v('numero')?>"></div>
        <div class="form-grupo" style="grid-column:span 3"><label>Complemento</label><input type="text" name="complemento" value="<?=$v('complemento')?>"></div>
        <div class="form-grupo" style="grid-column:span 2"><label>Bairro</label><input type="text" name="bairro" id="bairro" value="<?=$v('bairro')?>"></div>
        <div class="form-grupo"><label>Cidade</label><input type="text" name="cidade" id="cidade" value="<?=$v('cidade')?>"></div>
        <div class="form-grupo"><label>Estado</label><select name="estado" id="estado"><option value="">—</option>
          <?php foreach($estados as $uf): ?><option value="<?=$uf?>" <?=$d['estado']===$uf?'selected':''?>><?=$uf?></option><?php endforeach;?></select></div>
      </div>
      <div id="preview-localizacao" style="display:none;margin-bottom:1rem;padding:.85rem 1rem;background:#faf7ef;border-radius:8px;border-left:4px solid var(--cor-acento)">
        <div style="font-size:.78rem;color:var(--cor-secundario);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.5rem">Links gerados automaticamente</div>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap">
          <a id="btn-preview-maps" href="<?= h($im['link_maps'] ?? '#') ?>" target="_blank" class="btn btn-secundario btn-sm">📍 Google Maps</a>
          <a id="btn-preview-sv"   href="<?= h($im['link_street_view'] ?? '#') ?>" target="_blank" class="btn btn-secundario btn-sm">🏙️ Street View</a>
        </div>
        <div id="preview-endereco" style="font-size:.82rem;color:var(--cor-secundario);margin-top:.4rem"></div>
      </div>

      <!-- BLOCO 3 -->
      <div class="form-secao"><div class="form-secao-titulo">3. Dados da Matrícula</div></div>
      <div class="form-grid form-grid-3">
        <div class="form-grupo"><label>Número da matrícula</label><input type="text" name="numero_matricula" value="<?=$v('numero_matricula')?>"></div>
        <div class="form-grupo"><label>Data da matrícula</label><input type="date" name="data_matricula" value="<?=$v('data_matricula')?>"></div>
        <div class="form-grupo"><label>Comarca</label><input type="text" name="comarca" value="<?=$v('comarca')?>"></div>
        <div class="form-grupo" style="grid-column:span 2"><label>Cartório de Registro de Imóveis</label><input type="text" name="cartorio" value="<?=$v('cartorio')?>"></div>
        <div class="form-grupo"><label>Site do cartório</label><input type="url" name="site_cartorio" placeholder="https://…" value="<?=$v('site_cartorio')?>"></div>
        <div class="form-grupo"><label>Livro</label><input type="text" name="livro" value="<?=$v('livro')?>"></div>
        <div class="form-grupo"><label>Folha</label><input type="text" name="folha" value="<?=$v('folha')?>"></div>
      </div>

      <!-- BLOCO 4 -->
      <div class="form-secao"><div class="form-secao-titulo">4. Titularidade</div></div>
      <div class="form-grid form-grid-3">
        <div class="form-grupo"><label>Data de aquisição</label><input type="date" name="data_aquisicao" value="<?=$v('data_aquisicao')?>"></div>
        <div class="form-grupo"><label>Forma de aquisição</label><select name="forma_aquisicao"><option value="">—</option>
          <?php foreach(['compra','heranca','doacao','permuta','integralizacao','outro'] as $fa): ?>
          <option value="<?=$fa?>" <?=($d['forma_aquisicao']??'')===$fa?'selected':''?>><?=forma_aquisicao_label($fa)?></option>
          <?php endforeach; ?></select></div>
        <div class="form-grupo"><label>% de participação</label><input type="number" name="percentual_participacao" id="percentual_participacao" min="0" max="100" step="0.01" value="<?=$v('percentual_participacao')?>"></div>
        <?php $tem_coprop = isset($d['percentual_participacao']) && $d['percentual_participacao'] !== '' && (float)str_replace(',', '.', (string)$d['percentual_participacao']) < 100; ?>
        <div class="form-grupo" id="grupo-outros-proprietarios" style="grid-column:span 2;<?= $tem_coprop ? '' : 'display:none' ?>">
          <label>Outro(s) proprietário(s) *</label>
          <input type="text" name="outros_proprietarios" id="outros_proprietarios" placeholder="Nome do(s) coproprietário(s) e respectiva participação" value="<?=$v('outros_proprietarios')?>">
        </div>
      </div>

      <!-- BLOCO 5 -->
      <div class="form-secao"><div class="form-secao-titulo">5. Características Físicas</div></div>
      <div style="font-size:.8rem;font-weight:700;color:var(--cor-secundario);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.6rem">Áreas (m²)</div>
      <div class="form-grid form-grid-4" style="margin-bottom:1rem">
        <?php foreach(['area_privativa'=>'Privativa','area_total'=>'Total','area_construida'=>'Construída','area_comum'=>'Comum','area_terreno'=>'Terreno'] as $c=>$l): ?>
        <div class="form-grupo"><label><?=$l?></label><input type="text" name="<?=$c?>" placeholder="0,00" value="<?=mf_val((float)($d[$c]??0))?>"></div>
        <?php endforeach;?>
      </div>
      <div style="font-size:.8rem;font-weight:700;color:var(--cor-secundario);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.6rem">Ambientes</div>
      <div class="form-grid form-grid-4" style="margin-bottom:1rem">
        <?php foreach(['quartos'=>'Quartos','suites'=>'Suítes','banheiros'=>'Banheiros','lavabos'=>'Lavabos','vagas_garagem'=>'Vagas'] as $c=>$l): ?>
        <div class="form-grupo"><label><?=$l?></label><input type="number" name="<?=$c?>" min="0" max="99" value="<?=$v($c)?>"></div>
        <?php endforeach;?>
      </div>
      <div style="font-size:.8rem;font-weight:700;color:var(--cor-secundario);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.6rem">Estrutura</div>
      <div class="form-grid form-grid-4">
        <div class="form-grupo"><label>Andar</label><input type="number" name="andar" min="0" max="200" value="<?=$v('andar')?>"></div>
        <div class="form-grupo"><label>Nº da unidade</label><input type="text" name="numero_unidade" value="<?=$v('numero_unidade')?>"></div>
        <div class="form-grupo"><label>Face solar</label><select name="face_solar"><option value="">—</option>
          <?php foreach(['Norte','Sul','Leste','Oeste','Nordeste','Noroeste','Sudeste','Sudoeste'] as $fs): ?>
          <option value="<?=$fs?>" <?=($d['face_solar']??'')===$fs?'selected':''?>><?=$fs?></option>
          <?php endforeach;?></select></div>
        <div class="form-grupo"><label>Posição</label><select name="posicao_imovel"><option value="">—</option>
          <?php foreach(['Frente','Fundos','Lateral','Meio','Canto','Cobertura'] as $pos): ?>
          <option value="<?=$pos?>" <?=($d['posicao_imovel']??'')===$pos?'selected':''?>><?=$pos?></option>
          <?php endforeach;?></select></div>
        <div class="form-grupo" style="grid-column:span 4"><label>Vista</label><input type="text" name="vista" value="<?=$v('vista')?>"></div>
      </div>

      <!-- BLOCO 6 -->
      <div class="form-secao"><div class="form-secao-titulo">6. Dados Financeiros</div></div>
      <div style="font-size:.8rem;font-weight:700;color:var(--cor-secundario);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.6rem">Aquisição</div>
      <div class="form-grid form-grid-3" style="margin-bottom:1rem">
        <div class="form-grupo"><label>Valor de compra (R$)</label><input type="text" name="valor_compra" placeholder="0,00" value="<?=mf_val((float)($d['valor_compra']??0))?>"></div>
        <div class="form-grupo"><label>Valor de entrada (R$)</label><input type="text" name="valor_entrada" placeholder="0,00" value="<?=mf_val((float)($d['valor_entrada']??0))?>"></div>
        <div class="form-grupo"><label>Valor financiado (R$)</label><input type="text" name="valor_financiamento" placeholder="0,00" value="<?=mf_val((float)($d['valor_financiamento']??0))?>"></div>
        <div class="form-grupo"><label>Banco financiador</label><input type="text" name="banco_financiador" value="<?=$v('banco_financiador')?>"></div>
        <div class="form-grupo"><label>Prazo (meses)</label><input type="number" name="prazo_financiamento" min="0" max="600" value="<?=$v('prazo_financiamento')?>"></div>
        <div class="form-grupo"><label>Taxa de juros (% a.a.)</label><input type="text" name="taxa_juros_anual" placeholder="8,99" value="<?=$v('taxa_juros_anual')?>"></div>
      </div>
      <div style="font-size:.8rem;font-weight:700;color:var(--cor-secundario);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.6rem">Atualização patrimonial</div>
      <div class="form-grid form-grid-3" style="margin-bottom:1rem">
        <div class="form-grupo"><label>Valor de mercado (R$)</label><input type="text" name="valor_mercado" placeholder="0,00" value="<?=mf_val((float)($d['valor_mercado']??0))?>"></div>
        <div class="form-grupo"><label>Data da avaliação</label><input type="date" name="data_avaliacao_mercado" value="<?=$v('data_avaliacao_mercado')?>"></div>
        <div class="form-grupo"><label>Valor do m² (R$) <span style="font-weight:400;color:var(--cor-secundario);font-size:.8em">— automático</span></label><input type="text" name="valor_m2" id="valor_m2" placeholder="0,00" readonly title="Calculado automaticamente: valor de compra ÷ área total" style="background:#eef1f5;cursor:not-allowed" value="<?=mf_val((float)($d['valor_m2']??0))?>"></div>
        <div class="form-grupo"><label>Valor contábil (R$)</label><input type="text" name="valor_contabil" placeholder="0,00" value="<?=mf_val((float)($d['valor_contabil']??0))?>"></div>
        <div class="form-grupo" style="grid-column:span 2"><label>Empresa avaliadora</label><input type="text" name="empresa_avaliadora" value="<?=$v('empresa_avaliadora')?>"></div>
      </div>
      <div style="font-size:.8rem;font-weight:700;color:var(--cor-secundario);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.6rem">Custos mensais</div>
      <div class="form-grid form-grid-3">
        <?php foreach(['custo_condominio'=>'Condomínio','custo_iptu_mensal'=>'IPTU mensal','custo_seguro'=>'Seguro','custo_energia'=>'Energia','custo_agua'=>'Água','custo_internet'=>'Internet','custo_outros'=>'Outros'] as $c=>$l): ?>
        <div class="form-grupo"><label><?=$l?> (R$/mês)</label><input type="text" name="<?=$c?>" placeholder="0,00" value="<?=mf_val((float)($d[$c]??0))?>"></div>
        <?php endforeach;?>
      </div>

      <div style="display:flex;gap:.75rem;margin-top:2rem;padding-top:1.25rem;border-top:1px solid #dde2ea">
        <button type="submit" class="btn btn-primario">Salvar alterações</button>
        <a href="<?= base_url('imoveis/ficha?id='.$id) ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<script>
(function () {
  function atualizar_preview() {
    const log = document.getElementById('logradouro')?.value.trim() || '';
    const num = document.querySelector('[name=numero]')?.value.trim() || '';
    const bai = document.getElementById('bairro')?.value.trim() || '';
    const cid = document.getElementById('cidade')?.value.trim() || '';
    const est = document.getElementById('estado')?.value.trim() || '';
    if (!log || !cid) return;
    const end = [log, num, bai, cid, est, 'Brasil'].filter(Boolean).join(', ');
    const q   = encodeURIComponent(end);
    document.getElementById('btn-preview-maps').href = `https://www.google.com/maps/search/?api=1&query=${q}`;
    document.getElementById('btn-preview-sv').href   = `https://www.google.com/maps?q=${q}&layer=c`;
    document.getElementById('preview-endereco').textContent = end;
    document.getElementById('preview-localizacao').style.display = 'block';
  }
  ['logradouro','bairro','cidade','estado'].forEach(id => {
    const el = document.getElementById(id);
    if (el) { el.addEventListener('input', atualizar_preview); el.addEventListener('change', atualizar_preview); }
  });
  document.querySelector('[name=numero]')?.addEventListener('input', atualizar_preview);
  document.addEventListener('DOMContentLoaded', atualizar_preview);

  const inputPerc   = document.getElementById('percentual_participacao');
  const grupoOutros = document.getElementById('grupo-outros-proprietarios');
  const inputOutros = document.getElementById('outros_proprietarios');
  function toggle_coproprietarios() {
    if (!inputPerc || !grupoOutros) return;
    const val = parseFloat((inputPerc.value || '').replace(',', '.'));
    const mostra = !isNaN(val) && val > 0 && val < 100;
    grupoOutros.style.display = mostra ? '' : 'none';
    if (inputOutros) { inputOutros.required = mostra; if (!mostra) inputOutros.value = ''; }
  }
  inputPerc?.addEventListener('input', toggle_coproprietarios);
  document.addEventListener('DOMContentLoaded', toggle_coproprietarios);

  function parse_br(str) {
    if (str === undefined || str === null || str === '') return NaN;
    return parseFloat(String(str).replace(/\./g, '').replace(',', '.'));
  }
  function format_br(num) { return num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
  function calcular_valor_m2() {
    const out = document.getElementById('valor_m2');
    if (!out) return;
    const valor = parse_br(document.querySelector('[name=valor_compra]')?.value);
    const area  = parse_br(document.querySelector('[name=area_total]')?.value);
    out.value = (!isNaN(valor) && !isNaN(area) && area > 0) ? format_br(valor / area) : '';
  }
  ['valor_compra', 'area_total'].forEach(n => {
    document.querySelector('[name=' + n + ']')?.addEventListener('input', calcular_valor_m2);
  });
  document.addEventListener('DOMContentLoaded', calcular_valor_m2);
})();
</script>
<?php require APP_ROOT . '/includes/footer.php'; ?>
