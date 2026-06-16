<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Notas Fiscais';</script>
<div class="pagina-cabecalho"><h1 class="pagina-titulo">Notas de Entrada</h1><p class="pagina-subtitulo">Gerenciamento de notas fiscais de terceiros</p></div>
<div class="painel-filtros">
  <div class="filtros-cabecalho"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt=""><span>Filtros</span></div>
  <form method="GET" action="<?= BASE_URL ?>/notas"><div class="filtros-campos">
    <div class="campo-filtro"><label>Número NF</label><input type="text" name="numero" value="<?= Auxiliares::escapar($filtros['numero']??'') ?>" ></div>
    <div class="campo-filtro">
        <label style="display:flex;gap:8px;align-items:center;justify-content:space-between;">
            <span>Cód. Fornecedor</span>
            <button type="button" class="btn btn-secundario" style="padding:4px 6px;margin:0;" title="Buscar fornecedor" onclick="Scopi.iconeBusca('fornecedores','filtroFornNfCodigo','filtroFornNfNome')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width:13px;margin:0;" alt="Buscar"></button>
        </label>
        <div style="display: flex; gap: 8px; align-items: center;">
            <input type="text" id="filtroFornNfCodigo" name="fornecedor_codigo" value="<?= Auxiliares::escapar($filtros['fornecedor_codigo'] ?? '') ?>" class="campo-input" style="width: 100px; text-transform: uppercase;"  onblur="buscarFornecedorFiltro(this.value)">
            <span id="filtroFornNfNome" style="font-size: 0.8rem; color: var(--texto-secundario);"><?= empty($filtros['fornecedor_codigo']) ? 'Digite...' : 'Buscando...' ?></span>
        </div>
    </div>
    <div class="campo-filtro">
        <label style="display:flex;gap:8px;align-items:center;justify-content:space-between;">
            <span>Nº Ordem de Compra</span>
            <button type="button" class="btn btn-secundario" style="padding:4px 6px;margin:0;" title="Buscar ordem de compra" onclick="Scopi.iconeBusca('ordens','filtroOcNfCodigo','filtroOcNfNome')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width:13px;margin:0;" alt="Buscar"></button>
        </label>
        <div style="display: flex; gap: 8px; align-items: center;">
            <input type="text" id="filtroOcNfCodigo" name="ordem_numero" value="<?= Auxiliares::escapar($filtros['ordem_numero'] ?? '') ?>" class="campo-input" style="width: 100px; text-transform: uppercase;" >
            <span id="filtroOcNfNome" style="display: none;"></span>
        </div>
    </div>
    <div class="campo-filtro"><label>Data (De)</label><input type="date" name="data_inicial" value="<?= Auxiliares::escapar($filtros['data_inicial']??'') ?>"></div>
    <div class="campo-filtro"><label>Data (Até)</label><input type="date" name="data_final" value="<?= Auxiliares::escapar($filtros['data_final']??'') ?>"></div>
    <div class="campo-filtro"><label>Status</label><select name="status"><option value="">Todos</option><option value="registrada" <?= ($filtros['status']??'')==='registrada'?'selected':'' ?>>Não Lançada</option><option value="vinculada" <?= ($filtros['status']??'')==='vinculada'?'selected':'' ?>>Lançada</option><option value="cancelada" <?= ($filtros['status']??'')==='cancelado'?'selected':'' ?>>Cancelada</option></select></div>
    <div class="campo-filtro" style="flex:0;align-self:flex-end;"><button type="submit" class="btn btn-filtrar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt=""> Buscar</button></div>
  </div></form>
</div>
<div class="barra-acoes">
  <div class="grupo-botoes">
    <button class="btn btn-primario" onclick="Scopi.abrirModal('modalImportar')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Importar NF-e</button>
    <button class="btn btn-secundario" onclick="window.open('<?= BASE_URL ?>/notas/exportar' + window.location.search, '_blank')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Exportar</button>
  </div>
  <span style="font-size:.82rem;color:#888;"><?= count($notas) ?> registro(s)</span>
