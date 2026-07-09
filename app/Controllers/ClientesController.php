<?php
/**
 * Controller de clientes (acesso restrito a admin).
 * Métodos que tratam GET e POST espelham o comportamento das telas legadas.
 */
class ClientesController extends Controller
{
    /** GET clientes — lista. */
    public function index(): void
    {
        exige_admin();
        $clientes = Cliente::listarAtivos();
        $this->view('clientes/lista', compact('clientes'));
    }

    /** GET/POST clientes/novo — cadastro. */
    public function novo(): void
    {
        exige_admin();
        $erro = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome     = trim($_POST['nome'] ?? '');
            $tipo     = $_POST['tipo_pessoa'] ?? 'PF';
            $cpf_cnpj = preg_replace('/\D/', '', $_POST['cpf_cnpj'] ?? '');
            $email    = trim($_POST['email'] ?? '') ?: null;
            $telefone = trim($_POST['telefone'] ?? '') ?: null;
            $senha    = trim($_POST['senha'] ?? '');

            if (!$nome || !$cpf_cnpj) {
                $erro = 'Nome e CPF/CNPJ são obrigatórios.';
            } else {
                $usuario_id = ($email && $senha)
                    ? Cliente::criarUsuarioLogin($nome, $email, $senha)
                    : null;
                Cliente::criar([
                    'usuario_id'  => $usuario_id,
                    'tipo_pessoa' => $tipo,
                    'nome'        => $nome,
                    'cpf_cnpj'    => Cliente::formatarDocumento($cpf_cnpj, $tipo),
                    'email'       => $email,
                    'telefone'    => $telefone,
                ]);
                $this->redirect('clientes');
            }
        }

        $this->view('clientes/novo', compact('erro'));
    }

    /** GET/POST clientes/editar?id= — edição. */
    public function editar(): void
    {
        exige_admin();
        $id = (int) ($_GET['id'] ?? 0);
        $cliente = Cliente::buscar($id);
        if (!$cliente) {
            $this->redirect('clientes');
        }

        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome     = trim($_POST['nome'] ?? '');
            $tipo     = $_POST['tipo_pessoa'] ?? 'PF';
            $cpf_cnpj = trim($_POST['cpf_cnpj'] ?? '');
            $email    = trim($_POST['email'] ?? '') ?: null;
            $telefone = trim($_POST['telefone'] ?? '') ?: null;

            if (!$nome || !$cpf_cnpj) {
                $erro = 'Nome e CPF/CNPJ são obrigatórios.';
            } else {
                Cliente::atualizar($id, [
                    'tipo_pessoa' => $tipo,
                    'nome'        => $nome,
                    'cpf_cnpj'    => $cpf_cnpj,
                    'email'       => $email,
                    'telefone'    => $telefone,
                ]);
                $this->redirect('clientes');
            }
        }

        $this->view('clientes/editar', compact('cliente', 'erro'));
    }
}
