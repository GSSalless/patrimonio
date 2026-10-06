<?php
/**
 * Gestão Geral — visão do gestor (César): consolidado de TODOS os clientes.
 * Layout inspirado no Design System CZR (mockup, tela 1): saudação, faixa de
 * KPIs, gráfico de evolução + donut de composição, tarefas/pendências e
 * relógios mundiais e indicadores por área. A carteira de clientes fica em
 * /clientes (reunião 01/10/2026).
 *
 * Todos os números vêm do banco (patrimônio, lançamentos, agenda) — sem dados
 * fictícios. O gráfico de evolução é reconstruído do histórico real do banco
 * (avaliações, saldos e movimentos) pelos últimos 6 meses — ver
 * patrimonio_evolucao()/patrimonio_total_em_data() em includes/functions.php.
 */
class GestaoGeralController extends Controller
{
    public function index(): void
    {
        exige_admin();
        // Mesmo botão "Dashboard", dois modos: com cliente selecionado o painel
        // é o do cliente; a visão consolidada é a do modo gestor (sem cliente).
        if (cliente_selecionado()) $this->redirect('dashboard');

        $usuario        = usuario_logado();

        // Consolidados (Módulo 15) — patrimônio, indicadores por área, agenda.
        $pat        = patrimonio_consolidado();
        $ind        = indicadores_gestao();
        $alertas    = alertas_resumo();
        $fluxo      = fluxo_mensal();                 // receitas/despesas do mês
        $evolucao   = patrimonio_evolucao(null, 6);   // últimos 6 meses (todos os clientes)
        $pendencias = tarefas_pendencias();           // blocos de vencimentos

        // Variação do patrimônio vs. mês anterior (só quando há histórico real).
        $variacao = null;
        $n = count($evolucao);
        if ($n >= 2) {
            $ant = $evolucao[$n - 2]['total'];
            $atu = $evolucao[$n - 1]['total'];
            if ($ant > 0) $variacao = ($atu - $ant) / $ant * 100;
        }

        $this->view('gestao_geral/index', compact(
            'usuario', 'pat', 'ind', 'alertas',
            'fluxo', 'evolucao', 'pendencias', 'variacao'
        ));
    }
}
