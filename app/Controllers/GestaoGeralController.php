<?php
/**
 * Gestão Geral — visão do gestor (César): consolidado de TODOS os clientes.
 * Layout inspirado no Design System CZR (mockup, tela 1): saudação, faixa de
 * KPIs, gráfico de evolução + donut de composição, tarefas/pendências e
 * relógios mundiais, seguido da carteira de clientes.
 *
 * Todos os números vêm do banco (patrimônio, lançamentos, agenda) — sem dados
 * fictícios. O gráfico de evolução lê patrimonio_historico, que é alimentado a
 * cada acesso por patrimonio_snapshot_mensal().
 */
class GestaoGeralController extends Controller
{
    public function index(): void
    {
        exige_admin();

        // Grava o retrato do mês corrente (idempotente) antes de ler a série.
        patrimonio_snapshot_mensal();

        $usuario        = usuario_logado();
        $total_clientes = (int) db()->query('SELECT COUNT(*) FROM clientes WHERE ativo = 1')->fetchColumn();

        // Consolidados (Módulo 15) — patrimônio, indicadores por área, agenda.
        $pat        = patrimonio_consolidado();
        $ind        = indicadores_gestao();
        $alertas    = alertas_resumo();
        $fluxo      = fluxo_mensal();                 // receitas/despesas do mês
        $evolucao   = patrimonio_evolucao(null, 12);  // série mensal consolidada
        $pendencias = tarefas_pendencias();           // blocos de vencimentos

        // Variação do patrimônio vs. mês anterior (só quando há histórico real).
        $variacao = null;
        $n = count($evolucao);
        if ($n >= 2) {
            $ant = $evolucao[$n - 2]['total'];
            $atu = $evolucao[$n - 1]['total'];
            if ($ant > 0) $variacao = ($atu - $ant) / $ant * 100;
        }

        // Clientes com o patrimônio de cada um (para os cards + ordenação).
        $clientes = db()->query(
            'SELECT c.* FROM clientes c WHERE c.ativo = 1 ORDER BY c.nome'
        )->fetchAll();
        foreach ($clientes as &$c) {
            $c['patrimonio'] = patrimonio_consolidado((int) $c['id']);
        }
        unset($c);

        $this->view('gestao_geral/index', compact(
            'usuario', 'total_clientes', 'pat', 'ind', 'alertas',
            'fluxo', 'evolucao', 'pendencias', 'variacao', 'clientes'
        ));
    }
}
