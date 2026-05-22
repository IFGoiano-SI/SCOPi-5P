<?php
use Config\Auxiliares;
$flash = Auxiliares::obterFlash();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SCOPi - Recuperar Acesso</title>
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

      <h2>Recuperar Acesso</h2>

      <?php if (!empty($flash)): ?>
        <div class="mensagem-flash flash-<?= $flash['tipo'] ?>" style="margin-bottom:16px;">
          <?= Auxiliares::escapar($flash['texto']) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="<?= BASE_URL ?>/senha/recuperar">
        <div class="campo-login">
          <label for="email">E-mail</label>
          <div class="input-icone">
            <img src="<?= BASE_URL ?>/public/assets/icons/iconeEmail.svg" alt="">
            <input type="email" id="email" name="email" placeholder="seu@email.com" required autofocus autocomplete="email">
          </div>
        </div>

        <button type="submit" class="btn-login">Enviar link de recuperacao</button>
      </form>

      <div class="login-links" style="text-align:center;margin-top:16px;">
        <a href="<?= BASE_URL ?>/login" class="esqueci-senha">Voltar ao login</a>
      </div>
    </div>
  </div>
</div>

<p class="login-copyright">Todos os Direitos Reservados a SCOPi 2026</p>
</body>
</html>
