<?php
/**
 * @var array  $cli
 * @var string $erro
 * @var array  $d
 */
$page_title = 'Cadastrar Veículo';
require APP_ROOT . '/includes/header.php';
?>
<div class="container" style="max-width:960px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <div>
      <h2 class="card-titulo">Cadastrar Veículo</h2>
      <div style="font-size:.85rem;color:var(--cor-secundario)">Cliente: <strong><?= h($cli['nome']) ?></strong></div>
    </div>
    <a href="<?= base_url('veiculos') ?>" class="btn btn-secundario">← Voltar</a>
  </div>

  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>

  <div class="card">
    <form method="post" enctype="multipart/form-data">

      <!-- BLOCO 1 — IDENTIFICAÇÃO -->
      <div class="form-secao"><div class="form-secao-titulo">1. Identificação</div></div>
      <div class="form-grid form-grid-3">
        <div class="form-grupo"><label>Marca</label><input type="text" name="marca" placeholder="Ex.: Toyota" value="<?= h($d['marca'] ?? '') ?>"></div>
        <div class="form-grupo" style="grid-column:span 2"><label>Modelo *</label><input type="text" name="modelo" required placeholder="Ex.: Corolla Altis Hybrid" value="<?= h($d['modelo'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Ano de fabricação</label><input type="number" name="ano_fabricacao" min="1900" max="2100" placeholder="2024" value="<?= h($d['ano_fabricacao'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Ano do modelo</label><input type="number" name="ano_modelo" min="1900" max="2100" placeholder="2025" value="<?= h($d['ano_modelo'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Cor</label><input type="text" name="cor" placeholder="Ex.: Prata" value="<?= h($d['cor'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Combustível</label>
          <select name="combustivel">
            <option value="">—</option>
            <?php foreach (['gasolina','etanol','flex','diesel','gnv','eletrico','hibrido'] as $c): ?>
            <option value="<?= $c ?>" <?= (($d['combustivel']??'')===$c)?'selected':'' ?>><?= combustivel_label($c) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo"><label>Placa</label><input type="text" name="placa" placeholder="ABC1D23" maxlength="10" value="<?= h($d['placa'] ?? '') ?>" style="text-transform:uppercase"></div>
        <div class="form-grupo"><label>Renavam</label><input type="text" name="renavam" value="<?= h($d['renavam'] ?? '') ?>"></div>
        <div class="form-grupo" style="grid-column:span 2"><label>Chassi</label><input type="text" name="chassi" maxlength="30" value="<?= h($d['chassi'] ?? '') ?>" style="text-transform:uppercase"></div>
      </div>

      <!-- BLOCO 2 — DOCUMENTAÇÃO -->
      <div class="form-secao"><div class="form-secao-titulo">2. Documentação</div></div>
      <div class="form-grid form-grid-2">
        <div class="form-grupo"><label>Vencimento do licenciamento</label><input type="date" name="vencimento_licenciamento" value="<?= h($d['vencimento_licenciamento'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Multas</label><input type="text" name="multas" placeholder="Ex.: 1 multa pendente / sem multas" value="<?= h($d['multas'] ?? '') ?>"></div>
      </div>

      <!-- BLOCO 3 — SEGURO -->
      <div class="form-secao"><div class="form-secao-titulo">3. Seguro</div></div>
      <div class="form-grid form-grid-4">
        <div class="form-grupo" style="grid-column:span 2"><label>Seguradora</label><input type="text" name="seguradora" placeholder="Ex.: Porto Seguro" value="<?= h($d['seguradora'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Apólice</label><input type="text" name="apolice" value="<?= h($d['apolice'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Franquia (R$)</label><input type="text" name="franquia" placeholder="0,00" value="<?= h($d['franquia'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Vencimento do seguro</label><input type="date" name="vencimento_seguro" value="<?= h($d['vencimento_seguro'] ?? '') ?>"></div>
      </div>

      <!-- BLOCO 4 — FINANCEIRO -->
      <div class="form-secao"><div class="form-secao-titulo">4. Financeiro</div></div>
      <div class="form-grid form-grid-4">
        <div class="form-grupo"><label>Data de aquisição</label><input type="date" name="data_aquisicao" value="<?= h($d['data_aquisicao'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Valor de aquisição (R$)</label><input type="text" name="valor_aquisicao" placeholder="0,00" value="<?= h($d['valor_aquisicao'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Valor FIPE (R$)</label><input type="text" name="valor_fipe" placeholder="0,00" value="<?= h($d['valor_fipe'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Valor de mercado (R$)</label><input type="text" name="valor_mercado" placeholder="0,00" value="<?= h($d['valor_mercado'] ?? '') ?>"></div>
      </div>

      <!-- BLOCO 5 — CONTROLE -->
      <div class="form-secao"><div class="form-secao-titulo">5. Controle</div></div>
      <div class="form-grid form-grid-3">
        <div class="form-grupo"><label>KM atual</label><input type="number" name="km_atual" min="0" placeholder="0" value="<?= h($d['km_atual'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Consumo médio (km/l)</label><input type="text" name="consumo_medio" placeholder="0,00" value="<?= h($d['consumo_medio'] ?? '') ?>"></div>
        <div class="form-grupo" style="grid-column:1/-1"><label>Observações</label><input type="text" name="observacoes" value="<?= h($d['observacoes'] ?? '') ?>"></div>
      </div>

      <!-- BLOCO 6 — DOCUMENTOS -->
      <div class="form-secao"><div class="form-secao-titulo">6. Documentos</div></div>
      <div style="display:flex;flex-direction:column;gap:.6rem" id="upload-lista">
        <?php
        $upload_items = [
          ['field'=>'crlv',        'label'=>'CRLV',              'icon'=>'📄', 'accept'=>'.pdf,.jpg,.jpeg,.png', 'multiple'=>false],
          ['field'=>'apolice_doc', 'label'=>'Apólice de seguro', 'icon'=>'🛡️', 'accept'=>'.pdf,.jpg,.jpeg,.png', 'multiple'=>false],
          ['field'=>'nf_servico',  'label'=>'NF de serviços',    'icon'=>'🧾', 'accept'=>'.pdf,.jpg,.jpeg,.png', 'multiple'=>true ],
          ['field'=>'nf_peca',     'label'=>'NF de peças',       'icon'=>'🔧', 'accept'=>'.pdf,.jpg,.jpeg,.png', 'multiple'=>true ],
          ['field'=>'fotos',       'label'=>'Fotos do veículo',  'icon'=>'📷', 'accept'=>'.jpg,.jpeg,.png,.webp','multiple'=>true ],
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
                   accept="<?= $item['accept'] ?>" <?= $item['multiple'] ? 'multiple' : '' ?>
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
        <button type="submit" class="btn btn-primario">Salvar veículo</button>
        <a href="<?= base_url('veiculos') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<!-- Modal de confirmação de campos em aberto -->
<div class="modal-overlay" id="modal-confirma" style="display:none">
  <div class="modal-box" style="max-width:440px">
    <div class="modal-head"><h3 style="font-size:1.15rem;color:var(--cor-primaria)">Campos em aberto</h3></div>
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
.upload-row.tem-arquivo { border-color:#1a7a45; background:#f0faf4 !important; }
.upload-row.tem-arquivo .upload-check { background:#1a7a45; border-color:#1a7a45; }
.upload-row.tem-arquivo .check-icon { opacity:1 !important; animation:pop-in .35s ease forwards; }
.upload-row.tem-arquivo .upload-btn { background:#e6f4ec; color:#1a7a45; }
@keyframes pop-in { 0%{transform:scale(.4);opacity:0} 70%{transform:scale(1.2)} 100%{transform:scale(1);opacity:1} }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.upload-input').forEach(input => {
    input.addEventListener('change', function () {
      const row = document.getElementById('row-' + this.dataset.field);
      const fnLabel = row?.querySelector('.upload-filename');
      if (!row) return;
      if (this.files && this.files.length > 0) {
        const nomes = Array.from(this.files).map(f => f.name).join(', ');
        const mb = (Array.from(this.files).reduce((s,f)=>s+f.size,0)/1024/1024).toFixed(1);
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

  const form      = document.querySelector('form[method=post]');
  const modalConf = document.getElementById('modal-confirma');
  const ignorar   = ['file','hidden','submit','button','checkbox','radio'];
  function contar_vazios() {
    let vazios = 0;
    form.querySelectorAll('input, select, textarea').forEach(el => {
      const tipo = (el.type || '').toLowerCase();
      if (ignorar.includes(tipo)) return;
      if (el.offsetParent === null) return;
      if (el.name === 'modelo') return;
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
