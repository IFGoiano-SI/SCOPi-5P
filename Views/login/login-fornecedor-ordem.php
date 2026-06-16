<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SCOPi - Revisar Ordem de Compra</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/estilo.css">
</head>
<body>
<div class="pagina-login">
  <div class="login-esquerda">
    <div class="login-logo-area">
      <img src="<?= BASE_URL ?>/public/assets/icons/iconeLogotipo.svg" alt="SCOPi" style="width:100px; height:auto; margin-bottom:15px; filter:brightness(0) invert(1);">
      <p style="color:var(--branco); font-size:1.1rem; font-weight:300;">Portal de Compras para Fornecedores</p>
    </div>
  </div>

  <div class="login-direita">
    <div class="caixa-login">
      <h2>Acesso à Ordem de Compra</h2>
      <p class="subtitulo-login">Digite o CNPJ da sua empresa para confirmar o acesso e revisar a Ordem de Compra.</p>

      <?php
      $flash = \Config\Auxiliares::obterFlash();
      if (!empty($flash)):
      ?>
        <div class="mensagem-flash <?= $flash['color'] ?>" style="margin-bottom:15px; padding:10px; border-radius:6px; font-size:.8rem; background-color: var(--alerta); color: var(--branco); text-align: center;">
          <?= htmlspecialchars($flash['texto']) ?>
        </div>
      <?php endif; ?>

      <div class="info-cotacao" style="background: var(--fundo-tela); border-radius: 8px; padding: 12px; margin-bottom: 20px; font-size: 0.82rem; color: var(--texto); border-left: 3px solid var(--destaque);">
        <p>Você recebeu uma nova Ordem de Compra. Por favor, confirme seu CNPJ para validar o acesso aos itens e termos de entrega.</p>
      </div>

      <form method="POST" action="<?= BASE_URL ?>/login/fornecedor/ordem/entrar">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <div class="campo-login">
          <label for="cnpj">CNPJ da Empresa</label>
          <div class="input-icone">
            <img src="<?= BASE_URL ?>/public/assets/icons/iconeCadastro.svg" alt="">
            <input type="text" id="cnpj" name="cnpj" maxlength="18" placeholder="00.000.000/0000-00" required autofocus oninput="mascararCnpj(this)">
          </div>
        </div>

        <button type="submit" class="btn-login" style="margin-top: 10px;">Acessar Ordem de Compra</button>
      </form>

      <p style="font-size:.78rem; color:#999; text-align:center; margin-top:20px;">
        Problemas com o acesso? Entre em contato com a empresa compradora.
      </p>
    </div>
  </div>
</div>

<script>
function mascararCnpj(input) {
  let v = input.value.replace(/\D/g, '').substring(0, 14);
  if (v.length <= 2) v = v;
  else if (v.length <= 5) v = v.slice(0, 2) + '.' + v.slice(2);
  else if (v.length <= 8) v = v.slice(0, 2) + '.' + v.slice(2, 5) + '.' + v.slice(5);
  else if (v.length <= 12) v = v.slice(0, 2) + '.' + v.slice(2, 5) + '.' + v.slice(5, 8) + '/' + v.slice(8);
  else v = v.slice(0, 2) + '.' + v.slice(2, 5) + '.' + v.slice(5, 8) + '/' + v.slice(8, 12) + '-' + v.slice(12);
  input.value = v;
}
</script>
</body>
</html>
