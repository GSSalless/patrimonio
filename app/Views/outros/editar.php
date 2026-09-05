<?php
/**
 * @var array  $cli
 * @var array  $ob
 * @var array  $d
 * @var string $tipo
 * @var string $erro
 * @var string $ok
 * @var array  $docs_list
 */
$tipos_label = ['embarcacao'=>'Embarcação','joia'=>'Joia','obra_de_arte'=>'Obra de Arte','outro'=>'Outro'];
$tipo_label  = $tipos_label[$tipo] ?? $tipo;
$page_title  = 'Editar Bem';
require APP_ROOT . '/includes/header.php';
?>
<div class="container" style="max-width:960px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <div>
      <h2 class="card-titulo"><?= h($ob['codigo']) ?> — <?= h($ob['nome']) ?></h2>
      <div style="font-size:.85rem;color:var(--cor-secundario)"><?= h($tipo_label) ?> · Cliente: <strong><?= h($cli['nome']) ?></strong></div>
    </div>
    <a href="<?= base_url('outros') ?>" class="btn btn-secundario">← Voltar</a>
  </div>

  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>
  <?php if ($ok):   ?><div class="alerta alerta-sucesso">✅ <?= h($ok) ?></div><?php endif; ?>

  <div class="card">
    <form method="post" enctype="multipart/form-data">

      <!-- BLOCO 1 — IDENTIFICAÇÃO GERAL -->
      <div class="form-secao"><div class="form-secao-titulo">1. Identificação</div></div>
      <div class="form-grid form-grid-3">
        <div class="form-grupo" style="grid-column:1/-1"><label>Nome / Descrição *</label><input type="text" name="nome" required value="<?= h($d['nome'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Marca / Fabricante</label><input type="text" name="marca" value="<?= h($d['marca'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Modelo / Referência</label><input type="text" name="modelo" value="<?= h($d['modelo'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Ano</label><input type="number" name="ano" min="1800" max="2100" value="<?= h($d['ano'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Cor / Acabamento</label><input type="text" name="cor" value="<?= h($d['cor'] ?? '') ?>"></div>
        <div class="form-grupo" style="grid-column:span 2"><label>Descrição detalhada</label><input type="text" name="descricao" value="<?= h($d['descricao'] ?? '') ?>"></div>
      </div>

      <?php require APP_PATH . '/Views/outros/_campos_tipo.php'; ?>

      <!-- BLOCO SEGURO -->
      <div class="form-secao"><div class="form-secao-titulo">3. Seguro</div></div>
      <div class="form-grid form-grid-4">
        <div class="form-grupo" style="grid-column:span 2"><label>Seguradora</label><input type="text" name="seguradora" value="<?= h($d['seguradora'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Apólice</label><input type="text" name="apolice" value="<?= h($d['apolice'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Franquia (R$)</label><input type="text" name="franquia" value="<?= h($d['franquia'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Vencimento do seguro</label><input type="date" name="vencimento_seguro" value="<?= h($d['vencimento_seguro'] ?? '') ?>"></div>
      </div>

      <!-- BLOCO FINANCEIRO -->
      <div class="form-secao"><div class="form-secao-titulo">4. Financeiro</div></div>
      <div class="form-grid form-grid-3">
        <div class="form-grupo"><label>Data de aquisição</label><input type="date" name="data_aquisicao" value="<?= h($d['data_aquisicao'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Valor de aquisição (R$)</label><input type="text" name="valor_aquisicao" value="<?= h($d['valor_aquisicao'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Valor de mercado (R$)</label><input type="text" name="valor_mercado" value="<?= h($d['valor_mercado'] ?? '') ?>"></div>
      </div>

      <!-- DOCUMENTOS EXISTENTES -->
      <?php if ($docs_list): ?>
      <div class="form-secao"><div class="form-secao-titulo">5. Documentos Enviados</div></div>
      <div style="display:flex;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem">
        <?php foreach ($docs_list as $doc): ?>
        <a href="<?= base_url($doc['caminho']) ?>" target="_blank" class="card"
           style="padding:.6rem .9rem;display:flex;align-items:center;gap:.5rem;text-decoration:none;font-size:.85rem;min-width:0">
          <?= in_array(pathinfo($doc['nome_arquivo'],PATHINFO_EXTENSION), ['jpg','jpeg','png','webp']) ? '🖼️' : '📄' ?>
          <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px"><?= h($doc['nome_arquivo']) ?></span>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- UPLOAD NOVOS DOCUMENTOS -->
      <div class="form-secao"><div class="form-secao-titulo"><?= $docs_list ? '6.' : '5.' ?> Adicionar Documentos</div></div>
      <div style="display:flex;flex-direction:column;gap:.6rem">
        <?php
        $upload_items = [
          ['field'=>'foto_principal','label'=>'Nova foto principal', 'icon'=>'📷','accept'=>'.jpg,.jpeg,.png,.webp','multiple'=>false],
          ['field'=>'doc_laudo',     'label'=>'Laudo / Avaliação',  'icon'=>'📄','accept'=>'.pdf,.jpg,.jpeg,.png', 'multiple'=>false],
          ['field'=>'doc_apolice',   'label'=>'Apólice de seguro',  'icon'=>'🛡️','accept'=>'.pdf,.jpg,.jpeg,.png', 'multiple'=>false],
          ['field'=>'doc_outros',    'label'=>'Outros documentos',  'icon'=>'📎','accept'=>'.pdf,.jpg,.jpeg,.png', 'multiple'=>true ],
        ];
        foreach ($upload_items as $item): ?>
        <div class="upload-row" id="row-<?= $item['field'] ?>"
             style="display:flex;align-items:center;gap:1rem;padding:.75rem 1rem;border:1px solid var(--cor-borda);border-radius:8px;background:#fff">
          <div class="upload-check" style="width:28px;height:28px;border-radius:50%;border:2px solid #cbd2db;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .35s">
            <svg class="check-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="opacity:0"><polyline points="20 6 9 17 4 12"/></svg>
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

      <!-- OBSERVAÇÕES -->
      <div class="form-secao" style="margin-top:1.5rem"><div class="form-secao-titulo">Observações</div></div>
      <div class="form-grupo">
        <textarea name="observacoes" rows="3"><?= h($d['observacoes'] ?? '') ?></textarea>
      </div>

      <div style="display:flex;gap:.75rem;margin-top:2rem;padding-top:1.25rem;border-top:1px solid #dde2ea">
        <button type="submit" class="btn btn-primario">Salvar alterações</button>
        <a href="<?= base_url('outros') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>

  <?php $bem_id = (int) $ob['id']; ?>

  <?php if ($tipo === 'embarcacao'): ?>
  <!-- HISTÓRICO: MANUTENÇÕES (embarcação) -->
  <?php $bm_tipos = ['revisao'=>'Revisão','motor'=>'Motor','casco'=>'Casco','pintura'=>'Pintura','eletrica'=>'Elétrica','limpeza'=>'Limpeza','peca'=>'Troca de peça','outro'=>'Outro']; ?>
  <div class="card" id="manutencoes" style="margin-top:1.5rem">
    <div class="card-header" style="margin-bottom:1rem">
      <h3 class="card-titulo">🛥️ Manutenções / revisões</h3>
      <a href="<?= base_url('outros/manutencao?bem_id='.$bem_id) ?>" class="btn btn-primario btn-sm">+ Manutenção</a>
    </div>
    <?php if ($manutencoes): ?>
    <div style="overflow-x:auto">
    <table class="tabela">
      <thead><tr><th>Data</th><th>Tipo</th><th>Descrição</th><th>Fornecedor</th><th>Horím.</th><th>Valor</th><th>Garantia</th><th>Próxima</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($manutencoes as $mt): $garante = $mt['garantia_ate'] && $mt['garantia_ate'] >= date('Y-m-d'); ?>
        <tr>
          <td><?= data_br($mt['data']) ?></td>
          <td><span class="tag"><?= $bm_tipos[$mt['tipo']] ?? $mt['tipo'] ?></span></td>
          <td><?= h($mt['descricao']) ?></td>
          <td><?= h($mt['fornecedor'] ?? '—') ?></td>
          <td><?= $mt['horimetro'] ? number_format((int)$mt['horimetro'],0,',','.').'h' : '—' ?></td>
          <td><?= $mt['valor'] ? moeda((float)$mt['valor']) : '—' ?></td>
          <td><?php if ($mt['garantia_ate']): ?><span class="tag <?= $garante?'tag-verde':'tag-vermelho' ?>"><?= data_br($mt['garantia_ate']) ?></span><?php else: ?>—<?php endif; ?></td>
          <td><?= $mt['proxima_data'] ? data_br($mt['proxima_data']) : '—' ?></td>
          <td><a href="<?= base_url('outros/manutencao?bem_id='.$bem_id.'&id='.$mt['id']) ?>" class="btn btn-secundario btn-sm">Editar</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php else: ?><p style="color:var(--cor-secundario)">Nenhuma manutenção registrada.</p><?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- HISTÓRICO: AVALIAÇÕES -->
  <div class="card" id="avaliacoes" style="margin-top:1.5rem">
    <div class="card-header" style="margin-bottom:1rem">
      <h3 class="card-titulo">📈 Avaliações (histórico de valor)</h3>
      <a href="<?= base_url('outros/avaliacao?bem_id='.$bem_id) ?>" class="btn btn-primario btn-sm">+ Avaliação</a>
    </div>
    <?php if ($avaliacoes): ?>
    <div style="overflow-x:auto">
    <table class="tabela">
      <thead><tr><th>Data</th><th>Valor avaliado</th><th>Variação</th><th>Fonte</th><th>Observações</th><th></th></tr></thead>
      <tbody>
        <?php
        // As avaliações vêm em ordem decrescente; para variação, comparo com a imediatamente anterior no tempo.
        $n = count($avaliacoes);
        foreach ($avaliacoes as $i => $av):
          $ant = $avaliacoes[$i + 1] ?? null; // próxima no array = mais antiga
          $delta = $ant ? (float)$av['valor'] - (float)$ant['valor'] : null;
        ?>
        <tr>
          <td><?= data_br($av['data']) ?></td>
          <td><strong><?= moeda((float)$av['valor']) ?></strong></td>
          <td><?php if ($delta === null): ?><span style="color:var(--cor-secundario)">—</span>
              <?php elseif ($delta >= 0): ?><span style="color:#1a7a45">▲ <?= moeda($delta) ?></span>
              <?php else: ?><span style="color:#b82020">▼ <?= moeda(abs($delta)) ?></span><?php endif; ?></td>
          <td><?= h($av['fonte'] ?? '—') ?></td>
          <td><?= h($av['observacoes'] ?? '—') ?></td>
          <td><a href="<?= base_url('outros/avaliacao?bem_id='.$bem_id.'&id='.$av['id']) ?>" class="btn btn-secundario btn-sm">Editar</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php else: ?><p style="color:var(--cor-secundario)">Nenhuma avaliação registrada.</p><?php endif; ?>
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
</script>
<?php require APP_ROOT . '/includes/footer.php'; ?>
