<!-- ═══════════════════════════════════════════════════════
     MODAIS GLOBAIS (Busca Global e Histórico)
     ═══════════════════════════════════════════════════════ -->

<!-- Modal de Busca Global -->
<div class="overlay-modal" id="modalBuscaGlobal">
  <div class="modal modal-largo">
    <div class="modal-cabecalho">
      <div style="display: flex; align-items: center; gap: 10px; flex: 1;">
        <button class="btn btn-secundario" id="btnVoltarBusca" style="padding: 6px 12px; font-size: 0.85rem; display: none;" onclick="Scopi.voltarBuscaAninhada()">
          ← Voltar
        </button>
        <div class="modal-titulo">
          <img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt="">
          <span>Buscar <span id="buscaGlobalEntidadeNome">Registro</span></span>
        </div>
      </div>
      <button class="btn-fechar-modal" onclick="event.stopPropagation(); Scopi.fecharModal('modalBuscaGlobal')">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt="Fechar">
      </button>
    </div>
    <div class="modal-corpo">
      <div id="divBuscaTermoGlobal" style="display: flex; gap: 10px; margin-bottom: 15px;">
        <input type="text" id="inputBuscaGlobal" class="campo-input" style="flex: 1;"  placeholder="Buscar por termo..." onkeydown="if(event.key === 'Enter') Scopi.executarBuscaGlobal()">
        <button class="btn btn-primario" onclick="Scopi.executarBuscaGlobal()">Buscar</button>
      </div>

      <!-- Filtros Contextuais Avançados -->
      <div id="filtrosBuscaGlobal" style="margin-bottom: 15px; padding: 15px; background: #f9f9f9; border-radius: 6px; display: none; border: 1px solid #e0e0e0;">
        <div style="font-size: 0.85rem; font-weight: 500; margin-bottom: 10px; color: #666;">Filtros</div>
        <div id="filtrosCampos" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
          <!-- Gerado via JS conforme o tipo de busca -->
        </div>
      </div>

      <div class="tabela-container" style="max-height: 400px; overflow-y: auto;">
        <table class="tabela" id="tabelaBuscaGlobal">
          <thead id="theadBuscaGlobal">
            <!-- Gerado via JS -->
          </thead>
          <tbody id="tbodyBuscaGlobal">
            <tr><td colspan="5" style="text-align:center;color:#888;">Digite um termo e clique em buscar.</td></tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="modal-rodape">
      <button class="btn btn-secundario" onclick="event.stopPropagation(); Scopi.fecharModal('modalBuscaGlobal')">Cancelar</button>
    </div>
  </div>
</div>

<!-- Modal de Histórico -->
<div class="overlay-modal" id="modalHistoricoGlobal">
  <div class="modal modal-medio">
    <div class="modal-cabecalho">
      <div class="modal-titulo">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeHistorico.svg" alt="">
        <span id="historicoGlobalTitulo">Histórico</span>
      </div>
      <button class="btn-fechar-modal" onclick="event.stopPropagation(); Scopi.fecharModal('modalHistoricoGlobal')">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt="Fechar">
      </button>
    </div>
    <div class="modal-corpo" style="background: var(--fundo); padding: 20px;">
      
      <div id="timelineHistorico" style="position: relative; padding-left: 20px; border-left: 2px solid var(--borda);">
        <!-- Gerado via JS -->
      </div>
      
      <div id="historicoVazio" style="display: none; text-align: center; padding: 30px; color: #888;">
        Nenhum registro de histórico encontrado.
      </div>

    </div>
    <div class="modal-rodape">
      <button class="btn btn-secundario" onclick="event.stopPropagation(); Scopi.fecharModal('modalHistoricoGlobal')">Fechar</button>
    </div>
  </div>
</div>
