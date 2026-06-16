<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Cotações';</script>
<div class="pagina-cabecalho"><h1 class="pagina-titulo">Cotações</h1><p class="pagina-subtitulo">Gerenciamento de cotações com fornecedores</p></div>
<div class="painel-filtros">
  <div class="filtros-cabecalho"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt=""><span>Filtros</span></div>
  <form method="GET" action="<?= BASE_URL ?>/cotacoes"><div class="filtros-campos">
    <div class="campo-filtro"><label>Número</label><input type="text" name="numero" value="<?= Auxiliares::escapar($filtros['numero']??'') ?>"></div>
    <div class="campo-filtro">
        <label style="display:flex;gap:8px;align-items:center;justify-content:space-between;">
            <span>Cód. Fornecedor</span>
            <button type="button" class="btn btn-secundario" style="padding:4px 6px;margin:0;" title="Buscar fornecedor" onclick="Scopi.iconeBusca('fornecedores','filtroFornCotCodigo','filtroFornCotNome')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width:13px;margin:0;" alt="Buscar"></button>
        </label>
        <div style="display: flex; gap: 8px; align-items: center;">
            <input type="text" id="filtroFornCotCodigo" name="fornecedor_codigo" value="<?= Auxiliares::escapar($filtros['fornecedor_codigo'] ?? '') ?>" class="campo-input" style="width: 100px; text-transform: uppercase;"  onblur="buscarFornecedorFiltro(this.value)">
            <span id="filtroFornCotNome" style="font-size: 0.8rem; color: var(--texto-secundario);"><?= empty($filtros['fornecedor_codigo']) ? 'Digite...' : 'Buscando...' ?></span>
        </div>
    </div>
    <div class="campo-filtro"><label>Data Abertura</label><input type="date" name="data_abertura" value="<?= Auxiliares::escapar($filtros['data_abertura']??'') ?>"></div>
    <div class="campo-filtro"><label>Data Encerramento</label><input type="date" name="data_encerramento" value="<?= Auxiliares::escapar($filtros['data_encerramento']??'') ?>"></div>
    <div class="campo-filtro"><label>Status</label><select name="status"><option value="">Todos</option><option value="aberta">Aberta</option><option value="fechada">Fechada</option><option value="concluida">Concluída</option><option value="cancelada">Cancelada</option></select></div>
    <div class="campo-filtro" style="flex:0;align-self:flex-end;"><button type="submit" class="btn btn-filtrar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt=""> Filtrar</button></div>
  </div></form>
</div>
<div class="barra-acoes">
  <div class="grupo-botoes">
    <?php if(in_array($usuario['perfil'], ['comprador', 'administrador'])): ?>
      <button class="btn btn-primario" onclick="Scopi.abrirCadastro('modalCotacao','formCotacao')">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Nova Cotação
      </button>
    <?php endif; ?>
    <button class="btn btn-secundario" onclick="window.open('<?= BASE_URL ?>/cotacoes/exportar' + window.location.search, '_blank')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Exportar</button>
  </div>
  <span style="font-size:.82rem;color:#888;"><?= count($cotacoes) ?> registro(s)</span>
