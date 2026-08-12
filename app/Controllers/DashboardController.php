<?php
/**
 * Controller do dashboard. Reúne os dados de resumo (admin) ou do cliente
 * e renderiza a view, que reaproveita o header/footer legados.
 */
class DashboardController extends Controller
{
    public function index(): void
    {
        exige_login();
        $usuario = usuario_logado();

        // Cliente em foco: selecionado (admin) ou vinculado ao usuário (cliente)
        $cli = ($usuario['nivel'] === 'admin') ? cliente_selecionado() : null;
        if ($usuario['nivel'] === 'cliente') {
            $stmt = db()->prepare('SELECT * FROM clientes WHERE usuario_id = ? AND ativo = 1');
            $stmt->execute([$usuario['id']]);
            $cli = $stmt->fetch() ?: null;
            if ($cli) {
                selecionar_cliente($cli['id']);
                $cli = cliente_selecionado();
            }
        }

        // Visão gestor × visão cliente: admin sem cliente selecionado vai para a
        // Gestão Geral (consolidado). O Dashboard é sempre a visão de UM cliente.
        if (!$cli) {
            if ($usuario['nivel'] === 'admin') {
                $this->redirect('gestao-geral');
            }
            // Cliente sem registro vinculado: nada a mostrar.
            $this->view('dashboard/index', ['cli' => null, 'pat' => patrimonio_consolidado(0)]);
            return;
        }

        // Patrimônio consolidado do cliente em foco (Módulo 15).
        $pat = patrimonio_consolidado((int) $cli['id']);
        // Resumo de alertas para o badge da Agenda (Módulo 14).
        $alertas = alertas_resumo((int) $cli['id']);
        // Contagem de seguros (Módulo 11) para o badge do app.
        $stmt = db()->prepare('SELECT COUNT(*) FROM seguros WHERE cliente_id = ? AND ativo = 1');
        $stmt->execute([$cli['id']]);
        $qtd_seguros = (int) $stmt->fetchColumn();
        // Contagem de empresas (Módulo 03) para o badge do app.
        $stmt = db()->prepare('SELECT COUNT(*) FROM empresas WHERE cliente_id = ? AND ativo = 1');
        $stmt->execute([$cli['id']]);
        $qtd_empresas = (int) $stmt->fetchColumn();
        // Contagem de investimentos (Módulo 10) para o badge do app.
        $stmt = db()->prepare('SELECT COUNT(*) FROM investimentos WHERE cliente_id = ? AND ativo = 1');
        $stmt->execute([$cli['id']]);
        $qtd_investimentos = (int) $stmt->fetchColumn();
        // Contagem de fornecedores (Módulo 04) para o badge do app.
        $stmt = db()->prepare('SELECT COUNT(*) FROM fornecedores WHERE cliente_id = ? AND ativo = 1');
        $stmt->execute([$cli['id']]);
        $qtd_fornecedores = (int) $stmt->fetchColumn();

        $this->view('dashboard/index', compact('cli', 'pat', 'alertas', 'qtd_seguros', 'qtd_empresas', 'qtd_investimentos', 'qtd_fornecedores'));
    }
}
