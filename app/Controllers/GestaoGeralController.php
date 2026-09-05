<?php
/**
 * Gestão Geral — visão do gestor (César): consolidado de TODOS os clientes.
 * Separado do Dashboard, que passa a ser sempre a visão de UM cliente.
 * (Primeira versão: KPIs de patrimônio + grade de clientes. Caixa geral e
 *  tarefas multi-cliente entram na próxima fatia do R1.)
 */
class GestaoGeralController extends Controller
{
    public function index(): void
    {
        exige_admin();

        $total_clientes = (int) db()->query('SELECT COUNT(*) FROM clientes WHERE ativo = 1')->fetchColumn();

        // Patrimônio consolidado de TODOS os clientes (Módulo 15).
        $pat = patrimonio_consolidado();
        // Resumo de alertas de todos os clientes (Módulo 14).
        $alertas = alertas_resumo();

        // Clientes com o patrimônio de cada um (para os cards + ordenação por valor).
        $clientes = db()->query(
            'SELECT c.*,
                    (SELECT COUNT(*) FROM imoveis i WHERE i.cliente_id = c.id AND i.ativo = 1) AS qtd_imoveis
               FROM clientes c
              WHERE c.ativo = 1
              ORDER BY c.nome'
        )->fetchAll();
        foreach ($clientes as &$c) {
            $c['patrimonio'] = patrimonio_consolidado((int) $c['id']);
        }
        unset($c);

        $this->view('gestao_geral/index', compact('total_clientes', 'pat', 'alertas', 'clientes'));
    }
}