</div>
<div class="tabela-container">
  <table class="tabela">
    <thead><tr>
      <th>Nº Cotação</th>
      <th>Abertura / Encerramento</th>
      <th style="text-align:center;">Enviadas</th>
      <th style="text-align:center;">Respostas</th>
      <th>Fornecedor Vencedor</th>
      <th>Cód. Fornecedor</th>
      <th>Status</th>
      <th class="coluna-acoes"></th>
    </tr></thead>
    <tbody>
      <?php if(empty($cotacoes)): ?><tr><td colspan="8" style="text-align:center;padding:32px;color:#888;">Nenhuma cotação encontrada.</td></tr>
      <?php else: foreach($cotacoes as $c): ?>
      <tr>
        <td><span class="cod-clicavel" onclick="Scopi.abrirRegistro('modalCotacao','formCotacao','/cotacoes/dados',<?= $c['id'] ?>,'visualizar')"><?= Auxiliares::escapar($c['numero']??$c['id']) ?></span></td>
        <td style="font-size:0.82rem;">
          <?= !empty($c['data_abertura'])?date('d/m/Y',strtotime($c['data_abertura'])):'—' ?>
          <?php if(!empty($c['data_encerramento'])): ?><br><span style="color:#888;">até <?= date('d/m/Y',strtotime($c['data_encerramento'])) ?></span><?php endif; ?>
        </td>
        <td style="text-align:center;"><?= (int)($c['total_fornecedores']??0) ?></td>
        <td style="text-align:center;"><?= (int)($c['total_respostas']??0) ?></td>
        <td><?= Auxiliares::escapar($c['fornecedor_vencedor']??'—') ?></td>
        <td><?= Auxiliares::escapar($c['codigo_fornecedor_vencedor']??'—') ?></td>
        <td><span class="badge badge-<?= $c['status'] ?>"><?= Auxiliares::formatarStatus($c['status']) ?></span></td>
        <td class="coluna-acoes">
          <button class="btn-icone btn-editar-linha" onclick="Scopi.abrirRegistro('modalCotacao','formCotacao','/cotacoes/dados',<?= $c['id'] ?>,'visualizar')" title="Ver"><img src="<?= BASE_URL ?>/public/assets/icons/iconeEditar.svg" alt=""></button>
          <button class="btn-icone" onclick="window.open('<?= BASE_URL ?>/cotacoes/imprimir?id=<?= $c['id'] ?>', '_blank')" title="Imprimir"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""></button>
        </td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<div class="overlay-modal" id="modalCotacao">
  <div class="modal modal-largo" style="max-width: 90%; width: 900px;">
    <div class="modal-cabecalho">
      <div class="modal-titulo">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeOC.svg" alt="">
        <span>Cotação</span>
      </div>
      <button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalCotacao')">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt="">
      </button>
    </div>
    
    <div class="modal-corpo">
      <div class="conteudo-aba ativo" data-aba="editar">
        <form id="formCotacao" onsubmit="event.preventDefault();">
          <input type="hidden" name="id" id="cotacaoIdInput" value="0">

          <!-- CAMPOS DE EDICAO (Apenas se id > 0) -->
          <div id="blocoCamposCabecalhoEdicao" style="display:none;">
            <div class="grade-form" style="grid-template-columns: 1fr 1fr; margin-bottom: 15px;">
              <div class="campo-form">
                <label>Número</label>
                <input type="text" readonly class="campo-input" style="cursor: not-allowed;" value="" data-campo="numero">
              </div>
              <div class="campo-form">
                <label>Status</label>
                <input type="text" readonly class="campo-input" style="cursor: not-allowed;" value="Aberto" data-campo="status_texto">
              </div>
            </div>

            <div class="grade-form" style="grid-template-columns: 1fr 1fr; margin-bottom: 15px;">
              <div class="campo-form">
                <label>Comprador</label>
                <input type="text" readonly class="campo-input" style="cursor: not-allowed;" value="—" data-campo="nome_comprador">
              </div>
              <div class="campo-form">
                <label>&nbsp;</label>
              </div>
            </div>

            <div class="grade-form" style="grid-template-columns: 1fr 1fr; margin-bottom: 15px;">
              <div class="campo-form">
                <label>Fornecedores Convidados</label>
                <input type="text" readonly class="campo-input" style="cursor: not-allowed;" value="0 fornecedor(es)" id="cotacContadorFornInput">
              </div>
              <div class="campo-form">
                <label>&nbsp;</label>
                <button type="button" class="btn btn-secundario" id="btnGerenciarFornecedores" style="display:none;" onclick="abrirModalFornecedores()">Gerenciar</button>
              </div>
            </div>
          </div>

          <!-- ABAS INTERNAS (CAPA / ITENS) -->
          <div class="modal-abas" id="abasCotacaoEditar" style="margin-bottom: 15px;">
            <button type="button" class="aba-btn ativa" id="abaCotCapaBtn" onclick="mudarAbaEdicaoCot('capa')">1. Capa</button>
            <button type="button" class="aba-btn" id="abaCotItensBtn" onclick="mudarAbaEdicaoCot('itens')">2. Itens</button>
          </div>
          <div id="blocoCotCapa" style="display:block;">
            <!-- Campo para Nova Cotação (id === 0) -->
            <div class="campo-form" id="blocoNovaCotSolicitacao" style="margin-bottom:14px; display:none;">
              <label>Solicitação Aprovada *</label>
              <select name="solicitacao_id" id="novaCotSolicitacaoSel" class="campo-select" style="width: 100%;" onchange="aoSelecionarSolicitacaoNovaCot(this.value)">
                <option value="">Selecione uma solicitação...</option>
              </select>
            </div>

            <!-- Campos editáveis da capa (datas) -->
            <div id="bloCotDatas" class="grade-form" style="grid-template-columns:1fr 1fr;margin-bottom:14px;">
              <div class="campo-form">
                <label>Data de Abertura *</label>
                <input type="date" name="data_abertura" id="cotDataAbertura" required>
              </div>
              <div class="campo-form">
                <label>Data de Encerramento *</label>
                <input type="date" name="data_encerramento" id="cotDataEncerramento" required>
              </div>
            </div>

            <!-- Preview dos itens da solicitação selecionada (Nova cotação) -->
            <div id="previewItensSolicitacaoNova" style="display:none; margin-bottom:14px;">
              <span class="rotulo" style="margin-bottom: 6px; display: block; font-size: 0.85rem; color: var(--media);">Itens da Solicitação selecionada:</span>
              <div class="tabela-container" style="max-height: 150px; overflow-y: auto; border: 1px solid var(--borda);">
                <table class="tabela" id="tabPreviewItensSolicitacaoNova">
                  <thead>
                    <tr>
                      <th style="padding: 8px 12px;">Produto</th>
                      <th style="width:100px; text-align:right; padding: 8px 12px;">Quantidade</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>

            <!-- Seleção de fornecedores integrada (Nova cotação) -->
            <div id="blocoNovaCotFornecedores" style="display:none; margin-top:20px; border-top: 1px solid var(--borda); padding-top: 15px;">
              <span class="rotulo" style="margin-bottom: 12px; display: block; font-size: 0.9rem; color: var(--media); font-weight:600;">Selecione os Fornecedores para convite:</span>
              
              <!-- Filtro de fornecedores dentro da criação -->
              <div style="display: flex; gap: 8px; margin-bottom: 10px;">
                <input type="text" id="filtroFornNovaCot" placeholder="Filtrar fornecedores por nome ou código..." onkeyup="filtrarFornecedoresNovaCot()" class="campo-input" style="flex:1;">
                <button type="button" class="btn btn-secundario" style="font-size:0.75rem; padding:6px 10px;" onclick="marcarTodosFornNovaCot(true)">Marcar Todos</button>
                <button type="button" class="btn btn-secundario" style="font-size:0.75rem; padding:6px 10px;" onclick="marcarTodosFornNovaCot(false)">Desmarcar Todos</button>
              </div>

              <!-- Lista de fornecedores -->
              <div style="border: 1px solid var(--borda); border-radius: 6px; padding: 10px; max-height: 200px; overflow-y: auto; background-color: var(--branco);" id="novaCotFornecedoresChecklist">
                <?php foreach($fornecedoresAtivos as $f): ?>
                  <div class="fornecedor-chk-item-nova" style="display:flex;align-items:center;gap:8px;margin-bottom:6px;"
                       data-nome-codigo="<?= strtolower(Auxiliares::escapar(($f['codigo'] ?? '') . ' ' . $f['razao_social'] . ' ' . ($f['nome_fantasia'] ?? ''))) ?>"
                       data-categorias="<?= Auxiliares::escapar($f['categorias_ids'] ?? '') ?>">
                    <input type="checkbox" name="fornecedores[]" value="<?= $f['id'] ?>" id="nova_forn_chk_<?= $f['id'] ?>" style="width:16px;height:16px;accent-color:var(--media);">
                    <label for="nova_forn_chk_<?= $f['id'] ?>" style="font-size:0.78rem;cursor:pointer;user-select:none;flex:1;">
                      <strong><?= Auxiliares::escapar($f['codigo'] ?? '') ?> — <?= Auxiliares::escapar($f['razao_social']) ?></strong>
                      <span style="font-size:0.72rem;color:#888; display:block;">
                        CNPJ: <?= Auxiliares::escapar($f['cnpj']) ?>
                      </span>
                      <span class="badge-sugerido-forn-nova" id="nova_sug_forn_<?= $f['id'] ?>" style="display:none;font-size:0.68rem;background:var(--media);color:#fff;padding:1px 5px;border-radius:3px;margin-left:4px;">Sugerido</span>
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
            <div id="blocoItensCotacao" style="margin-top: 15px; border-top: 1px solid var(--borda); padding-top: 15px; display: none;">
            <span class="rotulo" style="margin-bottom: 12px; display: block; font-size: 1rem; color: var(--media);">Itens da Cotação</span>
            
            <div style="display:flex; gap:12px; margin-bottom:12px; flex-wrap:wrap;" id="acoesItensCotacao">
                <button type="button" class="btn btn-secundario" onclick="abrirModalImportarItens()"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Importar de Solicitação</button>
                <button type="button" class="btn btn-secundario" id="btnDesfazerImportacao" style="display:none;" onclick="desfazerImportacao()"><img src="<?= BASE_URL ?>/public/assets/icons/iconeLixeira.svg" style="width:14px;margin-right:6px;filter: brightness(0) invert(1);" alt="">Remover Importação</button>
            </div>
            
            <div style="margin-bottom:12px; display:flex; gap:8px; align-items:flex-end; flex-wrap:wrap;" id="blocoInclusaoManualCot">
               <div class="campo-form">
                 <label style="font-size:0.75rem;">Cód. Produto</label>
                 <div style="display:flex;gap:4px;align-items:center;">
                   <input type="text" id="cotProdutoCodigo" class="campo-input" style="width:120px;" onblur="buscarProdutoCotacao(this.value)">
                   <button type="button" class="btn btn-secundario" style="padding:6px 8px;height:36px;" onclick="Scopi.iconeBusca('produtos', 'cotProdutoCodigo', 'cotProdutoNome', null); setTimeout(()=>buscarProdutoCotacao(document.getElementById('cotProdutoCodigo').value), 500);">
                     <img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt="Buscar" style="width:14px;margin:0;">
                   </button>
                 </div>
                 <input type="hidden" id="cotProdutoId">
                 <span id="cotProdutoNome" style="font-size:0.78rem; color:var(--sucesso); font-weight:600; display:block; margin-top:3px;"></span>
               </div>
               <div class="campo-form">
                 <label style="font-size:0.75rem;">Quantidade</label>
                 <input type="number" id="cotQtdInput" min="0.01" step="any"  style="width:90px;" class="campo-input">
               </div>
               <div class="campo-form">
                 <label style="font-size:0.75rem;">Prazo Sugerido</label>
                 <input type="text" id="cotPrazoSugeridoInput" style="width:110px;" class="campo-input" placeholder="Ex: 30 dias">
               </div>
               <button type="button" class="btn btn-primario" id="btnAdicionarItemCot" style="height:36px;margin-bottom:1px;" onclick="adicionarItemCotacaoTabela()" disabled title="Salve a capa da cotação primeiro">
                 <img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Adicionar
               </button>
            </div>
            
            <div class="tabela-container">
              <table class="tabela" id="tabItensCotacao">
                <thead>
                  <tr>
                    <th style="width: 50px; text-align: center; padding: 8px 12px;">Nº Item</th>
                    <th style="padding: 8px 12px;">Produto</th>
                    <th style="width: 100px; text-align: center; padding: 8px 12px;">Quantidade</th>
                    <th style="width: 130px; padding: 8px 12px;">Prazo Sugerido</th>
                    <th style="width: 80px; padding: 8px 12px;"></th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
            <input type="hidden" name="itens_json" id="itensCotJsonInput" value="[]">
          </div>

          <div id="blocoComparativo" style="margin-top: 25px; display:none;">
            <h3 style="font-size: 0.9rem; font-weight: 600; color: var(--escura); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
              <img src="<?= BASE_URL ?>/public/assets/icons/iconeOC.svg" style="width: 16px;" alt="">
              Comparativo de Propostas Comerciais
            </h3>
            <div id="comparativoMatrixContainer" style="overflow-x: auto; border: 1px solid var(--borda); border-radius: 6px; background-color: var(--branco);">
            </div>
          </div>
        </form>
      </div>
    </div>
    
    <div class="modal-rodape">
      <button class="btn btn-secundario" id="btnVoltarCapaCot" style="display:none;" onclick="mudarAbaEdicaoCot('capa')">&larr; Voltar para Capa</button>
      <button class="btn btn-secundario" id="btnAvancarItensCot" style="display:none;" onclick="mudarAbaEdicaoCot('itens')">Avançar para Itens &rarr;</button>
      <button class="btn btn-secundario" onclick="Scopi.fecharModal('modalCotacao')">Fechar</button>
      <button class="btn btn-primario" id="btnConfirmarCriacaoCot" onclick="confirmarCriacaoCotacao()" style="display:none;"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Confirmar Criação</button>
      <button class="btn btn-primario btn-salvar-capa" id="btnSalvarCapaCot" onclick="salvarCapaCotacao()" style="display:none;"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Confirmar Capa</button>
      <button class="btn btn-primario btn-salvar" id="btnSalvarItensCot" onclick="salvarItensCotacao()" style="display:none;"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Salvar Itens</button>
    </div>
  </div>
</div>

