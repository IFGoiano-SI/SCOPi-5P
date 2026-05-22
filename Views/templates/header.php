<?php
use Config\Auxiliares;
$paginaAtual = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');
$basePath = trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($basePath !== '' && str_starts_with($paginaAtual, $basePath . '/')) {
    $paginaAtual = substr($paginaAtual, strlen($basePath) + 1);
}
$paginaAtual = $paginaAtual === 'home' ? 'inicio' : $paginaAtual;
$usuario     = $usuario ?? Auxiliares::usuarioLogado();
if (!function_exists('pAti')) {
function pAti(string $rota, string $atual): bool {
    return $rota === $atual || str_starts_with($atual, $rota . '/');
}
}
if (!function_exists('subAberto')) {
function subAberto(array $rotas, string $atual): bool {
    foreach ($rotas as $r) { if (pAti($r, $atual)) return true; }
    return false;
}
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Projeto</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/estilo.css">
  <script>const SCOPI_BASE = "<?= BASE_URL ?>";</script>
  <script src="<?= BASE_URL ?>/public/assets/js/scopi.js"></script>
</head>
<body>
<div class="layout">

<aside class="sidebar" id="sidebar">

  <div class="btn-menu-wrap">
    <button class="btn-menu" id="btnMenu" title="Retrair / expandir">
      <img src="<?= BASE_URL ?>/public/assets/icons/iconeMenu.svg" alt="Menu">
    </button>
  </div>

  <nav class="sidebar-nav">

    <div class="nav-item <?= pAti('inicio',$paginaAtual)?'ativo':'' ?>" data-tooltip="Página Inicial">
      <?php if(pAti('inicio',$paginaAtual)): ?><div class="selecao-ativa"></div><?php endif; ?>
      <a href="<?= BASE_URL ?>/inicio" class="nav-link <?= pAti('inicio',$paginaAtual)?'ativo':'' ?>">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeHome.svg" alt="" class="icone-nav">
        <span class="texto-nav">Página Inicial</span>
      </a>
    </div>

    <?php if (in_array($usuario['perfil'] ?? '', ['administrador', 'cadastrador'])): ?>
    <?php $cAb = subAberto(['usuarios','departamentos','fornecedores','produtos','categorias','condicoes-pagamento'],$paginaAtual); ?>
    <div class="nav-item <?= $cAb?'ativo aberto':'' ?>" data-tooltip="Cadastros">
      <?php if($cAb): ?><div class="selecao-ativa"></div><?php endif; ?>
      <button class="nav-link <?= $cAb?'ativo':'' ?>" onclick="toggleSubmenu(this)">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeCadastro.svg" alt="" class="icone-nav">
        <span class="texto-nav">Cadastros</span>
        <span class="seta-submenu">›</span>
      </button>
      <ul class="submenu">
        <li><a href="<?= BASE_URL ?>/usuarios"      class="nav-link <?= pAti('usuarios',$paginaAtual)?'ativo':'' ?>">
          <span class="texto-nav">Usuários</span></a></li>
        <li><a href="<?= BASE_URL ?>/departamentos" class="nav-link <?= pAti('departamentos',$paginaAtual)?'ativo':'' ?>">
          <span class="texto-nav">Departamentos</span></a></li>
        <li><a href="<?= BASE_URL ?>/fornecedores"  class="nav-link <?= pAti('fornecedores',$paginaAtual)?'ativo':'' ?>">
          <span class="texto-nav">Fornecedores</span></a></li>
        <li><a href="<?= BASE_URL ?>/produtos"      class="nav-link <?= pAti('produtos',$paginaAtual)?'ativo':'' ?>">
          <span class="texto-nav">Produtos</span></a></li>
        <li><a href="<?= BASE_URL ?>/categorias"    class="nav-link <?= pAti('categorias',$paginaAtual)?'ativo':'' ?>">
          <span class="texto-nav">Categorias</span></a></li>
        <li><a href="<?= BASE_URL ?>/condicoes-pagamento" class="nav-link <?= pAti('condicoes-pagamento',$paginaAtual)?'ativo':'' ?>">
          <span class="texto-nav">Condições de Pagamento</span></a></li>
      </ul>
    </div>
    <?php endif; ?>

    <div class="nav-item <?= pAti('solicitacoes',$paginaAtual)?'ativo':'' ?>" data-tooltip="Solicitações">
      <?php if(pAti('solicitacoes',$paginaAtual)): ?><div class="selecao-ativa"></div><?php endif; ?>
      <a href="<?= BASE_URL ?>/solicitacoes" class="nav-link <?= pAti('solicitacoes',$paginaAtual)?'ativo':'' ?>">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeSolicitacao.svg" alt="" class="icone-nav">
        <span class="texto-nav">Solicitações</span>
      </a>
    </div>

    <?php if (in_array($usuario['perfil'] ?? '', ['comprador', 'administrador', 'gerente'])): ?>
    <?php $oAb = subAberto(['ordens','cotacoes'],$paginaAtual); ?>
    <div class="nav-item <?= $oAb?'ativo aberto':'' ?>" data-tooltip="Ordens de Compra">
      <?php if($oAb): ?><div class="selecao-ativa"></div><?php endif; ?>
      <button class="nav-link <?= $oAb?'ativo':'' ?>" onclick="toggleSubmenu(this)">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeOC.svg" alt="" class="icone-nav">
        <span class="texto-nav">Ordens de Compra</span>
        <span class="seta-submenu">›</span>
      </button>
      <ul class="submenu">
        <li><a href="<?= BASE_URL ?>/cotacoes" class="nav-link <?= pAti('cotacoes',$paginaAtual)?'ativo':'' ?>">
          <span class="texto-nav">Cotações</span></a></li>
        <li><a href="<?= BASE_URL ?>/ordens"   class="nav-link <?= $paginaAtual==='ordens'?'ativo':'' ?>">
          <span class="texto-nav">Ordens de Compra</span></a></li>
      </ul>
    </div>
    <?php endif; ?>

    <?php if (in_array($usuario['perfil'] ?? '', ['contabilidade', 'comprador', 'administrador'])): ?>
    <?php $nAb = subAberto(['notas'],$paginaAtual); ?>
    <div class="nav-item <?= $nAb?'ativo aberto':'' ?>" data-tooltip="Notas de Entrada">
      <?php if($nAb): ?><div class="selecao-ativa"></div><?php endif; ?>
      <button class="nav-link <?= $nAb?'ativo':'' ?>" onclick="toggleSubmenu(this)">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeNF.svg" alt="" class="icone-nav">
        <span class="texto-nav">Notas de Entrada</span>
        <span class="seta-submenu">›</span>
      </button>
      <ul class="submenu">
        <li><a href="<?= BASE_URL ?>/notas"          class="nav-link <?= $paginaAtual==='notas'?'ativo':'' ?>">
          <span class="texto-nav">Notas Fiscais</span></a></li>
        <li><a href="<?= BASE_URL ?>/notas/importar" class="nav-link <?= $paginaAtual==='notas/importar'?'ativo':'' ?>">
          <span class="texto-nav">Importar NF-e</span></a></li>
      </ul>
    </div>
    <?php endif; ?>

  </nav>

  <div class="sidebar-linha"></div>

  <div class="sidebar-rodape">
    <a href="<?= BASE_URL ?>/login/sair" class="sidebar-sair" title="Sair">
      <img src="<?= BASE_URL ?>/public/assets/icons/iconeSair.svg" alt="Sair">
      <span class="texto-nav">Sair</span>
    </a>
    <div class="sidebar-usuario">
      <img src="<?= BASE_URL ?>/public/assets/icons/iconeUser.svg" alt="" class="avatar">
      <div class="info-usr">
        <div class="nome-usr"><?= Auxiliares::escapar($usuario['nome']) ?></div>
        <div class="perfil-usr"><?= ucfirst(Auxiliares::escapar($usuario['perfil'])) ?></div>
      </div>
    </div>
  </div>

</aside>

<div class="conteudo">

  <header class="topbar">
    <div class="topbar-logo">
      <img src="<?= BASE_URL ?>/public/assets/icons/iconeSCOPi.svg" alt="SCOPi" class="icone-logo">
      <img src="<?= BASE_URL ?>/public/assets/icons/iconeLogotipo.svg" alt="SCOPi" class="logotipo">
    </div>
    <div class="topbar-sep"></div>
    <div class="topbar-empresa">
      <span>Logo Contratante</span>
    </div>
    <div class="topbar-titulo-area">
      <div class="topbar-titulo-linha"></div>
      <span class="topbar-titulo" id="topbarTitulo">SCOPi</span>
    </div>
    <div class="topbar-direita">
      <button class="btn-notificacao" id="btnNotificacao" title="Notificações" onclick="Scopi.abrirNotificacoes()">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeNotificacao.svg" alt="Notificações">
        <span class="badge-notif" id="badgeNotif">3</span>
      </button>
    </div>
  </header>

  <main class="pagina">

<?php if(!empty($flash)): ?>
<div class="mensagem-flash flash-<?= $flash['tipo'] ?>">
  <img src="<?= BASE_URL ?>/public/assets/icons/iconeAlerta.svg" alt="">
  <?= Auxiliares::escapar($flash['texto']) ?>
</div>
<?php endif; ?>
