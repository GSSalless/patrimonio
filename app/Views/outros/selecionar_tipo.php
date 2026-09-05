<?php
/**
 * Etapa 1 do cadastro: seletor de tipo de bem.
 * @var array $cli
 */
$page_title = 'Cadastrar Bem';
require APP_ROOT . '/includes/header.php';
?>
<div class="container" style="max-width:700px">
  <div class="card-header" style="margin-bottom:1.25rem">
    <div>
      <h2 class="card-titulo">Cadastrar Bem</h2>
      <div style="font-size:.85rem;color:var(--cor-secundario)">Cliente: <strong><?= h($cli['nome']) ?></strong></div>
    </div>
    <a href="<?= base_url('outros') ?>" class="btn btn-secundario">← Voltar</a>
  </div>

  <div class="card" style="padding:2rem">
    <p style="color:var(--cor-secundario);margin-bottom:1.5rem;font-size:.95rem">Escolha o tipo de bem que deseja cadastrar:</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1rem">
      <?php
      $opcoes = [
        'embarcacao'  => ['🛥️', 'Embarcação', 'Jet ski, lancha, barco, veleiro…'],
        'joia'        => ['💎', 'Joia',        'Anel, colar, relógio, pulseira…'],
        'obra_de_arte'=> ['🖼️', 'Obra de Arte','Pintura, escultura, fotografia…'],
        'outro'       => ['📦', 'Outro',       'Qualquer outro bem de valor'],
      ];
      foreach ($opcoes as $val => [$icon, $label, $desc]): ?>
      <a href="<?= base_url('outros/novo?tipo=' . $val) ?>" class="card" style="text-align:center;padding:1.5rem 1rem;text-decoration:none;transition:border-color .2s,box-shadow .2s;cursor:pointer;border:2px solid var(--cor-borda)"
         onmouseover="this.style.borderColor='#1a6fba';this.style.boxShadow='0 4px 12px rgba(26,111,186,.15)'"
         onmouseout="this.style.borderColor='var(--cor-borda)';this.style.boxShadow=''">
        <div style="font-size:2.5rem;margin-bottom:.5rem"><?= $icon ?></div>
        <div style="font-weight:700;color:var(--cor-primaria);margin-bottom:.25rem"><?= $label ?></div>
        <div style="font-size:.78rem;color:var(--cor-secundario)"><?= $desc ?></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