<!-- MODAL GERENCIAR FORNECEDORES -->
<div class="overlay-modal" id="modalFornecedoresCotacao">
  <div class="modal" style="width:700px;">
    <div class="modal-cabecalho">
      <div class="modal-titulo">Convidar Fornecedores</div>
      <button class="btn-fechar-modal" onclick="event.stopPropagation(); Scopi.fecharModal('modalFornecedoresCotacao')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt=""></button>
    </div>
    <div class="modal-corpo">
      <!-- FILTROS INTELIGENTES -->
      <div style="margin-bottom: 15px; padding: 12px; background: #f9f9f9; border-radius: 6px; border: 1px solid #e0e0e0;">
        <div style="font-size: 0.85rem; font-weight: 500; margin-bottom: 10px; color: #666;">Filtros</div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; margin-bottom: 10px;">
          <div style="display:flex;flex-direction:column;">
            <label style="font-size:0.85rem;font-weight:500;margin-bottom:4px;">Código</label>
            <input type="text" id="filtroFornCodigoCot" placeholder="Ex: F001" onkeyup="filtrarFornecedoresChecklist()" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px;font-size:0.9rem;">
          </div>
          <div style="display:flex;flex-direction:column;">
            <label style="font-size:0.85rem;font-weight:500;margin-bottom:4px;">Razão Social</label>
            <input type="text" id="filtroFornRazaoSocialCot" placeholder="Ex: Empresa LTDA" onkeyup="filtrarFornecedoresChecklist()" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px;font-size:0.9rem;">
          </div>
          <div style="display:flex;flex-direction:column;">
            <label style="font-size:0.85rem;font-weight:500;margin-bottom:4px;">Nome Fantasia</label>
            <input type="text" id="filtroFornFantasiaCot" placeholder="Ex: Loja XYZ" onkeyup="filtrarFornecedoresChecklist()" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px;font-size:0.9rem;">
          </div>
          <div style="display:flex;flex-direction:column;">
            <label style="font-size:0.85rem;font-weight:500;margin-bottom:4px;">CNPJ</label>
            <input type="text" id="filtroFornCnpjCot" placeholder="Ex: 12.345.678" onkeyup="filtrarFornecedoresChecklist()" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px;font-size:0.9rem;">
          </div>
          <div style="display:flex;flex-direction:column;">
            <label style="font-size:0.85rem;font-weight:500;margin-bottom:4px;">Tipo</label>
            <select id="filtroFornTipoCot" onchange="filtrarFornecedoresChecklist()" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px;font-size:0.9rem;">
              <option value="">Todos</option>
              <option value="matriz">Matriz</option>
              <option value="filial">Filial</option>
            </select>
          </div>
          <div style="display:flex;flex-direction:column;">
            <label style="font-size:0.85rem;font-weight:500;margin-bottom:4px;display:flex;gap:8px;align-items:center;justify-content:space-between;">
              <span>Categoria</span>
              <button type="button" class="btn btn-secundario" style="padding:4px 6px;margin:0;" title="Buscar categoria" onclick="event.stopPropagation(); Scopi.iconeBusca('categorias','filtroFornCategoriaIdCot','filtroFornCategoriaNomeCot')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width:13px;margin:0;" alt="Buscar"></button>
            </label>
            <div style="display:flex;gap:8px;align-items:center;">
              <input type="text" id="filtroFornCategoriaIdCot" placeholder="Código ou ID" onblur="filtrarFornecedoresChecklist()" onkeyup="filtrarFornecedoresChecklist()" style="width:100px;padding:6px;border:1px solid #ddd;border-radius:4px;font-size:0.9rem;">
              <span id="filtroFornCategoriaNomeCot" style="font-size:0.8rem;color:var(--texto-secundario);flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Digite ou busque...</span>
            </div>
          </div>
        </div>
      </div>

      <!-- AÇÕES -->
      <div style="display:flex; gap:8px; margin-bottom:12px;">
        <button type="button" class="btn btn-secundario" style="font-size: 0.75rem; padding: 4px 8px;" onclick="document.querySelectorAll('#fornecedoresChecklist input[type=checkbox]:not(:disabled)').forEach(cb => { if(cb.parentElement.parentElement.style.display !== 'none') cb.checked = true; })">Marcar Todos Filtrados</button>
        <button type="button" class="btn btn-secundario" style="font-size: 0.75rem; padding: 4px 8px;" onclick="document.querySelectorAll('#fornecedoresChecklist input[type=checkbox]:not(:disabled)').forEach(cb => { if(cb.parentElement.parentElement.style.display !== 'none') cb.checked = false; })">Desmarcar Todos Filtrados</button>
      </div>

      <!-- LISTA DE FORNECEDORES -->
      <div style="border: 1px solid var(--borda); border-radius: 6px; padding: 10px; max-height: 280px; overflow-y: auto; background-color: var(--branco);" id="fornecedoresChecklist">
        <?php foreach($fornecedoresAtivos as $f): ?>
          <div class="fornecedor-chk-item" style="display:flex;align-items:center;gap:8px;margin-bottom:6px;"
               data-codigo="<?= strtolower(Auxiliares::escapar($f['codigo'] ?? '')) ?>"
               data-razao="<?= strtolower(Auxiliares::escapar($f['razao_social'] ?? '')) ?>"
               data-fantasia="<?= strtolower(Auxiliares::escapar($f['nome_fantasia'] ?? '')) ?>"
               data-cnpj="<?= strtolower(Auxiliares::escapar($f['cnpj'] ?? '')) ?>"
               data-tipo="<?= strtolower(Auxiliares::escapar($f['tipo'] ?? '')) ?>"
               data-categorias="<?= Auxiliares::escapar($f['categorias_ids'] ?? '') ?>">
            <input type="checkbox" name="forn_convite[]" value="<?= $f['id'] ?>" id="forn_chk_<?= $f['id'] ?>" style="width:16px;height:16px;accent-color:var(--media);">
            <label for="forn_chk_<?= $f['id'] ?>" style="font-size:0.78rem;cursor:pointer;user-select:none;flex:1;">
              <strong><?= Auxiliares::escapar($f['codigo'] ?? '') ?> — <?= Auxiliares::escapar($f['razao_social']) ?></strong>
              <span style="font-size:0.72rem;color:#888; display:block; margin-top:2px;">
                <?= !empty($f['nome_fantasia']) ? 'Fantasia: ' . Auxiliares::escapar($f['nome_fantasia']) . ' | ' : '' ?>
                CNPJ: <?= Auxiliares::escapar($f['cnpj']) ?>
                <?php if(!empty($f['tipo'])): ?><span style="background:#f0f0f0;padding:1px 4px;border-radius:2px;font-size:0.7rem;margin-left:4px;"><?= ucfirst($f['tipo']) ?></span><?php endif; ?>
              </span>
              <span class="badge-sugerido-forn" id="sug_forn_<?= $f['id'] ?>" style="display:none;font-size:0.68rem;background:var(--media);color:#fff;padding:1px 5px;border-radius:3px;margin-left:4px;">Sugerido</span>
            </label>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="modal-rodape">
      <button class="btn btn-secundario" onclick="event.stopPropagation(); Scopi.fecharModal('modalFornecedoresCotacao')">Fechar</button>
      <button class="btn btn-primario" onclick="enviarConvitesFornecedores()">Enviar Convites</button>
    </div>
  </div>
</div>

<!-- MODAL IMPORTAR ITENS SOLICITAÇÃO -->
<div class="overlay-modal" id="modalImportarCot">
  <div class="modal">
    <div class="modal-cabecalho">
      <div class="modal-titulo">Importar de Solicitação</div>
      <button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalImportarCot')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt=""></button>
    </div>
    <div class="modal-corpo">
      <div class="campo-form" style="margin-bottom:15px;">
        <label>Solicitação Autorizada</label>
        <select id="cotacSolicitacaoSel" onchange="carregarItensSolicitacao(this.value)" class="campo-select" style="width: 100%;">
          <option value="">Selecione uma solicitação...</option>
        </select>
      </div>
      <div id="itensSolicitacaoPreviewContainer" style="display: none;">
        <div class="tabela-container" style="border: 1px solid var(--borda); max-height: 200px; overflow-y: auto;">
          <table class="tabela" id="tabItensSolicitacaoPreview">
            <thead>
              <tr>
                <th style="width: 50px; text-align: center; padding: 8px 12px;">Nº Item</th>
                <th style="padding: 8px 12px;">Produto</th>
                <th style="width: 120px; text-align: right; padding: 8px 12px;">Quantidade</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="modal-rodape">
      <button class="btn btn-secundario" onclick="Scopi.fecharModal('modalImportarCot')">Cancelar</button>
      <button class="btn btn-primario" onclick="importarItensSelecionados()">Importar Itens</button>
    </div>
  </div>
</div>

<script>
const USER_PERFIL = "<?= $usuario['perfil'] ?>";
const USER_NOME = "<?= Auxiliares::escapar($usuario['nome']) ?>";
const USER_ID = <?= (int)$usuario['id'] ?>;
let _idCot = 0;
let _itensCotacaoAnterior = []; // Para desfazer importação

