<?php
/**
 * Etapa 2 do cadastro: formulário do bem (tipo já escolhido).
 * @var array  $cli
 * @var string $erro
 * @var string $tipo
 * @var array  $d
 */
$tipos_label = ['embarcacao'=>'Embarcação','joia'=>'Joia','obra_de_arte'=>'Obra de Arte','outro'=>'Outro'];
$tipo_label  = $tipos_label[$tipo] ?? $tipo;
$page_title  = 'Cadastrar ' . $tipo_label;
require APP_ROOT . '/includes/header.php';
?>
<div class="container" style="max-width:960px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <div>
      <h2 class="card-titulo">Cadastrar <?= h($tipo_label) ?></h2>
      <div style="font-size:.85rem;color:var(--cor-secundario)">Cliente: <strong><?= h($cli['nome']) ?></strong></div>
    </div>
    <div style="display:flex;gap:.5rem">
      <a href="<?= base_url('outros/novo') ?>" class="btn btn-secundario">↩ Trocar tipo</a>
      <a href="<?= base_url('outros') ?>" class="btn btn-secundario">← Voltar</a>
    </div>
  </div>

  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>

  <div class="card">
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="tipo" value="<?= h($tipo) ?>">

      <!-- BLOCO 1 — IDENTIFICAÇÃO GERAL -->
      <div class="form-secao"><div class="form-secao-titulo">1. Identificação</div></div>
      <div class="form-grid form-grid-3">
        <div class="form-grupo" style="grid-column:1/-1">
          <label>Nome / Descrição *</label>
          <input type="text" name="nome" required placeholder="Ex.: Lancha Focker 265 · Anel Solitário · Monet — Jardim" value="<?= h($d['nome'] ?? '') ?>">
        </div>
        <div class="form-grupo"><label>Marca / Fabricante</label><input type="text" name="marca" placeholder="Ex.: Focker, Vivara, Christie's" value="<?= h($d['marca'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Modelo / Referência</label><input type="text" name="modelo" placeholder="Modelo ou referência" value="<?= h($d['modelo'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Ano</label><input type="number" name="ano" min="1800" max="2100" placeholder="2024" value="<?= h($d['ano'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Cor / Acabamento</label><input type="text" name="cor" placeholder="Ex.: Branca, Ouro Rosé" value="<?= h($d['cor'] ?? '') ?>"></div>
        <div class="form-grupo" style="grid-column:span 2"><label>Descrição detalhada</label><input type="text" name="descricao" placeholder="Características adicionais" value="<?= h($d['descricao'] ?? '') ?>"></div>
      </div>

      <?php require APP_PATH . '/Views/outros/_campos_tipo.php'; ?>

      <!-- BLOCO SEGURO -->
      <div class="form-secao"><div class="form-secao-titulo">3. Seguro</div></div>
      <div class="form-grid form-grid-4">
        <div class="form-grupo" style="grid-column:span 2"><label>Seguradora</label><input type="text" name="seguradora" placeholder="Ex.: Porto Seguro" value="<?= h($d['seguradora'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Apólice</label><input type="text" name="apolice" value="<?= h($d['apolice'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Franquia (R$)</label><input type="text" name="franquia" placeholder="0,00" value="<?= h($d['franquia'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Vencimento do seguro</label><input type="date" name="vencimento_seguro" value="<?= h($d['vencimento_seguro'] ?? '') ?>"></div>
      </div>

      <!-- BLOCO FINANCEIRO -->
      <div class="form-secao"><div class="form-secao-titulo">4. Financeiro</div></div>
      <div class="form-grid form-grid-3">
        <div class="form-grupo"><label>Data de aquisição</label><input type="date" name="data_aquisicao" value="<?= h($d['data_aquisicao'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Valor de aquisição (R$)</label><input type="text" name="valor_aquisicao" placeholder="0,00" value="<?= h($d['valor_aquisicao'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Valor de mercado (R$)</label><input type="text" name="valor_mercado" placeholder="0,00" value="<?= h($d['valor_mercado'] ?? '') ?>"></div>
      </div>

      <!-- BLOCO DOCUMENTOS -->
      <div class="form-secao"><div class="form-secao-titulo">5. Documentos e Fotos</div></div>
      <div style="display:flex;flex-direction:column;gap:.6rem">
        <?php
        $upload_items = [
          ['field'=>'foto_principal','label'=>'Foto principal',    'icon'=>'📷','accept'=>'.jpg,.jpeg,.png,.webp','multiple'=>false],
          ['field'=>'doc_laudo',     'label'=>'Laudo / Avaliação', 'icon'=>'📄','accept'=>'.pdf,.jpg,.jpeg,.png', 'multiple'=>false],
          ['field'=>'doc_apolice',   'label'=>'Apólice de seguro', 'icon'=>'🛡️','accept'=>'.pdf,.jpg,.jpeg,.png', 'multiple'=>false],
          ['field'=>'doc_outros',    'label'=>'Outros documentos', 'icon'=>'📎','accept'=>'.pdf,.jpg,.jpeg,.png', 'multiple'=>true ],
        ];
        foreach ($upload_items as $item): ?>
        <div class="upload-row" id="row-<?= $item['field'] ?>"
             style="display:flex;align-items:center;gap:1rem;padding:.75rem 1rem;border:1px solid var(--cor-borda);border-radius:8px;background:#fff">
          <div class="upload-check" style="width:28px;height:28px;border-radius:50%;border:2px solid #cbd2db;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .35s">
            <svg class="check-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="opacity:0;transition:opacity .2s"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <span style="font-size:1.15rem"><?= $item['icon'] ?></span>
          <div style="flex:1;min-width:0">
            <div style="font-size:.9rem;font-weight:600;color:#1e2530"><?= $item['label'] ?></div>
            <div class="upload-filename" style="font-size:.78rem;color:var(--cor-secundario);margin-top:.1rem">Nenhum arquivo selecionado</div>
          </div>
          <label class="btn btn-secundario btn-sm upload-btn" style="cursor:pointer">
            Selecionar
            <input type="file" name="<?= $item['field'] ?><?= $item['multiple'] ? '[]' : '' ?>"
                   accept="<?= $item['accept'] ?>" <?= $item['multiple'] ? 'multiple' : '' ?>
                   class="upload-input" data-field="<?= $item['field'] ?>"
                   style="position:absolute;width:0;height:0;opacity:0">
          </label>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="margin-top:.75rem;font-size:.8rem;color:var(--cor-secundario)">Formatos aceitos: PDF, JPG, PNG, WEBP · Máx. 30 MB por arquivo</div>

      <div class="form-secao" style="margin-top:1.5rem"><div class="form-secao-titulo">6. Observações</div></div>
      <div class="form-grupo">
        <textarea name="observacoes" rows="3" placeholder="Informações adicionais, histórico, localização física…"><?= h($d['observacoes'] ?? '') ?></textarea>
      </div>

      <div style="display:flex;gap:.75rem;margin-top:2rem;padding-top:1.25rem;border-top:1px solid #dde2ea">
        <button type="submit" class="btn btn-primario">Salvar <?= h($tipo_label) ?></button>
        <a href="<?= base_url('outros') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<!-- Modal confirmação campos em aberto -->
<div class="modal-overlay" id="modal-confirma" style="display:none">
  <div class="modal-box" style="max-width:440px">
    <div class="modal-head"><h3 style="font-size:1.15rem;color:var(--cor-primaria)">Campos em aberto</h3></div>
    <div class="modal-body">
      <div class="alerta-pendencias" style="margin-bottom:0">
        ⚠️ Há <strong id="conf-num">0</strong> campo(s) sem preencher. Deseja salvar assim mesmo?
      </div>
    </div>
    <div class="modal-rodape">
      <button type="button" class="btn btn-secundario" id="conf-voltar">Continuar preenchendo</button>
      <button type="button" class="btn btn-primario" id="conf-salvar" style="margin-left:auto">Salvar assim mesmo</button>
    </div>
  </div>
</div>

<style>
.upload-row.tem-arquivo{border-color:#1a7a45;background:#f0faf4!important}
.upload-row.tem-arquivo .upload-check{background:#1a7a45;border-color:#1a7a45}
.upload-row.tem-arquivo .check-icon{opacity:1!important}
.upload-row.tem-arquivo .upload-btn{background:#e6f4ec;color:#1a7a45}
</style>
<script>
document.querySelectorAll('.upload-input').forEach(input=>{
  input.addEventListener('change',function(){
    const row=document.getElementById('row-'+this.dataset.field);
    const fn=row?.querySelector('.upload-filename');
    if(!row)return;
    if(this.files&&this.files.length>0){
      const nomes=Array.from(this.files).map(f=>f.name).join(', ');
      const mb=(Array.from(this.files).reduce((s,f)=>s+f.size,0)/1024/1024).toFixed(1);
      if(fn)fn.textContent=nomes+(this.files.length>1?` (${this.files.length} arquivos, ${mb} MB)`:` (${mb} MB)`);
      row.classList.add('tem-arquivo');
      row.querySelector('.upload-btn').textContent='Trocar';
    }else{
      if(fn)fn.textContent='Nenhum arquivo selecionado';
      row.classList.remove('tem-arquivo');
      row.querySelector('.upload-btn').textContent='Selecionar';
    }
  });
});

const form=document.querySelector('form[method=post]');
const modalConf=document.getElementById('modal-confirma');
const ignorar=['file','hidden','submit','button'];
function contarVazios(){
  let v=0;
  form.querySelectorAll('input,select,textarea').forEach(el=>{
    if(ignorar.includes((el.type||'').toLowerCase()))return;
    if(el.offsetParent===null)return;
    if(el.name==='nome'||el.name==='tipo')return;
    if(String(el.value).trim()==='')v++;
  });
  return v;
}
if(form&&modalConf){
  form.addEventListener('submit',function(e){
    if(form.dataset.confirmado==='1')return;
    const n=contarVazios();
    if(n>0){e.preventDefault();document.getElementById('conf-num').textContent=n;modalConf.style.display='flex';}
  });
  document.getElementById('conf-salvar')?.addEventListener('click',function(){form.dataset.confirmado='1';modalConf.style.display='none';form.submit();});
  document.getElementById('conf-voltar')?.addEventListener('click',function(){modalConf.style.display='none';});
  modalConf.addEventListener('click',function(e){if(e.target===modalConf)modalConf.style.display='none';});
}
</script>
<?php require APP_ROOT . '/includes/footer.php'; ?>