</div>
<div class="tabela-container">
  <table class="tabela">
    <thead><tr><th>Número</th><th>Ordem de Compra</th><th>Fornecedor</th><th>Data de Emissão</th><th>Valor</th><th>Status</th><th class="coluna-acoes"></th></tr></thead>
    <tbody>
      <?php if(empty($notas)): ?><tr><td colspan="5" style="text-align:center;padding:32px;color:#888;">Nenhuma nota encontrada.</td></tr>
      <?php else: foreach($notas as $n): ?>
      <tr>
        <td><span class="cod-clicavel" onclick="Scopi.abrirRegistro('modalNota','formNota','/notas/dados',<?= $n['id'] ?>,'visualizar')"><?= Auxiliares::escapar($n['numero']) ?></span></td>
        <td><?= Auxiliares::escapar($n['ordem_numero']??'—') ?></td>
        <td><?= Auxiliares::escapar($n['nome_fornecedor']??'—') ?></td>
        <td><?= !empty($n['data_emissao'])?date('d/m/Y',strtotime($n['data_emissao'])):'—' ?></td>
        <td>R$ <?= number_format($n['valor_total']??0,2,',','.') ?></td>
        <td><span class="badge badge-<?= $n['status']??'pendente' ?>"><?= Auxiliares::formatarStatus($n['status']??'registrada') ?></span></td>
        <td class="coluna-acoes">
          <button class="btn-icone btn-editar-linha" onclick="Scopi.abrirRegistro('modalNota','formNota','/notas/dados',<?= $n['id'] ?>,'visualizar')" title="Ver"><img src="<?= BASE_URL ?>/public/assets/icons/iconeEditar.svg" alt=""></button>
          <button class="btn-icone" onclick="window.open('<?= BASE_URL ?>/notas/imprimir?id=<?= $n['id'] ?>', '_blank')" title="Imprimir"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""></button>
        </td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Nota -->
<div class="overlay-modal" id="modalNota">
  <div class="modal modal-largo">
    <div class="modal-cabecalho"><div class="modal-titulo"><img src="<?= BASE_URL ?>/public/assets/icons/iconeNF.svg" alt=""><span>Nota Fiscal</span></div><button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalNota')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt=""></button></div>
    <div class="modal-corpo">
      <div class="grade-visualizar">
        <div class="campo-visualizar"><span class="rotulo">Número</span><span class="valor" data-campo="numero">—</span></div>
        <div class="campo-visualizar"><span class="rotulo">Data de Emissão</span><span class="valor" data-campo="data_emissao">—</span></div>
        <div class="campo-visualizar campo-completo"><span class="rotulo">Chave de Acesso</span><span class="valor" data-campo="chave_acesso" style="font-size:.78rem;word-break:break-all;">—</span></div>
        <div class="campo-visualizar"><span class="rotulo">Status</span><span class="badge" data-badge="status" style="margin-top:4px;">—</span></div>
        <div class="campo-visualizar campo-completo"><span class="rotulo">Fornecedor</span><span class="valor" data-campo="nome_fornecedor">—</span></div>
        <div class="campo-visualizar"><span class="rotulo">Valor Total</span><span class="valor" data-campo="valor_total">—</span></div>
        <div class="campo-visualizar"><span class="rotulo">Natureza da Operação</span><span class="valor" data-campo="natureza_operacao">—</span></div>
      </div>
      
      <div style="margin-top: 20px;">
          <h3 style="font-size: 0.95rem; margin-bottom: 10px; color: var(--texto-secundario);">Itens da Nota Fiscal</h3>
          <div class="tabela-container" style="border-radius: 6px; border: 1px solid var(--borda);">
              <table class="tabela" id="tabItensNf">
                  <thead>
                      <tr>
                          <th>Item</th>
                          <th>Descrição / Produto</th>
                          <th style="text-align: right;">Qtd.</th>
                          <th style="text-align: right;">V. Unit.</th>
                          <th style="text-align: right;">Total</th>
                          <th>De / Para (OC ➔ Item)</th>
                          <th></th>
                      </tr>
                  </thead>
                  <tbody></tbody>
              </table>
          </div>
      </div>
    </div>
    <div class="modal-rodape"><button class="btn btn-secundario" onclick="Scopi.fecharModal('modalNota')">Fechar</button></div>
  </div>
</div>

