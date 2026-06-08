<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Ordens de Compra';</script>
<div class="pagina-cabecalho"><h1 class="pagina-titulo">Ordens de Compra</h1><p class="pagina-subtitulo">Controle e acompanhamento das ordens de compra</p></div>
<div class="painel-filtros">
  <div class="filtros-cabecalho"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt=""><span>Filtros</span></div>
  <form method="GET" action="<?= BASE_URL ?>/ordens"><div class="filtros-campos">
    <div class="campo-filtro"><label>Número</label><input type="text" name="numero" value="<?= Auxiliares::escapar($filtros['numero']??'') ?>"></div>
    <div class="campo-filtro"><label>Data Emissão (De)</label><input type="date" name="data_inicial" value="<?= Auxiliares::escapar($filtros['data_inicial']??'') ?>"></div>
    <div class="campo-filtro"><label>Data Emissão (Até)</label><input type="date" name="data_final" value="<?= Auxiliares::escapar($filtros['data_final']??'') ?>"></div>
    <div class="campo-filtro">
      <label style="display:flex;gap:8px;align-items:center;justify-content:space-between;">
        <span>Cód. Fornecedor</span>
        <button type="button" class="btn btn-secundario" style="padding:4px 6px;margin:0;" title="Buscar fornecedor" onclick="Scopi.iconeBusca('fornecedores','filtroFornOrdCodigo','filtroFornOrdNome')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width:13px;margin:0;" alt="Buscar"></button>
      </label>
      <div style="display:flex;gap:8px;align-items:center;max-width:260px;">
        <input type="text" id="filtroFornOrdCodigo" name="fornecedor_codigo" value="<?= Auxiliares::escapar($filtros['fornecedor_codigo']??'') ?>" class="campo-input" style="width:100px;text-transform:uppercase;" onblur="buscarFornecedorFiltroOrd(this.value)">
        <span id="filtroFornOrdNome" style="font-size:0.8rem;color:var(--texto-secundario);flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= empty($filtros['fornecedor_codigo']) ? 'Digite...' : 'Buscando...' ?></span>
      </div>
    </div>
    <div class="campo-filtro"><label>Status</label>
      <select name="status">
        <option value="">Todos</option>
        <option value="aberto" <?= ($filtros['status']??'')==='aberto'?'selected':'' ?>>Aberto</option>
        <option value="autorizado" <?= ($filtros['status']??'')==='autorizado'?'selected':'' ?>>Autorizado</option>
        <option value="enviado" <?= ($filtros['status']??'')==='enviado'?'selected':'' ?>>Enviado</option>
        <option value="parcialmente_atendido" <?= ($filtros['status']??'')==='parcialmente_atendido'?'selected':'' ?>>Parcialmente Atendido</option>
        <option value="concluido" <?= ($filtros['status']??'')==='concluido'?'selected':'' ?>>Concluído</option>
        <option value="cancelado" <?= ($filtros['status']??'')==='cancelado'?'selected':'' ?>>Cancelado</option>
      </select>
    </div>
    <div class="campo-filtro" style="flex:0;align-self:flex-end;"><button type="submit" class="btn btn-filtrar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt=""> Filtrar</button></div>
  </div></form>
</div>
<div class="barra-acoes">
  <div class="grupo-botoes">
    <?php if(in_array($usuario['perfil'],['comprador','administrador'])): ?>
    <button class="btn btn-primario" onclick="Scopi.abrirCadastro('modalOrdem','formOrdem')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Nova Ordem</button>
    <?php endif; ?>
    <button class="btn btn-secundario" onclick="window.open('<?= BASE_URL ?>/ordens/exportar' + window.location.search, '_blank')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Exportar</button>
  </div>
  <span style="font-size:.82rem;color:#888;"><?= count($ordens) ?> registro(s)</span>
</div>
<div class="tabela-container">
  <table class="tabela">
    <thead><tr>
      <th>Número</th>
      <th>Descrição</th>
      <th>Fornecedor / Cód.</th>
      <th>Emissão</th>
      <th>Valor Total</th>
      <th>Status</th>
      <th class="coluna-acoes"></th>
    </tr></thead>
    <tbody>
      <?php if(empty($ordens)): ?><tr><td colspan="7" style="text-align:center;padding:32px;color:#888;">Nenhuma ordem encontrada.</td></tr>
      <?php else: foreach($ordens as $o): ?>
      <tr>
        <td><span class="cod-clicavel" onclick="Scopi.abrirRegistro('modalOrdem','formOrdem','/ordens/dados',<?= $o['id'] ?>,'visualizar')"><?= Auxiliares::escapar($o['numero']??$o['id']) ?></span></td>
        <td style="font-size:0.82rem;color:#555;"><?= Auxiliares::escapar($o['observacao']??'—') ?></td>
        <td>
          <?= Auxiliares::escapar($o['nome_fornecedor']??'—') ?>
          <?php if(!empty($o['codigo_fornecedor'])): ?><br><span style="font-size:0.75rem;color:#888;"><?= Auxiliares::escapar($o['codigo_fornecedor']) ?></span><?php endif; ?>
        </td>
        <td><?= !empty($o['emitido_em'])?date('d/m/Y',strtotime($o['emitido_em'])):'—' ?></td>
        <td>R$ <?= number_format($o['valor_total']??0,2,',','.') ?></td>
        <td><span class="badge badge-<?= str_replace('_','-',$o['status']) ?>"><?= Auxiliares::formatarStatus($o['status']) ?></span></td>
        <td class="coluna-acoes">
          <button class="btn-icone btn-editar-linha" onclick="Scopi.abrirRegistro('modalOrdem','formOrdem','/ordens/dados',<?= $o['id'] ?>,'editar')" title="Editar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeEditar.svg" alt=""></button>
          <button class="btn-icone" onclick="window.open('<?= BASE_URL ?>/ordens/imprimir?id=<?= $o['id'] ?>', '_blank')" title="Imprimir"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""></button>
        </td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<!-- MODAL ORDEM -->
