<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Ordens de Compra';</script>
<div class="pagina-cabecalho"><h1 class="pagina-titulo">Ordens de Compra</h1><p class="pagina-subtitulo">Controle e acompanhamento das ordens de compra</p></div>
<div class="painel-filtros">
  <div class="filtros-cabecalho"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt=""><span>Filtros</span></div>
  <form method="GET" action="<?= BASE_URL ?>/ordens"><div class="filtros-campos">
    <div class="campo-filtro"><label>Número</label><input type="text" name="numero" value="<?= Auxiliares::escapar($filtros['numero']??'') ?>" placeholder="OC-..."></div>
    <div class="campo-filtro"><label>Status</label><select name="status"><option value="">Todos</option><option value="aberta">Aberta</option><option value="autorizada">Autorizada</option><option value="enviada">Enviada</option><option value="parcialmente_atendida">Parcialmente Atendida</option><option value="concluida">Concluída</option><option value="cancelada">Cancelada</option></select></div>
    <div class="campo-filtro" style="flex:0;align-self:flex-end;"><button type="submit" class="btn btn-filtrar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt=""> Buscar</button></div>
  </div></form>
</div>
<div class="barra-acoes">
  <div class="grupo-botoes"><button class="btn btn-primario" onclick="Scopi.abrirCadastro('modalOrdem','formOrdem')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Nova Ordem</button><button class="btn btn-secundario" onclick="window.open('<?= BASE_URL ?>/ordens/exportar' + window.location.search, '_blank')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Exportar</button></div>
  <span style="font-size:.82rem;color:#888;"><?= count($ordens) ?> registro(s)</span>
</div>
<div class="tabela-container">
  <table class="tabela">
    <thead><tr><th>Número</th><th>Fornecedor</th><th>Emissão</th><th>Valor Total</th><th>Status</th><th class="coluna-acoes"></th></tr></thead>
    <tbody>
      <?php if(empty($ordens)): ?><tr><td colspan="6" style="text-align:center;padding:32px;color:#888;">Nenhuma ordem encontrada.</td></tr>
      <?php else: foreach($ordens as $o): ?>
      <tr>
        <td><span class="cod-clicavel" onclick="Scopi.abrirRegistro('modalOrdem','formOrdem','/ordens/dados',<?= $o['id'] ?>,'visualizar')"><?= Auxiliares::escapar($o['numero']??$o['id']) ?></span></td>
        <td><?= Auxiliares::escapar($o['nome_fornecedor']??'—') ?></td>
        <td><?= !empty($o['emitido_em'])?date('d/m/Y',strtotime($o['emitido_em'])):'—' ?></td>
        <td>R$ <?= number_format($o['valor_total']??0,2,',','.') ?></td>
        <td><span class="badge badge-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
        <td class="coluna-acoes">
          <button class="btn-icone btn-editar-linha" onclick="Scopi.abrirRegistro('modalOrdem','formOrdem','/ordens/dados',<?= $o['id'] ?>,'editar')" title="Editar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeEditar.svg" alt=""></button>
          <button class="btn-icone" onclick="window.open('<?= BASE_URL ?>/ordens/imprimir?id=<?= $o['id'] ?>', '_blank')" title="Imprimir"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""></button>
        </td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