<!-- Modal Importar NF-e -->
<div class="overlay-modal" id="modalImportar">
  <div class="modal modal-largo">
    <div class="modal-cabecalho"><div class="modal-titulo"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""><span>Importar NF-e</span></div><button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalImportar')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt=""></button></div>
    <div class="modal-corpo">
      <form id="formImportar" enctype="multipart/form-data" onsubmit="event.preventDefault(); importarNF()">
        <div id="blocoUploadNF">
            <div class="grade-form" style="grid-template-columns:1fr;">
            <div class="campo-form"><label>Arquivo XML (NF-e) *</label><input type="file" id="arquivo_xml" name="arquivo_xml" accept=".xml" required onchange="importarNF()"></div>
            <p style="font-size:.82rem;color:#888;">Selecione o arquivo XML da NF-e para importação automática dos dados.</p>
            </div>
        </div>
        <div id="blocoDadosNF" style="display:none; margin-top: 15px;">
            <input type="hidden" name="chave_acesso" id="nfChave">
            <input type="hidden" name="fornecedor_id" id="nfFornId">
            <input type="hidden" name="fornecedor_cnpj" id="nfFornCnpj">
            <input type="hidden" name="itens_json" id="nfItens">
            
            <div class="grade-visualizar">
                <div class="campo-visualizar"><span class="rotulo">Número</span><span class="valor" id="txtNfNum">—</span></div>
                <div class="campo-visualizar"><span class="rotulo">Data Emissão</span><span class="valor" id="txtNfData">—</span></div>
                <div class="campo-visualizar"><span class="rotulo">Valor Total</span><span class="valor" id="txtNfValor">—</span></div>
                <div class="campo-visualizar campo-completo"><span class="rotulo">Fornecedor</span><span class="valor" id="txtNfForn">—</span></div>
            </div>
            
            <div class="campo-form campo-completo" style="margin-top:20px; padding-top:15px; border-top:1px solid var(--borda);">
                <label style="color: var(--primario); font-weight: 600;">Vincular Ordem de Compra</label>
                <div style="display: flex; gap: 8px; align-items: center; margin-top: 8px;">
                    <input type="text" id="nfOrdemCodigo" class="campo-input" style="width: 140px; text-transform: uppercase;" onblur="buscarOrdemForm(this.value)" required>
                    <button type="button" class="btn btn-secundario" style="padding: 6px 8px;" title="Buscar ordem" onclick="Scopi.iconeBusca('ordens', 'nfOrdemCodigo', 'nfOrdemNome', 'nfOrdemId')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width: 14px; margin: 0;" alt="Buscar"></button>
                    <span id="nfOrdemNome" style="font-size: 0.9rem; color: var(--texto-secundario); font-style: italic; flex: 1;">Selecione a Ordem de Compra...</span>
                    <input type="hidden" name="ordem_id" id="nfOrdemId" required>
                </div>
            </div>
        </div>
      </form>
    </div>
    <div class="modal-rodape">
        <button class="btn btn-secundario" onclick="Scopi.fecharModal('modalImportar')">Cancelar</button>
        <button class="btn btn-primario" id="btnSalvarImportacao" style="display:none;" onclick="salvarImportacao()">
            <img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Salvar e Vincular
        </button>
    </div>
  </div>
</div>