<div class="overlay-modal" id="modalOrdem">
  <div class="modal modal-largo">
    <div class="modal-cabecalho">
      <div class="modal-titulo"><img src="<?= BASE_URL ?>/public/assets/icons/iconeOC.svg" alt=""><span>Ordem de Compra</span></div>
      <button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalOrdem')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt=""></button>
    </div>
    <div class="modal-abas">
      <button class="aba-btn ativa" data-aba="visualizar" onclick="Scopi.ativarAba('modalOrdem','visualizar')">Visualizar</button>
      <button class="aba-btn" data-aba="editar" onclick="Scopi.ativarAba('modalOrdem','editar')">Editar</button>
    </div>
    <div class="modal-corpo">
      <div class="conteudo-aba ativo" data-aba="visualizar">
        <div class="grade-visualizar">
          <div class="campo-visualizar"><span class="rotulo">Número</span><span class="valor" data-campo="numero"></span></div>
          <div class="campo-visualizar"><span class="rotulo">Status</span><span class="valor"><span class="badge" data-badge="status">—</span></span></div>
          <div class="campo-visualizar"><span class="rotulo">Autorizador</span><span class="valor" data-campo="nome_autorizador">—</span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Fornecedor</span><span class="valor" data-campo="nome_fornecedor">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Data de Emissão</span><span class="valor" data-campo="emitido_em">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Transportadora</span><span class="valor" data-campo="transportadora">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">CNPJ Transportadora</span><span class="valor" data-campo="cnpj_transportadora">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Modalidade de Frete</span><span class="valor" data-campo="modalidade_frete">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Prazo de Entrega</span><span class="valor" data-campo="prazo_entrega">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Valor Total</span><span class="valor" data-campo="valor_total">—</span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Observação</span><span class="valor" data-campo="observacao">—</span></div>
        </div>
        <div class="campo-visualizar campo-completo" style="margin-top:20px;">
          <span class="rotulo" style="margin-bottom:8px;">Itens da Ordem de Compra</span>
          <div class="tabela-container" style="border:1px solid var(--borda);">
            <table class="tabela" id="tabItensOrdVisualizar">
              <thead><tr>
                <th style="padding:8px 12px;">Produto</th>
                <th style="width:100px;text-align:right;padding:8px 12px;">Quantidade</th>
                <th style="width:130px;padding:8px 12px;">Prazo Entrega</th>
                <th style="width:100px;text-align:right;padding:8px 12px;">Cond. Pgto</th>
                <th style="width:120px;text-align:right;padding:8px 12px;">Subtotal</th>
              </tr></thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="conteudo-aba" data-aba="editar">
        <form id="formOrdem" onsubmit="event.preventDefault();">
          <input type="hidden" name="id" value="0">

          <!-- CAMPOS READ-ONLY -->
          <div class="grade-form" style="grid-template-columns: 1fr 1fr; margin-bottom: 15px;">
            <div class="campo-form">
              <label>Número</label>
              <input type="text" readonly class="campo-input" style="cursor: not-allowed;" value="" data-campo="numero">
            </div>
            <div class="campo-form">
              <label>Status</label>
              <input type="text" readonly class="campo-input" style="cursor: not-allowed;" value="Rascunho" data-campo="status_texto">
            </div>
          </div>

          <div class="grade-form" style="grid-template-columns: 1fr 1fr; margin-bottom: 15px;">
            <div class="campo-form">
              <label>Data de Emissão</label>
              <input type="text" readonly class="campo-input" style="cursor: not-allowed;" value="—" data-campo="emitido_em">
            </div>
            <div class="campo-form">
              <label>Comprador</label>
              <input type="text" readonly class="campo-input" style="cursor: not-allowed;" value="—" data-campo="nome_comprador">
            </div>
          </div>

          <div class="grade-form">
            <div class="campo-form campo-completo">
              <label style="display:flex;gap:8px;align-items:center;justify-content:space-between;">
                <span>Cód. Fornecedor *</span>
                <button type="button" class="btn btn-secundario" style="padding:4px 6px;margin:0;" onclick="Scopi.iconeBusca('fornecedores','ordemFornecedorCodigo','ordemFornecedorNome')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width:13px;margin:0;" alt="Buscar"></button>
              </label>
              <div style="display:flex;gap:8px;align-items:center;">
                <input type="text" id="ordemFornecedorCodigo" class="campo-input" style="width:150px;text-transform:uppercase;" onblur="buscarFornecedorOrdem(this.value)" required>
                <span id="ordemFornecedorNome" style="font-size:0.9rem;color:var(--texto-secundario);font-style:italic;">Digite o código do fornecedor...</span>
                <input type="hidden" name="fornecedor_id" id="ordemFornecedorId" value="">
              </div>
            </div>
            <div class="campo-form"><label>Transportadora</label><input type="text" name="transportadora"></div>
            <div class="campo-form"><label>CNPJ Transportadora</label><input type="text" name="cnpj_transportadora"></div>
            <div class="campo-form">
              <label>Modalidade de Frete</label>
              <select name="modalidade_frete">
                <option value="">Selecione...</option>
                <option value="CIF">CIF</option>
                <option value="FOB">FOB</option>
              </select>
            </div>
            <div class="campo-form">
              <label>Valor Total (R$)</label>
              <input type="text" id="ordemValorTotalReadonly" value="R$ 0,00" readonly class="campo-input" style="cursor: not-allowed;">
              <input type="hidden" name="valor_total" id="ordemValorTotalOculto" value="0.00">
            </div>
            <div class="campo-form campo-completo"><label>Observação</label><textarea name="observacao" rows="2"></textarea></div>
            <div id="blocoItensEdicaoOrd" class="campo-form campo-completo" style="margin-top:10px;border-top:1px solid var(--borda);padding-top:15px;display:none;">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <label style="margin:0;">Adicionar Produtos</label>
                <div style="display:flex;gap:8px;">
                  <button type="button" class="btn btn-secundario" onclick="abrirModalImportarOrd()" style="height:30px;font-size:0.8rem;padding:0 10px;">Importar de Solicitação</button>
                  <button type="button" class="btn btn-secundario" id="btnDesfazerImportacaoOrd" style="display:none;height:30px;font-size:0.8rem;padding:0 10px;" onclick="desfazerImportacaoOrd()"><img src="<?= BASE_URL ?>/public/assets/icons/iconeLixeira.svg" style="width:12px;margin-right:4px;filter: brightness(0) invert(1);" alt="">Remover</button>
                </div>
              </div>
              <div style="display:flex;gap:8px;margin-bottom:12px;align-items:center;">
                <div style="display:flex;flex:1;gap:8px;align-items:center;">
                  <input type="text" id="ordProdutoCodigo" class="campo-input" style="width:120px;" onblur="buscarProdutoOrdem(this.value)">
                  <button type="button" class="btn btn-secundario" style="padding:6px 8px;" onclick="Scopi.iconeBusca('produtos','ordProdutoCodigo','ordProdutoNome')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width:13px;margin:0;" alt="Buscar"></button>
                  <span id="ordProdutoNome" style="font-size:0.85rem;color:var(--texto-secundario);font-style:italic;">Digite o código...</span>
                  <input type="hidden" id="ordProdutoId" value="">
                  <input type="hidden" id="ordProdutoNomeHidden" value="">
                </div>
                <input type="number" id="ordQtdInput" class="campo-input" style="width:90px;" min="0.01" step="any" placeholder="Qtd">
                <input type="number" id="ordPrecoInput" class="campo-input" style="width:110px;" min="0.00" step="any" placeholder="Preço">
                <input type="date" id="ordPrazoInput" class="campo-input" style="width:140px;">
                <button type="button" class="btn btn-primario" onclick="adicionarItemTabelaOrd()" style="height:34px;">Adicionar</button>
              </div>
              <div class="tabela-container" style="border:1px solid var(--borda);max-height:200px;overflow-y:auto;">
                <table class="tabela" id="tabItensEditarOrd">
                  <thead><tr>
                    <th style="padding:8px 12px;">Produto</th>
                    <th style="width:90px;text-align:right;padding:8px 12px;">Qtd</th>
                    <th style="width:110px;text-align:right;padding:8px 12px;">Preço Un.</th>
                    <th style="width:140px;padding:8px 12px;">Prazo Entrega</th>
                    <th style="width:110px;text-align:right;padding:8px 12px;">Subtotal</th>
                    <th style="width:50px;padding:8px 12px;"></th>
                  </tr></thead>
                  <tbody></tbody>
                </table>
              </div>
              <input type="hidden" name="itens_json" id="itensOrdJsonInput" value="[]">
            </div>
          </div>
        </form>
      </div>
    </div>
    <div class="modal-rodape">
      <button class="btn btn-perigo" id="btnCancelarOrd" style="margin-right:auto;display:none;" onclick="cancelarOrdem()">Cancelar Ordem</button>
      <button class="btn btn-perigo" id="btnRetirarAutOrd" style="margin-right:auto; display:none;" onclick="Scopi.confirmarAcao('Retirar autorização desta ordem?','/ordens/desautorizar',{id:_idOrd})">Retirar Autorização</button>
      <button class="btn btn-secundario" onclick="Scopi.fecharModal('modalOrdem')">Fechar</button>
      <button class="btn btn-primario btn-salvar-capa" onclick="salvarCapaOrdem()" style="display:none;"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Confirmar Capa</button>
      <button class="btn btn-primario btn-salvar" onclick="salvarItensOrdem()" style="display:none;"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Salvar Itens</button>
    </div>
  </div>
