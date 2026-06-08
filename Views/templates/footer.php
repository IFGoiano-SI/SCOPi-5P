  </main><!-- /pagina -->

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
        <div id="notifDetalheConteudo" style="display:none;flex-direction:column;height:100%;">
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

<?php include 'modais_globais.php'; ?>

</div><!-- /conteudo -->
</div><!-- /layout -->
</body>
</html>