<script>
async function importarNF() {
    const fileInput = document.getElementById('arquivo_xml');
    if (!fileInput.files.length) return;
    
    const formData = new FormData();
    formData.append('arquivo_xml', fileInput.files[0]);
    
    try {
        const resp = await fetch(Scopi.url('/notas/importar'), {
            method: 'POST',
            credentials: 'include',
            body: formData,
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        });
        const data = await resp.json();
        
        if (data.sucesso && data.dados) {
            document.getElementById('blocoUploadNF').style.display = 'none';
            document.getElementById('blocoDadosNF').style.display = 'block';
            document.getElementById('btnSalvarImportacao').style.display = 'inline-flex';
            
            // Fill fields
            document.getElementById('txtNfNum').textContent = data.dados.numero || '—';
            document.getElementById('txtNfData').textContent = data.dados.data_emissao || '—';
            document.getElementById('txtNfValor').textContent = parseFloat(data.dados.valor_total || 0).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
            document.getElementById('txtNfForn').textContent = data.dados.fornecedor_nome ? `${data.dados.fornecedor_nome} (CNPJ: ${data.dados.fornecedor_cnpj})` : data.dados.fornecedor_cnpj;
            
            // Hidden inputs for saving
            document.getElementById('nfChave').value = data.dados.chave_acesso || '';
            document.getElementById('nfFornId').value = data.dados.fornecedor_id || '';
            document.getElementById('nfFornCnpj').value = data.dados.fornecedor_cnpj || '';
            document.getElementById('nfItens').value = JSON.stringify(data.dados.itens || []);
            
            // Extra form data needed by endpoint
            const form = document.getElementById('formImportar');
            let inputNum = form.querySelector('[name="numero"]');
            if(!inputNum) { inputNum = document.createElement('input'); inputNum.type='hidden'; inputNum.name='numero'; form.appendChild(inputNum); }
            inputNum.value = data.dados.numero || '';
            
            let inputVal = form.querySelector('[name="valor_total"]');
            if(!inputVal) { inputVal = document.createElement('input'); inputVal.type='hidden'; inputVal.name='valor_total'; form.appendChild(inputVal); }
            inputVal.value = data.dados.valor_total || 0;
            
            let inputDt = form.querySelector('[name="data_emissao"]');
            if(!inputDt) { inputDt = document.createElement('input'); inputDt.type='hidden'; inputDt.name='data_emissao'; form.appendChild(inputDt); }
            inputDt.value = data.dados.data_emissao || '';
        } else {
            Scopi.toast('erro', data.mensagem || 'Erro ao processar XML.');
        }
    } catch(err) {
        Scopi.toast('erro', 'Falha na comunicação ao enviar XML.');
    }
}

async function salvarImportacao() {
    const form = document.getElementById('formImportar');
    if(!form.reportValidity()) return;
    
    const ordemId = document.getElementById('nfOrdemId').value;
    if(!ordemId) {
        Scopi.toast('alerta', 'Você deve vincular uma Ordem de Compra.');
        return;
    }
    
    // O endpoint de salvar precisa dos itens serializados como chave ou recebemos do form.
    // O backend já faz insert na tabela e então vamos fazer o vínculo.
    try {
        // Primeiro salvar a nota
        const respSalvar = await fetch(Scopi.url('/notas/salvar'), {
            method: 'POST',
            credentials: 'include',
            body: new FormData(form),
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        });
        const dataSalvar = await respSalvar.json();

        if (dataSalvar.sucesso && dataSalvar.dados && dataSalvar.dados.id) {
            // Agora vincula com a OC
            const formVincular = new FormData();
            formVincular.append('nota_id', dataSalvar.dados.id);
            formVincular.append('ordem_id', ordemId);

            const respVincular = await fetch(Scopi.url('/notas/vincular'), {
                method: 'POST',
                credentials: 'include',
                body: formVincular,
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });
            const dataVincular = await respVincular.json();
            
            if(dataVincular.sucesso) {
                Scopi.toast('sucesso', 'Nota importada e vinculada com sucesso!');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                Scopi.toast('erro', dataVincular.mensagem || 'Nota salva, mas falha ao vincular OC.');
            }
        } else {
            Scopi.toast('erro', dataSalvar.mensagem || 'Erro ao salvar a Nota.');
        }
    } catch(err) {
        Scopi.toast('erro', 'Falha na comunicação ao salvar.');
    }
}

// Reset modal on close
const _origFecharModalNF = Scopi.fecharModal.bind(Scopi);
Scopi.fecharModal = function(id) {
    _origFecharModalNF(id);
    if(id === 'modalImportar') {
        document.getElementById('blocoUploadNF').style.display = 'block';
        document.getElementById('blocoDadosNF').style.display = 'none';
        document.getElementById('btnSalvarImportacao').style.display = 'none';
        document.getElementById('arquivo_xml').value = '';
        document.getElementById('nfOrdemId').value = '';
        document.getElementById('nfOrdemCodigo').value = '';
        document.getElementById('nfOrdemNome').textContent = 'Selecione a Ordem de Compra...';
    }
};

let _itensNfAbertos = [];