</div>

<!-- MODAL IMPORTAR DE SOLICITAÇÃO -->
<div class="overlay-modal" id="modalImportarOrd">
  <div class="modal">
    <div class="modal-cabecalho">
      <div class="modal-titulo">Importar de Solicitação</div>
      <button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalImportarOrd')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt=""></button>
    </div>
    <div class="modal-corpo">
      <div class="campo-form" style="margin-bottom:15px;">
        <label>Solicitação Autorizada</label>
        <select id="ordSolicitacaoSel" onchange="carregarItensSolicitacaoOrd(this.value)" class="campo-select" style="width:100%;">
          <option value="">Selecione uma solicitação...</option>
        </select>
      </div>
      <div id="itensSolicitacaoPreviewOrdContainer" style="display:none;">
        <div class="tabela-container" style="border:1px solid var(--borda);max-height:200px;overflow-y:auto;">
          <table class="tabela" id="tabItensSolicitacaoPreviewOrd">
            <thead><tr>
              <th style="width:30px;"><input type="checkbox" onchange="document.querySelectorAll('.chk-item-imp-ord').forEach(cb => cb.checked = this.checked)"></th>
              <th>Produto</th>
              <th style="width:120px;text-align:right;">Qtd</th>
            </tr></thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="modal-rodape">
      <button class="btn btn-secundario" onclick="Scopi.fecharModal('modalImportarOrd')">Cancelar</button>
      <button class="btn btn-primario" onclick="importarItensSelecionadosOrd()">Importar Itens</button>
    </div>
  </div>
