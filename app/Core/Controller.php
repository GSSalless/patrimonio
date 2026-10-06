<?php
/**
 * Controller base. Oferece helpers de renderização de view e redirecionamento.
 */
abstract class Controller
{
    /** Renderiza uma view de app/Views/{view}.php com os dados fornecidos. */
    protected function view(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    /**
     * Escopo das LISTAGENS — "mesmo menu, dois modos" (reunião 01/10/2026):
     *  - admin com cliente selecionado → esse cliente;
     *  - admin sem cliente             → null = todos os clientes (modo gestor);
     *  - usuário cliente               → sempre o próprio cadastro.
     * Telas de cadastro/edição continuam exigindo um cliente (clienteEmContexto).
     */
    protected function escopoCliente(array $usuario): ?array
    {
        if ($usuario['nivel'] === 'admin') {
            return cliente_selecionado();
        }
        $s = db()->prepare('SELECT * FROM clientes WHERE usuario_id = ? AND ativo = 1');
        $s->execute([$usuario['id']]);
        $cli = $s->fetch();
        if (!$cli) $this->redirect('login');
        return $cli;
    }

    /** Redireciona para um caminho relativo à BASE_URL. */
    protected function redirect(string $path): never
    {
        header('Location: ' . BASE_URL . ltrim($path, '/'));
        exit;
    }
}