Scopi.abrirRegistro = async function(idModal, idForm, urlDados, id, abaInicial='visualizar') {
    Scopi.abrirModal(idModal);
    const overlay = document.getElementById(idModal);
    const corpo   = overlay?.querySelector('.modal-corpo');
    if(!corpo) return;
    if(!overlay.dataset.htmlOriginal) overlay.dataset.htmlOriginal = corpo.innerHTML;
    corpo.innerHTML = '<div class="carregando-modal"><div class="spinner"></div> Carregando...</div>';
    try {
        const resp = await fetch(`${Scopi.url(urlDados)}?id=${id}`, {credentials:'include',headers:{'X-Requested-With':'XMLHttpRequest'}});
        const json = await resp.json();
        if(!json.sucesso) { Scopi.toast('erro', json.mensagem||'Erro ao carregar.'); Scopi.fecharModal(idModal); return; }
        
        corpo.innerHTML = overlay.dataset.htmlOriginal;
        Scopi.preencherVisualizacao(idModal, json.dados);
        
        _itensNfAbertos = json.dados.itens || [];
        renderItensNF();

        const titulo = overlay.querySelector('.modal-titulo span');
        if(titulo) titulo.textContent = `Visualizar #${json.dados.numero||json.dados.id}`;
    } catch(e) { Scopi.toast('erro','Falha na comunicação.'); Scopi.fecharModal(idModal); }
};

function renderItensNF() {
    const tbody = document.querySelector('#tabItensNf tbody');
    if (!tbody) return;
    tbody.innerHTML = '';
    
    if (_itensNfAbertos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:16px;">Nenhum item importado.</td></tr>';
        return;
    }
    
    _itensNfAbertos.forEach((item, idx) => {
        const tr = document.createElement('tr');
        
        let conteudoLancamento = '';
        let acoes = '';
        
        if (item.ordem_compra_item_id) {
            conteudoLancamento = `<span class="badge badge-lancado">Lançado: OC ${item.ordem_numero} - Item ${item.ordem_item_numero}</span>`;
            acoes = `<button type="button" class="btn btn-secundario" style="padding:4px 8px; font-size:0.75rem;" onclick="retirarLancamentoItem(${item.id})">Desfazer</button>`;
        } else {
            conteudoLancamento = `
                <div style="display:flex; gap:6px; align-items:center;">
                    <input type="text" id="ln_oc_${item.id}" class="campo-input" style="width:90px; padding:4px 6px; font-size:0.8rem; text-transform:uppercase;"  value="${item.numero_item_pedido || ''}">
                    <span style="color:#aaa;">-</span>
                    <input type="number" id="ln_item_${item.id}" class="campo-input" style="width:60px; padding:4px 6px; font-size:0.8rem;" >
                </div>
            `;
            acoes = `<button type="button" class="btn btn-primario" style="padding:4px 8px; font-size:0.75rem;" onclick="lancarItemNF(${item.id})">Lançar</button>`;
        }
        
        tr.innerHTML = `
            <td style="padding: 6px 12px;">${idx + 1}</td>
            <td style="padding: 6px 12px; font-size:0.85rem;">
                <strong>${item.produto_codigo || 'N/C'}</strong> - ${item.descricao}<br>
                <span style="font-size:0.75rem; color:#888;">NCM: ${item.ncm || '-'}</span>
            </td>
            <td style="text-align: right; padding: 6px 12px;">${parseFloat(item.quantidade).toFixed(2)} ${item.unidade || ''}</td>
            <td style="text-align: right; padding: 6px 12px;">R$ ${parseFloat(item.preco_unitario).toFixed(4)}</td>
            <td style="text-align: right; padding: 6px 12px;">R$ ${parseFloat(item.subtotal).toFixed(2)}</td>
            <td style="padding: 6px 12px;">${conteudoLancamento}</td>
            <td style="padding: 6px 12px; text-align:right;">${acoes}</td>
        `;
        tbody.appendChild(tr);
    });
}

