<?php
/**
 * Gestão Geral — layout Design System CZR (mockup, tela 1).
 * Saudação · faixa de KPIs · evolução (SVG) + donut de composição (SVG) ·
 * tarefas/pendências + relógios mundiais · indicadores por área.
 * (A carteira de clientes saiu daqui na reunião de 01/10/2026 — fica em /clientes.)
 *
 * Todos os valores vêm do banco (patrimônio, lançamentos, agenda). O gráfico
 * de evolução é reconstruído do histórico real (avaliações/saldos/movimentos)
 * pelos últimos 6 meses — ver patrimonio_evolucao() em includes/functions.php.
 *
 * @var array      $usuario
 * @var array      $pat        patrimonio_consolidado()
 * @var array      $ind        indicadores_gestao()
 * @var array      $alertas    alertas_resumo()
 * @var array      $fluxo      ['receitas','despesas'] do mês
 * @var array      $evolucao   [ ['competencia','total'], ... ]
 * @var array      $pendencias tarefas_pendencias()
 * @var float|null $variacao   % vs mês anterior (null se sem histórico)
 */
$page_title = 'Gestão Geral';
require APP_ROOT . '/includes/header.php';

$primeiro_nome = trim(explode(' ', trim($usuario['nome'] ?? 'César'))[0]);
$total_pat = (float) $pat['total'];

// Valor monetário compacto (R$ 1,2 mi) — usado no centro do donut e no eixo.
$moeda_curta = function (float $v): string {
    $abs = abs($v);
    if ($abs >= 1_000_000_000) return 'R$ ' . number_format($v / 1_000_000_000, 1, ',', '.') . ' bi';
    if ($abs >= 1_000_000) return 'R$ ' . number_format($v / 1_000_000, $abs >= 100_000_000 ? 0 : 1, ',', '.') . ' mi';
    if ($abs >= 1_000)     return 'R$ ' . number_format($v / 1_000, 0, ',', '.') . ' mil';
    return moeda($v);
};

// Composição do patrimônio (mesma paleta de tokens).
$comp = [
    ['label' => 'Imóveis',       'valor' => (float) $pat['imoveis_valor'],  'qtd' => (int) $pat['imoveis_qtd'],  'cor' => '#168BFF'],
    ['label' => 'Investimentos', 'valor' => (float) $pat['invest_valor'],   'qtd' => (int) $pat['invest_qtd'],   'cor' => '#22C7F2'],
    ['label' => 'Veículos',      'valor' => (float) $pat['veiculos_valor'], 'qtd' => (int) $pat['veiculos_qtd'], 'cor' => '#F59E0B'],
    ['label' => 'Outros bens',   'valor' => (float) $pat['outros_valor'],   'qtd' => (int) $pat['outros_qtd'],   'cor' => '#A855F7'],
    ['label' => 'Contas',        'valor' => (float) $pat['contas_saldo'],   'qtd' => (int) $pat['contas_qtd'],   'cor' => '#22C55E'],
];