</div>

<script>
let _idOrd = 0;
let _statusOrd = '';
let _itensOrdem = [];
let _itensOrdemAnterior = []; // Para desfazer importação
const USER_PERFIL_ORD = "<?= $usuario['perfil'] ?? '' ?>";
const USER_NOME_ORD = "<?= Auxiliares::escapar($usuario['nome'] ?? '') ?>";
const USER_ID_ORD = <?= (int)($usuario['id'] ?? 0) ?>;

/* ── Busca filtro ── */
async function buscarFornecedorFiltroOrd(codigo) {
    codigo = (codigo || '').trim();
    const span = document.getElementById('filtroFornOrdNome');
    if (!span) return;
    if (!codigo) { span.textContent = 'Digite...'; span.style.color = 'var(--texto-secundario)'; return; }
    span.textContent = 'Buscando...'; span.style.color = 'var(--texto-secundario)';
    try {
        const resp = await fetch(Scopi.url(`/fornecedores/consultar-codigo?codigo=${encodeURIComponent(codigo)}`), {credentials:'include'});
        const data = await resp.json();
        if (data.sucesso && data.dados) { span.textContent = data.dados.nome; span.style.color = 'var(--sucesso)'; }
        else { span.textContent = 'Não encontrado'; span.style.color = 'var(--alerta)'; }
    } catch(e) { span.textContent = 'Erro'; span.style.color = 'var(--alerta)'; }
}

/* ── Hooks de modal ── */
const _origAbrirRegistroOrd = Scopi.abrirRegistro.bind(Scopi);
Scopi.abrirRegistro = async function(idModal, idForm, url, id, aba) {
    if (idModal === 'modalOrdem') {
        _idOrd = id;
        await _origAbrirRegistroOrd(idModal, idForm, url, id, aba);
        try {
            const resp = await fetch(Scopi.url(`/ordens/dados?id=${id}`), {credentials:'include',headers:{'X-Requested-With':'XMLHttpRequest'}});
            const json = await resp.json();
            if (json.sucesso) {
                _itensOrdem = json.dados.itens || [];

                // Preencher campos read-only na aba de edição
                const dados = json.dados;
                document.querySelectorAll('#formOrdem [data-campo]').forEach(el => {
                    if (el.tagName === 'INPUT') {
                        const campo = el.getAttribute('data-campo');
                        if (campo === 'numero') el.value = dados.numero || '';
                        else if (campo === 'status_texto') el.value = dados.status ? Scopi.formatarStatus(dados.status) : '—';
                        else if (campo === 'emitido_em') el.value = dados.emitido_em ? new Date(dados.emitido_em).toLocaleDateString('pt-BR') : '—';
                        else if (campo === 'nome_comprador') el.value = dados.nome_comprador || '—';
                    }
                });

                const badge = document.querySelector('#modalOrdem [data-badge="status"]');
                _statusOrd = badge ? badge.textContent.trim().toLowerCase() : '';

                const tabEdit = document.querySelector('#modalOrdem .aba-btn[data-aba="editar"]');
                if (tabEdit) tabEdit.style.display = (_statusOrd !== 'aberto') ? 'none' : 'inline-block';

                if (aba === 'editar') {
                    document.getElementById('ordemValorTotalOculto').value = json.dados.valor_total || 0;
                    document.getElementById('ordemValorTotalReadonly').value = parseFloat(json.dados.valor_total || 0).toLocaleString('pt-BR', {style:'currency', currency:'BRL'});
                    const blocoItens = document.getElementById('blocoItensEdicaoOrd');
                    if (blocoItens) blocoItens.style.display = 'block';

                    const fornCod = document.getElementById('ordemFornecedorCodigo');
                    const fornNome = document.getElementById('ordemFornecedorNome');
                    const fornId = document.getElementById('ordemFornecedorId');
                    if (fornCod && json.dados.fornecedor_codigo) {
                        fornCod.value = json.dados.fornecedor_codigo;
                        if (fornNome) { fornNome.textContent = json.dados.nome_fornecedor || ''; fornNome.style.color = 'var(--sucesso)'; }
                        if (fornId) fornId.value = json.dados.fornecedor_id || '';
                    }
                }
                renderItensVisualizarOrd();
                renderItensEditarOrd();
            }
        } catch(e) { console.error(e); }
        atualizarBotoesOrdens(aba);
    } else {
        await _origAbrirRegistroOrd(idModal, idForm, url, id, aba);
    }
};