async function lancarItemNF(id) {
    const numOc = document.getElementById('ln_oc_' + id)?.value;
    const numItem = document.getElementById('ln_item_' + id)?.value;
    
    if (!numOc || !numItem) {
        Scopi.toast('erro', 'Preencha a OC e o Item para lançar.');
        return;
    }
    
    try {
        const formData = new URLSearchParams();
        formData.append('nf_item_id', id);
        formData.append('numero_oc', numOc);
        formData.append('numero_item_oc', numItem);
        
        const resp = await fetch(Scopi.url('/notas/lancar_item'), {
            method: 'POST',
            credentials: 'include',
            body: formData,
            headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'}
        });
        const json = await resp.json();
        
        if (json.sucesso) {
            Scopi.toast('sucesso', json.mensagem);
            recarregarNotaAtual();
        } else {
            Scopi.toast('erro', json.mensagem);
        }
    } catch(err) {
        Scopi.toast('erro', 'Erro de conexão.');
    }
}

async function retirarLancamentoItem(id) {
    Scopi.confirmar('Deseja realmente retirar o lançamento deste item? O saldo da OC voltará a ficar disponível.', async () => {
        try {
            const formData = new URLSearchParams();
            formData.append('nf_item_id', id);

            const resp = await fetch(Scopi.url('/notas/retirar_lancamento_item'), {
                method: 'POST',
                credentials: 'include',
                body: formData,
                headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'}
            });
            const json = await resp.json();

            if (json.sucesso) {
                Scopi.toast('sucesso', json.mensagem);
                recarregarNotaAtual();
            } else {
                Scopi.toast('erro', json.mensagem);
            }
        } catch(err) {
            Scopi.toast('erro', 'Erro de conexão.');
        }
    });
}

function recarregarNotaAtual() {
    const overlay = document.getElementById('modalNota');
    const id = overlay.querySelector('.btn-icone[title="Imprimir"]')?.getAttribute('onclick')?.match(/\d+/)?.[0];
    // Pegar ID de outro lugar, já que btn imprimir está na tabela principal
    // Vamos usar o número exibido para recarregar ou simplesmente usar Scopi.abrirRegistro de novo
    // Precisamos do ID que tá no form ou passamos como variável.
    // Como a modalNota é aberta, o último fetch ID foi salvo no modalNota.
    // Hack para recarregar
    window.location.reload();
}

async function buscarOrdemForm(codigo) {
    codigo = codigo.trim();
    const spanNome = document.getElementById('nfOrdemNome');
    const inputId = document.getElementById('nfOrdemId');
    
    if (!codigo) {
        spanNome.textContent = 'Selecione a Ordem de Compra...';
        spanNome.style.color = 'var(--texto-secundario)';
        inputId.value = '';
        return;
    }
    
    spanNome.textContent = 'Buscando...';
    spanNome.style.color = 'var(--texto-secundario)';
    
    try {
        const resp = await fetch(Scopi.url(`/ordens/consultar-codigo?codigo=${encodeURIComponent(codigo)}`), {credentials:'include'});
        const data = await resp.json();
        if (data.sucesso && data.dados) {
            spanNome.textContent = `Status: ${data.dados.status}`;
            spanNome.style.color = 'var(--sucesso)';
            inputId.value = data.dados.id;
        } else {
            spanNome.textContent = 'Ordem não encontrada';
            spanNome.style.color = 'var(--alerta)';
            inputId.value = '';
        }
    } catch (err) {
        spanNome.textContent = 'Erro ao buscar';
        spanNome.style.color = 'var(--alerta)';
        inputId.value = '';
    }
}

async function buscarFornecedorFiltro(codigo) {
    codigo = codigo.trim();
    const spanNome = document.getElementById('filtroFornNfNome');
    if (!spanNome) return;
    if (!codigo) { spanNome.textContent = 'Digite...'; return; }
    spanNome.textContent = 'Buscando...';
    try {
        const resp = await fetch(Scopi.url(`/fornecedores/consultar-codigo?codigo=${encodeURIComponent(codigo)}`), {credentials:'include'});
        const data = await resp.json();
        if (data.sucesso && data.dados) {
            spanNome.textContent = data.dados.razao_social;
            spanNome.style.color = 'var(--sucesso)';
        } else {
            spanNome.textContent = 'Não encontrado';
            spanNome.style.color = 'var(--alerta)';
        }
    } catch(e) {
        spanNome.textContent = 'Erro';
        spanNome.style.color = 'var(--alerta)';
    }
}

const codInicial = document.getElementById('filtroFornNfCodigo')?.value;
if (codInicial) buscarFornecedorFiltro(codInicial);
</script>

