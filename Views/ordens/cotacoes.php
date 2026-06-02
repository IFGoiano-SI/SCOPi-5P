<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Cotações';</script>
<div class="pagina-cabecalho"><h1 class="pagina-titulo">Cotações</h1><p class="pagina-subtitulo">Gerenciamento de cotações com fornecedores</p></div>
<div class="painel-filtros">
  <div class="filtros-cabecalho"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt=""><span>Filtros</span></div>
  <form method="GET" action="<?= BASE_URL ?>/cotacoes"><div class="filtros-campos">
    <div class="campo-filtro"><label>Status</label><select name="status"><option value="">Todos</option><option value="aberta">Aberta</option><option value="fechada">Fechada</option><option value="concluida">Concluída</option><option value="cancelada">Cancelada</option></select></div>
    <div class="campo-filtro"><label>A partir de</label><input type="date" name="periodo" value="<?= Auxiliares::escapar($filtros['periodo']??'') ?>"></div>
    <div class="campo-filtro" style="flex:0;align-self:flex-end;"><button type="submit" class="btn btn-filtrar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt=""> Buscar</button></div>
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
    <thead><tr><th>Nº Cotação</th><th>Solicitação</th><th>Abertura</th><th>Encerramento</th><th>Status</th><th class="coluna-acoes"></th></tr></thead>
    <tbody>
      <?php if(empty($cotacoes)): ?><tr><td colspan="6" style="text-align:center;padding:32px;color:#888;">Nenhuma cotação encontrada.</td></tr>
      <?php else: foreach($cotacoes as $c): ?>
      <tr>
        <td><span class="cod-clicavel" onclick="Scopi.abrirRegistro('modalCotacao','formCotacao','/cotacoes/dados',<?= $c['id'] ?>,'visualizar')"><?= Auxiliares::escapar($c['numero']??$c['id']) ?></span></td>
        <td><?= Auxiliares::escapar($c['num_solicitacao']??'—') ?></td>
        <td><?= !empty($c['data_abertura'])?date('d/m/Y',strtotime($c['data_abertura'])):'—' ?></td>
        <td><?= !empty($c['data_encerramento'])?date('d/m/Y',strtotime($c['data_encerramento'])):'—' ?></td>
        <td><span class="badge badge-<?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span></td>
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
  <div class="modal modal-largo" style="max-width: 90%; width: 1200px;">
    <div class="modal-cabecalho">
      <div class="modal-titulo">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeOC.svg" alt="">
        <span>Cotação</span>
      </div>
      <button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalCotacao')">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt="">
      </button>
    </div>
    
    <div class="modal-abas">
      <button class="aba-btn ativa" data-aba="visualizar" onclick="Scopi.ativarAba('modalCotacao','visualizar')">Visualizar</button>
      <?php if(in_array($usuario['perfil'], ['comprador', 'administrador'])): ?>
        <button class="aba-btn" data-aba="editar" onclick="Scopi.ativarAba('modalCotacao','editar')">Nova Cotação</button>
      <?php endif; ?>
    </div>
    
    <div class="modal-corpo">
      <div class="conteudo-aba ativo" data-aba="visualizar">
        <div class="grade-visualizar">
          <div class="campo-visualizar"><span class="rotulo">Número</span><span class="valor" data-campo="numero">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Status</span><span class="valor"><span class="badge" data-badge="status">—</span></span></div>
          <div class="campo-visualizar"><span class="rotulo">Abertura</span><span class="valor" data-campo="data_abertura">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Encerramento</span><span class="valor" data-campo="data_encerramento">—</span></div>
        </div>

        <div style="margin-top: 25px;">
          <h3 style="font-size: 0.9rem; font-weight: 600; color: var(--escura); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
            <img src="<?= BASE_URL ?>/public/assets/icons/iconeOC.svg" style="width: 16px;" alt="">
            Comparativo de Propostas Comerciais
          </h3>
          <div id="comparativoMatrixContainer" style="overflow-x: auto; border: 1px solid var(--borda); border-radius: 6px; background-color: var(--branco);">
            <!-- Populated dynamically via JS -->
          </div>
        </div>
      </div>
      
      <div class="conteudo-aba" data-aba="editar">
        <form id="formCotacao" onsubmit="event.preventDefault();Scopi.enviarFormulario('formCotacao','modalCotacao','/cotacoes/criar')">
          <input type="hidden" name="id" value="0">
          <div class="grade-form" style="grid-template-columns: 1fr;">
            
            <div class="campo-form">
              <label>Solicitação Autorizada *</label>
              <select name="solicitacao_id" id="cotacSolicitacaoSel" required onchange="carregarItensSolicitacao(this.value)" class="campo-select" style="width: 100%; padding: 8px 10px; border: 1px solid var(--borda); border-radius: 6px; font-size: 0.78rem;">
                <option value="">Selecione uma solicitação...</option>
              </select>
            </div>
            
            <div id="itensSolicitacaoPreviewContainer" style="margin-top: 10px; display: none;">
              <label style="margin-bottom: 6px; font-weight: 500; font-size: 0.78rem;">Itens da Solicitação Selecionada:</label>
              <div class="tabela-container" style="border: 1px solid var(--borda); max-height: 150px; overflow-y: auto;">
                <table class="tabela" id="tabItensSolicitacaoPreview">
                  <thead>
                    <tr>
                      <th style="padding: 6px 10px; text-align: left;">Produto</th>
                      <th style="width: 120px; text-align: right; padding: 6px 10px;">Quantidade</th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div>
            
            <div class="campo-form" style="margin-top: 15px; border-top: 1px solid var(--borda); padding-top: 15px;">
              <label style="margin-bottom: 6px; font-weight: 500; font-size: 0.78rem;">Selecionar Fornecedores para Convidar *</label>
              <input type="text" id="fornecedorBusca" placeholder="Pesquisar fornecedor por nome ou CNPJ..." oninput="filtrarFornecedoresChecklist(this.value)" class="campo-input" style="margin-bottom: 8px; font-size: 0.78rem; padding: 8px 10px; width: 100%;">
              <div style="border: 1px solid var(--borda); border-radius: 6px; padding: 10px; max-height: 200px; overflow-y: auto; background-color: var(--branco);" id="fornecedoresChecklist">
                <?php foreach($fornecedoresAtivos as $f): ?>
                  <div class="fornecedor-chk-item" style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;" data-nome="<?= strtolower(Auxiliares::escapar($f['razao_social'])) ?>" data-cnpj="<?= strtolower(Auxiliares::escapar($f['cnpj'])) ?>">
                    <input type="checkbox" name="fornecedores[]" value="<?= $f['id'] ?>" id="forn_chk_<?= $f['id'] ?>" style="width: 16px; height: 16px; accent-color: var(--media);">
                    <label for="forn_chk_<?= $f['id'] ?>" style="font-size: 0.78rem; cursor: pointer; user-select: none;">
                      <strong><?= Auxiliares::escapar($f['razao_social']) ?></strong> (CNPJ: <?= Auxiliares::escapar($f['cnpj']) ?>)
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
            
          </div>
        </form>
      </div>
    </div>
    
    <div class="modal-rodape">
      <button class="btn btn-secundario" onclick="Scopi.fecharModal('modalCotacao')">Fechar</button>
      <button class="btn btn-primario btn-salvar" onclick="Scopi.enviarFormulario('formCotacao','modalCotacao','/cotacoes/criar')">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Criar Cotação
      </button>
    </div>
  </div>
