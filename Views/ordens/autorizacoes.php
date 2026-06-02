<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Autorizações de Ordens de Compra';</script>
<div class="pagina-cabecalho">
    <h1 class="pagina-titulo">Autorizações de Ordens de Compra</h1>
    <p class="pagina-subtitulo">Autorização em lote de ordens de compra</p>
</div>

<div class="painel-filtros">
    <div class="filtros-cabecalho"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt=""><span>Filtros</span></div>
    <form method="GET" action="<?= BASE_URL ?>/ordens/autorizacoes">
        <div class="filtros-campos">
            <div class="campo-filtro">
                <label>Número</label>
                <input type="text" name="numero" value="<?= Auxiliares::escapar($filtros['numero']??'') ?>" placeholder="OC-...">
            </div>
            <div class="campo-filtro">
                <label>A partir de</label>
                <input type="date" name="periodo" value="<?= Auxiliares::escapar($filtros['periodo']??'') ?>">
            </div>
            
            <div class="campo-filtro">
                <label>Cód. Fornecedor</label>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <input type="text" id="filtroCodigoFornecedor" placeholder="Ex: FOR-123" value="<?= Auxiliares::escapar($_GET['codigo_fornecedor'] ?? '') ?>" style="width: 120px;" onblur="buscarFornecedorPorCodigo()">
                    <span id="nomeFornecedorBusca" style="font-size: 0.85rem; color: var(--texto-secundario); font-style: italic;">Digite o código...</span>
                </div>
                <input type="hidden" name="fornecedor_id" id="filtroFornecedorId" value="<?= Auxiliares::escapar($filtros['fornecedor_id'] ?? '') ?>">
                <input type="hidden" name="codigo_fornecedor" id="filtroCodigoFornecedorHidden" value="<?= Auxiliares::escapar($_GET['codigo_fornecedor'] ?? '') ?>">
            </div>
            
            <div class="campo-filtro" style="flex:0;align-self:flex-end;">
                <button type="submit" class="btn btn-filtrar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt=""> Buscar</button>
            </div>
        </div>
    </form>
</div>

<div class="barra-acoes" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; background: #fff; border-radius: 8px; border: 1px solid var(--borda); margin-bottom: 20px;">
    <div style="display: flex; align-items: center; gap: 15px;">
        <span id="contadorSelecionados" style="font-weight: 600; color: var(--escuro);">0 itens selecionados</span>
    </div>
    <button class="btn btn-primario" id="btnAutorizarLote" onclick="autorizarEmLote()" disabled style="opacity: 0.5;">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Autorizar Selecionados
    </button>
</div>

<div class="tabela-container">
    <table class="tabela" id="tabelaAutorizacoes">
        <thead>
            <tr>
                <th style="width: 40px; text-align: center;">
                    <input type="checkbox" id="checkTodos" onclick="toggleTodos(this)">
                </th>
                <th>Número</th>
                <th>Fornecedor</th>
                <th>Emissão</th>
                <th>Valor Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($ordens)): ?>
                <tr><td colspan="6" style="text-align:center;padding:32px;color:#888;">Nenhuma ordem de compra aguardando autorização.</td></tr>
            <?php else: foreach($ordens as $o): ?>
                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" class="check-item" value="<?= $o['id'] ?>" onclick="atualizarContador()">
                    </td>
                    <td>
                        <span class="cod-clicavel" onclick="abrirModalVisualizacao(<?= $o['id'] ?>)">
                            <?= Auxiliares::escapar($o['numero']??$o['id']) ?>
                        </span>
                    </td>
                    <td><?= Auxiliares::escapar($o['nome_fornecedor']??'—') ?></td>
                    <td><?= !empty($o['emitido_em'])?date('d/m/Y',strtotime($o['emitido_em'])):'—' ?></td>
                    <td>R$ <?= number_format($o['valor_total']??0,2,',','.') ?></td>
                    <td><span class="badge badge-aberta">Aberta</span></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para Visualização Reutilizado -->
<div class="overlay-modal" id="modalOrdem">
    <div class="modal modal-largo">
        <div class="modal-cabecalho">
            <div class="modal-titulo">
                <img src="<?= BASE_URL ?>/public/assets/icons/iconeOC.svg" alt="">
                <span>Ordem de Compra</span>
            </div>
            <button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalOrdem')">
                <img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt="">
            </button>
        </div>
        <div class="modal-corpo">
            <div class="grade-visualizar">
                <div class="campo-visualizar"><span class="rotulo">Número</span><span class="valor" id="visNumero">—</span></div>
                <div class="campo-visualizar"><span class="rotulo">Status</span><span class="valor"><span class="badge badge-aberta">Aberta</span></span></div>
                <div class="campo-visualizar campo-completo"><span class="rotulo">Fornecedor</span><span class="valor" id="visFornecedor">—</span></div>
                <div class="campo-visualizar"><span class="rotulo">Condição de Pagamento</span><span class="valor" id="visCondicao">—</span></div>
                <div class="campo-visualizar"><span class="rotulo">Modalidade de Frete</span><span class="valor" id="visFrete">—</span></div>
                <div class="campo-visualizar"><span class="rotulo">Prazo de Entrega</span><span class="valor" id="visPrazo">—</span></div>
                <div class="campo-visualizar"><span class="rotulo">Valor Total</span><span class="valor" id="visValor">—</span></div>
                <div class="campo-visualizar campo-completo"><span class="rotulo">Observação</span><span class="valor" id="visObservacao">—</span></div>
            </div>
            
            <div class="campo-visualizar campo-completo" style="margin-top: 20px;">
                <span class="rotulo" style="margin-bottom: 8px;">Itens da Ordem de Compra</span>
                <div class="tabela-container" style="border: 1px solid var(--borda);">
                    <table class="tabela" id="tabItensVisualizar">
                        <thead>
                            <tr>
                                <th style="padding: 8px 12px;">Produto</th>
                                <th style="width: 100px; text-align: right; padding: 8px 12px;">Quantidade</th>
                                <th style="width: 120px; text-align: right; padding: 8px 12px;">Val. Unit. (R$)</th>
                                <th style="width: 120px; text-align: right; padding: 8px 12px;">Subtotal (R$)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- preenchido dinamicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal-rodape">
            <button class="btn btn-secundario" onclick="Scopi.fecharModal('modalOrdem')">Fechar</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Busca inicial se já tiver código preenchido
    const codInicial = document.getElementById('filtroCodigoFornecedor').value.trim();
    if(codInicial) buscarFornecedorPorCodigo();
});