<div class="overlay-modal" id="modalOrdem">
  <div class="modal modal-largo">
    <div class="modal-cabecalho"><div class="modal-titulo"><img src="<?= BASE_URL ?>/public/assets/icons/iconeOC.svg" alt=""><span>Ordem de Compra</span></div><button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalOrdem')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt=""></button></div>
    <div class="modal-abas"><button class="aba-btn ativa" data-aba="visualizar" onclick="Scopi.ativarAba('modalOrdem','visualizar')">Visualizar</button><button class="aba-btn" data-aba="editar" onclick="Scopi.ativarAba('modalOrdem','editar')">Editar</button></div>
    <div class="modal-corpo">
      <div class="conteudo-aba ativo" data-aba="visualizar">
        <div class="grade-visualizar">
          <div class="campo-visualizar"><span class="rotulo">Número</span><span class="valor" data-campo="numero">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Status</span><span class="valor"><span class="badge" data-badge="status">—</span></span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Fornecedor</span><span class="valor" data-campo="nome_fornecedor">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Data de Emissão</span><span class="valor" data-campo="emitido_em">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Condição de Pagamento</span><span class="valor" data-campo="condicao_pagamento">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Modalidade de Frete</span><span class="valor" data-campo="modalidade_frete">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Prazo de Entrega</span><span class="valor" data-campo="prazo_entrega">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Valor Total</span><span class="valor" data-campo="valor_total">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Observação</span><span class="valor" data-campo="observacao">—</span></div>
        </div>
        
        <div class="campo-visualizar campo-completo" style="margin-top: 20px;">
          <span class="rotulo" style="margin-bottom: 8px;">Itens da Ordem de Compra</span>
          <div class="tabela-container" style="border: 1px solid var(--borda);">
            <table class="tabela" id="tabItensOrdVisualizar">
              <thead>
                <tr>
                  <th style="padding: 8px 12px;">Produto</th>
                  <th style="width: 120px; text-align: right; padding: 8px 12px;">Quantidade</th>
                  <th style="width: 120px; text-align: right; padding: 8px 12px;">Preço Unit.</th>
                  <th style="width: 120px; text-align: right; padding: 8px 12px;">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <!-- populated dynamically -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="conteudo-aba" data-aba="editar">
        <form id="formOrdem" onsubmit="event.preventDefault();Scopi.enviarFormulario('formOrdem','modalOrdem','/ordens/salvar')">
          <input type="hidden" name="id" value="0">
          <div class="grade-form">
            <div class="campo-form campo-completo">
              <label>Cód. Fornecedor *</label>
              <div style="display: flex; gap: 8px; align-items: center;">
                  <input type="text" id="ordemFornecedorCodigo" class="campo-input" style="width: 150px; text-transform: uppercase;" placeholder="Ex: FOR-123" onblur="buscarFornecedorOrdem(this.value)" required>
                  <span id="ordemFornecedorNome" style="font-size: 0.9rem; color: var(--texto-secundario); font-style: italic;">Digite o código do fornecedor...</span>
                  <input type="hidden" name="fornecedor_id" id="ordemFornecedorId" value="">
              </div>
            </div>
            <div class="campo-form">
              <label>Condição de Pagamento</label>
              <input type="text" name="condicao_pagamento" placeholder="Ex: 30/60/90 dias">
            </div>
            <div class="campo-form">
                <label>Modalidade de Frete</label>
                <select name="modalidade_frete">
                    <option value="">Selecione...</option>
                    <option value="CIF">CIF</option>
                    <option value="FOB">FOB</option>
                </select>
            </div>
            <div class="campo-form"><label>Prazo de Entrega</label><input type="text" name="prazo_entrega"></div>
            <div class="campo-form"><label>Valor Total (R$)</label><input type="text" name="valor_total_exibicao" id="ordemValorTotalReadonly" value="R$ 0,00" readonly class="campo-input" style="background-color: #f5f5f5;"></div>
            <input type="hidden" name="valor_total" id="ordemValorTotalOculto" value="0.00">
            <div class="campo-form campo-completo"><label>Observação</label><textarea name="observacao" rows="2"></textarea></div>
            
            <div id="blocoItensEdicaoOrd" class="campo-form campo-completo" style="margin-top: 10px; border-top: 1px solid var(--borda); padding-top: 15px; display: none;">
              <label style="margin-bottom: 8px;">Adicionar Produtos</label>
              <div style="display: flex; gap: 8px; margin-bottom: 12px; align-items: center;">
                <div style="display: flex; flex: 1; gap: 8px; align-items: center;">
                    <input type="text" id="ordProdutoCodigo" class="campo-input" style="width: 120px; padding: 8px 10px; border: 1px solid var(--borda); border-radius: 6px; font-size: 0.78rem;" placeholder="Cód. Produto" onblur="buscarProdutoOrdem(this.value)">
                    <span id="ordProdutoNome" style="font-size: 0.85rem; color: var(--texto-secundario); font-style: italic;">Digite o código...</span>
                    <input type="hidden" id="ordProdutoId" value="">
                    <input type="hidden" id="ordProdutoNomeHidden" value="">
                </div>
                <input type="number" id="ordQtdInput" class="campo-input" style="width: 90px; padding: 8px 10px; border: 1px solid var(--borda); border-radius: 6px; font-size: 0.78rem;" min="0.01" step="any" placeholder="Qtd.">
                <input type="number" id="ordPrecoInput" class="campo-input" style="width: 110px; padding: 8px 10px; border: 1px solid var(--borda); border-radius: 6px; font-size: 0.78rem;" min="0.00" step="any" placeholder="Preço Un. R$">
                <button type="button" class="btn btn-primario" onclick="adicionarItemTabelaOrd()" style="height: 34px;">Adicionar</button>
              </div>
              
              <span class="rotulo" style="margin-bottom: 8px;">Lista de Itens</span>
              <div class="tabela-container" style="border: 1px solid var(--borda); max-height: 200px; overflow-y: auto;">
                <table class="tabela" id="tabItensEditarOrd">
                  <thead>
                    <tr>
                      <th style="padding: 8px 12px;">Produto</th>
                      <th style="width: 90px; text-align: right; padding: 8px 12px;">Qtd</th>
                      <th style="width: 110px; text-align: right; padding: 8px 12px;">Preço Un.</th>
                      <th style="width: 110px; text-align: right; padding: 8px 12px;">Subtotal</th>
                      <th style="width: 50px; padding: 8px 12px;"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- populated dynamically -->
                  </tbody>
                </table>
              </div>
              <input type="hidden" name="itens_json" id="itensOrdJsonInput" value="[]">
            </div>
          </div>
        </form>
      </div>
    </div>
    <div class="modal-rodape">
      <button class="btn btn-perigo" id="btnCancelarOrd" style="margin-right:auto; display:none;" onclick="cancelarOrdem()">Cancelar Ordem</button>
      <button class="btn" id="btnAutorizarOrd" style="background-color: var(--sucesso); color: var(--branco); display:none;" onclick="autorizarOrdem()">Autorizar</button>
      <button class="btn" id="btnDesautorizarOrd" style="background-color: #E65100; color: var(--branco); display:none;" onclick="desautorizarOrdem()">Retirar Autorização</button>
      <button class="btn btn-secundario" onclick="Scopi.fecharModal('modalOrdem')">Fechar</button>
      <button class="btn btn-primario btn-salvar-capa" onclick="salvarCapaOrdem()" style="display:none;"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Confirmar Capa</button>
      <button class="btn btn-primario btn-salvar" onclick="Scopi.enviarFormulario('formOrdem','modalOrdem','/ordens/salvar')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Salvar Itens</button>
    </div>
  </div>
