<?php
/**
 * Controller do hub de patrimônio (categorias de bens do cliente — ou de
 * todos os clientes, no modo gestor).
 */
class PatrimonioController extends Controller
{
    public function index(): void
    {
        exige_login();
        $usuario = usuario_logado();

        $cli = $this->escopoCliente($usuario);   // null = todos os clientes

        $qtd_imoveis  = $this->contar('imoveis', $cli['id'] ?? null);
        $qtd_veiculos = $this->contar('veiculos', $cli['id'] ?? null);
        $qtd_outros   = $this->contar('outros_bens', $cli['id'] ?? null);

        $this->view('patrimonio/index', compact('cli', 'qtd_imoveis', 'qtd_veiculos', 'qtd_outros'));
    }

    /** Conta os bens ativos de um cliente (ou de todos os clientes ativos, se null). */
    private function contar(string $tabela, ?int $clienteId): int
    {
        $sql = "SELECT COUNT(*) FROM {$tabela} t JOIN clientes c ON c.id = t.cliente_id
                 WHERE t.ativo = 1 AND c.ativo = 1";
        $params = [];
        if ($clienteId !== null) { $sql .= ' AND t.cliente_id = ?'; $params[] = $clienteId; }
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}