async function buscarFornecedorPorCodigo() {
    const input = document.getElementById('filtroCodigoFornecedor');
    const span = document.getElementById('nomeFornecedorBusca');
    const inputId = document.getElementById('filtroFornecedorId');
    const inputHiddenCod = document.getElementById('filtroCodigoFornecedorHidden');
    
    const codigo = input.value.trim();
    inputHiddenCod.value = codigo;
    
    if (!codigo) {
        span.textContent = 'Digite o código...';
        span.style.color = 'var(--texto-secundario)';
        inputId.value = '';
        return;
    }
    
    span.textContent = 'Buscando...';
    span.style.color = 'var(--texto-secundario)';
    
    try {
        const resp = await fetch(`${SCOPI_BASE}/fornecedores/consultar-codigo?codigo=${encodeURIComponent(codigo)}`);
        const data = await resp.json();
        if (data.sucesso && data.dados) {
            span.textContent = data.dados.nome;
            span.style.color = 'var(--sucesso)';
            inputId.value = data.dados.id;
        } else {
            span.textContent = 'Fornecedor não encontrado';
            span.style.color = 'var(--alerta)';
            inputId.value = '';
        }
    } catch (err) {
        span.textContent = 'Erro ao buscar';
        span.style.color = 'var(--alerta)';
        inputId.value = '';
    }
}

function toggleTodos(source) {
    const checkboxes = document.querySelectorAll('.check-item');
    checkboxes.forEach(cb => cb.checked = source.checked);
    atualizarContador();
}

function atualizarContador() {
    const count = document.querySelectorAll('.check-item:checked').length;
    const btn = document.getElementById('btnAutorizarLote');
    const span = document.getElementById('contadorSelecionados');
    
    span.textContent = `${count} item(ns) selecionado(s)`;
    if (count > 0) {
        btn.disabled = false;
        btn.style.opacity = '1';
    } else {
        btn.disabled = true;
        btn.style.opacity = '0.5';
    }
}

async function autorizarEmLote() {
    const checkboxes = document.querySelectorAll('.check-item:checked');
    if (checkboxes.length === 0) return;
    
    const ids = Array.from(checkboxes).map(cb => cb.value);
    
    if (confirm(`Deseja realmente autorizar as ${ids.length} ordens de compra selecionadas?`)) {
        const formData = new FormData();
        formData.append('ids', JSON.stringify(ids));
        
        try {
            const resp = await fetch(`${SCOPI_BASE}/ordens/autorizar-lote`, {
                method: 'POST',
                body: formData
            });
            const data = await resp.json();
            if (data.sucesso) {
                alert(data.mensagem);
                window.location.reload();
            } else {
                alert(data.mensagem || 'Erro ao processar as autorizações.');
            }
        } catch (err) {
            alert('Erro de comunicação com o servidor.');
        }
    }
}

async function abrirModalVisualizacao(id) {
    try {
        const resp = await fetch(`${SCOPI_BASE}/ordens/dados?id=${id}`);
        const data = await resp.json();
        if (data.sucesso) {
            const r = data.dados;
            document.getElementById('visNumero').textContent = r.numero || '—';
            document.getElementById('visFornecedor').textContent = r.nome_fornecedor || '—';
            document.getElementById('visCondicao').textContent = r.condicao_pagamento || '—';
            document.getElementById('visFrete').textContent = r.modalidade_frete || '—';
            document.getElementById('visPrazo').textContent = r.prazo_entrega || '—';
            document.getElementById('visValor').textContent = 'R$ ' + parseFloat(r.valor_total).toFixed(2).replace('.', ',');
            document.getElementById('visObservacao').textContent = r.observacao || '—';
            
            const tbody = document.querySelector('#tabItensVisualizar tbody');
            tbody.innerHTML = '';
            if (r.itens && r.itens.length > 0) {
                r.itens.forEach(it => {
                    const vlUn = parseFloat(it.preco_unitario||0).toFixed(2).replace('.',',');
                    const subt = (parseFloat(it.quantidade||0) * parseFloat(it.preco_unitario||0)).toFixed(2).replace('.',',');
                    tbody.innerHTML += `
                        <tr>
                            <td>${it.produto_nome}</td>
                            <td style="text-align: right;">${it.quantidade}</td>
                            <td style="text-align: right;">R$ ${vlUn}</td>
                            <td style="text-align: right;">R$ ${subt}</td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;color:#888;">Nenhum item</td></tr>`;
            }
            
            Scopi.abrirModal('modalOrdem');
        } else {
            alert('Erro ao carregar dados da ordem.');
        }
    } catch (err) {
        alert('Erro ao comunicar com o servidor.');
    }
}
</script>
