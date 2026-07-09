<?php
/**
 * Ficha do imóvel — hub central com modais por área.
 * @var array      $im
 * @var int        $id
 * @var bool       $novo
 * @var string     $aba_abrir
 * @var bool       $is_admin
 * @var array      $iptu_list, $cond_fat, $reformas, $lancamentos, $documentos, $cond_docs, $condominios_lista
 * @var array|false $contrato, $condominio
 */
$page_title = h($im['nome_referencia']) . ' — Ficha';
require APP_ROOT . '/includes/header.php';

if (!function_exists('linha')) {
    function linha($label, $val) {
        if (!$val || $val === '—') return;
        echo "<div style='display:flex;gap:.5rem;margin-bottom:.5rem'><span style='min-width:160px;color:var(--cor-secundario);font-size:.85rem'>$label</span><span style='font-size:.9rem;font-weight:500'>$val</span></div>";
    }
}
?>
<div class="container">

  <?php if ($novo): ?>
  <div class="alerta alerta-sucesso">Imóvel cadastrado com sucesso!</div>
  <?php endif; ?>

  <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:.5rem">
    <a href="<?= base_url('imoveis') ?>" class="btn btn-secundario btn-sm">← Voltar</a>
    <?php if ($is_admin): ?>
    <a href="<?= base_url('imoveis/editar?id='.$id) ?>" class="btn btn-secundario btn-sm">✏️ Editar</a>
    <?php endif; ?>
  </div>

  <!-- HUB CENTRAL -->
  <div class="hub">
    <svg class="hub-linhas" viewBox="0 0 100 100" preserveAspectRatio="none">
      <line x1="50" y1="50" x2="50"   y2="12"/>
      <line x1="50" y1="50" x2="79.7" y2="26.3"/>
      <line x1="50" y1="50" x2="87.0" y2="58.5"/>
      <line x1="50" y1="50" x2="66.5" y2="84.2"/>
      <line x1="50" y1="50" x2="33.5" y2="84.2"/>
      <line x1="50" y1="50" x2="13.0" y2="58.5"/>
      <line x1="50" y1="50" x2="20.3" y2="26.3"/>
    </svg>

    <div class="hub-centro">
      <div class="hub-centro-cod"><?= h($im['codigo']) ?></div>
      <div class="hub-centro-nome"><?= h($im['nome_referencia']) ?></div>
      <div class="hub-centro-valor"><?= $im['valor_mercado'] ? moeda((float)$im['valor_mercado']) : '—' ?></div>
    </div>

    <button class="hub-no" style="left:50%;top:12%"    onclick="abrirModal('m-resumo')">
      <span class="hub-no-tile hub-tile-azul"><i class="bi bi-clipboard-check-fill"></i></span><span class="hub-no-lb">Cadastro</span>
    </button>
    <button class="hub-no" style="left:79.7%;top:26.3%" onclick="abrirModal('m-financeiro')">
      <span class="hub-no-tile hub-tile-verde"><i class="bi bi-cash-coin"></i></span><span class="hub-no-lb">Financeiro</span>
    </button>
    <button class="hub-no" style="left:87.0%;top:58.5%" onclick="abrirModal('m-reformas')">
      <span class="hub-no-tile hub-tile-laranja"><i class="bi bi-hammer"></i></span><span class="hub-no-lb">Reformas</span>
    </button>
    <button class="hub-no" style="left:66.5%;top:84.2%" onclick="abrirModal('m-manutencoes')">
      <span class="hub-no-tile hub-tile-vermelho"><i class="bi bi-tools"></i></span><span class="hub-no-lb">Manutenções</span>
    </button>
    <button class="hub-no" style="left:33.5%;top:84.2%" onclick="abrirModal('m-condominio')">
      <span class="hub-no-tile hub-tile-roxo"><i class="bi bi-building"></i></span><span class="hub-no-lb">Condomínio</span>
    </button>
    <button class="hub-no" style="left:13.0%;top:58.5%" onclick="abrirModal('m-aluguel')">
      <span class="hub-no-tile hub-tile-roxo"><i class="bi bi-key-fill"></i></span><span class="hub-no-lb">Aluguel</span>
    </button>
    <button class="hub-no" style="left:20.3%;top:26.3%" onclick="abrirModal('m-documentos')">
      <span class="hub-no-tile hub-tile-ciano"><i class="bi bi-folder-fill"></i></span><span class="hub-no-lb">Documentos</span>
    </button>
  </div>

  <!-- MODAL: RESUMO -->
  <div class="modal-overlay" id="m-resumo" style="display:none">
    <div class="modal-box modal-lg">
      <div class="modal-head">
        <h3 style="font-size:1.15rem;color:var(--cor-primaria)">📋 Cadastro — <?= h($im['nome_referencia']) ?></h3>
        <button type="button" class="modal-fechar" onclick="fecharModal('m-resumo')">&times;</button>
      </div>
      <div class="modal-body">
        <div style="font-size:.85rem;color:var(--cor-secundario);margin-bottom:1rem">
          <?= h($im['codigo']) ?> · <?= tipo_imovel_label($im['tipo']) ?> · <?= finalidade_label($im['finalidade']) ?>
          &nbsp;<span class="tag <?= $im['situacao']==='pronto'?'tag-verde':'tag-laranja' ?>"><?= situacao_label($im['situacao']) ?></span>
          <?php if ($im['logradouro']): ?>
          <div style="margin-top:.4rem">📍 <?= h(trim($im['logradouro'].' '.$im['numero'].($im['complemento']?', '.$im['complemento']:''))) ?>, <?= h($im['bairro'].' — '.$im['cidade'].'/'.$im['estado']) ?></div>
          <?php endif; ?>
        </div>
        <div class="form-grid form-grid-2" style="gap:1.5rem">
          <div>
            <div class="card-titulo" style="margin-bottom:1rem">Identificação</div>
            <?php linha('Código', h($im['codigo'])); ?>
            <?php linha('Tipo', tipo_imovel_label($im['tipo'])); ?>
            <?php linha('Finalidade', finalidade_label($im['finalidade'])); ?>
            <?php linha('Situação', situacao_label($im['situacao'])); ?>
            <?php if ($im['inscricao_municipal']) linha('Insc. Municipal', h($im['inscricao_municipal'])); ?>
            <?php if ($im['data_aquisicao']) linha('Data de aquisição', data_br($im['data_aquisicao']) . ($im['forma_aquisicao'] ? ' (' . forma_aquisicao_label($im['forma_aquisicao']) . ')' : '')); ?>
            <?php if ($im['banco_financiador']) linha('Financiamento', h($im['banco_financiador'])); ?>
          </div>
          <div>
            <div class="card-titulo" style="margin-bottom:1rem">Financeiro</div>
            <?php if ($im['valor_compra'])   linha('Valor de compra',  moeda((float)$im['valor_compra'])); ?>
            <?php if ($im['valor_entrada'])  linha('Entrada',          moeda((float)$im['valor_entrada'])); ?>
            <?php if ($im['valor_financiamento']) linha('Financiado',  moeda((float)$im['valor_financiamento'])); ?>
            <?php if ($im['valor_mercado'])  linha('Valor de mercado', moeda((float)$im['valor_mercado'])); ?>
            <?php if ($im['valor_compra'] && $im['valor_mercado']):
              $ganho = (float)$im['valor_mercado'] - (float)$im['valor_compra'];
              linha('Ganho de capital', moeda($ganho) . ' (' . number_format(($ganho/(float)$im['valor_compra'])*100,1,',','.') . '%)');
            endif; ?>
          </div>
        </div>
        <hr style="border:none;border-top:1px solid #dde2ea;margin:1.25rem 0">
        <div class="card-titulo" style="margin-bottom:1rem">Custos mensais</div>
        <div class="form-grid form-grid-3">
          <?php foreach (['custo_condominio'=>'Condomínio','custo_iptu_mensal'=>'IPTU (mensal)','custo_energia'=>'Energia','custo_agua'=>'Água','custo_internet'=>'Internet','custo_outros'=>'Outros'] as $campo => $label): ?>
            <?php if ($im[$campo]): ?>
            <div style="background:#f9fafc;padding:.75rem;border-radius:6px">
              <div style="font-size:.78rem;color:var(--cor-secundario);text-transform:uppercase;letter-spacing:.04em"><?= $label ?></div>
              <div style="font-size:1rem;font-weight:700;color:var(--cor-primaria)"><?= moeda((float)$im[$campo]) ?>/mês</div>
            </div>
            <?php endif; ?>
          <?php endforeach; ?>
          <div style="background:var(--cor-primaria);padding:.75rem;border-radius:6px;color:#fff">
            <div style="font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;opacity:.7">Total mensal</div>
            <div style="font-size:1rem;font-weight:700"><?= moeda(custo_mensal_total($im)) ?>/mês</div>
          </div>
        </div>
        <?php if ($im['logradouro'] && $im['cidade']):
          $link_maps = $im['link_maps'] ?: gerar_links_localizacao($im)['maps'];
          $link_sv   = $im['link_street_view'] ?: gerar_links_localizacao($im)['street_view'];
          $end_share = implode(', ', array_filter([$im['logradouro'].' '.($im['numero']??''), $im['bairro']??'', ($im['cidade']??'').'/'.($im['estado']??'')]));
        ?>
        <div style="margin-top:1.25rem;display:flex;gap:.6rem;flex-wrap:wrap">
          <?php if ($link_maps): ?><a href="<?= h($link_maps) ?>" target="_blank" class="btn btn-secundario btn-sm">📍 Google Maps</a><?php endif; ?>
          <?php if ($link_sv): ?><a href="<?= h($link_sv) ?>" target="_blank" class="btn btn-secundario btn-sm">🏙️ Street View</a><?php endif; ?>
          <a href="https://api.whatsapp.com/send?text=<?= urlencode('📍 ' . $end_share . "\n" . $link_maps) ?>" target="_blank" class="btn btn-secundario btn-sm">📲 Compartilhar</a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- MODAL: FINANCEIRO -->
  <div class="modal-overlay" id="m-financeiro" style="display:none">
    <div class="modal-box modal-lg">
      <div class="modal-head">
        <h3 style="font-size:1.15rem;color:var(--cor-primaria)">💰 Financeiro</h3>
        <button type="button" class="modal-fechar" onclick="fecharModal('m-financeiro')">&times;</button>
      </div>
      <div class="modal-body">
        <?php if ($is_admin): ?>
        <div style="display:flex;gap:.6rem;margin-bottom:1rem;flex-wrap:wrap">
          <a href="<?= base_url('financeiro/iptu?imovel_id='.$id) ?>" class="btn btn-secundario btn-sm">+ IPTU</a>
          <a href="<?= base_url('financeiro/fatura-condominio?imovel_id='.$id) ?>" class="btn btn-secundario btn-sm">+ Fatura Condomínio</a>
          <a href="<?= base_url('financeiro/lancamento?imovel_id='.$id) ?>" class="btn btn-secundario btn-sm">+ Lançamento</a>
        </div>
        <?php endif; ?>

        <div class="card-titulo" style="margin-bottom:.6rem">IPTU</div>
        <?php if ($iptu_list): ?>
        <table class="tabela" style="margin-bottom:1.5rem">
          <thead><tr><th>Ano</th><th>Inscrição</th><th>Valor Total</th><th>Parcelas</th><th>Venc. 1ª</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach ($iptu_list as $ip): ?>
            <tr>
              <td><strong><?= $ip['ano'] ?></strong></td>
              <td><?= h($ip['inscricao_iptu'] ?? '—') ?></td>
              <td><?= moeda((float)$ip['valor_total']) ?></td>
              <td><?= $ip['parcelas'] ?>x</td>
              <td><?= data_br($ip['data_vencimento_1']) ?></td>
              <td><span class="tag <?= $ip['pago'] ? 'tag-verde' : 'tag-laranja' ?>"><?= $ip['pago'] ? 'Pago' : 'Em aberto' ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?><p style="color:var(--cor-secundario);margin-bottom:1.5rem">Nenhum IPTU registrado.</p><?php endif; ?>

        <div class="card-titulo" style="margin-bottom:.6rem">Condomínio — Faturas mensais</div>
        <?php if ($cond_fat): ?>
        <table class="tabela" style="margin-bottom:1.5rem">
          <thead><tr><th>Competência</th><th>Valor</th><th>Extra</th><th>Status</th><th>Boleto</th></tr></thead>
          <tbody>
            <?php foreach ($cond_fat as $f): ?>
            <tr>
              <td><?= date('m/Y', strtotime($f['competencia'])) ?></td>
              <td><?= moeda((float)$f['valor']) ?></td>
              <td><?= h($f['descricao_extra'] ?? '—') ?></td>
              <td><span class="tag <?= $f['pago'] ? 'tag-verde' : 'tag-laranja' ?>"><?= $f['pago'] ? 'Pago' : 'Em aberto' ?></span></td>
              <td><?php if ($f['arquivo_boleto']): ?><a href="<?= base_url($f['arquivo_boleto']) ?>" target="_blank" class="btn btn-secundario btn-sm">📄</a><?php else: ?>—<?php endif; ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?><p style="color:var(--cor-secundario);margin-bottom:1.5rem">Nenhuma fatura registrada.</p><?php endif; ?>

        <div class="card-titulo" style="margin-bottom:.6rem">Lançamentos financeiros</div>
        <?php if ($lancamentos): ?>
        <table class="tabela">
          <thead><tr><th>Competência</th><th>Categoria</th><th>Descrição</th><th>Tipo</th><th>Valor</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach ($lancamentos as $l): ?>
            <tr>
              <td><?= data_br($l['data_competencia']) ?></td>
              <td><span class="tag"><?= h($l['categoria']) ?></span></td>
              <td><?= h($l['descricao']) ?></td>
              <td><span class="tag <?= $l['tipo']==='receita'?'tag-verde':'tag-vermelho' ?>"><?= $l['tipo'] ?></span></td>
              <td><?= moeda((float)$l['valor']) ?></td>
              <td><span class="tag <?= $l['pago']?'tag-verde':'tag-laranja' ?>"><?= $l['pago']?'Pago':'Em aberto' ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?><p style="color:var(--cor-secundario)">Nenhum lançamento registrado.</p><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- MODAL: REFORMAS -->
  <div class="modal-overlay" id="m-reformas" style="display:none">
    <div class="modal-box modal-lg">
      <div class="modal-head">
        <h3 style="font-size:1.15rem;color:var(--cor-primaria)">🔨 Reformas</h3>
        <button type="button" class="modal-fechar" onclick="fecharModal('m-reformas')">&times;</button>
      </div>
      <div class="modal-body">
        <?php if ($is_admin): ?>
        <div style="margin-bottom:1rem"><a href="<?= base_url('reformas/nova?imovel_id='.$id) ?>" class="btn btn-primario btn-sm">+ Nova reforma</a></div>
        <?php endif; ?>
        <?php if ($reformas): ?>
        <?php foreach ($reformas as $r): ?>
        <div style="border:1px solid var(--cor-borda);border-radius:10px;padding:1rem;margin-bottom:1rem">
          <div class="card-header" style="margin-bottom:.75rem">
            <div>
              <div class="card-titulo"><?= h($r['descricao']) ?></div>
              <div style="font-size:.85rem;color:var(--cor-secundario)">
                <?= $r['data_inicio'] ? data_br($r['data_inicio']) : '' ?>
                <?= $r['data_fim_real'] ? ' → ' . data_br($r['data_fim_real']) : ($r['data_fim_prevista'] ? ' (prev. ' . data_br($r['data_fim_prevista']) . ')' : '') ?>
                <?php if ($r['fornecedor']): ?> &middot; <?= h($r['fornecedor']) ?><?php endif; ?>
              </div>
            </div>
            <div style="text-align:right">
              <span class="tag <?= ['planejado'=>'tag-azul','em_andamento'=>'tag-laranja','concluido'=>'tag-verde','cancelado'=>'tag-vermelho'][$r['status']] ?? '' ?>">
                <?= ['planejado'=>'Planejado','em_andamento'=>'Em andamento','concluido'=>'Concluído','cancelado'=>'Cancelado'][$r['status']] ?? $r['status'] ?>
              </span>
              <?php if ($is_admin): ?>
              <br><a href="<?= base_url('reformas/editar?id='.$r['id'].'&imovel_id='.$id) ?>" class="btn btn-secundario btn-sm" style="margin-top:.4rem">Editar</a>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-grid form-grid-2" style="gap:1rem">
            <?php if ($r['custo_previsto']): ?>
            <div style="background:#f9fafc;padding:.75rem;border-radius:6px">
              <div style="font-size:.78rem;color:var(--cor-secundario);text-transform:uppercase">Custo previsto</div>
              <div style="font-size:1.05rem;font-weight:700"><?= moeda((float)$r['custo_previsto']) ?></div>
            </div>
            <?php endif; ?>
            <?php if ($r['custo_realizado']): ?>
            <div style="background:#f9fafc;padding:.75rem;border-radius:6px">
              <div style="font-size:.78rem;color:var(--cor-secundario);text-transform:uppercase">Custo realizado</div>
              <div style="font-size:1.05rem;font-weight:700;color:<?= $r['custo_realizado'] > $r['custo_previsto'] ? '#b82020' : '#1a7a45' ?>"><?= moeda((float)$r['custo_realizado']) ?></div>
            </div>
            <?php endif; ?>
          </div>
          <?php
          $docs_ref = db()->prepare('SELECT * FROM documentos WHERE tipo_referencia = "reforma" AND referencia_id = ?');
          $docs_ref->execute([$r['id']]); $docs_ref = $docs_ref->fetchAll();
          if ($docs_ref): ?>
          <div style="margin-top:.75rem;display:flex;gap:.5rem;flex-wrap:wrap">
            <?php foreach ($docs_ref as $dref): ?>
            <a href="<?= base_url($dref['caminho']) ?>" target="_blank" class="btn btn-secundario btn-sm">📎 <?= h($dref['nome_arquivo']) ?></a>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <p style="color:var(--cor-secundario);text-align:center;padding:1.5rem">Nenhuma reforma cadastrada.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- MODAL: MANUTENÇÕES -->
  <div class="modal-overlay" id="m-manutencoes" style="display:none">
    <div class="modal-box modal-lg">
      <div class="modal-head">
        <h3 style="font-size:1.15rem;color:var(--cor-primaria)">🛠️ Manutenções</h3>
        <button type="button" class="modal-fechar" onclick="fecharModal('m-manutencoes')">&times;</button>
      </div>
      <div class="modal-body">
        <?php
        $manut_tipos = ['eletrica'=>'Elétrica','hidraulica'=>'Hidráulica','pintura'=>'Pintura','ar_condicionado'=>'Ar-condicionado','estrutural'=>'Estrutural','jardim'=>'Jardim/Área externa','elevador'=>'Elevador','seguranca'=>'Segurança','eletrodomestico'=>'Eletrodoméstico','outro'=>'Outro'];
        if ($is_admin): ?>
        <div style="margin-bottom:1rem"><a href="<?= base_url('manutencoes/nova?imovel_id='.$id) ?>" class="btn btn-primario btn-sm">+ Nova manutenção</a></div>
        <?php endif; ?>
        <?php if ($manutencoes): ?>
        <table class="tabela">
          <thead><tr><th>Data</th><th>Tipo</th><th>Descrição</th><th>Fornecedor</th><th>Valor</th><th>Garantia</th><?php if($is_admin):?><th></th><?php endif;?></tr></thead>
          <tbody>
            <?php foreach ($manutencoes as $mt):
              $garante = $mt['garantia_ate'] && $mt['garantia_ate'] >= date('Y-m-d'); ?>
            <tr>
              <td><?= data_br($mt['data']) ?></td>
              <td><span class="tag"><?= $manut_tipos[$mt['tipo']] ?? $mt['tipo'] ?></span></td>
              <td><?= h($mt['descricao']) ?>
                <?php
                $docs_mt = db()->prepare('SELECT * FROM documentos WHERE tipo_referencia = "manutencao" AND referencia_id = ?');
                $docs_mt->execute([$mt['id']]); $docs_mt = $docs_mt->fetchAll();
                foreach ($docs_mt as $dmt): ?>
                <a href="<?= base_url($dmt['caminho']) ?>" target="_blank" title="<?= h($dmt['nome_arquivo']) ?>" style="margin-left:.35rem">📎</a>
                <?php endforeach; ?>
              </td>
              <td><?= h($mt['fornecedor'] ?? '—') ?></td>
              <td><?= $mt['valor'] ? moeda((float)$mt['valor']) : '—' ?></td>
              <td><?php if ($mt['garantia_ate']): ?><span class="tag <?= $garante ? 'tag-verde':'tag-vermelho' ?>"><?= data_br($mt['garantia_ate']) ?></span><?php else: ?>—<?php endif; ?></td>
              <?php if ($is_admin): ?><td><a href="<?= base_url('manutencoes/editar?id='.$mt['id'].'&imovel_id='.$id) ?>" class="btn btn-secundario btn-sm">Editar</a></td><?php endif; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
        <p style="color:var(--cor-secundario);text-align:center;padding:1.5rem">Nenhuma manutenção registrada.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- MODAL: ALUGUEL -->
  <div class="modal-overlay" id="m-aluguel" style="display:none">
    <div class="modal-box">
      <div class="modal-head">
        <h3 style="font-size:1.15rem;color:var(--cor-primaria)">🔑 Aluguel</h3>
        <button type="button" class="modal-fechar" onclick="fecharModal('m-aluguel')">&times;</button>
      </div>
      <div class="modal-body">
        <?php if ($is_admin): ?>
        <div style="margin-bottom:1rem"><a href="<?= base_url('locacao/novo?imovel_id='.$id) ?>" class="btn btn-primario btn-sm">+ Novo contrato</a></div>
        <?php endif; ?>
        <?php if ($contrato): ?>
        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1rem">
          <span class="card-titulo">Contrato vigente</span> <span class="tag tag-verde">Ativo</span>
        </div>
        <div class="form-grid form-grid-2" style="gap:1.25rem">
          <div>
            <?php linha('Locatário', h($contrato['locatario_nome'])); ?>
            <?php if ($contrato['locatario_cpf_cnpj']) linha('CPF/CNPJ', h($contrato['locatario_cpf_cnpj'])); ?>
            <?php linha('Início',  data_br($contrato['data_inicio'])); ?>
            <?php linha('Término', $contrato['data_fim'] ? data_br($contrato['data_fim']) : 'Indeterminado'); ?>
          </div>
          <div style="background:#faf7ef;padding:1.25rem;border-radius:8px;text-align:center">
            <div style="font-size:.8rem;color:var(--cor-secundario);text-transform:uppercase">Aluguel mensal</div>
            <div style="font-size:2rem;font-weight:800;color:var(--cor-primaria)"><?= moeda((float)$contrato['valor_aluguel']) ?></div>
            <div style="font-size:.85rem;color:var(--cor-secundario)">Vence todo dia <?= $contrato['dia_vencimento'] ?></div>
          </div>
        </div>
        <?php if ($contrato['observacoes']): ?>
        <p style="margin-top:1rem;color:var(--cor-secundario);font-size:.9rem"><?= h($contrato['observacoes']) ?></p>
        <?php endif; ?>
        <?php else: ?>
        <p style="color:var(--cor-secundario);text-align:center;padding:1.5rem">Nenhum contrato de locação ativo.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- MODAL: DOCUMENTOS -->
  <div class="modal-overlay" id="m-documentos" style="display:none">
    <div class="modal-box modal-lg">
      <div class="modal-head">
        <h3 style="font-size:1.15rem;color:var(--cor-primaria)">📁 Documentos</h3>
        <button type="button" class="modal-fechar" onclick="fecharModal('m-documentos')">&times;</button>
      </div>
      <div class="modal-body">
        <?php if ($is_admin): ?>
        <div style="margin-bottom:1rem"><a href="<?= base_url('documentos/upload?tipo=imovel&ref='.$id) ?>" class="btn btn-primario btn-sm">+ Anexar documento</a></div>
        <?php endif; ?>

        <?php
        // Checklist documental — documentos esperados x anexados
        $checklist = [
          'matricula'       => 'Matrícula atualizada',
          'escritura'       => 'Escritura',
          'contrato_compra' => 'Contrato de compra',
          'iptu'            => 'Carnê de IPTU',
          'habite_se'       => 'Habite-se',
          'foto'            => 'Fotos do imóvel',
        ];
        $cats_presentes = array_column($documentos, 'categoria');
        $check_ok = 0;
        foreach ($checklist as $cat => $lbl) if (in_array($cat, $cats_presentes)) $check_ok++;
        $check_total = count($checklist);
        ?>
        <div style="background:#f9fafc;border:1px solid var(--cor-borda);border-radius:10px;padding:1rem;margin-bottom:1.25rem">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem">
            <span class="card-titulo">Checklist documental</span>
            <span class="tag <?= $check_ok===$check_total ? 'tag-verde' : ($check_ok? 'tag-laranja':'tag-vermelho') ?>"><?= $check_ok ?>/<?= $check_total ?></span>
          </div>
          <div style="display:flex;flex-wrap:wrap;gap:.5rem">
            <?php foreach ($checklist as $cat => $lbl): $tem = in_array($cat, $cats_presentes); ?>
            <span class="tag <?= $tem ? 'tag-verde':'' ?>" style="display:inline-flex;align-items:center;gap:.35rem<?= $tem?'':';opacity:.7' ?>">
              <i class="bi <?= $tem ? 'bi-check-circle-fill' : 'bi-circle' ?>"></i> <?= $lbl ?>
            </span>
            <?php endforeach; ?>
          </div>
        </div>

        <?php if ($documentos): ?>
        <table class="tabela">
          <thead><tr><th>Categoria</th><th>Arquivo</th><th>Emissão</th><th>Validade</th><th>Descrição</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($documentos as $doc): ?>
            <tr>
              <td><span class="tag"><?= h($doc['categoria']) ?></span></td>
              <td><?= h($doc['nome_arquivo']) ?></td>
              <td><?= data_br($doc['data_emissao']) ?></td>
              <td><?= $doc['data_validade'] ? data_br($doc['data_validade']) : '—' ?></td>
              <td><?= h($doc['descricao'] ?? '—') ?></td>
              <td><a href="<?= base_url($doc['caminho']) ?>" target="_blank" class="btn btn-secundario btn-sm">Abrir</a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
        <p style="color:var(--cor-secundario);text-align:center;padding:1.5rem">Nenhum documento anexado.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- MODAL: CONDOMÍNIO -->
  <div class="modal-overlay" id="m-condominio" style="display:none">
    <div class="modal-box modal-lg">
      <div class="modal-head">
        <h3 style="font-size:1.15rem;color:var(--cor-primaria)">🏢 Condomínio</h3>
        <button type="button" class="modal-fechar" onclick="fecharModal('m-condominio')">&times;</button>
      </div>
      <div class="modal-body">
        <?php if ($condominio): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem">
          <div>
            <div class="card-titulo"><?= h($condominio['nome']) ?></div>
            <div style="font-size:.85rem;color:var(--cor-secundario)">
              <?= h(trim(($condominio['endereco'] ?? '') . ($condominio['cidade'] ? ' — ' . $condominio['cidade'] . '/' . $condominio['estado'] : ''), ' —')) ?>
              <?= $condominio['cnpj'] ? ' · CNPJ ' . h($condominio['cnpj']) : '' ?>
            </div>
          </div>
          <?php if ($is_admin): ?>
          <div style="display:flex;gap:.5rem">
            <a href="<?= base_url('condominios/editar?id='.$condominio['id'].'&imovel_id='.$id) ?>" class="btn btn-secundario btn-sm">✏️ Editar</a>
            <a href="<?= base_url('condominios/vincular?imovel_id='.$id.'&condominio_id=0') ?>" class="btn btn-secundario btn-sm" onclick="return confirm('Desvincular este condomínio do imóvel?')">Desvincular</a>
          </div>
          <?php endif; ?>
        </div>

        <div class="form-grid form-grid-2" style="gap:1.5rem">
          <div>
            <div class="card-titulo" style="margin-bottom:1rem">Síndico / Administradora</div>
            <?php
              if ($condominio['sindico_nome'])     linha('Síndico', h($condominio['sindico_nome']));
              if ($condominio['sindico_telefone'])  linha('Tel. síndico', h($condominio['sindico_telefone']));
              if ($condominio['sindico_email'])     linha('E-mail síndico', h($condominio['sindico_email']));
              if ($condominio['mandato_fim'])       linha('Fim do mandato', data_br($condominio['mandato_fim']));
              if ($condominio['administradora'])    linha('Administradora', h($condominio['administradora']));
              if ($condominio['administradora_telefone']) linha('Tel. administradora', h($condominio['administradora_telefone']));
              if ($condominio['numero_blocos'])     linha('Blocos', (int)$condominio['numero_blocos']);
              if ($condominio['numero_unidades'])   linha('Unidades', (int)$condominio['numero_unidades']);
            ?>
          </div>
          <div>
            <div class="card-titulo" style="margin-bottom:1rem">Valores</div>
            <?php
              if ($condominio['valor_taxa'])          linha('Taxa condominial', moeda((float)$condominio['valor_taxa']) . '/mês');
              if ($condominio['valor_fundo_reserva']) linha('Fundo de reserva', moeda((float)$condominio['valor_fundo_reserva']) . '/mês');
              if ($condominio['dia_vencimento'])      linha('Vencimento', 'todo dia ' . (int)$condominio['dia_vencimento']);
            ?>
          </div>
        </div>

        <hr style="border:none;border-top:1px solid #dde2ea;margin:1.25rem 0">
        <div class="card-titulo" style="margin-bottom:.75rem">Comodidades</div>
        <?php
          $coms = condominio_comodidades();
          $tem_alguma = false;
        ?>
        <div style="display:flex;flex-wrap:wrap;gap:.5rem">
          <?php foreach ($coms as $col => [$lbl, $ic]): if (!empty($condominio[$col])): $tem_alguma = true; ?>
          <span class="tag tag-verde" style="display:inline-flex;align-items:center;gap:.35rem"><i class="bi <?= $ic ?>"></i> <?= $lbl ?></span>
          <?php endif; endforeach; ?>
          <?php if (!$tem_alguma): ?><span style="color:var(--cor-secundario);font-size:.9rem">Nenhuma comodidade marcada.</span><?php endif; ?>
        </div>

        <hr style="border:none;border-top:1px solid #dde2ea;margin:1.25rem 0">
        <div class="card-titulo" style="margin-bottom:.75rem">Documentos</div>
        <?php if ($cond_docs): ?>
        <div style="display:flex;flex-wrap:wrap;gap:.5rem">
          <?php foreach ($cond_docs as $doc): ?>
          <a href="<?= base_url($doc['caminho']) ?>" target="_blank" class="btn btn-secundario btn-sm">
            <i class="bi <?= $doc['categoria']==='cnpj' ? 'bi-card-text' : 'bi-file-earmark-text' ?>"></i>
            <?= h($doc['categoria']==='cnpj' ? 'Cartão CNPJ' : ($doc['categoria']==='contrato' ? 'Contrato' : $doc['categoria'])) ?>
          </a>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <span style="color:var(--cor-secundario);font-size:.9rem">Nenhum documento enviado.<?php if ($is_admin): ?> Use <strong>Editar</strong> para anexar o cartão CNPJ e o contrato.<?php endif; ?></span>
        <?php endif; ?>

        <?php if ($condominio['observacoes']): ?>
        <p style="margin-top:1rem;color:var(--cor-secundario);font-size:.9rem"><strong>Obs.:</strong> <?= h($condominio['observacoes']) ?></p>
        <?php endif; ?>

        <?php else: ?>
        <div style="text-align:center;padding:1.5rem 0">
          <div style="font-size:2.5rem">🏢</div>
          <p style="color:var(--cor-secundario);margin:.5rem 0 1.25rem">Nenhum condomínio vinculado a este imóvel.</p>
          <?php if ($is_admin): ?>
          <a href="<?= base_url('condominios/novo?imovel_id='.$id) ?>" class="btn btn-primario">+ Criar e vincular condomínio</a>

          <?php if ($condominios_lista): ?>
          <div style="margin-top:1.75rem;max-width:420px;margin-left:auto;margin-right:auto;text-align:left">
            <div style="font-size:.82rem;color:var(--cor-secundario);margin-bottom:.4rem">…ou vincular um condomínio já cadastrado:</div>
            <form method="get" action="<?= base_url('condominios/vincular') ?>" style="display:flex;gap:.5rem">
              <input type="hidden" name="imovel_id" value="<?= $id ?>">
              <select name="condominio_id" required style="flex:1">
                <option value="">— selecione —</option>
                <?php foreach ($condominios_lista as $cl): ?>
                <option value="<?= $cl['id'] ?>"><?= h($cl['nome']) ?><?= $cl['cidade'] ? ' (' . h($cl['cidade']) . ')' : '' ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="btn btn-secundario">Vincular</button>
            </form>
          </div>
          <?php endif; ?>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>
<script>
  function abrirModal(id) { const m = document.getElementById(id); if (m) m.style.display = 'flex'; }
  function fecharModal(id) { const m = document.getElementById(id); if (m) m.style.display = 'none'; }

  document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.style.display = 'none'; });
  });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
  });

  (function () {
    const alvo = <?= json_encode($aba_abrir) ?> || (location.hash ? location.hash.substring(1) : '');
    if (alvo) abrirModal('m-' + alvo);
  })();
</script>
<?php require APP_ROOT . '/includes/footer.php'; ?>
