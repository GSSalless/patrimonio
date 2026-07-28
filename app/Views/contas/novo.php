<?php
/**
 * Cadastro de conta financeira.
 * @var array  $cli
 * @var string $erro
 * @var array  $d
 */
$page_title = 'Cadastrar Conta Financeira';
require APP_ROOT . '/includes/header.php';
?>
<div class="container" style="max-width:960px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <div>
      <h2 class="card-titulo">Cadastrar Conta Financeira</h2>
      <div style="font-size:.85rem;color:var(--cor-secundario)">Cliente: <strong><?= h($cli['nome']) ?></strong></div>
    </div>
    <a href="<?= base_url('contas') ?>" class="btn btn-secundario">← Voltar</a>
  </div>

  <?php if ($erro): ?><div class="alerta alerta-erro"><?= h($erro) ?></div><?php endif; ?>

  <div class="card">
    <form method="post" enctype="multipart/form-data">
      <?php require APP_PATH . '/Views/contas/_campos.php'; ?>

      <!-- SALDO INICIAL (vira o 1º registro do histórico de saldos) -->
      <div class="form-secao"><div class="form-secao-titulo">6. Saldo inicial</div></div>
      <div class="form-grid form-grid-3">
        <div class="form-grupo"><label>Saldo atual (R$)</label><input type="text" name="saldo_inicial" placeholder="0,00" value="<?= h($d['saldo_inicial'] ?? '') ?>"></div>
        <div class="form-grupo"><label>Data do saldo</label><input type="date" name="saldo_inicial_data" value="<?= h($d['saldo_inicial_data'] ?? date('Y-m-d')) ?>"></div>
        <div style="align-self:end;font-size:.8rem;color:var(--cor-secundario);padding-bottom:.6rem">Depois, atualize pelo histórico de saldos na edição.</div>
      </div>

      <!-- DOCUMENTOS -->
      <div class="form-secao"><div class="form-secao-titulo">7. Documentos</div></div>
      <div class="form-grid form-grid-3">
        <div class="form-grupo"><label>📄 Contrato de abertura</label><input type="file" name="doc_contrato" accept=".pdf,.jpg,.jpeg,.png"></div>
        <div class="form-grupo"><label>🧾 Extrato</label><input type="file" name="doc_extrato" accept=".pdf,.jpg,.jpeg,.png"></div>
        <div class="form-grupo"><label>📎 Outros documentos</label><input type="file" name="doc_outros" accept=".pdf,.jpg,.jpeg,.png"></div>
      </div>
      <div style="font-size:.8rem;color:var(--cor-secundario)">Formatos aceitos: PDF, JPG, PNG · Máx. 30 MB por arquivo</div>

      <div class="form-secao" style="margin-top:1.5rem"><div class="form-secao-titulo">8. Observações</div></div>
      <div class="form-grupo">
        <textarea name="observacoes" rows="3" placeholder="Informações adicionais sobre a conta…"><?= h($d['observacoes'] ?? '') ?></textarea>
      </div>

      <div style="display:flex;gap:.75rem;margin-top:2rem;padding-top:1.25rem;border-top:1px solid #dde2ea">
        <button type="submit" class="btn btn-primario">Salvar conta</button>
        <a href="<?= base_url('contas') ?>" class="btn btn-secundario">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