</div>

<script>
let _idOrd = 0;
let _statusOrd = '';
let _itensOrdem = [];
const USER_PERFIL = "<?= $usuario['perfil'] ?? '' ?>";

const _origAbrirRegistroOrd = Scopi.abrirRegistro.bind(Scopi);
Scopi.abrirRegistro = async function(idModal, idForm, url, id, aba) {
  if (idModal === 'modalOrdem') {
      _idOrd = id;
      await _origAbrirRegistroOrd(idModal, idForm, url, id, aba);
      const badgeSituacao = document.querySelector(`#${idModal} [data-badge="status"]`);
      if (badgeSituacao) {
          _statusOrd = badgeSituacao.textContent.trim().toLowerCase();
      }
      
      try {
          const resp = await fetch(`${SCOPI_BASE}/ordens/dados?id=${id}`, {headers:{'X-Requested-With':'XMLHttpRequest'}});
          const json = await resp.json();
          if (json.sucesso) {
              _itensOrdem = json.dados.itens || [];
              
              if (aba === 'editar') {
                  document.getElementById('ordemValorTotalOculto').value = json.dados.valor_total || 0;
                  const vTotal = parseFloat(json.dados.valor_total || 0).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
                  document.getElementById('ordemValorTotalReadonly').value = vTotal;
              }
              
              const tabEditarBtn = document.querySelector('#modalOrdem .aba-btn[data-aba="editar"]');
              if (tabEditarBtn) {
                  if (_statusOrd !== 'aberta') {
                      tabEditarBtn.style.display = 'none';
                  } else {
                      tabEditarBtn.style.display = 'inline-block';
                  }
              }
              
              const blocoItens = document.getElementById('blocoItensEdicaoOrd');
              if (blocoItens) blocoItens.style.display = 'block';
              
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
      _idOrd = 0;
      _statusOrd = 'aberta';
      _itensOrdem = [];
      
      const tabEditarBtn = document.querySelector('#modalOrdem .aba-btn[data-aba="editar"]');
      if (tabEditarBtn) tabEditarBtn.style.display = 'inline-block';
      
      const blocoItens = document.getElementById('blocoItensEdicaoOrd');
      if (blocoItens) blocoItens.style.display = 'none';
      
      document.getElementById('ordemValorTotalReadonly').value = 'R$ 0,00';
      document.getElementById('ordemValorTotalOculto').value = '0';
      
      renderItensEditarOrd();
      atualizarBotoesOrdens('editar');
      
      document.getElementById('ordemFornecedorCodigo').value = '';
      document.getElementById('ordemFornecedorNome').textContent = 'Digite o código do fornecedor...';
      document.getElementById('ordemFornecedorNome').style.color = 'var(--texto-secundario)';
      document.getElementById('ordemFornecedorId').value = '';
  }
};

const _origAtivarAbaOrd = Scopi.ativarAba.bind(Scopi);
Scopi.ativarAba = function(idModal, aba) {
  _origAtivarAbaOrd(idModal, aba);
  if (idModal === 'modalOrdem') {
      atualizarBotoesOrdens(aba);
  }
};

function atualizarBotoesOrdens(aba) {
  const btnCancelar = document.getElementById('btnCancelarOrd');
  const btnAutorizar = document.getElementById('btnAutorizarOrd');
  const btnDesautorizar = document.getElementById('btnDesautorizarOrd');
  const btnSalvar = document.querySelector('#modalOrdem .btn-salvar');
  const btnSalvarCapa = document.querySelector('#modalOrdem .btn-salvar-capa');
  
  if (btnCancelar) btnCancelar.style.display = 'none';
  if (btnAutorizar) btnAutorizar.style.display = 'none';
  if (btnDesautorizar) btnDesautorizar.style.display = 'none';
  if (btnSalvar) btnSalvar.style.display = 'none';
  if (btnSalvarCapa) btnSalvarCapa.style.display = 'none';
  
  if (aba === 'visualizar' && _idOrd > 0) {
      if (_statusOrd === 'aberta' || _statusOrd === 'autorizada') {
          if (btnCancelar) btnCancelar.style.display = 'inline-flex';
      }
      if (_statusOrd === 'aberta' && (USER_PERFIL === 'gerente' || USER_PERFIL === 'administrador')) {
          if (btnAutorizar) btnAutorizar.style.display = 'inline-flex';
      }
      if (_statusOrd === 'autorizada' && (USER_PERFIL === 'gerente' || USER_PERFIL === 'administrador')) {
          if (btnDesautorizar) btnDesautorizar.style.display = 'inline-flex';
      }
  } else if (aba === 'editar') {
      if (_idOrd === 0) {
          if (btnSalvarCapa) btnSalvarCapa.style.display = 'inline-flex';
      } else {
          if (btnSalvar && _statusOrd === 'aberta') {
              btnSalvar.style.display = 'inline-flex';
          }
      }
  }
}

async function salvarCapaOrdem() {
    const form = document.getElementById('formOrdem');
    if (!form.reportValidity()) return;
    
    document.getElementById('itensOrdJsonInput').value = '[]';
    
    const btn = document.querySelector('#modalOrdem .btn-salvar-capa');
    if (btn) { btn.disabled = true; btn.textContent = 'Salvando...'; }
    
    try {
        const resp = await fetch(Scopi.url('/ordens/salvar'), {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
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
    } catch (e) {
        Scopi.toast('erro', 'Falha na comunicação.');
    } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = '<img src="'+SCOPI_BASE+'/public/assets/icons/iconeInserir.svg" alt=""> Confirmar Capa'; }
    }
}

async function buscarFornecedorOrdem(codigo) {
    codigo = codigo.trim();
    const spanNome = document.getElementById('ordemFornecedorNome');
    const inputId = document.getElementById('ordemFornecedorId');
    
    if (!codigo) {
        spanNome.textContent = 'Digite o código do fornecedor...';
        spanNome.style.color = 'var(--texto-secundario)';
        inputId.value = '';
        return;
    }
    
    spanNome.textContent = 'Buscando...';
    spanNome.style.color = 'var(--texto-secundario)';
    
    try {
        const resp = await fetch(`${SCOPI_BASE}/fornecedores/consultar-codigo?codigo=${encodeURIComponent(codigo)}`);
        const data = await resp.json();
        if (data.sucesso && data.dados) {
            spanNome.textContent = data.dados.nome;
            spanNome.style.color = 'var(--sucesso)';
            inputId.value = data.dados.id;
        } else {
            spanNome.textContent = 'Fornecedor não encontrado ou inativo';
            spanNome.style.color = 'var(--alerta)';
            inputId.value = '';
        }
    } catch (err) {
        spanNome.textContent = 'Erro ao buscar';
        spanNome.style.color = 'var(--alerta)';
        inputId.value = '';
    }
}

function cancelarOrdem() {
    if (!_idOrd) return;
    Scopi.confirmarAcao('Cancelar esta ordem?','/ordens/cancelar',{id:_idOrd});
}
function autorizarOrdem() {
    if (!_idOrd) return;
    Scopi.confirmarAcao('Autorizar esta ordem?','/ordens/autorizar',{id:_idOrd});
}
function desautorizarOrdem() {
    if (!_idOrd) return;
    Scopi.confirmarAcao('Retirar autorização desta ordem?','/ordens/desautorizar',{id:_idOrd});
}

async function buscarProdutoOrdem(codigo) {
    codigo = codigo.trim();
    const spanNome = document.getElementById('ordProdutoNome');
    const inputId = document.getElementById('ordProdutoId');
    const inputNomeHidden = document.getElementById('ordProdutoNomeHidden');
    
    if (!codigo) {
        spanNome.textContent = 'Digite o código...';
        spanNome.style.color = 'var(--texto-secundario)';
        inputId.value = '';
        inputNomeHidden.value = '';
        return;
    }
    
    spanNome.textContent = 'Buscando...';
    spanNome.style.color = 'var(--texto-secundario)';
    
    try {
        const resp = await fetch(`${SCOPI_BASE}/produtos/consultar-codigo?codigo=${encodeURIComponent(codigo)}`);
        const data = await resp.json();
        if (data.sucesso && data.dados) {
            spanNome.textContent = `${data.dados.nome} (${data.dados.categoria_nome || 'Sem categoria'})`;
            spanNome.style.color = 'var(--sucesso)';
            inputId.value = data.dados.id;
            inputNomeHidden.value = data.dados.nome;
        } else {
            spanNome.textContent = 'Produto não encontrado/inativo';
            spanNome.style.color = 'var(--alerta)';
            inputId.value = '';
            inputNomeHidden.value = '';
        }
    } catch (err) {
        spanNome.textContent = 'Erro ao buscar';
        spanNome.style.color = 'var(--alerta)';
        inputId.value = '';
        inputNomeHidden.value = '';
    }
}

function adicionarItemTabelaOrd() {
    const id = document.getElementById('ordProdutoId').value;
    const nome = document.getElementById('ordProdutoNomeHidden').value;
    const qtdInput = document.getElementById('ordQtdInput');
    const precoInput = document.getElementById('ordPrecoInput');
    
    const qtd = parseFloat(qtdInput.value);
    const preco = parseFloat(precoInput.value);
    
    if (!id) {
        Scopi.toast('alerta', 'Selecione um produto válido primeiro.');
        return;
    }
    if (isNaN(qtd) || qtd <= 0) {
        Scopi.toast('alerta', 'Informe uma quantidade válida maior que zero.');
        return;
    }
    if (isNaN(preco) || preco < 0) {
        Scopi.toast('alerta', 'Informe um preço válido.');
        return;
    }
    
    const idx = _itensOrdem.findIndex(i => parseInt(i.produto_id) === parseInt(id));
    if (idx >= 0) {
        _itensOrdem[idx].quantidade = parseFloat(_itensOrdem[idx].quantidade) + qtd;
        _itensOrdem[idx].preco_unitario = preco;
    } else {
        _itensOrdem.push({
            produto_id: parseInt(id),
            produto_nome: nome,
            quantidade: qtd,
            preco_unitario: preco
        });
    }
    
    document.getElementById('ordProdutoCodigo').value = '';
    document.getElementById('ordProdutoNome').textContent = 'Digite o código...';
    document.getElementById('ordProdutoNome').style.color = 'var(--texto-secundario)';
    document.getElementById('ordProdutoId').value = '';
    document.getElementById('ordProdutoNomeHidden').value = '';
    qtdInput.value = '';
    precoInput.value = '';
    
    renderItensEditarOrd();
}

function removerItemOrd(index) {
    _itensOrdem.splice(index, 1);
    renderItensEditarOrd();
}

function renderItensEditarOrd() {
    const tbody = document.querySelector('#tabItensEditarOrd tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    let total = 0;
    if (_itensOrdem.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 12px; color: #888; font-style: italic;">Nenhum produto adicionado.</td></tr>';
    } else {
        _itensOrdem.forEach((item, index) => {
            const tr = document.createElement('tr');
            
            const subtotal = item.quantidade * item.preco_unitario;
            total += subtotal;
            
            tr.innerHTML = `
                <td style="padding: 8px 12px;">${item.produto_nome || item.nome_produto}</td>
                <td style="text-align: right; padding: 8px 12px;">${item.quantidade}</td>
                <td style="text-align: right; padding: 8px 12px;">R$ ${parseFloat(item.preco_unitario).toFixed(2)}</td>
                <td style="text-align: right; padding: 8px 12px;">R$ ${subtotal.toFixed(2)}</td>
                <td style="text-align: center; padding: 8px 12px;">
                    <button type="button" class="btn-icone" style="padding: 4px;" onclick="removerItemOrd(${index})" title="Remover"><img src="${SCOPI_BASE}/public/assets/icons/iconeFechar.svg" style="width: 14px; height: 14px;" alt="Remover"></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }
    
    document.getElementById('itensOrdJsonInput').value = JSON.stringify(_itensOrdem);
    
    document.getElementById('ordemValorTotalOculto').value = total.toFixed(2);
    document.getElementById('ordemValorTotalReadonly').value = total.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
}

function renderItensVisualizarOrd() {
    const tbody = document.querySelector('#tabItensOrdVisualizar tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    if (_itensOrdem.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 12px; color: #888; font-style: italic;">Nenhum produto.</td></tr>';
    } else {
        _itensOrdem.forEach((item) => {
            const tr = document.createElement('tr');
            const subtotal = item.quantidade * item.preco_unitario;
            tr.innerHTML = `
                <td style="padding: 8px 12px;">${item.produto_nome || item.nome_produto}</td>
                <td style="text-align: right; padding: 8px 12px;">${item.quantidade}</td>
                <td style="text-align: right; padding: 8px 12px;">R$ ${parseFloat(item.preco_unitario).toFixed(2)}</td>
                <td style="text-align: right; padding: 8px 12px;">R$ ${subtotal.toFixed(2)}</td>
            `;
            tbody.appendChild(tr);
        });
    }
}
</script>
