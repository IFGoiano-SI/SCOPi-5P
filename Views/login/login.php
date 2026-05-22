<?php
use Config\Auxiliares;
$flash = Auxiliares::obterFlash();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SCOPi - Login</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/estilo.css">
</head>
<body>
<div class="pagina-login">
  <div class="login-esquerda">
    <div class="login-conteudo-esq">
      <div style="margin-bottom:32px;">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeSCOPi.svg" alt="SCOPi" style="width:200px;height:auto;filter:brightness(0) invert(1);display:block;margin:0 auto;">
      </div>
      <div style="margin-bottom:32px;">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeLogotipo.svg" alt="SCOPi" style="height:50px;filter:brightness(0) invert(1);display:block;margin:0 auto;">
      </div>
      <p class="login-descricao">Sistema de Controle de<br>Ordens e Produto Integrado</p>
    </div>
  </div>

  <div class="login-direita">
    <div class="caixa-login">
      <div class="caixa-login-logo">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeLogotipo.svg" alt="SCOPi">
      </div>

      <h2>Acesso ao Sistema</h2>

      <?php if (!empty($flash)): ?>
        <div class="mensagem-flash flash-<?= $flash['tipo'] ?>" style="margin-bottom:16px;">
          <?= Auxiliares::escapar($flash['texto']) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="<?= BASE_URL ?>/login/entrar">
        <div class="campo-login">
          <label for="email">E-mail</label>
          <div class="input-icone">
            <img src="<?= BASE_URL ?>/public/assets/icons/iconeEmail.svg" alt="">
            <input type="email" id="email" name="email" placeholder="seu@email.com" required autofocus>
          </div>
        </div>

        <div class="campo-login">
          <label for="senha">Senha</label>
          <div class="input-icone">
            <img src="<?= BASE_URL ?>/public/assets/icons/iconeSenha.svg" alt="">
            <input type="password" id="senha" name="senha" placeholder="********" required>
          </div>
        </div>

        <a href="<?= BASE_URL ?>/senha/recuperar" class="esqueci-senha">Esqueci minha senha</a>
        <button type="submit" class="btn-login">Entrar</button>
      </form>
    </div>
  </div>
</div>

<p class="login-copyright">Todos os Direitos Reservados a SCOPi 2026</p>
</body>
</html>
