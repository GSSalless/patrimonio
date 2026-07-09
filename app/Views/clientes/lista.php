<?php
/**
 * @var array $clientes
 */
$page_title = 'Clientes';
require APP_ROOT . '/includes/header.php';
?>
<div class="container">
  <div class="card-header" style="margin-bottom:1.25rem">
    <h2 class="card-titulo" style="font-size:1.3rem">Clientes</h2>
    <a href="<?= base_url('clientes/novo') ?>" class="btn btn-primario">+ Novo cliente</a>
  </div>

  <div class="card">
    <?php if ($clientes): ?>
    <table class="tabela">
      <thead>
        <tr>
          <th>Nome</th>
          <th>Tipo</th>
          <th>CPF / CNPJ</th>
          <th>E-mail</th>
          <th>Telefone</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($clientes as $c): ?>
        <tr>
          <td><strong><?= h($c['nome']) ?></strong></td>
          <td><span class="tag"><?= $c['tipo_pessoa'] ?></span></td>
          <td><?= h($c['cpf_cnpj']) ?></td>
          <td><?= h($c['email'] ?? '—') ?></td>
          <td><?= h($c['telefone'] ?? '—') ?></td>
          <td style="text-align:right;white-space:nowrap">
            <a href="<?= base_url('dashboard?cliente_id=' . $c['id']) ?>" class="btn btn-secundario btn-sm">Ver patrimônio</a>
            <a href="<?= base_url('clientes/editar?id=' . $c['id']) ?>" class="btn btn-secundario btn-sm">Editar</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
    <p style="color:var(--cor-secundario);text-align:center;padding:2rem">Nenhum cliente cadastrado.</p>
    <?php endif; ?>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
