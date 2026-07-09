<?php
/**
 * Controller de autenticação. Usa a função login() de includes/auth.php,
 * garantindo que a sessão criada seja compatível com o header/footer e o
 * controle de acesso (exige_login / exige_admin).
 */
class AuthController extends Controller
{
    /** GET / e GET /login — exibe a tela de login. */
    public function showLogin(): void
    {
        if (usuario_logado()) {
            $this->redirect('dashboard');
        }
        $this->view('auth/login', ['erro' => '', 'email' => '']);
    }

    /** POST /login — processa a autenticação. */
    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if (login($email, $senha)) {
            $this->redirect('dashboard');
        }

        $this->view('auth/login', [
            'erro'  => 'E-mail ou senha incorretos.',
            'email' => $email,
        ]);
    }

    /** GET /logout — encerra a sessão. */
    public function logout(): void
    {
        logout();
    }
}