const _origAbrirCadastroOrd = Scopi.abrirCadastro.bind(Scopi);
Scopi.abrirCadastro = function(idModal, idForm) {
    _origAbrirCadastroOrd(idModal, idForm);
    if (idModal === 'modalOrdem') {
        _idOrd = 0; _statusOrd = 'aberto'; _itensOrdem = [];

        // Preencher campos read-only na aba de edição com data atual e usuário logado
        document.querySelectorAll('#formOrdem [data-campo]').forEach(el => {
            if (el.tagName === 'INPUT') {
                const campo = el.getAttribute('data-campo');
                if (campo === 'numero') el.value = '';
                else if (campo === 'status_texto') el.value = 'Aberto';
                else if (campo === 'emitido_em') el.value = new Date().toLocaleDateString('pt-BR');
                else if (campo === 'nome_comprador') el.value = USER_NOME_ORD || '—';
            }
        });

        const tabEdit = document.querySelector('#modalOrdem .aba-btn[data-aba="editar"]');
        if (tabEdit) tabEdit.style.display = 'inline-block';
        const blocoItens = document.getElementById('blocoItensEdicaoOrd');
        if (blocoItens) blocoItens.style.display = 'none';
        document.getElementById('ordemValorTotalReadonly').value = 'R$ 0,00';
        document.getElementById('ordemValorTotalOculto').value = '0';
        document.getElementById('ordemFornecedorCodigo').value = '';
        document.getElementById('ordemFornecedorNome').textContent = 'Digite o código do fornecedor...';
        document.getElementById('ordemFornecedorNome').style.color = 'var(--texto-secundario)';
        document.getElementById('ordemFornecedorId').value = '';

        // Ativar a aba "editar" por padrão
        Scopi.ativarAba('modalOrdem', 'editar');

        renderItensEditarOrd();
        atualizarBotoesOrdens('editar');
    }
};

const _origAtivarAbaOrd = Scopi.ativarAba.bind(Scopi);
Scopi.ativarAba = function(idModal, aba) {
    _origAtivarAbaOrd(idModal, aba);
    if (idModal === 'modalOrdem') atualizarBotoesOrdens(aba);
};

function atualizarBotoesOrdens(aba) {
    const btnCancelar  = document.getElementById('btnCancelarOrd');
    const btnRetirarAut = document.getElementById('btnRetirarAutOrd');
    const btnSalvar    = document.querySelector('#modalOrdem .btn-salvar');
    const btnCapa      = document.querySelector('#modalOrdem .btn-salvar-capa');
    [btnCancelar, btnRetirarAut, btnSalvar, btnCapa].forEach(b => { if(b) b.style.display = 'none'; });

    if (aba === 'visualizar') {
        // Apenas botões padrão
    } else if (aba === 'editar') {
        if (_idOrd === 0 && btnCapa) btnCapa.style.display = 'inline-flex';
        else if (_statusOrd === 'aberto' && btnSalvar) btnSalvar.style.display = 'inline-flex';
        if (_idOrd > 0 && ['aberto','autorizado'].includes(_statusOrd) && btnCancelar) btnCancelar.style.display = 'inline-flex';
        if (_idOrd > 0 && _statusOrd === 'autorizado' && btnRetirarAut) btnRetirarAut.style.display = 'inline-flex';
    }
}