// Delta helper (seta + cor conforme sinal).
$delta_html = function (?float $pct): string {
    if ($pct === null) return '<span class="gg2-kpi-delta gg2-mut">— sem histórico ainda</span>';
    $up = $pct >= 0;
    $ico = $up ? '▲' : '▼';
    $cls = $up ? 'gg2-up' : 'gg2-down';
    return '<span class="gg2-kpi-delta ' . $cls . '">' . $ico . ' '
         . number_format(abs($pct), 1, ',', '.') . '% vs. mês anterior</span>';
};
?>
<div class="container gg2">

  <!-- Saudação -->
  <div class="gg2-hello">
    <div>
      <h2 class="gg2-hi">Olá, <?= h($primeiro_nome) ?>!</h2>
      <p class="gg2-sub">Aqui está o panorama consolidado de toda a operação.</p>
    </div>
    <div class="gg2-quote">“Organização hoje,<br>mais patrimônio amanhã.”</div>
  </div>

  <!-- Faixa de KPIs -->
  <div class="gg2-kpis">
    <div class="gg2-kpi">
      <div class="gg2-kpi-ico" style="color:var(--primary)"><i class="bi bi-safe2"></i></div>
      <div class="gg2-kpi-l">Patrimônio sob gestão</div>
      <div class="gg2-kpi-n"><?= moeda($total_pat) ?></div>
      <?= $delta_html($variacao) ?>
    </div>
    <div class="gg2-kpi">
      <div class="gg2-kpi-ico" style="color:var(--success)"><i class="bi bi-arrow-down-left-circle"></i></div>
      <div class="gg2-kpi-l">Receitas (mês)</div>
      <div class="gg2-kpi-n"><?= moeda((float) $fluxo['receitas']) ?></div>
      <span class="gg2-kpi-delta gg2-mut">lançamentos de <?= h(strftime_pt_mes()) ?></span>
    </div>
    <div class="gg2-kpi">
      <div class="gg2-kpi-ico" style="color:var(--warning)"><i class="bi bi-arrow-up-right-circle"></i></div>
      <div class="gg2-kpi-l">Despesas (mês)</div>
      <div class="gg2-kpi-n"><?= moeda((float) $fluxo['despesas']) ?></div>
      <span class="gg2-kpi-delta gg2-mut">lançamentos de <?= h(strftime_pt_mes()) ?></span>
    </div>
    <a class="gg2-kpi" href="<?= base_url('tarefas') ?>">
      <div class="gg2-kpi-ico" style="color:var(--danger)"><i class="bi bi-exclamation-triangle"></i></div>
      <div class="gg2-kpi-l">Pendências críticas</div>
      <div class="gg2-kpi-n"><?= (int) ($alertas['urgentes'] ?? 0) ?></div>
      <span class="gg2-kpi-delta gg2-mut">requerem sua atenção</span>
    </a>
  </div>

  <!-- Evolução + Donut -->
  <div class="gg2-grid gg2-grid-73">
    <!-- Evolução do patrimônio -->
    <section class="card gg2-panel">
      <div class="gg2-panel-h">
        <h3>Evolução do patrimônio sob gestão</h3>
        <span class="gg2-chip"><?= count($evolucao) ?> <?= count($evolucao) == 1 ? 'mês' : 'meses' ?></span>
      </div>
      <?php
        $vals = $evolucao ? array_column($evolucao, 'total') : [];
        $tem_dado = $vals && max($vals) > 0;
        if ($tem_dado):
        // Geometria do gráfico de área/linha (SVG responsivo por viewBox).
        $W = 720; $H = 240; $padL = 8; $padR = 8; $padT = 16; $padB = 26;
        $max = max($vals); $min = min($vals);
        $span = ($max - $min) > 0 ? ($max - $min) : ($max > 0 ? $max : 1);
        $lo = $min - $span * 0.15; $hi = $max + $span * 0.15;
        $rng = ($hi - $lo) > 0 ? ($hi - $lo) : 1;
        $n = count($evolucao);
        $ix = fn($i) => $padL + ($i / max(1, $n - 1)) * ($W - $padL - $padR);
        $iy = fn($v) => $padT + (1 - (($v - $lo) / $rng)) * ($H - $padT - $padB);
        $pts = [];
        foreach ($evolucao as $i => $e) $pts[] = round($ix($i), 1) . ',' . round($iy($e['total']), 1);
        $linha = implode(' ', $pts);
        $area  = "$padL," . ($H - $padB) . ' ' . $linha . ' ' . ($W - $padR) . ',' . ($H - $padB);
        $ultimo = $evolucao[$n - 1];
      ?>
      <div class="gg2-evol">
        <svg viewBox="0 0 <?= $W ?> <?= $H ?>" class="gg2-evol-svg" preserveAspectRatio="none" role="img"
             aria-label="Evolução do patrimônio">
          <defs>
            <linearGradient id="gEvol" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%"  stop-color="#168BFF" stop-opacity=".35"/>
              <stop offset="100%" stop-color="#168BFF" stop-opacity="0"/>
            </linearGradient>
          </defs>
          <polygon points="<?= h($area) ?>" fill="url(#gEvol)"/>
          <polyline points="<?= h($linha) ?>" fill="none" stroke="#22C7F2" stroke-width="2.5"
                    stroke-linejoin="round" stroke-linecap="round"/>
          <?php foreach ($evolucao as $i => $e): ?>
            <circle cx="<?= round($ix($i),1) ?>" cy="<?= round($iy($e['total']),1) ?>" r="3" fill="#22C7F2"/>
          <?php endforeach; ?>
        </svg>
        <div class="gg2-evol-x">
          <?php foreach ($evolucao as $e): ?>
            <span><?= h(competencia_curta($e['competencia'])) ?></span>
          <?php endforeach; ?>
        </div>
        <div class="gg2-evol-foot">
          Último: <b><?= moeda($ultimo['total']) ?></b> · <?= h(competencia_curta($ultimo['competencia'])) ?>
        </div>
      </div>
      <?php else: ?>
      <div class="gg2-vazio">
        <i class="bi bi-graph-up"></i>
        <p>Ainda não há bens com valor lançado nos últimos 6 meses.<br>
           Conforme os patrimônios forem cadastrados/avaliados, a evolução aparece aqui.</p>
        <?php if ($total_pat > 0): ?><div class="gg2-vazio-n"><?= moeda($total_pat) ?> <span>hoje</span></div><?php endif; ?>
      </div>
      <?php endif; ?>
    </section>

    <!-- Distribuição por categoria (donut) -->
    <section class="card gg2-panel">
      <div class="gg2-panel-h"><h3>Distribuição por categoria</h3></div>
      <?php if ($total_pat > 0):
        $R = 64; $SW = 16; $C = 2 * M_PI * $R; $off = 0; // circunferência e offset acumulado
      ?>
      <div class="gg2-donut">
        <svg viewBox="0 0 160 160" class="gg2-donut-svg" role="img" aria-label="Composição do patrimônio">
          <g transform="translate(80,80) rotate(-90)">
            <circle r="<?= $R ?>" fill="none" stroke="#10233A" stroke-width="<?= $SW ?>"/>
            <?php foreach ($comp as $seg): if ($seg['valor'] <= 0) continue;
              $frac = $seg['valor'] / $total_pat;
              $len  = $frac * $C;
              $dash = round($len, 2) . ' ' . round($C - $len, 2);
              $doff = round(-$off, 2);
              $off += $len;
            ?>
            <circle r="<?= $R ?>" fill="none" stroke="<?= $seg['cor'] ?>" stroke-width="<?= $SW ?>"
                    stroke-dasharray="<?= $dash ?>" stroke-dashoffset="<?= $doff ?>"/>
            <?php endforeach; ?>
          </g>
          <text x="80" y="80" text-anchor="middle" class="gg2-donut-t"><?= h($moeda_curta($total_pat)) ?></text>
          <text x="80" y="97" text-anchor="middle" class="gg2-donut-s">Total</text>
        </svg>
        <div class="gg2-donut-leg">
          <?php foreach ($comp as $seg): if ($seg['valor'] <= 0) continue;
            $pct = round($seg['valor'] / $total_pat * 100, 1); ?>
          <div class="gg2-leg">
            <span class="gg2-leg-dot" style="background:<?= $seg['cor'] ?>"></span>
            <span class="gg2-leg-lab"><?= h($seg['label']) ?></span>
            <span class="gg2-leg-pct"><?= number_format($pct, 1, ',', '.') ?>%</span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php else: ?>
      <div class="gg2-vazio"><i class="bi bi-pie-chart"></i><p>Sem patrimônio cadastrado ainda.</p></div>
      <?php endif; ?>
    </section>
  </div>

  <!-- Tarefas/Pendências + Relógios -->
  <div class="gg2-grid gg2-grid-64">
    <section class="card gg2-panel">
      <div class="gg2-panel-h">
        <h3>Tarefas e Pendências</h3>
        <a class="gg2-vertodas" href="<?= base_url('tarefas') ?>">Ver todas →</a>
      </div>
      <?php if ($pendencias): ?>
      <ul class="gg2-tarefas">
        <?php foreach ($pendencias as $p): ?>
        <li>
          <a href="<?= base_url($p['link']) ?>">
            <span class="gg2-tar-dot gg2-dot-<?= h($p['cor']) ?>"></span>
            <span class="gg2-tar-lab"><?= h($p['rotulo']) ?></span>
            <span class="gg2-tar-n"><?= (int) $p['n'] ?></span>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php else: ?>
      <div class="gg2-vazio gg2-vazio-sm"><i class="bi bi-check2-circle"></i><p>Nenhuma pendência no radar.</p></div>
      <?php endif; ?>
    </section>

    <section class="card gg2-panel">
      <div class="gg2-panel-h"><h3>Relógios mundiais</h3></div>
      <div class="gg2-relogios" id="relogios-mundiais">
        <?php
        $cidades = [
            ['São Paulo', 'America/Sao_Paulo'],
            ['Nova York', 'America/New_York'],
            ['Londres',   'Europe/London'],
            ['Dubai',     'Asia/Dubai'],
        ];
        foreach ($cidades as [$cidade, $tz]): ?>
          <div class="gg2-relogio" data-tz="<?= h($tz) ?>">
            <div class="gg2-rel-cid"><?= h($cidade) ?></div>
            <div class="gg2-rel-hora">--:--</div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>

  <!-- Indicadores por área (Módulo 15) -->
  <div class="gg2-ind">
    <a class="gg2-indcard" href="<?= base_url('contas') ?>">
      <div class="gg2-ind-top"><i class="bi bi-cash-coin"></i> Financeiro</div>
      <div class="gg2-ind-n"><?= moeda((float) ($ind['financeiro']['contas_saldo'] + $ind['financeiro']['invest_valor'])) ?></div>
      <div class="gg2-ind-s"><?= (int) $ind['financeiro']['contas_qtd'] ?> contas · <?= (int) $ind['financeiro']['invest_qtd'] ?> aplicações</div>
    </a>
    <a class="gg2-indcard" href="<?= base_url('colaboradores') ?>">
      <div class="gg2-ind-top"><i class="bi bi-person-badge"></i> RH</div>
      <div class="gg2-ind-n"><?= (int) $ind['rh']['colaboradores'] ?></div>
      <div class="gg2-ind-s"><?php if ($ind['rh']['ferias'] || $ind['rh']['treinamentos']): ?><?= (int) $ind['rh']['ferias'] ?> férias · <?= (int) $ind['rh']['treinamentos'] ?> treino<?php else: ?>colaboradores ativos<?php endif; ?></div>
    </a>
    <a class="gg2-indcard" href="<?= base_url('contratos') ?>">
      <div class="gg2-ind-top"><i class="bi bi-file-earmark-text"></i> Contratos</div>
      <div class="gg2-ind-n"><?= (int) $ind['contratos']['ativos'] ?></div>
      <div class="gg2-ind-s <?= $ind['contratos']['vencendo'] ? 'gg2-warn' : '' ?>"><?= $ind['contratos']['vencendo'] ? (int) $ind['contratos']['vencendo'] . ' vencendo em 30d' : 'ativos' ?></div>
    </a>
    <a class="gg2-indcard" href="<?= base_url('seguros') ?>">
      <div class="gg2-ind-top"><i class="bi bi-shield-check"></i> Seguros</div>
      <div class="gg2-ind-n"><?= (int) $ind['seguros']['vigentes'] ?></div>
      <div class="gg2-ind-s <?= $ind['seguros']['vencendo'] ? 'gg2-warn' : '' ?>"><?= $ind['seguros']['vencendo'] ? (int) $ind['seguros']['vencendo'] . ' vencendo em 30d' : 'vigentes' ?></div>
    </a>
  </div>

</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
