<?php use Config\Auxiliares; ?>
<script>
document.getElementById('topbarTitulo').textContent = 'Autorizações de Ordens de Compra';

function formatarStatus(status) {
    return status.replace(/_/g, ' ').split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
}

async function abrirModalVisualizacao(id) {
    try {
        const resp = await fetch(Scopi.url(`/ordens/dados?id=${id}`), {credentials:'include'});
        const data = await resp.json();
        if (data.sucesso && data.dados) {
            const r = data.dados;

            // Preencher campos de visualização
            document.querySelectorAll('#modalOrdem [data-campo]').forEach(el => {
                const campo = el.getAttribute('data-campo');
                if (campo === 'numero') el.textContent = r.numero || '—';
                else if (campo === 'nome_fornecedor') el.textContent = r.nome_fornecedor || '—';
                else if (campo === 'nome_comprador') el.textContent = r.nome_comprador || '—';
                else if (campo === 'transportadora') el.textContent = r.transportadora || '—';
                else if (campo === 'modalidade_frete') el.textContent = r.modalidade_frete || '—';
                else if (campo === 'observacao') el.textContent = r.observacao || '—';
                else if (campo === 'emitido_em_fmt') el.textContent = r.emitido_em ? new Date(r.emitido_em).toLocaleDateString('pt-BR') : '—';
                else if (campo === 'valor_total_fmt') el.textContent = 'R$ ' + parseFloat(r.valor_total||0).toFixed(2).replace('.', ',');
            });

            // Atualizar badge de status
            const badgeStatus = document.querySelector('#modalOrdem [data-badge="status"]');
            if (badgeStatus) {
                badgeStatus.textContent = formatarStatus(r.status || 'aberto');
                badgeStatus.className = 'badge badge-' + (r.status || 'aberto').replace(/_/g, '-');
            }

            // Preencher tabela de itens
            const tbody = document.querySelector('#tabItensVisualizar tbody');
            tbody.innerHTML = '';
            if (r.itens && r.itens.length > 0) {
                r.itens.forEach((it, idx) => {
                    const vlUn = parseFloat(it.preco_unitario||0).toFixed(2).replace('.', ',');
                    const subt = (parseFloat(it.quantidade||0) * parseFloat(it.preco_unitario||0)).toFixed(2).replace('.', ',');
                    tbody.innerHTML += `
                        <tr>
                            <td style="text-align: center; padding: 8px 12px;">${idx + 1}</td>
                            <td style="padding: 8px 12px;">${it.produto_nome}</td>
                            <td style="text-align: right; padding: 8px 12px;">${parseFloat(it.quantidade).toFixed(2)}</td>
                            <td style="text-align: right; padding: 8px 12px;">R$ ${vlUn}</td>
                            <td style="text-align: right; padding: 8px 12px;">${it.condicao_pagamento_descricao || '—'}</td>
                            <td style="text-align: right; padding: 8px 12px;">R$ ${subt}</td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:#888;padding:12px;">Nenhum item</td></tr>`;
            }

            Scopi.abrirModal('modalOrdem');
        } else {
            alert('Erro: ' + (data.mensagem || 'Dados não encontrados'));
        }
    } catch (err) {
        alert('Erro ao comunicar com o servidor: ' + err.message);
    }
}
</script>
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
                <input type="text" name="numero" value="<?= Auxiliares::escapar($filtros['numero']??'') ?>" >
            </div>
            <div class="campo-filtro">
                <label>Data Emissão (Inicial)</label>
                <input type="date" name="data_inicial" value="<?= Auxiliares::escapar($filtros['data_inicial']??'') ?>">
            </div>
            <div class="campo-filtro">
                <label>Data Emissão (Final)</label>
                <input type="date" name="data_final" value="<?= Auxiliares::escapar($filtros['data_final']??'') ?>">
            </div>
            <div class="campo-filtro">
                <label style="display:flex;gap:8px;align-items:center;justify-content:space-between;">
                    <span>Cód. Fornecedor</span>
                    <button type="button" class="btn btn-secundario" style="padding:4px 6px;margin:0;" title="Buscar fornecedor" onclick="Scopi.iconeBusca('fornecedores','filtroFornOcAutCodigo','filtroFornOcAutNome')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width:13px;margin:0;" alt="Buscar"></button>
                </label>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <input type="text" id="filtroFornOcAutCodigo" name="fornecedor_codigo" value="<?= Auxiliares::escapar($filtros['fornecedor_codigo'] ?? '') ?>" class="campo-input" style="width: 100px; text-transform: uppercase;"  onblur="buscarFornecedorFiltro(this.value)">
                    <span id="filtroFornOcAutNome" style="font-size: 0.8rem; color: var(--texto-secundario);"><?= empty($filtros['fornecedor_codigo']) ? 'Digite...' : 'Buscando...' ?></span>
                </div>
            </div>
            <div class="campo-filtro">
                <label>Nº Cotação</label>
                <input type="text" name="num_cotacao" value="<?= Auxiliares::escapar($filtros['num_cotacao']??'') ?>" >
            </div>
            <div class="campo-filtro">
                <label>Status</label>
                <select name="status">
                    <option value="">Todos</option>
                    <option value="aberto" <?= ($filtros['status'] ?? 'aberto') === 'aberto' ? 'selected' : '' ?>>Aberta</option>
                    <option value="autorizada" <?= ($filtros['status'] ?? '') === 'autorizado' ? 'selected' : '' ?>>Autorizada</option>
                </select>
            </div>
            
            <div class="campo-filtro" style="flex:0;align-self:flex-end;">
                <button type="submit" class="btn btn-filtrar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt=""> Filtrar</button>
            </div>
        </div>
    </form>
</div>

<div class="barra-acoes" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; background: #fff; border-radius: 8px; border: 1px solid var(--borda); margin-bottom: 20px;">
    <div style="display: flex; align-items: center; gap: 15px;">
        <span id="contadorSelecionados" style="font-weight: 600; color: var(--escuro);">0 itens selecionados</span>
    </div>
    <button class="btn btn-primario" id="btnAutorizarLote" onclick="autorizarEmLote()" disabled style="opacity: 0.5; pointer-events: none;">
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
                    <td><span class="badge badge-<?= str_replace('_', '-', $o['status'] ?? 'aberto') ?>"><?= ucwords(str_replace('_', ' ', $o['status'] ?? 'aberto')) ?></span></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para Visualização -->
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
        <div class="modal-abas">
            <button class="aba-btn ativa" data-aba="visualizar" onclick="Scopi.ativarAba('modalOrdem','visualizar')">Visualizar</button>
        </div>
        <div class="modal-corpo">
            <div class="conteudo-aba ativo" data-aba="visualizar">
                <div class="grade-visualizar">
                    <div class="campo-visualizar"><span class="rotulo">Número</span><span class="valor" data-campo="numero"></span></div>
                    <div class="campo-visualizar"><span class="rotulo">Status</span><span class="valor"><span class="badge" data-badge="status">—</span></span></div>
                    <div class="campo-visualizar"><span class="rotulo">Fornecedor</span><span class="valor" data-campo="nome_fornecedor">—</span></div>
                    <div class="campo-visualizar"><span class="rotulo">Comprador</span><span class="valor" data-campo="nome_comprador">—</span></div>
                    <div class="campo-visualizar"><span class="rotulo">Data Emissão</span><span class="valor" data-campo="emitido_em_fmt">—</span></div>
                    <div class="campo-visualizar"><span class="rotulo">Transportadora</span><span class="valor" data-campo="transportadora">—</span></div>
                    <div class="campo-visualizar"><span class="rotulo">Modalidade de Frete</span><span class="valor" data-campo="modalidade_frete">—</span></div>
                    <div class="campo-visualizar"><span class="rotulo">Valor Total</span><span class="valor" data-campo="valor_total_fmt">—</span></div>
                    <div class="campo-visualizar campo-completo"><span class="rotulo">Observação</span><span class="valor" data-campo="observacao">—</span></div>
                </div>

                <div class="campo-visualizar campo-completo" style="margin-top: 20px;">
                    <span class="rotulo" style="margin-bottom: 8px;">Itens da Ordem de Compra</span>
                    <div class="tabela-container" style="border: 1px solid var(--borda);">
                        <table class="tabela" id="tabItensVisualizar">
                            <thead>
                                <tr>
                                    <th style="width: 60px; text-align: center; padding: 8px 12px;">Nº Item</th>
                                    <th style="padding: 8px 12px;">Produto</th>
                                    <th style="width: 100px; text-align: right; padding: 8px 12px;">Quantidade</th>
                                    <th style="width: 120px; text-align: right; padding: 8px 12px;">Val. Unit. (R$)</th>
                                    <th style="width: 120px; text-align: right; padding: 8px 12px;">Cond. Pagto</th>
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
        </div>
        <div class="modal-rodape">
            <button class="btn btn-secundario" onclick="Scopi.fecharModal('modalOrdem')">Fechar</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const codInicial = document.getElementById('filtroFornOcAutCodigo')?.value?.trim();
    if (codInicial) buscarFornecedorFiltro(codInicial);
});

