<?php
/**
 * Tela de login — página isolada (não usa header/footer).
 * @var string $erro
 * @var string $email
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CZR Soluções — Acesso</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --bg: #07111F; --surface: #0D1C2B; --sidebar: #081522;
    --primary: #168BFF; --primary-h: #0F6FD6; --secondary: #22C7F2;
    --text: #F5F9FC; --text-2: #94A3B8; --text-3: #64748B;
    --border: #18304A; --danger: #EF4444;
    --sans: 'Inter', -apple-system, 'Segoe UI', Arial, sans-serif;
  }
  body {
    font-family: var(--sans);
    background: radial-gradient(120% 120% at 100% 0%, rgba(34,199,242,.08), transparent 55%), var(--bg);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    color: var(--text);
  }
  .login-wrapper {
    display: flex;
    width: 860px;
    max-width: 98vw;
    min-height: 520px;
    border-radius: 18px;
    overflow: hidden;
    background: var(--surface);
    border: 1px solid var(--border);
    box-shadow: 0 24px 70px rgba(0,0,0,.5);
  }

  /* Painel esquerdo — marca */
  .login-left {
    flex: 1;
    background: linear-gradient(160deg, #0D1C2B 0%, #081522 60%, #071626 100%);
    padding: 48px 40px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
  }
  .login-left::after {
    content: ''; position: absolute; top: 0; right: 0; bottom: 0; width: 1px;
    background: linear-gradient(180deg, transparent, rgba(34,199,242,.5), transparent);
  }
  .brand { display: flex; align-items: center; gap: .75rem; }
  .brand-mark {
    width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 1rem; color: #fff;
    background: linear-gradient(150deg, var(--primary), var(--secondary));
    box-shadow: 0 6px 18px rgba(22,139,255,.4);
  }
  .brand h1 { font-size: 22px; font-weight: 800; color: var(--text); line-height: 1; }
  .brand p { font-size: 11px; color: var(--secondary); margin-top: 4px; letter-spacing: .18em; text-transform: uppercase; font-weight: 600; }

  .left-body h2 { font-size: 24px; font-weight: 700; line-height: 1.35; margin-bottom: 14px; color: var(--text); }
  .left-body > p { font-size: 13px; color: var(--text-2); line-height: 1.6; }
  .feature-list { margin-top: 24px; display: flex; flex-direction: column; gap: 10px; }
  .feature { display: flex; align-items: center; gap: 10px; font-size: 13px; color: var(--text-2); }
  .feature span { font-size: 16px; }
  .left-footer { font-size: 11px; color: var(--text-3); }

  /* Painel direito — formulário */
  .login-right {
    width: 380px;
    flex-shrink: 0;
    background: var(--surface);
    padding: 48px 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }
  .login-right h2 { font-size: 23px; font-weight: 700; color: var(--text); margin-bottom: 6px; }
  .login-right > p { font-size: 13px; color: var(--text-2); margin-bottom: 28px; }

  .field { margin-bottom: 16px; }
  .field label { display: block; font-size: 11.5px; font-weight: 700; color: var(--text-2); text-transform: uppercase; letter-spacing: .6px; margin-bottom: 6px; }
  .field input {
    width: 100%; padding: 11px 14px; border: 1px solid var(--border);
    border-radius: 10px; font-size: 14px; color: var(--text); background: #0A1826;
    font-family: var(--sans);
    outline: none; transition: border .2s, box-shadow .2s;
  }
  .field input::placeholder { color: var(--text-3); }
  .field input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(22,139,255,.18); }

  .forgot { text-align: right; margin-top: -8px; margin-bottom: 20px; }
  .forgot a { font-size: 12px; color: var(--primary); text-decoration: none; }
  .forgot a:hover { text-decoration: underline; }

  .btn-login {
    width: 100%; padding: 13px;
    background: var(--primary);
    color: #fff;
    border: none; border-radius: 10px; font-size: 14.5px; font-weight: 700;
    font-family: var(--sans); letter-spacing: .02em;
    cursor: pointer; transition: background .2s, box-shadow .2s;
    box-shadow: 0 4px 14px rgba(22,139,255,.35);
  }
  .btn-login:hover { background: var(--primary-h); box-shadow: 0 6px 18px rgba(22,139,255,.45); }

  .error-msg {
    background: rgba(239,68,68,.14); border: 1px solid rgba(239,68,68,.4);
    border-radius: 8px; padding: 10px 14px; font-size: 13px; color: var(--danger);
    margin-bottom: 16px;
  }

  @media (max-width: 700px) {
    .login-left { display: none; }
    .login-right { width: 100%; padding: 36px 28px; }
  }
</style>
</head>
<body>

<div class="login-wrapper">
  <!-- ESQUERDA -->
  <div class="login-left">
    <div class="brand">
      <div class="brand-mark">CZR</div>
      <div>
        <h1>CZR Soluções</h1>
        <p>Gestão Patrimonial</p>
      </div>
    </div>
    <div class="left-body">
      <h2>Controle total do seu patrimônio em um só lugar</h2>
      <p>Gerencie imóveis, veículos, investimentos, documentos e fluxo financeiro de forma simples e organizada.</p>
      <div class="feature-list">
        <div class="feature"><span>🏠</span> Cadastro completo de imóveis, veículos e bens</div>
        <div class="feature"><span>💰</span> Controle financeiro e fluxo de caixa</div>
        <div class="feature"><span>📁</span> Documentos e contratos centralizados</div>
        <div class="feature"><span>📊</span> Dashboard com visão patrimonial consolidada</div>
      </div>
    </div>
    <div class="left-footer">© 2026 CZR Soluções · Desenvolvido por Gilson Sales</div>
  </div>

  <!-- DIREITA -->
  <div class="login-right">
    <h2>Bem-vindo de volta</h2>
    <p>Faça login para acessar o sistema</p>

    <?php if (!empty($erro)): ?>
      <div class="error-msg"><?= h($erro) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL ?>index.php?url=login" autocomplete="on">
      <div class="field">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" placeholder="voce@exemplo.com"
               value="<?= h($email ?? '') ?>" required autofocus>
      </div>
      <div class="field">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" placeholder="••••••••" required>
      </div>
      <div class="forgot"><a href="#">Esqueci minha senha</a></div>

      <button type="submit" class="btn-login">Entrar →</button>
    </form>
  </div>
</div>

</body>
</html>
