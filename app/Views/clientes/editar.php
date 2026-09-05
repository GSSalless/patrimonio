<?php
/**
 * Cadastro completo da pessoa (Módulo 01).
 * @var array  $cliente
 * @var string $erro
 * @var array  $subs        ['telefones'=>[], 'emails'=>[], 'enderecos'=>[], 'familiares'=>[], 'emergencia'=>[]]
 * @var array  $documentos
 */
$page_title = 'Cadastro — ' . $cliente['nome'];
require APP_ROOT . '/includes/header.php';

$nomeExib = $cliente['nome_completo'] ?: $cliente['nome'];
$endParts = array_filter([
    trim(($cliente['logradouro'] ?? '') . ' ' . ($cliente['numero'] ?? '')),
    $cliente['bairro'] ?? '',
    trim(($cliente['cidade'] ?? '') . (($cliente['uf'] ?? '') ? '/' . $cliente['uf'] : '')),
]);
$wa = link_whatsapp($cliente['telefone'] ?? '');
$del = fn($tipo, $itemId) => base_url("clientes/item-remover?tipo=$tipo&item=$itemId&id=" . $cliente['id']);
?>
<div class="container" style="max-width:960px">
  <div class="card-header" style="margin-bottom:1rem">
    <a href="<?= base_url('clientes') ?>" class="btn btn-secundario">← Voltar</a>
    <a href="<?= base_url('dashboard?cliente_id=' . $cliente['id']) ?>" class="btn btn-secundario">Ver patrimônio →</a>
  </div>

  <!-- Resumo no topo: nome completo + CPF + endereço + WhatsApp -->
  <div class="card" style="margin-bottom:1.25rem">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap">
      <div>
        <div style="font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700"><?= h($nomeExib) ?></div>
        <div style="color:var(--cor-secundario);margin-top:.25rem">
          <span class="tag"><?= $cliente['tipo_pessoa'] ?></span>
          <?= $cliente['tipo_pessoa'] === 'PF' ? 'CPF' : 'CNPJ' ?>: <strong><?= h($cliente['cpf_cnpj']) ?></strong>
        </div>
        <?php if ($endParts): ?>
          <div style="color:var(--cor-secundario);margin-top:.35rem">
            <i class="bi bi-geo-alt"></i> <?= h(implode(' · ', $endParts)) ?>
          </div>
        <?php endif; ?>
        <?php if ($cliente['telefone']): ?>
          <div style="color:var(--cor-secundario);margin-top:.2rem"><i class="bi bi-telephone"></i> <?= h($cliente['telefone']) ?></div>
        <?php endif; ?>
      </div>
      <?php if ($wa): ?>
        <a href="<?= h($wa) ?>" target="_blank" rel="noopener"
           class="btn" style="background:#25d366;color:#fff;border:none;white-space:nowrap">
          <i class="bi bi-whatsapp"></i> WhatsApp
        </a>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>

  <!-- ===== DADOS ===== -->
  <div class="card" id="dados" style="margin-bottom:1.25rem">
    <h3 style="margin-top:0">Dados da pessoa</h3>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="secao" value="dados">
      <?php require __DIR__ . '/_campos.php'; ?>
      <div style="margin-top:1.25rem">
        <button type="submit" class="btn btn-primario">Salvar dados</button>
      </div>
    </form>
  </div>

  <!-- ===== TELEFONES ===== -->
  <div class="card" id="telefones" style="margin-bottom:1.25rem">
    <h3 style="margin-top:0"><i class="bi bi-telephone"></i> Telefones adicionais</h3>
    <?php if ($subs['telefones']): ?>
      <table class="tabela">
        <thead><tr><th>Rótulo</th><th>Número</th><th>WhatsApp</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($subs['telefones'] as $t): ?>
          <tr>
            <td><?= h($t['rotulo'] ?? '—') ?></td>
            <td><?= h($t['numero']) ?></td>
            <td><?= $t['whatsapp'] ? '<i class="bi bi-whatsapp" style="color:#25d366"></i> sim' : '—' ?></td>
            <td style="text-align:right"><a href="<?= $del('telefones', $t['id']) ?>" class="btn btn-secundario btn-sm" onclick="return confirmar_exclusao('Remover este telefone?')">Excluir</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?><p style="color:var(--cor-secundario)">Nenhum telefone adicional.</p><?php endif; ?>
    <form method="post" class="form-grid form-grid-2" style="margin-top:.75rem;align-items:end">
      <input type="hidden" name="secao" value="telefones">
      <div class="form-grupo"><label>Rótulo</label><input type="text" name="rotulo" placeholder="Comercial, Recado…"></div>
      <div class="form-grupo"><label>Número *</label><input type="text" name="numero" required placeholder="(11) 90000-0000"></div>
      <div class="form-grupo"><label style="display:flex;align-items:center;gap:.4rem;cursor:pointer"><input type="checkbox" name="whatsapp" value="1" style="width:auto"> É WhatsApp</label></div>
      <div class="form-grupo"><button type="submit" class="btn btn-primario">+ Adicionar</button></div>
    </form>
  </div>

  <!-- ===== E-MAILS ===== -->
  <div class="card" id="emails" style="margin-bottom:1.25rem">
    <h3 style="margin-top:0"><i class="bi bi-envelope"></i> E-mails adicionais</h3>
    <?php if ($subs['emails']): ?>
      <table class="tabela">
        <thead><tr><th>Rótulo</th><th>E-mail</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($subs['emails'] as $e): ?>
          <tr>
            <td><?= h($e['rotulo'] ?? '—') ?></td>
            <td><?= h($e['email']) ?></td>
            <td style="text-align:right"><a href="<?= $del('emails', $e['id']) ?>" class="btn btn-secundario btn-sm" onclick="return confirmar_exclusao('Remover este e-mail?')">Excluir</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?><p style="color:var(--cor-secundario)">Nenhum e-mail adicional.</p><?php endif; ?>
    <form method="post" class="form-grid form-grid-2" style="margin-top:.75rem;align-items:end">
      <input type="hidden" name="secao" value="emails">
      <div class="form-grupo"><label>Rótulo</label><input type="text" name="rotulo" placeholder="Pessoal, Comercial…"></div>
      <div class="form-grupo"><label>E-mail *</label><input type="email" name="email" required></div>
      <div class="form-grupo"><button type="submit" class="btn btn-primario">+ Adicionar</button></div>
    </form>
  </div>

  <!-- ===== ENDEREÇOS ===== -->
  <div class="card" id="enderecos" style="margin-bottom:1.25rem">
    <h3 style="margin-top:0"><i class="bi bi-geo-alt"></i> Endereços adicionais</h3>
    <?php if ($subs['enderecos']): ?>
      <table class="tabela">
        <thead><tr><th>Rótulo</th><th>Endereço</th><th>Cidade/UF</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($subs['enderecos'] as $en):
          $linha = trim(($en['logradouro'] ?? '') . ' ' . ($en['numero'] ?? '') . ' ' . ($en['complemento'] ?? '')); ?>
          <tr>
            <td><?= h($en['rotulo'] ?? '—') ?></td>
            <td><?= h($linha ?: '—') ?><?= $en['bairro'] ? ' — ' . h($en['bairro']) : '' ?></td>
            <td><?= h(trim(($en['cidade'] ?? '') . (($en['uf'] ?? '') ? '/' . $en['uf'] : ''))) ?: '—' ?></td>
            <td style="text-align:right"><a href="<?= $del('enderecos', $en['id']) ?>" class="btn btn-secundario btn-sm" onclick="return confirmar_exclusao('Remover este endereço?')">Excluir</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?><p style="color:var(--cor-secundario)">Nenhum endereço adicional.</p><?php endif; ?>
    <form method="post" style="margin-top:.75rem">
      <input type="hidden" name="secao" value="enderecos">
      <div class="form-grid form-grid-2">
        <div class="form-grupo"><label>Rótulo</label><input type="text" name="rotulo" placeholder="Comercial, Praia…"></div>
        <div class="form-grupo"><label>CEP</label><input type="text" name="cep" maxlength="9" oninput="mascara_cep(this)"></div>
        <div class="form-grupo" style="grid-column:1/-1"><label>Logradouro</label><input type="text" name="logradouro"></div>
        <div class="form-grupo"><label>Número</label><input type="text" name="numero"></div>
        <div class="form-grupo"><label>Complemento</label><input type="text" name="complemento"></div>
        <div class="form-grupo"><label>Bairro</label><input type="text" name="bairro"></div>
        <div class="form-grupo"><label>Cidade</label><input type="text" name="cidade"></div>
        <div class="form-grupo"><label>UF</label><input type="text" name="uf" maxlength="2" style="text-transform:uppercase"></div>
      </div>
      <div style="margin-top:.75rem"><button type="submit" class="btn btn-primario">+ Adicionar endereço</button></div>
    </form>
  </div>

  <!-- ===== FAMÍLIA / SUCESSÃO ===== -->
  <div class="card" id="familiares" style="margin-bottom:1.25rem">
    <h3 style="margin-top:0"><i class="bi bi-people"></i> Família e sucessão</h3>
    <?php if ($subs['familiares']): ?>
      <table class="tabela">
        <thead><tr><th>Parentesco</th><th>Nome</th><th>CPF</th><th>Nascimento</th><th>Contato</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($subs['familiares'] as $f): ?>
          <tr>
            <td><span class="tag"><?= h(parentesco_label($f['parentesco'])) ?></span></td>
            <td><?= h($f['nome']) ?><?= $f['observacoes'] ? '<br><small style="color:var(--cor-secundario)">' . h($f['observacoes']) . '</small>' : '' ?></td>
            <td><?= h($f['cpf'] ?? '—') ?></td>
            <td><?= data_br($f['data_nascimento']) ?></td>
            <td><?= h($f['contato'] ?? '—') ?></td>
            <td style="text-align:right"><a href="<?= $del('familiares', $f['id']) ?>" class="btn btn-secundario btn-sm" onclick="return confirmar_exclusao('Remover este familiar?')">Excluir</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?><p style="color:var(--cor-secundario)">Nenhum familiar cadastrado.</p><?php endif; ?>
    <form method="post" style="margin-top:.75rem">
      <input type="hidden" name="secao" value="familiares">
      <div class="form-grid form-grid-2">
        <div class="form-grupo">
          <label>Parentesco *</label>
          <select name="parentesco">
            <?php foreach (['conjuge'=>'Cônjuge','ex_conjuge'=>'Ex-cônjuge','pai'=>'Pai','mae'=>'Mãe','filho'=>'Filho(a)','dependente'=>'Dependente','herdeiro'=>'Herdeiro(a)','irmao'=>'Irmão(ã)','neto'=>'Neto(a)','outro'=>'Outro'] as $k=>$lbl): ?>
              <option value="<?= $k ?>"><?= $lbl ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo"><label>Nome *</label><input type="text" name="nome" required></div>
        <div class="form-grupo"><label>CPF</label><input type="text" name="cpf" oninput="mascara_cpf_cnpj(this)" maxlength="18"></div>
        <div class="form-grupo"><label>Nascimento</label><input type="date" name="data_nascimento"></div>
        <div class="form-grupo"><label>Contato</label><input type="text" name="contato" placeholder="telefone/e-mail"></div>
        <div class="form-grupo"><label>Observações</label><input type="text" name="observacoes"></div>
      </div>
      <div style="margin-top:.75rem"><button type="submit" class="btn btn-primario">+ Adicionar familiar</button></div>
    </form>
  </div>

  <!-- ===== CONTATOS DE EMERGÊNCIA ===== -->
  <div class="card" id="emergencia" style="margin-bottom:1.25rem">
    <h3 style="margin-top:0"><i class="bi bi-exclamation-triangle"></i> Contatos de emergência</h3>
    <?php if ($subs['emergencia']): ?>
      <table class="tabela">
        <thead><tr><th>Nome</th><th>Parentesco</th><th>Telefone</th><th>Obs.</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($subs['emergencia'] as $c): ?>
          <tr>
            <td><?= h($c['nome']) ?></td>
            <td><?= h($c['parentesco'] ?? '—') ?></td>
            <td><?= h($c['telefone'] ?? '—') ?></td>
            <td><?= h($c['observacoes'] ?? '—') ?></td>
            <td style="text-align:right"><a href="<?= $del('emergencia', $c['id']) ?>" class="btn btn-secundario btn-sm" onclick="return confirmar_exclusao('Remover este contato?')">Excluir</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?><p style="color:var(--cor-secundario)">Nenhum contato de emergência.</p><?php endif; ?>
    <form method="post" class="form-grid form-grid-2" style="margin-top:.75rem;align-items:end">
      <input type="hidden" name="secao" value="emergencia">
      <div class="form-grupo"><label>Nome *</label><input type="text" name="nome" required></div>
      <div class="form-grupo"><label>Parentesco</label><input type="text" name="parentesco"></div>
      <div class="form-grupo"><label>Telefone</label><input type="text" name="telefone"></div>
      <div class="form-grupo"><label>Observações</label><input type="text" name="observacoes"></div>
      <div class="form-grupo"><button type="submit" class="btn btn-primario">+ Adicionar</button></div>
    </form>
  </div>

  <!-- ===== ACESSO DO CLIENTE ===== -->
  <div class="card" id="acesso" style="margin-bottom:1.25rem">
    <h3 style="margin-top:0"><i class="bi bi-key"></i> Acesso do cliente</h3>
    <p style="color:var(--cor-secundario);margin-top:0">
      Cria (ou redefine) o login para o cliente acessar a própria área — vê apenas o patrimônio dele,
      em modo somente leitura. Endereço de login: <code><?= h(base_url('login')) ?></code>.
    </p>
    <?php if (!empty($login)): ?>
      <p style="margin:.25rem 0 1rem">
        <span class="tag" style="background:#0f2e1a;color:#7be0a3">Acesso ativo</span>
        &nbsp;E-mail atual: <strong><?= h($login['email']) ?></strong>
        <?php if (empty($login['ativo'])): ?><span class="tag" style="background:#5c1a1a;color:#f0a3a3">inativo</span><?php endif; ?>
      </p>
    <?php else: ?>
      <p style="margin:.25rem 0 1rem">
        <span class="tag" style="background:#4a3a10;color:#e6c15a">Sem acesso</span>
        &nbsp;Este cliente ainda não tem login.
      </p>
    <?php endif; ?>
    <form method="post" class="form-grid form-grid-2" style="align-items:end">
      <input type="hidden" name="secao" value="acesso">
      <div class="form-grupo">
        <label>E-mail de acesso</label>
        <input type="email" name="acesso_email" value="<?= h($login['email'] ?? $cliente['email'] ?? '') ?>"
               placeholder="email@dominio.com" autocomplete="off">
      </div>
      <div class="form-grupo">
        <label><?= !empty($login) ? 'Nova senha (deixe em branco p/ manter)' : 'Senha (mín. 8 caracteres)' ?></label>
        <input type="text" name="acesso_senha" placeholder="mín. 8 caracteres" autocomplete="off">
      </div>
      <div class="form-grupo">
        <button type="submit" class="btn btn-primario">
          <?= !empty($login) ? 'Salvar / Redefinir senha' : 'Criar acesso' ?>
        </button>
      </div>
    </form>
  </div>

  <!-- ===== DOCUMENTOS ===== -->
  <div class="card" id="documentos" style="margin-bottom:2rem">
    <h3 style="margin-top:0"><i class="bi bi-folder2-open"></i> Documentos da pessoa</h3>
    <?php if ($documentos): ?>
      <table class="tabela">
        <thead><tr><th>Categoria</th><th>Arquivo</th><th>Enviado em</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($documentos as $d): ?>
          <tr>
            <td><span class="tag"><?= h($d['categoria']) ?></span></td>
            <td><?= h($d['nome_arquivo']) ?></td>
            <td><?= data_br($d['criado_em']) ?></td>
            <td style="text-align:right"><a href="<?= url_documento($d) ?>" target="_blank" rel="noopener" class="btn btn-secundario btn-sm">Abrir</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p style="color:var(--cor-secundario)">Nenhum documento. O testamento pode ser anexado na seção “Dados da pessoa”.</p>
    <?php endif; ?>
  </div>

</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