async function salvarCapaOrdem() {
    const form = document.getElementById('formOrdem');
    if (!form.reportValidity()) return;
    document.getElementById('itensOrdJsonInput').value = '[]';
    const btn = document.querySelector('#modalOrdem .btn-salvar-capa');
    if (btn) { btn.disabled = true; btn.textContent = 'Salvando...'; }
    try {
        const resp = await fetch(Scopi.url('/ordens/salvar'), { method:'POST', credentials:'include', body:new FormData(form), headers:{'X-Requested-With':'XMLHttpRequest'} });
        const json = await resp.json();
        if (json.sucesso && json.dados && json.dados.id) {
            Scopi.toast('sucesso', 'Capa salva! Agora insira os itens.');
            _idOrd = json.dados.id;
            form.querySelector('[name="id"]').value = _idOrd;
            const blocoItens = document.getElementById('blocoItensEdicaoOrd');
            if (blocoItens) blocoItens.style.display = 'block';
            atualizarBotoesOrdens('editar');
        } else {
            Scopi.toast('erro', json.mensagem || 'Erro ao salvar capa.');
        }
    } catch(e) { Scopi.toast('erro', 'Falha na comunicação.'); }
    finally { if (btn) { btn.disabled = false; btn.innerHTML = '<img src="'+SCOPI_BASE+'/public/assets/icons/iconeInserir.svg" alt=""> Confirmar Capa'; } }
}

async function salvarItensOrdem() {
    const form = document.getElementById('formOrdem');
    try {
        const resp = await fetch(Scopi.url('/ordens/salvar'), { method:'POST', credentials:'include', body:new FormData(form), headers:{'X-Requested-With':'XMLHttpRequest'} });
        const json = await resp.json();
        if (json.sucesso) { Scopi.toast('sucesso', json.mensagem || 'Itens salvos.'); setTimeout(()=>location.reload(), 900); }
        else Scopi.toast('erro', json.mensagem || 'Erro ao salvar itens.');
    } catch(e) { Scopi.toast('erro', 'Falha na comunicação.'); }
}

async function buscarFornecedorOrdem(codigo) {
    codigo = (codigo || '').trim();
    const spanNome = document.getElementById('ordemFornecedorNome');
    const inputId  = document.getElementById('ordemFornecedorId');
    if (!codigo) { if(spanNome){spanNome.textContent='Digite o código do fornecedor...';spanNome.style.color='var(--texto-secundario)';} if(inputId) inputId.value=''; return; }
    if(spanNome){spanNome.textContent='Buscando...';spanNome.style.color='var(--texto-secundario)';}
    try {
        const resp = await fetch(Scopi.url(`/fornecedores/consultar-codigo?codigo=${encodeURIComponent(codigo)}`), {credentials:'include'});
        const data = await resp.json();
        if (data.sucesso && data.dados) {
            if(spanNome){spanNome.textContent=data.dados.nome;spanNome.style.color='var(--sucesso)';}
            if(inputId) inputId.value = data.dados.id;
        } else {
            if(spanNome){spanNome.textContent='Fornecedor não encontrado ou inativo';spanNome.style.color='var(--alerta)';}
            if(inputId) inputId.value = '';
        }
    } catch(e) { if(spanNome){spanNome.textContent='Erro ao buscar';spanNome.style.color='var(--alerta)';} }
}

function cancelarOrdem()    { if(!_idOrd) return; Scopi.confirmarAcao('Cancelar esta ordem?','/ordens/cancelar',{id:_idOrd}); }

async function buscarProdutoOrdem(codigo) {
    codigo = (codigo || '').trim();
    const spanNome  = document.getElementById('ordProdutoNome');
    const inputId   = document.getElementById('ordProdutoId');
    const inputNome = document.getElementById('ordProdutoNomeHidden');
    if (!codigo) { if(spanNome){spanNome.textContent='Digite o código...';spanNome.style.color='var(--texto-secundario)';} if(inputId) inputId.value=''; if(inputNome) inputNome.value=''; return; }
    if(spanNome){spanNome.textContent='Buscando...';spanNome.style.color='var(--texto-secundario)';}
    try {
        const resp = await fetch(Scopi.url(`/produtos/consultar-codigo?codigo=${encodeURIComponent(codigo)}`), {credentials:'include'});
        const data = await resp.json();
        if (data.sucesso && data.dados) {
            if(spanNome){spanNome.textContent=`${data.dados.nome} (${data.dados.categoria_nome||'Sem categoria'})`;spanNome.style.color='var(--sucesso)';}
            if(inputId) inputId.value = data.dados.id;
            if(inputNome) inputNome.value = data.dados.nome;
        } else {
            if(spanNome){spanNome.textContent='Produto não encontrado/inativo';spanNome.style.color='var(--alerta)';}
            if(inputId) inputId.value=''; if(inputNome) inputNome.value='';
        }
    } catch(e) { if(spanNome){spanNome.textContent='Erro ao buscar';spanNome.style.color='var(--alerta)';} }
}