</div>

<script>
const USER_PERFIL = "<?= $usuario['perfil'] ?>";
let _idCot = 0;
let _statusCot = 'aberta';
let _cotacaoDados = null;

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
        _idCot = 0;
        _statusCot = 'aberta';
        const searchInput = document.getElementById('fornecedorBusca');
        if (searchInput) searchInput.value = '';
        filtrarFornecedoresChecklist('');
        
        document.querySelectorAll('#fornecedoresChecklist input[type="checkbox"]').forEach(cb => cb.checked = false);
        
        const preview = document.getElementById('itensSolicitacaoPreviewContainer');
        if (preview) preview.style.display = 'none';
        
        carregarSolicitacoesAutorizadas();
        atualizarBotoesRodape('editar');
    }
};

const _origAbrirRegistro = Scopi.abrirRegistro;
Scopi.abrirRegistro = async function(idModal, idForm, urlDados, id, abaInicial='visualizar') {
    if (idModal === 'modalCotacao') {
        _idCot = id;
        await _origAbrirRegistro(idModal, idForm, urlDados, id, abaInicial);
        try {
            const resp = await fetch(`${Scopi.url(urlDados)}?id=${id}`, {headers:{'X-Requested-With':'XMLHttpRequest'}});
            const json = await resp.json();
            if (json.sucesso) {
                _cotacaoDados = json.dados;
                _statusCot = json.dados.status || 'aberta';
                
                const dataAberturaField = document.querySelector('#modalCotacao [data-campo="data_abertura"]');
                if (dataAberturaField && json.dados.data_abertura) {
                    dataAberturaField.textContent = formatarData(json.dados.data_abertura);
                }
                const dataEncerramentoField = document.querySelector('#modalCotacao [data-campo="data_encerramento"]');
                if (dataEncerramentoField && json.dados.data_encerramento) {
                    dataEncerramentoField.textContent = formatarData(json.dados.data_encerramento);
                } else if (dataEncerramentoField) {
                    dataEncerramentoField.textContent = '—';
                }

                renderMatrix(json.dados);
                atualizarBotoesRodape(abaInicial);
            }
        } catch(e) {
            console.error(e);
        }
    } else {
        await _origAbrirRegistro(idModal, idForm, urlDados, id, abaInicial);
    }
};