async function buscarFornecedorFiltro(codigo) {
    codigo = (codigo || '').trim();
    const span = document.getElementById('filtroFornOcAutNome');
    if (!span) return;
    if (!codigo) { span.textContent = 'Digite...'; span.style.color = 'var(--texto-secundario)'; return; }
    span.textContent = 'Buscando...'; span.style.color = 'var(--texto-secundario)';
    try {
        const resp = await fetch(Scopi.url(`/fornecedores/consultar-codigo?codigo=${encodeURIComponent(codigo)}`), {credentials:'include'});
        const data = await resp.json();
        if (data.sucesso && data.dados) {
            span.textContent = data.dados.nome; span.style.color = 'var(--sucesso)';
        } else {
            span.textContent = 'Não encontrado'; span.style.color = 'var(--alerta)';
        }
    } catch(e) { span.textContent = 'Erro'; span.style.color = 'var(--alerta)'; }
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
        btn.style.pointerEvents = 'auto';
        btn.style.cursor = 'pointer';
    } else {
        btn.disabled = true;
        btn.style.opacity = '0.5';
        btn.style.pointerEvents = 'none';
        btn.style.cursor = 'not-allowed';
    }
}

async function autorizarEmLote() {
    const checkboxes = document.querySelectorAll('.check-item:checked');
    if (checkboxes.length === 0) return;

    const ids = Array.from(checkboxes).map(cb => cb.value);

    Scopi.confirmar(`Deseja realmente autorizar as ${ids.length} ordens de compra selecionadas?`, async () => {
        const formData = new FormData();
        formData.append('ids', JSON.stringify(ids));

        try {
            const resp = await fetch(Scopi.url('/ordens/autorizar-lote'), {
                method: 'POST',
                credentials: 'include',
                body: formData
            });
            const data = await resp.json();
            if (data.sucesso) {
                Scopi.toast('sucesso', data.mensagem);
                setTimeout(() => window.location.reload(), 1200);
            } else {
                Scopi.toast('erro', data.mensagem || 'Erro ao processar as autorizações.');
            }
        } catch (err) {
            Scopi.toast('erro', 'Erro de comunicação com o servidor.');
        }
    });
}
</script>
