<?php
/**
 * View de login (MVC). Visual "PatriControl" ligado ao backend real:
 * o formulário faz POST para o front controller, que chama AuthController::login().
 *
 * @var string $erro   Mensagem de erro (vazia se não houver)
 * @var string $email  E-mail preenchido (para repopular o campo)
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PatriControl — Acesso</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --preto: #0e0e10; --preto-2: #1b1b1f;
    --dourado: #b8912f; --dourado-claro: #d4af37;
    --marfim: #f6f4ee;
    --serif: 'Playfair Display', Georgia, serif;
    --sans: 'Inter', 'Segoe UI', Arial, sans-serif;
  }
  body {
    font-family: var(--sans);
    background: var(--marfim);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
  }
  .login-wrapper {
    display: flex;
    width: 860px;
    max-width: 98vw;
    min-height: 520px;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid rgba(212,175,55,.25);
    box-shadow: 0 24px 70px rgba(0,0,0,.28);
  }
  .login-left {
    flex: 1;
    background: linear-gradient(160deg, #16161a 0%, var(--preto) 60%, #241f14 100%);
    padding: 48px 40px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    color: #f4f1e8;
    position: relative;
  }
  .login-left::after {
    content: ''; position: absolute; top: 0; right: 0; bottom: 0; width: 1px;
    background: linear-gradient(180deg, transparent, rgba(212,175,55,.5), transparent);
  }
  .brand h1 { font-family: var(--serif); font-size: 27px; font-weight: 800; color: var(--dourado-claro); letter-spacing: .3px; }
  .brand p { font-size: 13px; color: rgba(244,241,232,.5); margin-top: 4px; letter-spacing: .04em; }
  .left-body h2 { font-family: var(--serif); font-size: 24px; font-weight: 700; line-height: 1.35; margin-bottom: 14px; color: #f4f1e8; }
  .left-body p { font-size: 13px; color: rgba(244,241,232,.62); line-height: 1.6; }
  .feature-list { margin-top: 24px; display: flex; flex-direction: column; gap: 10px; }
  .feature { display: flex; align-items: center; gap: 10px; font-size: 13px; color: rgba(244,241,232,.8); }
  .feature span { font-size: 16px; }
  .left-footer { font-size: 11px; color: rgba(244,241,232,.3); }

  .login-right {
    width: 380px;
    flex-shrink: 0;
    background: #fff;
    padding: 48px 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }
  .login-right h2 { font-family: var(--serif); font-size: 23px; font-weight: 700; color: var(--preto); margin-bottom: 6px; }
  .login-right > p { font-size: 13px; color: #7a7361; margin-bottom: 28px; }

  .field { margin-bottom: 16px; }
  .field label { display: block; font-size: 11.5px; font-weight: 700; color: #7a7361; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 6px; }
  .field input {
    width: 100%; padding: 11px 14px; border: 1.5px solid #e7e0d0;
    border-radius: 8px; font-size: 14px; color: #1c1a17; background: #fffdf9;
    font-family: var(--sans);
    outline: none; transition: border .2s, box-shadow .2s;
  }
  .field input:focus { border-color: var(--dourado); box-shadow: 0 0 0 3px rgba(184,145,47,.15); background: #fff; }

  .forgot { text-align: right; margin-top: -8px; margin-bottom: 20px; }
  .forgot a { font-size: 12px; color: var(--dourado); text-decoration: none; }
  .forgot a:hover { text-decoration: underline; }

  .btn-login {
    width: 100%; padding: 13px;
    background: linear-gradient(180deg, #d4af37 0%, var(--dourado) 100%);
    color: #201a08;
    border: none; border-radius: 8px; font-size: 14.5px; font-weight: 700;
    font-family: var(--sans); letter-spacing: .02em;
    cursor: pointer; transition: filter .2s, box-shadow .2s;
    box-shadow: 0 4px 14px rgba(184,145,47,.35);
  }
  .btn-login:hover { filter: brightness(1.06); box-shadow: 0 6px 18px rgba(184,145,47,.45); }

  .error-msg {
    background: #f8e7e7; border: 1px solid #e6b8b8;
    border-radius: 7px; padding: 10px 14px; font-size: 13px; color: #b23b3b;
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
      <h1>🏛 PatriControl</h1>
      <p>Plataforma de Gestão Patrimonial</p>
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
    <div class="left-footer">© 2026 PatriControl · Desenvolvido por Gilson Sales</div>
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
