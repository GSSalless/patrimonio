<?php
/**
 * Controller do hub de patrimônio (categorias de bens do cliente).
 */
class PatrimonioController extends Controller
{
    public function index(): void
    {
        exige_login();
        $usuario = usuario_logado();

        $cli = ($usuario['nivel'] === 'admin') ? cliente_selecionado() : null;
        if ($usuario['nivel'] === 'cliente') {
            $stmt = db()->prepare('SELECT * FROM clientes WHERE usuario_id = ? AND ativo = 1');
            $stmt->execute([$usuario['id']]);
            $cli = $stmt->fetch();
            if ($cli) {
                selecionar_cliente($cli['id']);
                $cli = cliente_selecionado();
            }
        }
        if (!$cli) $this->redirect('dashboard');

        $qtd_imoveis  = $this->contar('imoveis', $cli['id']);
        $qtd_veiculos = $this->contar('veiculos', $cli['id']);
        $qtd_outros   = $this->contar('outros_bens', $cli['id']);

        $this->view('patrimonio/index', compact('cli', 'qtd_imoveis', 'qtd_veiculos', 'qtd_outros'));
    }

    private function contar(string $tabela, int $clienteId): int
    {
        $stmt = db()->prepare("SELECT COUNT(*) FROM {$tabela} WHERE cliente_id = ? AND ativo = 1");
        $stmt->execute([$clienteId]);
        return (int) $stmt->fetchColumn();
    }
}