function adicionarItemTabelaOrd() {
    const id    = document.getElementById('ordProdutoId').value;
    const nome  = document.getElementById('ordProdutoNomeHidden').value;
    const qtd   = parseFloat(document.getElementById('ordQtdInput').value);
    const preco = parseFloat(document.getElementById('ordPrecoInput').value);
    const prazo = document.getElementById('ordPrazoInput').value.trim();
    if (!id) { Scopi.toast('erro', 'Selecione um produto válido primeiro.'); return; }
    if (isNaN(qtd) || qtd <= 0) { Scopi.toast('erro', 'Informe uma quantidade válida.'); return; }
    if (isNaN(preco) || preco < 0) { Scopi.toast('erro', 'Informe um preço válido.'); return; }
    const idx = _itensOrdem.findIndex(i => parseInt(i.produto_id) === parseInt(id));
    if (idx >= 0) { _itensOrdem[idx].quantidade += qtd; _itensOrdem[idx].preco_unitario = preco; _itensOrdem[idx].prazo_entrega = prazo; }
    else _itensOrdem.push({ produto_id: parseInt(id), produto_nome: nome, quantidade: qtd, preco_unitario: preco, prazo_entrega: prazo });
    document.getElementById('ordProdutoCodigo').value = '';
    document.getElementById('ordProdutoNome').textContent = 'Digite o código...';
    document.getElementById('ordProdutoNome').style.color = 'var(--texto-secundario)';
    document.getElementById('ordProdutoId').value = '';
    document.getElementById('ordProdutoNomeHidden').value = '';
    document.getElementById('ordQtdInput').value = '';
    document.getElementById('ordPrecoInput').value = '';
    document.getElementById('ordPrazoInput').value = '';
    renderItensEditarOrd();
}

function removerItemOrdConfirm(index) {
    Scopi.confirmar('Deseja realmente apagar este item?', () => {
        removerItemOrd(index);
    });
}

function removerItemOrd(index) {
    _itensOrdem.splice(index, 1);
    atualizarBotoesOrd();
    renderItensEditarOrd();
}

function desfazerImportacaoOrd() {
    if (_itensOrdemAnterior.length === 0) {
        Scopi.toast('alerta', 'Nenhuma importação para desfazer.');
        return;
    }
    Scopi.confirmar('Deseja desfazer a importação de itens?', () => {
        _itensOrdem = JSON.parse(JSON.stringify(_itensOrdemAnterior));
        _itensOrdemAnterior = [];
        atualizarBotoesOrd();
        renderItensEditarOrd();
        Scopi.toast('sucesso', 'Importação desfeita.');
    });
}

function atualizarBotoesOrd() {
    const temHistorico = _itensOrdemAnterior && _itensOrdemAnterior.length > 0;
    const btnDesfazer = document.getElementById('btnDesfazerImportacaoOrd');
    if (btnDesfazer) btnDesfazer.style.display = temHistorico ? 'inline-flex' : 'none';
}

