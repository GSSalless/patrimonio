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
            $this->view('dashboard/index', ['cli' => null, 'qtd_imoveis' => 0, 'qtd_contas' => 0]);
            return;
        }

        $stmt = db()->prepare('SELECT COUNT(*) FROM imoveis WHERE cliente_id = ? AND ativo = 1');
        $stmt->execute([$cli['id']]);
        $qtd_imoveis = (int) $stmt->fetchColumn();

        $stmt = db()->prepare('SELECT COUNT(*) FROM contas_financeiras WHERE cliente_id = ? AND ativo = 1');
        $stmt->execute([$cli['id']]);
        $qtd_contas = (int) $stmt->fetchColumn();

        $this->view('dashboard/index', compact('cli', 'qtd_imoveis', 'qtd_contas'));
    }
}
