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
        $total_imoveis  = (int) db()->query('SELECT COUNT(*) FROM imoveis WHERE ativo = 1')->fetchColumn();
        $valor_imoveis  = (float) db()->query('SELECT COALESCE(SUM(valor_mercado),0) FROM imoveis WHERE ativo = 1')->fetchColumn();
        $total_contas   = (int) db()->query('SELECT COUNT(*) FROM contas_financeiras WHERE ativo = 1')->fetchColumn();
        $saldo_contas   = (float) db()->query('SELECT COALESCE(SUM(saldo_atual),0) FROM contas_financeiras WHERE ativo = 1')->fetchColumn();

        // Clientes com contagem de imóveis (para os cards)
        $clientes = db()->query(
            'SELECT c.*,
                    (SELECT COUNT(*) FROM imoveis i WHERE i.cliente_id = c.id AND i.ativo = 1) AS qtd_imoveis
               FROM clientes c
              WHERE c.ativo = 1
              ORDER BY c.nome'
        )->fetchAll();

        $this->view('gestao_geral/index', compact(
            'total_clientes', 'total_imoveis', 'valor_imoveis',
            'total_contas', 'saldo_contas', 'clientes'
        ));
    }
}
