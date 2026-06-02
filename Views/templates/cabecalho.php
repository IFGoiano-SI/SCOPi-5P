<?php
/**
 * cabecalho.php — Template principal do SCOPi
 * Sidebar: 260px expandida | 72px retraída
 * Subseções do menu: SEM ícone, fundo #510B76, radius 5px
 * Modal de notificações embutido
 */
use Config\Auxiliares;
$paginaAtual = trim($_GET['rota'] ?? '');
$usuario     = $usuario ?? Auxiliares::usuarioLogado();

function pAti(string $rota, string $atual): bool {
    return $rota === $atual || str_starts_with($atual, $rota . '/');
}
function subAberto(array $rotas, string $atual): bool {
    foreach ($rotas as $r) { if (pAti($r, $atual)) return true; }
    return false;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SCOPi</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/estilo.css">
</head>
<body>
<div class="layout">

<!-- ═══════════════ SIDEBAR ═══════════════ -->
<aside class="sidebar" id="sidebar">

  <div class="btn-menu-wrap">
    <button class="btn-menu" id="btnMenu" title="Retrair / expandir">
      <img src="<?= BASE_URL ?>/public/assets/icons/iconeMenu.svg" alt="Menu">
    </button>
  </div>

  <nav class="sidebar-nav">

    <!-- Página Inicial -->
    <div class="nav-item <?= pAti('inicio',$paginaAtual)?'ativo':'' ?>" data-tooltip="Página Inicial">
      <?php if(pAti('inicio',$paginaAtual)): ?><div class="selecao-ativa"></div><?php endif; ?>
      <a href="<?= BASE_URL ?>/inicio" class="nav-link <?= pAti('inicio',$paginaAtual)?'ativo':'' ?>">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeHome.svg" alt="" class="icone-nav">
        <span class="texto-nav">Página Inicial</span>
      </a>
    </div>

    <!-- Cadastros (RF02: exibir conforme perfil) -->
    <?php
    $perfilUsuario = $usuario['perfil'] ?? 'usuario';
    $podeCadastros = in_array($perfilUsuario, ['administrador','cadastrador','gerente','comprador','usuario']);
    ?>
    <?php if($podeCadastros): ?>
    <?php $cAb = subAberto(['usuarios','departamentos','fornecedores','produtos','categorias'],$paginaAtual); ?>
    <div class="nav-item <?= $cAb?'ativo aberto':'' ?>" data-tooltip="Cadastros">
      <?php if($cAb): ?><div class="selecao-ativa"></div><?php endif; ?>
      <button class="nav-link <?= $cAb?'ativo':'' ?>" onclick="toggleSubmenu(this)">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeCadastro.svg" alt="" class="icone-nav">
        <span class="texto-nav">Cadastros</span>
        <span class="seta-submenu">›</span>
      </button>
      <ul class="submenu">
        <?php if(in_array($perfilUsuario, ['administrador'])): ?>
        <li><a href="<?= BASE_URL ?>/usuarios"      class="nav-link <?= pAti('usuarios',$paginaAtual)?'ativo':'' ?>">
          <span class="texto-nav">Usuários</span></a></li>
        <?php endif; ?>
        <?php if(in_array($perfilUsuario, ['administrador','cadastrador'])): ?>
        <li><a href="<?= BASE_URL ?>/departamentos" class="nav-link <?= pAti('departamentos',$paginaAtual)?'ativo':'' ?>">
          <span class="texto-nav">Departamentos</span></a></li>
        <li><a href="<?= BASE_URL ?>/fornecedores"  class="nav-link <?= pAti('fornecedores',$paginaAtual)?'ativo':'' ?>">
          <span class="texto-nav">Fornecedores</span></a></li>
        <li><a href="<?= BASE_URL ?>/produtos"      class="nav-link <?= pAti('produtos',$paginaAtual)?'ativo':'' ?>">
          <span class="texto-nav">Produtos</span></a></li>
        <li><a href="<?= BASE_URL ?>/categorias"    class="nav-link <?= pAti('categorias',$paginaAtual)?'ativo':'' ?>">
          <span class="texto-nav">Categorias</span></a></li>
        <?php endif; ?>
      </ul>
    </div>
    <?php endif; ?>

    <!-- Solicitações (RF09: todos os funcionários podem solicitar) -->
    <?php if(in_array($perfilUsuario, ['administrador','cadastrador','comprador','gerente','usuario'])): ?>
    <div class="nav-item <?= pAti('solicitacoes',$paginaAtual)?'ativo':'' ?>" data-tooltip="Solicitações">
      <?php if(pAti('solicitacoes',$paginaAtual)): ?><div class="selecao-ativa"></div><?php endif; ?>
      <a href="<?= BASE_URL ?>/solicitacoes" class="nav-link <?= pAti('solicitacoes',$paginaAtual)?'ativo':'' ?>">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeSolicitacao.svg" alt="" class="icone-nav">
        <span class="texto-nav">Solicitações</span>
      </a>
    </div>
    <?php endif; ?>

    <!-- Ordens de Compra (RF10/RF13: comprador e gerente) -->
    <?php if(in_array($perfilUsuario, ['administrador','comprador','gerente'])): ?>
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

    <!-- Notas de Entrada (RF14: contabilidade, compras e admin) -->
    <?php if(in_array($perfilUsuario, ['administrador','contabilidade','comprador'])): ?>
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
<!-- ═══════════════ FIM SIDEBAR ═══════════════ -->

<div class="conteudo">

  <!-- TOPBAR -->
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
      <button class="btn-notificacao" id="btnNotificacao" title="Notificações"
              onclick="Scopi.abrirNotificacoes()">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeNotificacao.svg" alt="Notificações">
        <span class="badge-notif" id="badgeNotif" style="display:none;">0</span>
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


<!-- ═══════════════════════════════════════════════════════
     MODAL DE NOTIFICAÇÕES
     Layout: painel lista (esquerda) + painel detalhe (direita)
     ═══════════════════════════════════════════════════════ -->
<div class="overlay-modal notif-modal" id="modalNotificacoes">
  <div class="modal modal-largo" style="max-width:860px;max-height:85vh;">

    <!-- Cabeçalho do modal -->
    <div class="modal-cabecalho">
      <div class="modal-titulo">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeNotificacao.svg" alt="">
        <span>Notificações</span>
      </div>
      <button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalNotificacoes')">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt="Fechar">
      </button>
    </div>

    <!-- Corpo: layout dois painéis -->
    <div class="notif-layout">

      <!-- ── Painel Esquerdo: Lista ── -->
      <div class="notif-lista-painel">

        <div class="notif-lista-header">
          <h3>Caixa de Entrada</h3>
          <button class="btn-ler-tudo" onclick="Scopi.Notif.lerTodas()">
            Marcar todas como lidas
          </button>
        </div>

        <!-- Filtros por categoria -->
        <div class="notif-filtros">
          <button class="notif-filtro-btn ativo" data-filtro="todas"
                  onclick="Scopi.Notif.filtrar('todas', this)">Todas</button>
          <button class="notif-filtro-btn" data-filtro="solicitacao"
                  onclick="Scopi.Notif.filtrar('solicitacao', this)">Solicitações</button>
          <button class="notif-filtro-btn" data-filtro="ordem"
                  onclick="Scopi.Notif.filtrar('ordem', this)">Ordens</button>
          <button class="notif-filtro-btn" data-filtro="cotacao"
                  onclick="Scopi.Notif.filtrar('cotacao', this)">Cotações</button>
          <button class="notif-filtro-btn" data-filtro="nota"
                  onclick="Scopi.Notif.filtrar('nota', this)">Notas</button>
          <button class="notif-filtro-btn" data-filtro="alerta"
                  onclick="Scopi.Notif.filtrar('alerta', this)">Alertas</button>
        </div>

        <!-- Lista de notificações (preenchida pelo JS) -->
        <div class="notif-lista" id="notifLista"></div>

      </div><!-- /painel esquerdo -->

      <!-- ── Painel Direito: Detalhe ── -->
      <div class="notif-detalhe-painel" id="notifDetalhe">
        <!-- Estado vazio inicial -->
        <div class="notif-vazio" id="notifVazio">
          <img src="<?= BASE_URL ?>/public/assets/icons/iconeNotificacao.svg" alt="">
          <p>Selecione uma notificação para ler</p>
        </div>
        <!-- Detalhe (mostrado ao selecionar) -->
        <div id="notifDetalheConteudo" style="display:none;display:flex;flex-direction:column;height:100%;">
          <div class="notif-detalhe-header">
            <div class="notif-detalhe-assunto" id="notifDetAssunto"></div>
            <div class="notif-detalhe-meta">
              <span class="notif-badge-cat" id="notifDetCategoria"></span>
              <span class="notif-detalhe-meta-item">
                <img src="<?= BASE_URL ?>/public/assets/icons/iconeAlerta.svg" alt="">
                <span id="notifDetTempo"></span>
              </span>
              <span class="notif-detalhe-meta-item">
                <img src="<?= BASE_URL ?>/public/assets/icons/iconeUser.svg" alt="">
                <span id="notifDetRemetente"></span>
              </span>
            </div>
          </div>
          <div class="notif-detalhe-corpo">
            <div class="notif-detalhe-texto" id="notifDetTexto"></div>
          </div>
          <div class="notif-detalhe-footer">
            <div class="notif-detalhe-acoes">
              <button class="btn btn-secundario" style="font-size:.72rem;padding:5px 12px;"
                      onclick="Scopi.Notif.marcarLida()" id="btnMarcarLida">
                Marcar como lida
              </button>
              <button class="btn btn-perigo" style="font-size:.72rem;padding:5px 12px;"
                      onclick="Scopi.Notif.excluir()" id="btnExcluirNotif">
                Excluir
              </button>
            </div>
            <button class="btn btn-secundario" style="font-size:.72rem;padding:5px 12px;"
                    onclick="Scopi.fecharModal('modalNotificacoes')">
              Fechar
            </button>
          </div>
        </div>
      </div><!-- /painel direito -->

    </div><!-- /notif-layout -->
  </div>
</div>
<!-- ═══════════════ FIM MODAL NOTIFICAÇÕES ═══════════════ -->