const _origAtivarAba = Scopi.ativarAba;
Scopi.ativarAba = function(modalId, aba) {
    _origAtivarAba(modalId, aba);
    if (modalId === 'modalCotacao') {
        atualizarBotoesRodape(aba);
    }
};

function atualizarBotoesRodape(aba) {
    const btnSalvar = document.querySelector('#modalCotacao .btn-salvar');
    if (!btnSalvar) return;
    
    if (aba === 'visualizar') {
        btnSalvar.style.display = 'none';
    } else if (aba === 'editar') {
        btnSalvar.style.display = 'inline-flex';
    }
}

async function carregarSolicitacoesAutorizadas() {
    const sel = document.getElementById('cotacSolicitacaoSel');
    if (!sel) return;
    sel.innerHTML = '<option value="">Carregando solicitações...</option>';
    
    try {
        const resp = await fetch(Scopi.url('/solicitacoes/autorizadas'), {headers:{'X-Requested-With':'XMLHttpRequest'}});
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
    
    tbody.innerHTML = '<tr><td colspan="2" style="text-align:center;padding:10px;">Carregando itens...</td></tr>';
    preview.style.display = 'block';
    
    try {
        const resp = await fetch(Scopi.url(`/solicitacoes/dados?id=${solicitacaoId}`), {headers:{'X-Requested-With':'XMLHttpRequest'}});
        const json = await resp.json();
        if (json.sucesso && json.dados && json.dados.itens) {
            let html = '';
            json.dados.itens.forEach(item => {
                html += `
                    <tr>
                        <td style="padding: 6px 10px;">${item.nome_produto} (${item.codigo_produto || '—'})</td>
                        <td style="text-align: right; padding: 6px 10px; font-weight: 600;">${parseFloat(item.quantidade).toFixed(2)}</td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        } else {
            tbody.innerHTML = '<tr><td colspan="2" style="text-align:center;padding:10px;color:var(--alerta);">Erro ao carregar os itens desta solicitação.</td></tr>';
        }
    } catch(e) {
        tbody.innerHTML = '<tr><td colspan="2" style="text-align:center;padding:10px;color:var(--alerta);">Erro na comunicação.</td></tr>';
    }
}

function filtrarFornecedoresChecklist(busca) {
    const termo = busca.toLowerCase().trim();
    document.querySelectorAll('.fornecedor-chk-item').forEach(item => {
        const nome = item.dataset.nome || '';
        const cnpj = item.dataset.cnpj || '';
        if (nome.includes(termo) || cnpj.includes(termo)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

function selecionarPropostaVencedora(cotacaoId, cotacaoFornecedorId) {
    Scopi.confirmarAcao(
        'Confirmar esta proposta como vencedora? Esta ação encerrará a cotação e gerará a Ordem de Compra correspondente.',
        '/cotacoes/selecionar-vencedor',
        { cotacao_id: cotacaoId, cotacao_fornecedor_id: cotacaoFornecedorId }
    );
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
        let statusText = f.status.charAt(0).toUpperCase() + f.status.slice(1);
        let badgeClass = 'badge-pendente';
        if (f.status === 'respondido') badgeClass = 'badge-concluida';
        if (f.status === 'recusado') badgeClass = 'badge-cancelada';
        if (f.status === 'visualizado') badgeClass = 'badge-em-aberto';

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
                const desc = parseFloat(prop.desconto_valor || 0);
                const subComDesc = Math.max(0, sub - desc);
                html += `
                    <div style="font-weight: 600; color: var(--media);">${formatarMoeda(prop.preco_unitario)}</div>
                    <div style="font-size: 0.75rem; color: #666; margin-top: 2px;">Subtotal: ${formatarMoeda(sub)}</div>
                    ${desc > 0 ? `
                        <div style="font-size: 0.72rem; color: var(--alerta); margin-top: 1px;">Desc: -${formatarMoeda(desc)}</div>
                        <div style="font-size: 0.75rem; color: #2e7d32; font-weight: 600;">Total: ${formatarMoeda(subComDesc)}</div>
                    ` : ''}
                    <div style="font-size: 0.72rem; color: #444; margin-top: 4px;">Prazo: ${prop.prazo_entrega} dias</div>
                    ${prop.observacao ? `<div style="font-size: 0.70rem; color: #888; font-style: italic; margin-top: 4px; max-width: 180px; margin-left: auto; margin-right: auto; line-height: 1.2;">Obs: ${prop.observacao}</div>` : ''}
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

    html += renderHeaderParamRow('Condição Pagamento', f => {
        if (f.status !== 'respondido') return '—';
        return f.condicao_pagamento || 'Não informado';
    });

    html += renderHeaderParamRow('Impostos', f => {
        if (f.status !== 'respondido') return '—';
        return formatarMoeda(f.impostos);
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

    html += renderHeaderParamRow('Garantia', f => {
        if (f.status !== 'respondido') return '—';
        return f.garantia || '—';
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
                    const desc = parseFloat(prop.desconto_valor || 0);
                    subtotalItens += Math.max(0, sub - desc);
                }
            });
            const impostos = parseFloat(f.impostos || 0);
            const taxas = parseFloat(f.taxas_adicionais || 0);

            const totalGeral = Math.max(0, subtotalItens + impostos + taxas);
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
            html += `<span class="badge badge-concluida" style="padding: 6px 12px; font-size: 0.78rem;">✓ Vencedora (OC Gerada)</span>`;
        } else {
            if (cotacao.status !== 'fechada' && cotacao.status !== 'cancelada' && f.status === 'respondido') {
                if (USER_PERFIL === 'comprador' || USER_PERFIL === 'administrador') {
                    html += `
                        <button type="button" class="btn" style="background-color: var(--media); color: var(--branco); font-size: 0.75rem; padding: 6px 12px;" 
                                onclick="selecionarPropostaVencedora(${cotacao.id}, ${f.id})">
                            Selecionar Vencedor
                        </button>
                    `;
                } else {
                    html += `<span style="color: #888; font-size: 0.75rem;">Aguardando seleção</span>`;
                }
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

    container.innerHTML = html;
}
</script>