/* ── Navegação interna capa ↔ itens ── */
function mudarAbaEdicaoCot(aba) {
    const blocoCapa  = document.getElementById('blocoCotCapa');
    const blocoItens = document.getElementById('blocoItensCotacao');
    const blocoComp  = document.getElementById('blocoComparativo');
    const btnCapa    = document.getElementById('abaCotCapaBtn');
    const btnItens   = document.getElementById('abaCotItensBtn');
    const btnVoltar  = document.getElementById('btnVoltarCapaCot');
    const btnAvancar = document.getElementById('btnAvancarItensCot');

    if (_idCot === 0) {
        document.getElementById('abasCotacaoEditar').style.display = 'none';
    } else {
        document.getElementById('abasCotacaoEditar').style.display = 'flex';
    }

    if (aba === 'capa') {
        if (blocoCapa)  blocoCapa.style.display  = 'block';
        if (blocoItens) blocoItens.style.display = 'none';
        if (btnCapa)    btnCapa.classList.add('ativa');
        if (btnItens)   btnItens.classList.remove('ativa');
        if (btnVoltar)  btnVoltar.style.display  = 'none';
        if (btnAvancar && _idCot > 0) btnAvancar.style.display = 'inline-flex';
        else if (btnAvancar) btnAvancar.style.display = 'none';
    } else {
        if (blocoCapa)  blocoCapa.style.display  = 'none';
        if (blocoItens) blocoItens.style.display = 'block';
        if (btnCapa)    btnCapa.classList.remove('ativa');
        if (btnItens)   btnItens.classList.add('ativa');
        if (btnVoltar)  btnVoltar.style.display  = 'inline-flex';
        if (btnAvancar) btnAvancar.style.display = 'none';
    }
    if (blocoComp) blocoComp.style.display = (_idCot > 0) ? 'block' : 'none';
    atualizarBotoesCotacao();
}
let _statusCot = 'aberta';
let _cotacaoDados = null;
let _itensCotacao = [];