function excluirItemOrdServidor(itemId) {
    Scopi.confirmar('Deseja realmente apagar este item?', () => {
        fetch(Scopi.url('/ordens/excluir_item'), { method:'POST', credentials:'include', headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'}, body:'id='+itemId })
        .then(r => r.json())
        .then(json => {
            if (json.sucesso) { Scopi.toast('sucesso', json.mensagem); _itensOrdem = _itensOrdem.filter(i => parseInt(i.id) !== parseInt(itemId)); renderItensEditarOrd(); }
            else Scopi.toast('erro', json.mensagem);
        }).catch(() => Scopi.toast('erro', 'Erro ao apagar.'));
    });
}

function renderItensEditarOrd() {
    const tbody = document.querySelector('#tabItensEditarOrd tbody');
    if (!tbody) return;
    let total = 0;
    if (_itensOrdem.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:12px;color:#888;font-style:italic;">Nenhum produto adicionado.</td></tr>';
    } else {
        tbody.innerHTML = '';
        _itensOrdem.forEach((item, index) => {
            const sub = (item.quantidade || 0) * (item.preco_unitario || 0);
            total += sub;
            const tr = document.createElement('tr');
            const dataPrazo = item.prazo_entrega ? new Date(item.prazo_entrega).toLocaleDateString('pt-BR') : '—';
            tr.innerHTML = `
                <td style="padding:8px 12px;">${item.produto_nome || item.nome_produto}</td>
                <td style="text-align:right;padding:8px 12px;">${item.quantidade}</td>
                <td style="text-align:right;padding:8px 12px;">R$ ${parseFloat(item.preco_unitario||0).toFixed(2)}</td>
                <td style="padding:8px 12px;font-size:0.85rem;">${dataPrazo}</td>
                <td style="text-align:right;padding:8px 12px;">R$ ${sub.toFixed(2)}</td>
                <td style="text-align:center;padding:8px 12px;">
                    ${_statusOrd === 'aberto' ? `<button type="button" class="btn-icone" onclick="removerItemOrdConfirm(${index})" title="Remover"><img src="${SCOPI_BASE}/public/assets/icons/iconeFechar.svg" style="width:12px;filter:brightness(0) saturate(100%) invert(18%) sepia(85%) saturate(2200%) hue-rotate(330deg);" alt=""></button>${item.id?`<button type="button" class="btn-icone" onclick="excluirItemOrdServidor(${item.id})" title="Excluir"><img src="${SCOPI_BASE}/public/assets/icons/iconeLixeira.svg" style="width:12px;" alt=""></button>`:''}` : ''}
                </td>
            `;
            tbody.appendChild(tr);
        });
    }
    document.getElementById('itensOrdJsonInput').value = JSON.stringify(_itensOrdem);
    document.getElementById('ordemValorTotalOculto').value = total.toFixed(2);
    document.getElementById('ordemValorTotalReadonly').value = total.toLocaleString('pt-BR', {style:'currency', currency:'BRL'});

    atualizarBotoesOrd();
}

function renderItensVisualizarOrd() {
    const tbody = document.querySelector('#tabItensOrdVisualizar tbody');
    if (!tbody) return;
    if (_itensOrdem.length === 0) { tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:12px;color:#888;font-style:italic;">Nenhum produto.</td></tr>'; return; }
    tbody.innerHTML = '';
    _itensOrdem.forEach(item => {
        const sub = (item.quantidade || 0) * (item.preco_unitario || 0);
        const dataPrazo = item.prazo_entrega ? new Date(item.prazo_entrega).toLocaleDateString('pt-BR') : '—';
        const condPag = item.condicao_pagamento_descricao || item.condicao_pagamento || '—';
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="padding:8px 12px;">${item.produto_nome || item.nome_produto}</td>
            <td style="text-align:right;padding:8px 12px;">${item.quantidade}</td>
            <td style="padding:8px 12px;font-size:0.85rem;">${dataPrazo}</td>
            <td style="text-align:right;padding:8px 12px;">${condPag}</td>
            <td style="text-align:right;padding:8px 12px;">R$ ${sub.toFixed(2)}</td>
        `;
        tbody.appendChild(tr);
    });
}

async function abrirModalImportarOrd() {
    const sel = document.getElementById('ordSolicitacaoSel');
    sel.innerHTML = '<option value="">Carregando...</option>';
    document.getElementById('itensSolicitacaoPreviewOrdContainer').style.display = 'none';
    Scopi.abrirModal('modalImportarOrd');
    try {
        const resp = await fetch(Scopi.url('/solicitacoes/autorizadas'), {credentials:'include'});
        const json = await resp.json();
        sel.innerHTML = '<option value="">Selecione uma solicitação...</option>';
        if (json.sucesso && json.dados) {
            json.dados.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = `Solicitação ${s.numero} — ${s.nome_departamento || '—'} (${s.nome_solicitante || '—'})`;
                sel.appendChild(opt);
            });
        }
    } catch(e) { sel.innerHTML = '<option value="">Erro ao carregar.</option>'; }
}

async function carregarItensSolicitacaoOrd(idSol) {
    const container = document.getElementById('itensSolicitacaoPreviewOrdContainer');
    const tbody = document.querySelector('#tabItensSolicitacaoPreviewOrd tbody');
    tbody.innerHTML = '';
    if (!idSol) { container.style.display = 'none'; return; }
    try {
        const resp = await fetch(Scopi.url('/solicitacoes/dados?id='+idSol), {credentials:'include'});
        const json = await resp.json();
        if (json.sucesso && json.dados && json.dados.itens) {
            json.dados.itens.forEach(item => {
                const valJson = JSON.stringify({ solicitacao_item_id: item.id, produto_id: item.produto_id, produto_nome: item.nome_produto, quantidade: item.quantidade, preco_unitario: 0 });
                const tr = document.createElement('tr');
                tr.innerHTML = `<td><input type="checkbox" class="chk-item-imp-ord" value='${valJson.replace(/'/g,"&#39;")}'></td><td>${item.nome_produto} (${item.codigo_produto || '—'})</td><td style="text-align:right;">${parseFloat(item.quantidade).toFixed(2)}</td>`;
                tbody.appendChild(tr);
            });
            container.style.display = 'block';
        }
    } catch(e) { console.error(e); }
}

async function importarItensSelecionadosOrd() {
    const selecionados = document.querySelectorAll('.chk-item-imp-ord:checked');
    if (selecionados.length === 0) { Scopi.toast('erro', 'Selecione ao menos um item para importar.'); return; }

    // Salvar estado anterior para desfazer
    _itensOrdemAnterior = JSON.parse(JSON.stringify(_itensOrdem));

    const sel = document.getElementById('ordSolicitacaoSel');
    const solicitacaoId = sel ? parseInt(sel.value) : 0;
    if (solicitacaoId > 0 && _idOrd > 0) {
        try {
            await fetch(Scopi.url('/ordens/vincular-solicitacao'), { method:'POST', credentials:'include', headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'}, body: new URLSearchParams({ordem_id:_idOrd, solicitacao_id:solicitacaoId}) });
        } catch(e) {}
    }
    selecionados.forEach(chk => {
        const item = JSON.parse(chk.value);
        const idx = _itensOrdem.findIndex(i => parseInt(i.produto_id) === parseInt(item.produto_id));
        if (idx >= 0) _itensOrdem[idx].quantidade = parseFloat(_itensOrdem[idx].quantidade) + parseFloat(item.quantidade);
        else _itensOrdem.push(item);
    });
    renderItensEditarOrd();
    Scopi.toast('sucesso', 'Itens importados com sucesso.');
    Scopi.fecharModal('modalImportarOrd');
}

document.addEventListener('DOMContentLoaded', () => {
    const codInicial = document.getElementById('filtroFornOrdCodigo')?.value?.trim();
    if (codInicial) buscarFornecedorFiltroOrd(codInicial);
});
</script>
