<?php
/**
 * @var array  $cli   Cliente selecionado
 * @var string $erro
 * @var array  $d      Repopulação após erro ($_POST)
 */
$page_title = 'Cadastrar Imóvel';
require APP_ROOT . '/includes/header.php';
$estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
?>
<div class="container" style="max-width:960px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <div>
      <h2 class="card-titulo">Cadastrar Imóvel</h2>
      <div style="font-size:.85rem;color:var(--cor-secundario)">Cliente: <strong><?= h($cli['nome']) ?></strong></div>
    </div>
    <a href="<?= base_url('imoveis') ?>" class="btn btn-secundario">← Voltar</a>
  </div>

  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>

  <div class="card">
    <form method="post" enctype="multipart/form-data">

      <!-- BLOCO 1 — IDENTIFICAÇÃO -->
      <div class="form-secao"><div class="form-secao-titulo">1. Identificação</div></div>
      <div class="form-grid form-grid-2">
        <div class="form-grupo" style="grid-column:1/-1">
          <label>Nome de referência *</label>
          <input type="text" name="nome_referencia" required placeholder="Ex.: 4D Complex – unid. 321"
                 value="<?= h($d['nome_referencia'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label>Tipo do imóvel *</label>
          <select name="tipo">
            <?php foreach (['apartamento','casa','sala_comercial','terreno','galpao','loja','hotel','outro'] as $t): ?>
            <option value="<?= $t ?>" <?= (($d['tipo']??'apartamento')===$t)?'selected':'' ?>><?= tipo_imovel_label($t) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo">
          <label>Finalidade *</label>
          <select name="finalidade">
            <?php foreach (['residencial','comercial','mista','locacao','investimento'] as $f): ?>
            <option value="<?= $f ?>" <?= (($d['finalidade']??'residencial')===$f)?'selected':'' ?>><?= finalidade_label($f) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo">
          <label>Situação *</label>
          <select name="situacao">
            <option value="pronto"        <?= (($d['situacao']??'pronto')==='pronto')?'selected':'' ?>>Pronto</option>
            <option value="em_construcao" <?= (($d['situacao']??'')==='em_construcao')?'selected':'' ?>>Em Construção</option>
            <option value="na_planta"     <?= (($d['situacao']??'')==='na_planta')?'selected':'' ?>>Na Planta</option>
          </select>
        </div>
        <div class="form-grupo">
          <label>Nº Inscrição Municipal / IPTU</label>
          <input type="text" name="inscricao_municipal" value="<?= h($d['inscricao_municipal'] ?? '') ?>">
        </div>
      </div>

      <!-- BLOCO 2 — LOCALIZAÇÃO -->
      <div class="form-secao"><div class="form-secao-titulo">2. Localização</div></div>
      <div class="form-grid form-grid-4">
        <div class="form-grupo">
          <label>CEP</label>
          <input type="text" name="cep" id="cep" maxlength="9" placeholder="00000-000"
                 oninput="mascara_cep(this)" onblur="busca_cep(this)" value="<?= h($d['cep'] ?? '') ?>">
        </div>
        <div class="form-grupo" style="grid-column:span 3">
          <label>Logradouro</label>
          <input type="text" name="logradouro" id="logradouro" value="<?= h($d['logradouro'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label>Número</label>
          <input type="text" name="numero" value="<?= h($d['numero'] ?? '') ?>">
        </div>
        <div class="form-grupo" style="grid-column:span 3">
          <label>Complemento</label>
          <input type="text" name="complemento" value="<?= h($d['complemento'] ?? '') ?>">
        </div>
        <div class="form-grupo" style="grid-column:span 2">
          <label>Bairro</label>
          <input type="text" name="bairro" id="bairro" value="<?= h($d['bairro'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label>Cidade</label>
          <input type="text" name="cidade" id="cidade" value="<?= h($d['cidade'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label>Estado</label>
          <select name="estado" id="estado">
            <option value="">—</option>
            <?php foreach ($estados as $uf): ?>
            <option value="<?= $uf ?>" <?= (($d['estado']??'')===$uf)?'selected':'' ?>><?= $uf ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div id="preview-localizacao" style="display:none;margin-bottom:1rem;padding:.85rem 1rem;background:#faf7ef;border-radius:8px;border-left:4px solid var(--cor-acento)">
        <div style="font-size:.78rem;color:var(--cor-secundario);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.5rem">Links gerados automaticamente</div>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap">
          <a id="btn-preview-maps" href="#" target="_blank" class="btn btn-secundario btn-sm">📍 Google Maps</a>
          <a id="btn-preview-sv"   href="#" target="_blank" class="btn btn-secundario btn-sm">🏙️ Street View</a>
        </div>
        <div id="preview-endereco" style="font-size:.82rem;color:var(--cor-secundario);margin-top:.4rem"></div>
      </div>

      <!-- BLOCO 3 — DADOS DA MATRÍCULA -->
      <div class="form-secao"><div class="form-secao-titulo">3. Dados da Matrícula</div></div>
      <div class="form-grid form-grid-3">
        <div class="form-grupo">
          <label>Número da matrícula</label>
          <input type="text" name="numero_matricula" value="<?= h($d['numero_matricula'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label>Data da matrícula</label>
          <input type="date" name="data_matricula" value="<?= h($d['data_matricula'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label>Comarca</label>
          <input type="text" name="comarca" value="<?= h($d['comarca'] ?? '') ?>">
        </div>
        <div class="form-grupo" style="grid-column:span 2">
          <label>Cartório de Registro de Imóveis</label>
          <input type="text" name="cartorio" placeholder="Ex.: 3º Cartório de Registro de Imóveis de SP" value="<?= h($d['cartorio'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label>Site do cartório</label>
          <input type="url" name="site_cartorio" placeholder="https://…" value="<?= h($d['site_cartorio'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label>Livro</label>
          <input type="text" name="livro" value="<?= h($d['livro'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label>Folha</label>
          <input type="text" name="folha" value="<?= h($d['folha'] ?? '') ?>">
        </div>
      </div>

      <!-- BLOCO 4 — TITULARIDADE -->
      <div class="form-secao"><div class="form-secao-titulo">4. Titularidade</div></div>
      <div class="form-grid form-grid-3">
        <div class="form-grupo">
          <label>Data de aquisição</label>
          <input type="date" name="data_aquisicao" value="<?= h($d['data_aquisicao'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label>Forma de aquisição</label>
          <select name="forma_aquisicao">
            <option value="">— selecione —</option>
            <?php foreach (['compra','heranca','doacao','permuta','integralizacao','outro'] as $fa): ?>
            <option value="<?= $fa ?>" <?= (($d['forma_aquisicao']??'')===$fa)?'selected':'' ?>><?= forma_aquisicao_label($fa) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo">
          <label>Percentual de participação (%)</label>
          <input type="number" name="percentual_participacao" id="percentual_participacao" min="0" max="100" step="0.01"
                 placeholder="100,00" value="<?= h($d['percentual_participacao'] ?? '') ?>">
        </div>
        <?php $tem_coprop = isset($d['percentual_participacao']) && $d['percentual_participacao'] !== '' && (float)str_replace(',', '.', $d['percentual_participacao']) < 100; ?>
        <div class="form-grupo" id="grupo-outros-proprietarios" style="grid-column:span 2;<?= $tem_coprop ? '' : 'display:none' ?>">
          <label>Outro(s) proprietário(s) *</label>
          <input type="text" name="outros_proprietarios" id="outros_proprietarios"
                 placeholder="Nome do(s) coproprietário(s) e respectiva participação"
                 value="<?= h($d['outros_proprietarios'] ?? '') ?>">
        </div>
      </div>

      <!-- BLOCO 5 — CARACTERÍSTICAS FÍSICAS -->
      <div class="form-secao"><div class="form-secao-titulo">5. Características Físicas</div></div>
      <div style="font-size:.8rem;font-weight:700;color:var(--cor-secundario);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.6rem">Áreas (m²)</div>
      <div class="form-grid form-grid-4" style="margin-bottom:1rem">
        <div class="form-grupo"><label>Área privativa</label><input type="text" name="area_privativa" placeholder="0,00" value="<?= h($d['area_privativa'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Área total</label><input type="text" name="area_total" placeholder="0,00" value="<?= h($d['area_total'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Área construída</label><input type="text" name="area_construida" placeholder="0,00" value="<?= h($d['area_construida'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Área comum</label><input type="text" name="area_comum" placeholder="0,00" value="<?= h($d['area_comum'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Área do terreno</label><input type="text" name="area_terreno" placeholder="0,00" value="<?= h($d['area_terreno'] ?? '') ?>"></div>
      </div>
      <div style="font-size:.8rem;font-weight:700;color:var(--cor-secundario);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.6rem">Ambientes</div>
      <div class="form-grid form-grid-4" style="margin-bottom:1rem">
        <?php foreach (['quartos'=>'Quartos','suites'=>'Suítes','banheiros'=>'Banheiros','lavabos'=>'Lavabos','vagas_garagem'=>'Vagas de garagem'] as $campo => $label): ?>
        <div class="form-grupo">
          <label><?= $label ?></label>
          <input type="number" name="<?= $campo ?>" min="0" max="99" value="<?= h($d[$campo] ?? '') ?>">
        </div>
        <?php endforeach; ?>
      </div>
      <div style="font-size:.8rem;font-weight:700;color:var(--cor-secundario);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.6rem">Estrutura</div>
      <div class="form-grid form-grid-4">
        <div class="form-grupo">
          <label>Andar</label>
          <input type="number" name="andar" min="0" max="200" value="<?= h($d['andar'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label>Nº da unidade</label>
          <input type="text" name="numero_unidade" placeholder="Ex.: 321" value="<?= h($d['numero_unidade'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label>Face solar</label>
          <select name="face_solar">
            <option value="">—</option>
            <?php foreach (['Norte','Sul','Leste','Oeste','Nordeste','Noroeste','Sudeste','Sudoeste'] as $fs): ?>
            <option value="<?= $fs ?>" <?= (($d['face_solar']??'')===$fs)?'selected':'' ?>><?= $fs ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo">
          <label>Posição do imóvel</label>
          <select name="posicao_imovel">
            <option value="">—</option>
            <?php foreach (['Frente','Fundos','Lateral','Meio','Canto','Cobertura'] as $pos): ?>
            <option value="<?= $pos ?>" <?= (($d['posicao_imovel']??'')===$pos)?'selected':'' ?>><?= $pos ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo" style="grid-column:span 4">
          <label>Vista</label>
          <input type="text" name="vista" placeholder="Ex.: Vista para o mar, vista para o parque…" value="<?= h($d['vista'] ?? '') ?>">
        </div>
      </div>

      <!-- BLOCO 6 — DADOS FINANCEIROS -->
      <div class="form-secao"><div class="form-secao-titulo">6. Dados Financeiros</div></div>
      <div style="font-size:.8rem;font-weight:700;color:var(--cor-secundario);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.6rem">Aquisição</div>
      <div class="form-grid form-grid-3" style="margin-bottom:1rem">
        <div class="form-grupo"><label>Valor de compra (R$)</label><input type="text" name="valor_compra" placeholder="0,00" value="<?= h($d['valor_compra'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Valor de entrada (R$)</label><input type="text" name="valor_entrada" placeholder="0,00" value="<?= h($d['valor_entrada'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Valor financiado (R$)</label><input type="text" name="valor_financiamento" placeholder="0,00" value="<?= h($d['valor_financiamento'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Banco financiador</label><input type="text" name="banco_financiador" placeholder="Ex.: Caixa Econômica Federal" value="<?= h($d['banco_financiador'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Prazo (meses)</label><input type="number" name="prazo_financiamento" min="0" max="600" placeholder="360" value="<?= h($d['prazo_financiamento'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Taxa de juros (% ao ano)</label><input type="text" name="taxa_juros_anual" placeholder="8,99" value="<?= h($d['taxa_juros_anual'] ?? '') ?>"></div>
      </div>
      <div style="font-size:.8rem;font-weight:700;color:var(--cor-secundario);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.6rem">Atualização patrimonial</div>
      <div class="form-grid form-grid-3" style="margin-bottom:1rem">
        <div class="form-grupo"><label>Valor de mercado (R$)</label><input type="text" name="valor_mercado" placeholder="0,00" value="<?= h($d['valor_mercado'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Data da avaliação</label><input type="date" name="data_avaliacao_mercado" value="<?= h($d['data_avaliacao_mercado'] ?? date('Y-m-d')) ?>"></div>
        <div class="form-grupo"><label>Valor do m² (R$) <span style="font-weight:400;color:var(--cor-secundario);font-size:.8em">— automático</span></label><input type="text" name="valor_m2" id="valor_m2" placeholder="0,00" readonly title="Calculado automaticamente: valor de compra ÷ área total" style="background:#eef1f5;cursor:not-allowed" value="<?= h($d['valor_m2'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Valor contábil (R$)</label><input type="text" name="valor_contabil" placeholder="0,00" value="<?= h($d['valor_contabil'] ?? '') ?>"></div>
        <div class="form-grupo" style="grid-column:span 2"><label>Empresa avaliadora</label><input type="text" name="empresa_avaliadora" placeholder="Ex.: CBRE, JLL, Cushman & Wakefield…" value="<?= h($d['empresa_avaliadora'] ?? '') ?>"></div>
      </div>

      <!-- BLOCO 7 — DOCUMENTAÇÃO -->
      <div class="form-secao"><div class="form-secao-titulo">7. Documentação</div></div>
      <div style="display:flex;flex-direction:column;gap:.6rem" id="upload-lista">
        <?php
        $upload_items = [
          ['field'=>'escritura',            'label'=>'Escritura',              'icon'=>'📜', 'accept'=>'.pdf,.jpg,.jpeg,.png', 'multiple'=>false],
          ['field'=>'matricula',            'label'=>'Matrícula do imóvel',    'icon'=>'📋', 'accept'=>'.pdf,.jpg,.jpeg,.png', 'multiple'=>false],
          ['field'=>'certidao_negativa',    'label'=>'Certidão negativa',      'icon'=>'✅', 'accept'=>'.pdf,.jpg,.jpeg,.png', 'multiple'=>false],
          ['field'=>'doc_iptu',             'label'=>'IPTU / Carnê',           'icon'=>'🧾', 'accept'=>'.pdf,.jpg,.jpeg,.png', 'multiple'=>false],
          ['field'=>'contrato_compra',      'label'=>'Contrato de compra',     'icon'=>'🤝', 'accept'=>'.pdf,.jpg,.jpeg,.png', 'multiple'=>false],
          ['field'=>'laudo',                'label'=>'Laudo de avaliação',     'icon'=>'📊', 'accept'=>'.pdf,.jpg,.jpeg,.png', 'multiple'=>false],
          ['field'=>'habite_se',            'label'=>'Habite-se',              'icon'=>'🏛️', 'accept'=>'.pdf,.jpg,.jpeg,.png', 'multiple'=>false],
          ['field'=>'convencao_condominio', 'label'=>'Convenção de condomínio','icon'=>'📑', 'accept'=>'.pdf,.jpg,.jpeg,.png', 'multiple'=>false],
          ['field'=>'planta',               'label'=>'Planta aprovada',        'icon'=>'📐', 'accept'=>'.pdf,.jpg,.jpeg,.png', 'multiple'=>false],
          ['field'=>'alvara',               'label'=>'Alvará',                 'icon'=>'🏢', 'accept'=>'.pdf,.jpg,.jpeg,.png', 'multiple'=>false],
          ['field'=>'fotos',                'label'=>'Fotos do imóvel',        'icon'=>'📷', 'accept'=>'.jpg,.jpeg,.png,.webp','multiple'=>true ],
        ];
        foreach ($upload_items as $item):
        ?>
        <div class="upload-row" id="row-<?= $item['field'] ?>"
             style="display:flex;align-items:center;gap:1rem;padding:.75rem 1rem;border:1px solid var(--cor-borda);border-radius:8px;transition:all .3s;background:#fff">
          <div class="upload-check" style="width:28px;height:28px;border-radius:50%;border:2px solid #cbd2db;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .35s">
            <svg class="check-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="opacity:0;transition:opacity .2s">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
          </div>
          <span style="font-size:1.15rem"><?= $item['icon'] ?></span>
          <div style="flex:1;min-width:0">
            <div style="font-size:.9rem;font-weight:600;color:#1e2530"><?= $item['label'] ?></div>
            <div class="upload-filename" style="font-size:.78rem;color:var(--cor-secundario);margin-top:.1rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
              Nenhum arquivo selecionado
            </div>
          </div>
          <label class="btn btn-secundario btn-sm upload-btn" style="cursor:pointer;white-space:nowrap">
            Selecionar
            <input type="file" name="<?= $item['field'] ?><?= $item['multiple'] ? '[]' : '' ?>"
                   accept="<?= $item['accept'] ?>"
                   <?= $item['multiple'] ? 'multiple' : '' ?>
                   class="upload-input" data-field="<?= $item['field'] ?>"
                   style="position:absolute;width:0;height:0;opacity:0">
          </label>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="margin-top:.75rem;font-size:.8rem;color:var(--cor-secundario)">
        Formatos aceitos: PDF, JPG, PNG, WEBP · Tamanho máximo por arquivo: 30 MB
      </div>

      <div style="display:flex;gap:.75rem;margin-top:2rem;padding-top:1.25rem;border-top:1px solid #dde2ea">
        <button type="submit" class="btn btn-primario">Salvar imóvel</button>
        <a href="<?= base_url('imoveis') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<!-- Modal de confirmação de campos em aberto -->
<div class="modal-overlay" id="modal-confirma" style="display:none">
  <div class="modal-box" style="max-width:440px">
    <div class="modal-head">
      <h3 style="font-size:1.15rem;color:var(--cor-primaria)">Campos em aberto</h3>
    </div>
    <div class="modal-body">
      <div class="alerta-pendencias" style="margin-bottom:0">
        ⚠️ Há <strong id="conf-num">0</strong> campo(s) sem preencher neste cadastro. Deseja salvar assim mesmo?
      </div>
    </div>
    <div class="modal-rodape">
      <button type="button" class="btn btn-secundario" id="conf-voltar">Continuar preenchendo</button>
      <button type="button" class="btn btn-primario" id="conf-salvar" style="margin-left:auto">Salvar assim mesmo</button>
    </div>
  </div>
</div>
<style>
.upload-row.tem-arquivo { border-color: #1a7a45; background: #f0faf4 !important; }
.upload-row.tem-arquivo .upload-check { background: #1a7a45; border-color: #1a7a45; }
.upload-row.tem-arquivo .check-icon { opacity: 1 !important; }
.upload-row.tem-arquivo .upload-btn { background: #e6f4ec; color: #1a7a45; }
@keyframes pop-in { 0% { transform: scale(0.4); opacity: 0; } 70% { transform: scale(1.2); } 100% { transform: scale(1); opacity: 1; } }
.upload-row.tem-arquivo .check-icon { animation: pop-in .35s ease forwards; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
  function atualizar_maps() {
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
    document.getElementById(id)?.addEventListener('input',  atualizar_maps);
    document.getElementById(id)?.addEventListener('change', atualizar_maps);
  });
  document.querySelector('[name=numero]')?.addEventListener('input', atualizar_maps);
  atualizar_maps();

  document.querySelectorAll('.upload-input').forEach(input => {
    input.addEventListener('change', function () {
      const field    = this.dataset.field;
      const row      = document.getElementById('row-' + field);
      const fnLabel  = row?.querySelector('.upload-filename');
      if (!row) return;
      if (this.files && this.files.length > 0) {
        const nomes = Array.from(this.files).map(f => f.name).join(', ');
        const total = Array.from(this.files).reduce((s, f) => s + f.size, 0);
        const mb    = (total / 1024 / 1024).toFixed(1);
        if (fnLabel) fnLabel.textContent = nomes + (this.files.length > 1 ? ` (${this.files.length} arquivos, ${mb} MB)` : ` (${mb} MB)`);
        row.classList.add('tem-arquivo');
        row.querySelector('.upload-btn').textContent = 'Trocar';
      } else {
        if (fnLabel) fnLabel.textContent = 'Nenhum arquivo selecionado';
        row.classList.remove('tem-arquivo');
        row.querySelector('.upload-btn').textContent = 'Selecionar';
      }
    });
  });

  const inputPerc   = document.getElementById('percentual_participacao');
  const grupoOutros = document.getElementById('grupo-outros-proprietarios');
  const inputOutros = document.getElementById('outros_proprietarios');
  function toggle_coproprietarios() {
    if (!inputPerc || !grupoOutros) return;
    const v = parseFloat((inputPerc.value || '').replace(',', '.'));
    const mostra = !isNaN(v) && v > 0 && v < 100;
    grupoOutros.style.display = mostra ? '' : 'none';
    if (inputOutros) { inputOutros.required = mostra; if (!mostra) inputOutros.value = ''; }
  }
  inputPerc?.addEventListener('input', toggle_coproprietarios);
  toggle_coproprietarios();

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
  calcular_valor_m2();

  const form        = document.querySelector('form[method=post]');
  const modalConf   = document.getElementById('modal-confirma');
  const ignorar     = ['file', 'hidden', 'submit', 'button', 'checkbox', 'radio'];
  function contar_vazios() {
    let vazios = 0;
    form.querySelectorAll('input, select, textarea').forEach(el => {
      const tipo = (el.type || '').toLowerCase();
      if (ignorar.includes(tipo)) return;
      if (el.offsetParent === null) return;
      if (el.name === 'nome_referencia') return;
      if (String(el.value).trim() === '') vazios++;
    });
    return vazios;
  }
  if (form && modalConf) {
    form.addEventListener('submit', function (e) {
      if (form.dataset.confirmado === '1') return;
      const n = contar_vazios();
      if (n > 0) { e.preventDefault(); document.getElementById('conf-num').textContent = n; modalConf.style.display = 'flex'; }
    });
    document.getElementById('conf-salvar')?.addEventListener('click', function () {
      form.dataset.confirmado = '1'; modalConf.style.display = 'none'; form.submit();
    });
    document.getElementById('conf-voltar')?.addEventListener('click', function () { modalConf.style.display = 'none'; });
    modalConf.addEventListener('click', function (e) { if (e.target === modalConf) modalConf.style.display = 'none'; });
  }
});
</script>
<?php require APP_ROOT . '/includes/footer.php'; ?>