function formatarMoeda(valor) {
    return parseFloat(valor || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function formatarData(dataStr) {
    if (!dataStr) return '—';
    const partes = dataStr.split('-');
    if (partes.length === 3) {
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }
    return dataStr;
}

const _origAbrirCadastro = Scopi.abrirCadastro;
Scopi.abrirCadastro = function(idModal, idForm) {
    _origAbrirCadastro(idModal, idForm);
    if (idModal === 'modalCotacao') {
        _idCot = 0; _statusCot = 'aberta'; _cotacaoDados = null; _itensCotacao = [];
        document.getElementById('cotacaoIdInput').value = '0';
        document.querySelector('#modalCotacao [data-campo="numero"]').textContent = '';

        // Show/hide blocks for creation vs edit
        document.getElementById('blocoCamposCabecalhoEdicao').style.display = 'none';
        document.getElementById('blocoNovaCotSolicitacao').style.display = 'block';
        document.getElementById('previewItensSolicitacaoNova').style.display = 'none';
        document.getElementById('blocoNovaCotFornecedores').style.display = 'none';
        
        // Load approved requests dropdown
        carregarSolicitacoesAutorizadasCriacao();

        // Reset suppliers checklist
        document.querySelectorAll('#novaCotFornecedoresChecklist input[type=checkbox]').forEach(cb => {
            cb.checked = false;
        });

        // Preencher campos read-only na aba de edição com dados do usuário logado
        document.querySelectorAll('#formCotacao [data-campo]').forEach(el => {
            if (el.tagName === 'INPUT') {
                const campo = el.getAttribute('data-campo');
                if (campo === 'numero') el.value = '';
                else if (campo === 'status_texto') el.value = 'Aberta';
                else if (campo === 'nome_comprador') el.value = USER_NOME || '—';
            }
        });
        document.getElementById('cotacContadorFornInput').value = '0 fornecedor(es)';

        const bStatus = document.querySelector('#modalCotacao [data-badge="status"]');
        if (bStatus) { bStatus.textContent = 'Nova'; bStatus.className = 'badge badge-aberta'; }
        // Limpar campos de data
        const dAb = document.getElementById('cotDataAbertura');
        const dEnc = document.getElementById('cotDataEncerramento');
        if (dAb)  { dAb.value = ''; dAb.removeAttribute('readonly'); }
        if (dEnc) { dEnc.value = ''; dEnc.removeAttribute('readonly'); }
        document.getElementById('bloCotDatas').style.display = 'grid';
        mudarAbaEdicaoCot('capa');
        document.getElementById('btnGerenciarFornecedores').style.display = 'inline-flex';
        document.getElementById('blocoComparativo').style.display = 'none';
        document.getElementById('btnAdicionarItemCot').setAttribute('disabled', '');
        document.getElementById('btnAdicionarItemCot').title = 'Salve a capa da cotação primeiro';

        // Ativar a aba "editar" por padrão
        Scopi.ativarAba('modalCotacao', 'editar');

        // Garantir que apenas "Confirmar Criação" está visível em nova cotação (DEPOIS de ativar aba)
        document.getElementById('btnConfirmarCriacaoCot').style.display = 'inline-flex';
        document.getElementById('btnSalvarCapaCot').style.display = 'none';
        document.getElementById('btnSalvarItensCot').style.display = 'none';

        renderItensCotacaoTabela();
    }
};

const _origAbrirRegistro = Scopi.abrirRegistro;
Scopi.abrirRegistro = async function(idModal, idForm, urlDados, id, abaInicial='editar') {
    if (idModal === 'modalCotacao') {
        _idCot = id;
        await _origAbrirRegistro(idModal, idForm, urlDados, id, 'editar');
        try {
            const resp = await fetch(`${Scopi.url(urlDados)}?id=${id}`, {credentials:'include',headers:{'X-Requested-With':'XMLHttpRequest'}});
            const json = await resp.json();
            if (json.sucesso) {
                _cotacaoDados = json.dados;
                _statusCot = json.dados.status || 'aberta';
                _itensCotacao = json.dados.itens || [];

                // Show/hide blocks for creation vs edit
                document.getElementById('blocoCamposCabecalhoEdicao').style.display = 'block';
                document.getElementById('blocoNovaCotSolicitacao').style.display = 'none';
                document.getElementById('previewItensSolicitacaoNova').style.display = 'none';
                document.getElementById('blocoNovaCotFornecedores').style.display = 'none';

                // Preencher campos read-only na aba de edição
                const dados = json.dados;
                document.querySelectorAll('#formCotacao [data-campo]').forEach(el => {
                    if (el.tagName === 'INPUT') {
                        const campo = el.getAttribute('data-campo');
                        if (campo === 'numero') el.value = dados.numero || '';
                        else if (campo === 'status_texto') el.value = dados.status ? Scopi.formatarStatus(dados.status) : '—';
                        else if (campo === 'nome_comprador') el.value = dados.nome_comprador || '—';
                    }
                });
                document.getElementById('cotacContadorFornInput').value = (dados.total_fornecedores || 0) + ' fornecedor(es)';

                document.getElementById('cotacaoIdInput').value = id;
                document.querySelector('#modalCotacao [data-campo="numero"]').textContent = json.dados.numero;

                const bStatus = document.querySelector('#modalCotacao [data-badge="status"]');
                if (bStatus) { bStatus.textContent = Scopi.formatarStatus(json.dados.status); bStatus.className = 'badge badge-' + json.dados.status; }

                // Preencher campos de data
                const dAb  = document.getElementById('cotDataAbertura');
                const dEnc = document.getElementById('cotDataEncerramento');
                if (dAb)  dAb.value  = json.dados.data_abertura    || '';
                if (dEnc) dEnc.value = json.dados.data_encerramento || '';
                // Se não for aberta, tornar readonly
                const editavel = _statusCot === 'aberta' && (USER_PERFIL === 'comprador' || USER_PERFIL === 'administrador');
                if (dAb)  { if (editavel) dAb.removeAttribute('readonly');  else dAb.setAttribute('readonly',''); }
                if (dEnc) { if (editavel) dEnc.removeAttribute('readonly'); else dEnc.setAttribute('readonly',''); }

                const numFornecedores = (json.dados.fornecedores || []).length;

                // Sugestão automática por categoria (RF10)
                _marcarFornecedoresSugeridos(json.dados.itens || []);
                
                if (_statusCot === 'aberta' && (USER_PERFIL === 'comprador' || USER_PERFIL === 'administrador')) {
                    document.getElementById('btnGerenciarFornecedores').style.display = 'inline-flex';
                    mudarAbaEdicaoCot('capa');
                    document.getElementById('acoesItensCotacao').style.display = 'flex';
                    document.getElementById('blocoInclusaoManualCot').style.display = 'flex';
                    document.getElementById('btnAdicionarItemCot').removeAttribute('disabled');
                    document.getElementById('btnAdicionarItemCot').title = 'Adicionar item à cotação';

                    document.getElementById('btnConfirmarCriacaoCot').style.display = 'none';
                    document.getElementById('btnSalvarCapaCot').style.display = 'none';
                    document.getElementById('btnSalvarItensCot').style.display = 'inline-flex';
                } else {
                    document.getElementById('btnGerenciarFornecedores').style.display = 'none';
                    mudarAbaEdicaoCot('capa');
                    document.getElementById('acoesItensCotacao').style.display = 'none';
                    document.getElementById('blocoInclusaoManualCot').style.display = 'none';
                    document.getElementById('btnAdicionarItemCot').setAttribute('disabled', '');
                    document.getElementById('btnAdicionarItemCot').title = 'Esta cotação não pode ser editada';

                    document.getElementById('btnConfirmarCriacaoCot').style.display = 'none';
                    document.getElementById('btnSalvarCapaCot').style.display = 'none';
                    document.getElementById('btnSalvarItensCot').style.display = 'none';
                }
                
                renderItensCotacaoTabela();
                
                document.getElementById('blocoComparativo').style.display = 'block';
                renderMatrix(json.dados);
            }
        } catch(e) {
            console.error(e);
        }
    } else {
        await _origAbrirRegistro(idModal, idForm, urlDados, id, abaInicial);
    }
};

async function salvarCapaCotacao() {
    const dAb  = document.getElementById('cotDataAbertura');
    const dEnc = document.getElementById('cotDataEncerramento');
    if (!dAb?.value || !dEnc?.value) { Scopi.toast('erro', 'Informe as datas de abertura e encerramento.'); return; }
    if (dEnc.value < dAb.value) { Scopi.toast('erro', 'A data de encerramento não pode ser anterior à abertura.'); return; }

    const formData = new FormData(document.getElementById('formCotacao'));
    try {
        const resp = await fetch(Scopi.url('/cotacoes/salvarCapa'), { method:'POST', credentials:'include', body:formData, headers:{'X-Requested-With':'XMLHttpRequest'} });
        const json = await resp.json();
        if (json.sucesso) {
            Scopi.toast('sucesso', json.mensagem);
            _idCot = json.dados.id;
            document.getElementById('cotacaoIdInput').value = _idCot;
            document.getElementById('btnGerenciarFornecedores').style.display = 'inline-flex';
            document.getElementById('btnSalvarCapaCot').style.display = 'none';
            document.getElementById('btnSalvarItensCot').style.display = 'inline-flex';
            document.getElementById('btnAdicionarItemCot').removeAttribute('disabled');
            document.getElementById('btnAdicionarItemCot').title = 'Adicionar item à cotação';
            if (dAb)  dAb.setAttribute('readonly', '');
            if (dEnc) dEnc.setAttribute('readonly', '');
            mudarAbaEdicaoCot('itens');
            Scopi.abrirRegistro('modalCotacao', 'formCotacao', '/cotacoes/dados', _idCot);
        } else {
            Scopi.toast('erro', json.mensagem);
        }
    } catch(e) {
        Scopi.toast('erro', 'Erro ao salvar capa da cotação.');
    }
}

/* ── RF10: Marcar fornecedores sugeridos por correspondência de categoria ── */
function _marcarFornecedoresSugeridos(itens) {
    // Coletar categoria_ids dos itens da cotação
    const categoriasItens = new Set(itens.map(i => String(i.categoria_id)).filter(Boolean));

    document.querySelectorAll('#fornecedoresChecklist .fornecedor-chk-item').forEach(item => {
        const catsForn = (item.dataset.categorias || '').split(',').filter(Boolean);
        const sugerido = catsForn.some(c => categoriasItens.has(c));
        const badge = item.querySelector('.badge-sugerido-forn');
        if (badge) badge.style.display = sugerido ? 'inline' : 'none';
    });
}

async function salvarItensCotacao() {
    if (_idCot === 0) return;
    const formData = new FormData();
    formData.append('id', _idCot);
    formData.append('itens_json', JSON.stringify(_itensCotacao));
    
    try {
        const resp = await fetch(Scopi.url('/cotacoes/salvarItens'), { method:'POST', credentials:'include', body:formData, headers:{'X-Requested-With':'XMLHttpRequest'} });
        const json = await resp.json();
        if (json.sucesso) {
            Scopi.toast('sucesso', json.mensagem);
            setTimeout(() => location.reload(), 900);
        } else {
            Scopi.toast('alerta', json.mensagem);
        }
    } catch(e) {
        Scopi.toast('alerta', 'Erro ao salvar itens.');
    }
}

function renderItensCotacaoTabela() {
    const tbody = document.querySelector('#tabItensCotacao tbody');
    tbody.innerHTML = '';
    
    if (_itensCotacao.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#888;">Nenhum produto adicionado</td></tr>';
        return;
    }

    _itensCotacao.forEach((it, idx) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="text-align:center; padding:8px 12px;">${idx+1}</td>
            <td style="padding: 8px 12px;">${it.nome_produto} (${it.codigo_produto || ''})</td>
            <td style="text-align:right; padding: 8px 12px;">${parseFloat(it.quantidade).toFixed(2)}</td>
            <td style="padding: 8px 12px; font-size:0.85rem;">${it.prazo_entrega || '—'}</td>
            <td style="text-align:center; padding: 8px 12px;">
                ${_statusCot === 'aberta' ? `
                    <button type="button" class="btn-icone" onclick="removerItemCotacaoTabela(${idx})" title="Remover">
                        <img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" style="width:12px;filter:brightness(0) saturate(100%) invert(18%) sepia(85%) saturate(2200%) hue-rotate(330deg);" alt="">
                    </button>
                    ${it.id ? `
                    <button type="button" class="btn-icone" onclick="excluirItemCotacaoServidor(${it.id})" title="Excluir do Banco de Dados">
                        <img src="<?= BASE_URL ?>/public/assets/icons/iconeLixeira.svg" style="width:12px;filter:brightness(0) saturate(100%) invert(18%) sepia(85%) saturate(2200%) hue-rotate(330deg);" alt="">
                    </button>` : ''}
                ` : ''}
            </td>
        `;
        tbody.appendChild(tr);
    });
    
    const input = document.getElementById('itensCotJsonInput');
    if (input) {
        input.value = JSON.stringify(_itensCotacao);
    }

    atualizarBotoesCotacao();
}

function excluirItemCotacaoServidor(itemId) {
    Scopi.confirmar('Deseja realmente apagar este item? A solicitação será revertida para "Em Aberto".', () => {
        excluirItemCotacaoServidor_confirmed(itemId);
    });
    return;
}

function excluirItemCotacaoServidor_confirmed(itemId) {
    
    fetch(Scopi.url('/cotacoes/excluir_item'), {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: 'id=' + itemId
    })
    .then(r => r.json())
    .then(json => {
        if (json.sucesso) {
            Scopi.toast('sucesso', json.mensagem);
            _itensCotacao = _itensCotacao.filter(i => parseInt(i.id) !== parseInt(itemId));
            renderItensCotacaoTabela();
        } else {
            Scopi.toast('alerta', json.mensagem);
        }
    })
    .catch(e => Scopi.toast('alerta', 'Erro ao apagar.'));
}

async function buscarProdutoCotacao(codigo) {
    if (!codigo) return;
    try {
        const resp = await fetch(Scopi.url('/produtos/consultar-codigo?codigo=' + encodeURIComponent(codigo)), {credentials:'include',headers:{'X-Requested-With':'XMLHttpRequest'}});
        const json = await resp.json();
        if (json.sucesso && json.dados) {
            document.getElementById('cotProdutoId').value = json.dados.id;
            document.getElementById('cotProdutoNome').textContent = json.dados.nome;
        } else {
            document.getElementById('cotProdutoId').value = '';
            document.getElementById('cotProdutoNome').textContent = 'Produto não encontrado';
            document.getElementById('cotProdutoNome').style.color = 'var(--alerta)';
        }
    } catch(e) { }
}

function adicionarItemCotacaoTabela() {
    const pId = document.getElementById('cotProdutoId').value;
    const pCod = document.getElementById('cotProdutoCodigo').value;
    let pNome = document.getElementById('cotProdutoNome').textContent;
    const qtd = parseFloat(document.getElementById('cotQtdInput').value);
    const prazo = document.getElementById('cotPrazoSugeridoInput').value.trim();

    if (!pId || !pNome || pNome === 'Produto não encontrado' || isNaN(qtd) || qtd <= 0) {
        Scopi.toast('alerta', 'Selecione um produto válido e informe a quantidade.');
        return;
    }

    const idx = _itensCotacao.findIndex(i => parseInt(i.produto_id) === parseInt(pId));
    if (idx >= 0) {
        _itensCotacao[idx].quantidade = parseFloat(_itensCotacao[idx].quantidade) + qtd;
        if (prazo) _itensCotacao[idx].prazo_entrega = prazo;
    } else {
        _itensCotacao.push({
            produto_id: pId,
            codigo_produto: pCod,
            nome_produto: pNome,
            quantidade: qtd,
            prazo_entrega: prazo
        });
    }

    document.getElementById('cotProdutoId').value = '';
    document.getElementById('cotProdutoCodigo').value = '';
    document.getElementById('cotProdutoNome').textContent = '';
    document.getElementById('cotQtdInput').value = '';
    document.getElementById('cotPrazoSugeridoInput').value = '';

    renderItensCotacaoTabela();
}

function removerItemCotacaoTabela(idx) {
    _itensCotacao.splice(idx, 1);
    atualizarBotoesCotacao();
    renderItensCotacaoTabela();
}

function desfazerImportacao() {
    if (_itensCotacaoAnterior.length === 0) {
        Scopi.toast('alerta', 'Nenhuma importação para desfazer.');
        return;
    }
    Scopi.confirmar('Deseja desfazer a importação de itens?', () => {
        _itensCotacao = JSON.parse(JSON.stringify(_itensCotacaoAnterior));
        _itensCotacaoAnterior = [];
        atualizarBotoesCotacao();
        renderItensCotacaoTabela();
        Scopi.toast('sucesso', 'Importação desfeita.');
    });
}

function atualizarBotoesCotacao() {
    const temHistorico = _itensCotacaoAnterior && _itensCotacaoAnterior.length > 0;
    const btnDesfazer = document.getElementById('btnDesfazerImportacao');
    if (btnDesfazer) btnDesfazer.style.display = temHistorico ? 'inline-flex' : 'none';
}

function abrirModalFornecedores() {
    // Limpar filtros
    document.getElementById('filtroFornCodigoCot').value = '';
    document.getElementById('filtroFornRazaoSocialCot').value = '';
    document.getElementById('filtroFornFantasiaCot').value = '';
    document.getElementById('filtroFornCnpjCot').value = '';
    document.getElementById('filtroFornTipoCot').value = '';

    document.querySelectorAll('#fornecedoresChecklist input[type=checkbox]').forEach(cb => {
        cb.checked = false;
        cb.disabled = false;
    });

    // Mostrar todos os fornecedores
    document.querySelectorAll('.fornecedor-chk-item').forEach(item => {
        item.style.display = 'flex';
    });

    if (_cotacaoDados && _cotacaoDados.fornecedores) {
        _cotacaoDados.fornecedores.forEach(f => {
            const cb = document.getElementById('forn_chk_' + f.fornecedor_id);
            if (cb) {
                cb.checked = true;
                cb.disabled = true; // Já enviado
            }
        });
    }
    
    _marcarFornecedoresSugeridos(_itensCotacao);
    Scopi.abrirModal('modalFornecedoresCotacao');
}

async function enviarConvitesFornecedores() {
    if (_idCot === 0) return;
    
    const fornecedorIds = [];
    document.querySelectorAll('#fornecedoresChecklist input[type=checkbox]:checked:not(:disabled)').forEach(cb => {
        fornecedorIds.push(cb.value);
    });
    
    if (fornecedorIds.length === 0) {
        Scopi.toast('alerta', 'Nenhum novo fornecedor selecionado para convite.');
        return;
    }
    
    const formData = new FormData();
    formData.append('cotacao_id', _idCot);
    fornecedorIds.forEach(id => formData.append('fornecedores[]', id));
    
    try {
        const resp = await fetch(Scopi.url('/cotacoes/convidarFornecedores'), { method:'POST', credentials:'include', body:formData, headers:{'X-Requested-With':'XMLHttpRequest'} });
        const json = await resp.json();
        if (json.sucesso) {
            Scopi.toast('sucesso', json.mensagem);
            Scopi.fecharModal('modalFornecedoresCotacao');
            Scopi.abrirRegistro('modalCotacao', 'formCotacao', '/cotacoes/dados', _idCot);
        } else {
            Scopi.toast('alerta', json.mensagem);
        }
    } catch(e) {
        Scopi.toast('alerta', 'Erro ao convidar fornecedores.');
    }
}

function abrirModalImportarItens() {
    document.getElementById('itensSolicitacaoPreviewContainer').style.display = 'none';
    document.getElementById('cotacSolicitacaoSel').innerHTML = '<option value="">Carregando...</option>';
    carregarSolicitacoesAutorizadas();
    Scopi.abrirModal('modalImportarCot');
}

async function carregarSolicitacoesAutorizadas() {
    const sel = document.getElementById('cotacSolicitacaoSel');
    if (!sel) return;
    sel.innerHTML = '<option value="">Carregando solicitações...</option>';
    
    try {
        const resp = await fetch(Scopi.url('/solicitacoes/autorizadas'), {credentials:'include',headers:{'X-Requested-With':'XMLHttpRequest'}});
        const json = await resp.json();
        if (json.sucesso && json.dados) {
            let html = '<option value="">Selecione uma solicitação...</option>';
            json.dados.forEach(s => {
                html += `<option value="${s.id}">Solicitação: ${s.numero} - Dep: ${s.nome_departamento || '—'} (Solicitante: ${s.nome_solicitante || '—'})</option>`;
            });
            sel.innerHTML = html;
        } else {
            sel.innerHTML = '<option value="">Nenhuma solicitação autorizada encontrada.</option>';
        }
    } catch(e) {
        sel.innerHTML = '<option value="">Erro ao carregar solicitações.</option>';
    }
}

async function carregarItensSolicitacao(solicitacaoId) {
    const preview = document.getElementById('itensSolicitacaoPreviewContainer');
    const tbody = document.querySelector('#tabItensSolicitacaoPreview tbody');
    if (!preview || !tbody) return;
    
    if (!solicitacaoId) {
        preview.style.display = 'none';
        tbody.innerHTML = '';
        return;
    }
    
    tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:10px;">Carregando itens...</td></tr>';
    preview.style.display = 'block';
    
    try {
        const resp = await fetch(Scopi.url(`/solicitacoes/dados?id=${solicitacaoId}`), {credentials:'include',headers:{'X-Requested-With':'XMLHttpRequest'}});
        const json = await resp.json();
        if (json.sucesso && json.dados && json.dados.itens) {
            tbody.innerHTML = '';
            json.dados.itens.forEach((item, idx) => { if(item.status !== "autorizada") return;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="width: 50px; text-align: center; padding: 8px 12px;"><input type="checkbox" class="chk-item-imp" value='${JSON.stringify({solicitacao_item_id: item.id, produto_id: item.produto_id, codigo_produto: item.codigo_produto, nome_produto: item.nome_produto, quantidade: item.quantidade})}'></td>
                    <td style="padding: 8px 12px;">${item.nome_produto} (${item.codigo_produto || '—'})</td>
                    <td style="text-align: right; padding: 8px 12px; font-weight: 600;">${parseFloat(item.quantidade).toFixed(2)}</td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:10px;color:var(--alerta);">Erro ao carregar os itens desta solicitação.</td></tr>';
        }
    } catch(e) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:10px;color:var(--alerta);">Erro na comunicação.</td></tr>';
    }
}

async function importarItensSelecionados() {
    const selecionados = document.querySelectorAll('.chk-item-imp:checked');
    if (selecionados.length === 0) {
        Scopi.toast('alerta', 'Selecione ao menos um item para importar.');
        return;
    }

    // Salvar estado anterior para desfazer
    _itensCotacaoAnterior = JSON.parse(JSON.stringify(_itensCotacao));

    const sel = document.getElementById('cotacSolicitacaoSel');
    const solicitacaoId = sel ? parseInt(sel.value) : 0;

    if (solicitacaoId > 0 && _idCot > 0) {
        try {
            const resp = await fetch(Scopi.url('/cotacoes/vincular-solicitacao'), {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: new URLSearchParams({ cotacao_id: _idCot, solicitacao_id: solicitacaoId })
            });
            const json = await resp.json();
            if (!json.sucesso) {
                Scopi.toast('alerta', json.mensagem || 'Erro ao vincular solicitação.');
            }
        } catch(e) {
            console.error(e);
        }
    }
    
    selecionados.forEach(chk => {
        const item = JSON.parse(chk.value);
        const idx = _itensCotacao.findIndex(i => parseInt(i.produto_id) === parseInt(item.produto_id));
        if (idx >= 0) {
            _itensCotacao[idx].quantidade = parseFloat(_itensCotacao[idx].quantidade) + parseFloat(item.quantidade);
        } else {
            _itensCotacao.push(item);
        }
    });
    
    renderItensCotacaoTabela();
    Scopi.toast('sucesso', 'Itens importados com sucesso.');
    Scopi.fecharModal('modalImportarCot');
}

function filtrarFornecedoresChecklist() {
    const codigo = document.getElementById('filtroFornCodigoCot')?.value.toLowerCase().trim() || '';
    const razao = document.getElementById('filtroFornRazaoSocialCot')?.value.toLowerCase().trim() || '';
    const fantasia = document.getElementById('filtroFornFantasiaCot')?.value.toLowerCase().trim() || '';
    const cnpj = document.getElementById('filtroFornCnpjCot')?.value.toLowerCase().trim() || '';
    const tipo = document.getElementById('filtroFornTipoCot')?.value.toLowerCase().trim() || '';
    const categoriaId = document.getElementById('filtroFornCategoriaIdCot')?.value.trim() || '';

    document.querySelectorAll('.fornecedor-chk-item').forEach(item => {
        const itemCodigo = (item.dataset.codigo || '').toLowerCase();
        const itemRazao = (item.dataset.razao || '').toLowerCase();
        const itemFantasia = (item.dataset.fantasia || '').toLowerCase();
        const itemCnpj = (item.dataset.cnpj || '').toLowerCase();
        const itemTipo = (item.dataset.tipo || '').toLowerCase();
        const itemCategorias = (item.dataset.categorias || '').split(',').filter(Boolean);

        let match = true;

        if (codigo && !itemCodigo.includes(codigo)) match = false;
        if (razao && !itemRazao.includes(razao)) match = false;
        if (fantasia && !itemFantasia.includes(fantasia)) match = false;
        if (cnpj && !itemCnpj.includes(cnpj)) match = false;
        if (tipo && !itemTipo.includes(tipo)) match = false;
        if (categoriaId && !itemCategorias.includes(categoriaId)) match = false;

        item.style.display = match ? 'flex' : 'none';
    });
}

function confirmarVencedoresItens(cotacaoId) {
    const radios = document.querySelectorAll('.vencedor-radio-item:checked');
    if (radios.length === 0) {
        Scopi.toast('alerta', 'Selecione o vencedor de pelo menos um item da cotação.');
        return;
    }

    const propostaIds = Array.from(radios).map(r => parseInt(r.value));

    Scopi.confirmar('Deseja aprovar os fornecedores selecionados para cada item?', () => {
        Scopi.confirmar('Deseja gerar as ORDENS DE COMPRA automaticamente agora?', () => {
            _confirmarVencedoresItens_execute(cotacaoId, propostaIds, true);
        }, () => {
            _confirmarVencedoresItens_execute(cotacaoId, propostaIds, false);
        });
    });
}

function _confirmarVencedoresItens_execute(cotacaoId, propostaIds, gerarOC) {
    const btnToast = Scopi.toast('info', 'Processando...', 5000);

    fetch(Scopi.url('/cotacoes/selecionar-vencedor'), {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            cotacao_id: cotacaoId,
            proposta_ids: propostaIds,
            gerar_oc: gerarOC ? 1 : 0
        })
    })
    .then(r => r.json())
    .then(json => {
        if (json.sucesso) {
            Scopi.toast('sucesso', json.mensagem);
            Scopi.fecharModal('modalCotacao');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            Scopi.toast('alerta', json.mensagem);
        }
    })
    .catch(e => Scopi.toast('alerta', 'Falha na requisição.'));
}

function renderMatrix(cotacao) {
    const container = document.getElementById('comparativoMatrixContainer');
    if (!container) return;

    const itens = cotacao.itens || [];
    const fornecedores = cotacao.fornecedores || [];

    if (fornecedores.length === 0) {
        container.innerHTML = '<div style="padding: 20px; text-align: center; color: #888;">Nenhum fornecedor convidado para esta cotação.</div>';
        return;
    }

    let html = `
        <table class="tabela" style="width:100%; border-collapse:collapse; min-width:800px;">
            <thead>
                <tr>
                    <th style="padding: 10px; border-bottom: 2px solid var(--borda); text-align: left; background-color: var(--fundo-app);">Produto / Item</th>
                    <th style="padding: 10px; border-bottom: 2px solid var(--borda); text-align: right; width: 80px; background-color: var(--fundo-app);">Qtd.</th>
    `;

    fornecedores.forEach(f => {
        let statusText = Scopi.formatarStatus(f.status);
        let badgeClass = 'badge-pendente';
        if (f.status === 'respondido') badgeClass = 'badge-concluida';
        if (f.status === 'recusado') badgeClass = 'badge-cancelada';
        if (f.status === 'visualizado') badgeClass = 'badge-em-aberta';

        html += `
            <th style="padding: 10px; border-bottom: 2px solid var(--borda); text-align: center; min-width: 200px; border-left: 1px solid var(--borda); background-color: var(--fundo-app);">
                <div style="font-weight: 600; font-size: 0.82rem; color: var(--texto);">${f.razao_social}</div>
                <div style="font-size: 0.72rem; color: #666; margin-top: 2px;">CNPJ: ${f.cnpj}</div>
                <div style="margin-top: 6px;"><span class="badge ${badgeClass}">${statusText}</span></div>
            </th>
        `;
    });

    html += `
                </tr>
            </thead>
            <tbody>
    `;

    // 1. Render items row by row
    itens.forEach(item => {
        html += `
            <tr style="border-bottom: 1px solid var(--borda);">
                <td style="padding: 10px; font-weight: 500;">
                    ${item.nome_produto}
                    <div style="font-size: 0.72rem; color: #888; margin-top: 2px;">Cód: ${item.codigo_produto || '—'}</div>
                </td>
                <td style="padding: 10px; text-align: right; font-weight: 500;">${parseFloat(item.quantidade).toFixed(2)}</td>
        `;

        fornecedores.forEach(f => {
            const prop = f.propostas ? f.propostas[item.produto_id] : null;
            html += `<td style="padding: 10px; text-align: center; border-left: 1px solid var(--borda); vertical-align: top;">`;
            if (f.status === 'respondido' && prop) {
                const sub = parseFloat(prop.preco_unitario) * parseFloat(item.quantidade);
                const taxas = parseFloat(prop.taxas || 0);
                const subTotalItem = sub + taxas;
                const isWinner = parseInt(prop.vencedora) === 1;

                html += `
                    <div style="${isWinner ? 'border: 2px solid var(--sucesso); background-color: rgba(76, 175, 80, 0.05); padding: 8px; border-radius: 6px; position: relative;' : ''}">
                        ${isWinner ? `<span class="badge badge-concluida" style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%); font-size: 0.65rem; padding: 2px 6px;">✓ Vencedora</span>` : ''}
                        <div style="font-weight: 600; color: var(--media);">${formatarMoeda(prop.preco_unitario)}</div>
                        <div style="font-size: 0.75rem; color: #666; margin-top: 2px;">Subtotal: ${formatarMoeda(sub)}</div>
                        ${taxas > 0 ? `
                            <div style="font-size: 0.72rem; color: var(--alerta); margin-top: 1px;">Taxas: +${formatarMoeda(taxas)}</div>
                            <div style="font-size: 0.75rem; color: #2e7d32; font-weight: 600;">Total: ${formatarMoeda(subTotalItem)}</div>
                        ` : ''}
                        <div style="font-size: 0.72rem; color: #444; margin-top: 4px;">Prazo: ${prop.prazo_entrega} dias</div>
                        <div style="font-size: 0.72rem; color: #444; margin-top: 2px;">Cond. Pagto: ${prop.condicao_pagamento || '—'}</div>
                        <div style="font-size: 0.72rem; color: #444; margin-top: 2px;">Garantia: ${prop.garantia || '—'}</div>
                        ${prop.observacao ? `<div style="font-size: 0.70rem; color: #888; font-style: italic; margin-top: 4px; max-width: 180px; margin-left: auto; margin-right: auto; line-height: 1.2;">Obs: ${prop.observacao}</div>` : ''}
                        
                        ${(cotacao.status !== 'fechada' && cotacao.status !== 'cancelada') ? `
                            <div style="margin-top: 8px; border-top: 1px dashed var(--borda); padding-top: 6px;">
                                <label style="font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; color: var(--texto);">
                                    <input type="radio" class="vencedor-radio-item" name="item_vencedor_${item.produto_id}" value="${prop.id}" ${isWinner ? 'checked' : ''} style="cursor: pointer;">
                                    <span>Vencedor</span>
                                </label>
                            </div>
                        ` : ''}
                    </div>
                `;
            } else {
                html += `<span style="color: #bbb; font-size: 0.8rem;">—</span>`;
            }
            html += `</td>`;
        });

        html += `</tr>`;
    });

    const renderHeaderParamRow = (label, getValFn) => {
        let rowHtml = `
            <tr style="border-bottom: 1px solid var(--borda); background-color: rgba(228, 215, 234, 0.15);">
                <td colspan="2" style="padding: 8px 10px; font-weight: 600; font-size: 0.78rem; color: var(--escura);">${label}</td>
        `;
        fornecedores.forEach(f => {
            rowHtml += `<td style="padding: 8px 10px; text-align: center; border-left: 1px solid var(--borda); font-size: 0.78rem;">${getValFn(f)}</td>`;
        });
        rowHtml += `</tr>`;
        return rowHtml;
    };

    html += renderHeaderParamRow('Modalidade Frete', f => {
        if (f.status !== 'respondido') return '—';
        let txt = f.modalidade_frete || 'Não informado';
        if (f.transportadora) txt += ` (${f.transportadora})`;
        return txt;
    });

    html += renderHeaderParamRow('Taxas Adicionais', f => {
        if (f.status !== 'respondido') return '—';
        return formatarMoeda(f.taxas_adicionais);
    });

    html += renderHeaderParamRow('Prazo de Entrega', f => {
        if (f.status !== 'respondido') return '—';
        return f.prazo_entrega ? f.prazo_entrega + ' dias' : 'Não informado';
    });

    html += renderHeaderParamRow('Validade Proposta', f => {
        if (f.status !== 'respondido') return '—';
        return formatarData(f.validade_proposta);
    });

    html += renderHeaderParamRow('Observações Gerais', f => {
        if (f.status !== 'respondido') return '—';
        return f.observacao ? `<div style="font-size:0.75rem; text-align:left; max-width:200px; margin:0 auto; max-height:80px; overflow-y:auto; line-height:1.3; font-style:italic;">${f.observacao}</div>` : '—';
    });

    html += `
        <tr style="border-bottom: 2px solid var(--media); background-color: rgba(228, 215, 234, 0.3); font-weight: 600;">
            <td colspan="2" style="padding: 10px; font-size: 0.82rem; color: var(--escura);">Valor Total</td>
    `;
    fornecedores.forEach(f => {
        html += `<td style="padding: 10px; text-align: center; border-left: 1px solid var(--borda); font-size: 0.85rem; color: var(--media);">`;
        if (f.status === 'respondido') {
            let subtotalItens = 0;
            itens.forEach(item => {
                const prop = f.propostas ? f.propostas[item.produto_id] : null;
                if (prop) {
                    const sub = parseFloat(prop.preco_unitario) * parseFloat(item.quantidade);
                    const taxas = parseFloat(prop.taxas || 0);
                    subtotalItens += Math.max(0, sub + taxas);
                }
            });
            const taxasGlobais = parseFloat(f.taxas_adicionais || 0);

            const totalGeral = Math.max(0, subtotalItens + taxasGlobais);
            html += `<span style="font-weight:700;">${formatarMoeda(totalGeral)}</span>`;
        } else {
            html += `—`;
        }
        html += `</td>`;
    });
    html += `</tr>`;

    html += `
        <tr>
            <td colspan="2" style="padding: 12px 10px;"></td>
    `;
    fornecedores.forEach(f => {
        html += `<td style="padding: 12px 10px; text-align: center; border-left: 1px solid var(--borda);">`;
        if (parseInt(f.vencedora) === 1) {
            html += `<span class="badge badge-concluida" style="padding: 6px 12px; font-size: 0.78rem;">✓ Vencedor (OC Gerada)</span>`;
        } else {
            if (cotacao.status !== 'fechada' && cotacao.status !== 'cancelada') {
                html += `<span style="color: #888; font-size: 0.75rem; font-style: italic;">Aguardando escolha</span>`;
            } else {
                html += `<span style="color: #bbb; font-size: 0.75rem;">—</span>`;
            }
        }
        html += `</td>`;
    });
    html += `</tr>`;

    html += `
            </tbody>
        </table>
    `;

    if (cotacao.status !== 'fechada' && cotacao.status !== 'cancelada') {
        if (USER_PERFIL === 'comprador' || USER_PERFIL === 'administrador') {
            html += `
                <div style="display: flex; justify-content: flex-end; margin-top: 20px; padding: 15px; border-top: 1px solid var(--borda); background-color: var(--fundo-app); border-radius: 6px;">
                    <button type="button" class="btn" style="background-color: var(--media); color: var(--branco); font-weight: 600; padding: 10px 20px; font-size: 0.85rem;" 
                            onclick="confirmarVencedoresItens(${cotacao.id})">
                        Confirmar Vencedores e Gerar OCs
                    </button>
                </div>
            `;
        }
    }

    container.innerHTML = html;
}

// ==========================================
// FUNÇÕES PARA O NOVO FLUXO DE CRIAÇÃO
// ==========================================
async function carregarSolicitacoesAutorizadasCriacao() {
    const sel = document.getElementById('novaCotSolicitacaoSel');
    if (!sel) return;
    sel.innerHTML = '<option value="">Carregando solicitações...</option>';
    
    try {
        const resp = await fetch(Scopi.url('/solicitacoes/autorizadas'), {credentials:'include',headers:{'X-Requested-With':'XMLHttpRequest'}});
        const json = await resp.json();
        if (json.sucesso && json.dados) {
            let html = '<option value="">Selecione uma solicitação...</option>';
            json.dados.forEach(s => {
                html += `<option value="${s.id}">Solicitação: ${s.numero} - Dep: ${s.nome_departamento || '—'} (Solicitante: ${s.nome_solicitante || '—'})</option>`;
            });
            sel.innerHTML = html;
        } else {
            sel.innerHTML = '<option value="">Nenhuma solicitação autorizada encontrada.</option>';
        }
    } catch(e) {
        sel.innerHTML = '<option value="">Erro ao carregar solicitações.</option>';
    }
}

async function aoSelecionarSolicitacaoNovaCot(solicitacaoId) {
    const previewDiv = document.getElementById('previewItensSolicitacaoNova');
    const tbody = document.querySelector('#tabPreviewItensSolicitacaoNova tbody');
    const fornDiv = document.getElementById('blocoNovaCotFornecedores');
    
    if (!solicitacaoId) {
        previewDiv.style.display = 'none';
        tbody.innerHTML = '';
        fornDiv.style.display = 'none';
        return;
    }
    
    tbody.innerHTML = '<tr><td colspan="2" style="text-align:center;padding:10px;">Carregando itens...</td></tr>';
    previewDiv.style.display = 'block';
    
    try {
        const resp = await fetch(Scopi.url(`/solicitacoes/dados?id=${solicitacaoId}`), {credentials:'include',headers:{'X-Requested-With':'XMLHttpRequest'}});
        const json = await resp.json();
        if (json.sucesso && json.dados && json.dados.itens) {
            tbody.innerHTML = '';
            
            const itensValidos = json.dados.itens.filter(item => {
                return item.status !== 'em_cotacao' && item.status !== 'concluido' && item.status !== 'cancelado';
            });
            
            if (itensValidos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="2" style="text-align:center;padding:10px;color:var(--alerta);">Esta solicitação não possui itens pendentes para cotação.</td></tr>';
                fornDiv.style.display = 'none';
                return;
            }
            
            itensValidos.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="padding: 8px 12px;">${item.nome_produto} (${item.codigo_produto || '—'})</td>
                    <td style="text-align: right; padding: 8px 12px; font-weight: 600;">${parseFloat(item.quantidade).toFixed(2)}</td>
                `;
                tbody.appendChild(tr);
            });
            
            fornDiv.style.display = 'block';
            
            // Mark suggested suppliers
            const categoriasItens = new Set(itensValidos.map(i => String(i.categoria_id)).filter(Boolean));
            document.querySelectorAll('#novaCotFornecedoresChecklist .fornecedor-chk-item-nova').forEach(item => {
                const catsForn = (item.dataset.categorias || '').split(',').filter(Boolean);
                const sugerido = catsForn.some(c => categoriasItens.has(c));
                const badge = item.querySelector('.badge-sugerido-forn-nova');
                if (badge) badge.style.display = sugerido ? 'inline' : 'none';
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="2" style="text-align:center;padding:10px;color:var(--alerta);">Erro ao carregar os itens desta solicitação.</td></tr>';
            fornDiv.style.display = 'none';
        }
    } catch(e) {
        tbody.innerHTML = '<tr><td colspan="2" style="text-align:center;padding:10px;color:var(--alerta);">Erro na comunicação.</td></tr>';
        fornDiv.style.display = 'none';
    }
}

function filtrarFornecedoresNovaCot() {
    const query = document.getElementById('filtroFornNovaCot').value.toLowerCase().trim();
    document.querySelectorAll('#novaCotFornecedoresChecklist .fornecedor-chk-item-nova').forEach(item => {
        const text = item.dataset.nomeCodigo || '';
        item.style.display = text.includes(query) ? 'flex' : 'none';
    });
}

function marcarTodosFornNovaCot(marcar) {
    document.querySelectorAll('#novaCotFornecedoresChecklist input[type=checkbox]').forEach(cb => {
        if (cb.parentElement.style.display !== 'none') {
            cb.checked = marcar;
        }
    });
}

async function confirmarCriacaoCotacao() {
    const solSel = document.getElementById('novaCotSolicitacaoSel');
    const dAb = document.getElementById('cotDataAbertura');
    const dEnc = document.getElementById('cotDataEncerramento');
    
    if (!solSel || !solSel.value) { Scopi.toast('erro', 'Selecione uma solicitação aprovada.'); return; }
    if (!dAb?.value || !dEnc?.value) { Scopi.toast('erro', 'Informe as datas de abertura e encerramento.'); return; }
    if (dEnc.value < dAb.value) { Scopi.toast('erro', 'A data de encerramento não pode ser anterior à abertura.'); return; }
    
    const fornecedorIds = [];
    document.querySelectorAll('#novaCotFornecedoresChecklist input[type=checkbox]:checked').forEach(cb => {
        fornecedorIds.push(cb.value);
    });
    
    if (fornecedorIds.length === 0) {
        Scopi.toast('erro', 'Selecione ao menos um fornecedor.');
        return;
    }
    
    const btnConfirm = document.getElementById('btnConfirmarCriacaoCot');
    if (btnConfirm) btnConfirm.disabled = true;
    
    const formData = new FormData(document.getElementById('formCotacao'));
    
    try {
        const resp = await fetch(Scopi.url('/cotacoes/criarCompleta'), { 
            method: 'POST', 
            credentials: 'include', 
            body: formData, 
            headers: {'X-Requested-With': 'XMLHttpRequest'} 
        });
        const json = await resp.json();
        if (json.sucesso) {
            Scopi.toast('sucesso', json.mensagem);
            Scopi.fecharModal('modalCotacao');
            setTimeout(() => location.reload(), 1000);
        } else {
            Scopi.toast('erro', json.mensagem);
            if (btnConfirm) btnConfirm.disabled = false;
        }
    } catch(e) {
        Scopi.toast('erro', 'Erro ao criar cotação.');
        if (btnConfirm) btnConfirm.disabled = false;
    }
}
</script>


