<?php use Config\Auxiliares; ?>
<script>
document.getElementById('topbarTitulo').textContent = 'Autorizações de Solicitações';

function formatarStatus(status) {
    return status.replace(/_/g, ' ').split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
}

async function abrirModalVisualizacao(id) {
    try {
        const resp = await fetch(Scopi.url(`/solicitacoes/dados?id=${id}`), {credentials:'include'});
        const data = await resp.json();
        if (data.sucesso && data.dados) {
            const r = data.dados;

            // Preencher campos de visualização
            document.querySelectorAll('#modalSolicitacao [data-campo]').forEach(el => {
                const campo = el.getAttribute('data-campo');
                if (campo === 'numero') el.textContent = r.numero || '—';
                else if (campo === 'nome_departamento') el.textContent = r.nome_departamento || '—';
                else if (campo === 'nome_solicitante') el.textContent = r.nome_solicitante || '—';
                else if (campo === 'justificativa') el.textContent = r.justificativa || '—';
            });

            // Atualizar badge de status
            const badgeStatus = document.querySelector('#modalSolicitacao [data-badge="status"]');
            if (badgeStatus) {
                badgeStatus.textContent = formatarStatus(r.status || 'aberto');
                badgeStatus.className = 'badge badge-' + (r.status || 'aberto').replace(/_/g, '-');
            }

            // Preencher tabela de itens
            const tbody = document.querySelector('#tabItensVisualizar tbody');
            tbody.innerHTML = '';
            if (r.itens && r.itens.length > 0) {
                r.itens.forEach((it, idx) => {
                    tbody.innerHTML += `
                        <tr>
                            <td style="text-align: center; padding: 8px 12px;">${idx + 1}</td>
                            <td style="padding: 8px 12px;">${it.nome_produto || it.produto_nome || ''}</td>
                            <td style="text-align: right; padding: 8px 12px;">${parseFloat(it.quantidade).toFixed(2)}</td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="3" style="text-align:center;color:#888;padding:12px;">Nenhum item</td></tr>`;
            }

            Scopi.abrirModal('modalSolicitacao');
        } else {
            alert('Erro: ' + (data.mensagem || 'Dados não encontrados'));
        }
    } catch (err) {
        alert('Erro ao comunicar com o servidor: ' + err.message);
    }
}
</script>
<div class="pagina-cabecalho">
    <h1 class="pagina-titulo">Autorizações de Solicitações</h1>
    <p class="pagina-subtitulo">Autorização em lote de solicitações de produtos</p>
</div>

<div class="painel-filtros">
    <div class="filtros-cabecalho"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt=""><span>Filtros</span></div>
    <form method="GET" action="<?= BASE_URL ?>/solicitacoes/autorizacoes">
        <div class="filtros-campos">
            <div class="campo-filtro">
                <label>Número</label>
                <input type="text" name="numero" value="<?= Auxiliares::escapar($filtros['numero']??'') ?>" >
            </div>
            <div class="campo-filtro">
                <label>A partir de</label>
                <input type="date" name="periodo" value="<?= Auxiliares::escapar($filtros['periodo']??'') ?>">
            </div>
            <div class="campo-filtro">
                <label style="display:flex;gap:8px;align-items:center;justify-content:space-between;">
                    <span>Matrícula Solicitante</span>
                    <button type="button" class="btn btn-secundario" style="padding:4px 6px;margin:0;" title="Buscar usuário" onclick="Scopi.iconeBusca('usuarios','filtroMatricula','filtroNomeUsuario')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width:13px;margin:0;" alt="Buscar"></button>
                </label>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <input type="text" id="filtroMatricula" name="matricula" value="<?= Auxiliares::escapar($filtros['matricula'] ?? '') ?>" class="campo-input" style="width: 100px;"  onblur="buscarUsuarioFiltro(this.value)">
                    <span id="filtroNomeUsuario" style="font-size: 0.8rem; color: var(--texto-secundario);"><?= empty($filtros['matricula']) ? 'Digite...' : 'Buscando...' ?></span>
                </div>
            </div>
            <div class="campo-filtro">
                <label style="display:flex;gap:8px;align-items:center;justify-content:space-between;">
                    <span>Cód. Departamento</span>
                    <button type="button" class="btn btn-secundario" style="padding:4px 6px;margin:0;" title="Buscar departamento" <?= ($usuario['perfil'] === 'gerente') ? 'disabled' : '' ?> onclick="Scopi.iconeBusca('departamentos','filtroDepCodigo','filtroDepNome')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width:13px;margin:0;" alt="Buscar"></button>
                </label>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <input type="text" id="filtroDepCodigo" name="departamento_codigo" value="<?= Auxiliares::escapar($filtros['departamento_codigo'] ?? '') ?>" class="campo-input" style="width: 100px; text-transform: uppercase;"  <?= ($usuario['perfil'] === 'gerente') ? 'readonly' : '' ?> onblur="buscarDepartamentoFiltro(this.value)">
                    <span id="filtroDepNome" style="font-size: 0.8rem; color: var(--texto-secundario);"><?= empty($filtros['departamento_codigo']) ? 'Digite...' : 'Buscando...' ?></span>
                </div>
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
                <th>Departamento</th>
                <th>Solicitante</th>
                <th>Data</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($solicitacoes)): ?>
                <tr><td colspan="6" style="text-align:center;padding:32px;color:#888;">Nenhuma solicitação aguardando autorização.</td></tr>
            <?php else: foreach($solicitacoes as $s): ?>
                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" class="check-item" value="<?= $s['id'] ?>" onclick="atualizarContador()">
                    </td>
                    <td>
                        <span class="cod-clicavel" onclick="abrirModalVisualizacao(<?= $s['id'] ?>)">
                            <?= Auxiliares::escapar($s['numero']) ?>
                        </span>
                    </td>
                    <td><?= Auxiliares::escapar($s['nome_departamento']??'—') ?></td>
                    <td><?= Auxiliares::escapar($s['nome_solicitante']??'—') ?></td>
                    <td><?= date('d/m/Y', strtotime($s['criado_em'])) ?></td>
                    <td><span class="badge badge-<?= str_replace('_', '-', $s['status'] ?? 'aberto') ?>"><?= ucwords(str_replace('_', ' ', $s['status'] ?? 'aberto')) ?></span></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para Visualização Reutilizado -->
<div class="overlay-modal" id="modalSolicitacao">
    <div class="modal modal-largo">
        <div class="modal-cabecalho">
            <div class="modal-titulo">
                <img src="<?= BASE_URL ?>/public/assets/icons/iconeSolicitacao.svg" alt="">
                <span>Solicitação</span>
            </div>
            <button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalSolicitacao')">
                <img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt="">
            </button>
        </div>
        <div class="modal-abas">
            <button class="aba-btn ativa" data-aba="visualizar" onclick="Scopi.ativarAba('modalSolicitacao','visualizar')">Visualizar</button>
        </div>
        <div class="modal-corpo">
            <div class="conteudo-aba ativo" data-aba="visualizar">
                <div class="grade-visualizar">
                    <div class="campo-visualizar"><span class="rotulo">Número</span><span class="valor" data-campo="numero">—</span></div>
                    <div class="campo-visualizar"><span class="rotulo">Status</span><span class="valor"><span class="badge" data-badge="status">—</span></span></div>
                    <div class="campo-visualizar"><span class="rotulo">Departamento</span><span class="valor" data-campo="nome_departamento">—</span></div>
                    <div class="campo-visualizar"><span class="rotulo">Solicitante</span><span class="valor" data-campo="nome_solicitante">—</span></div>
                    <div class="campo-visualizar campo-completo"><span class="rotulo">Justificativa</span><span class="valor" data-campo="justificativa">—</span></div>
                </div>

                <div class="campo-visualizar campo-completo" style="margin-top: 20px;">
                    <span class="rotulo" style="margin-bottom: 8px;">Itens da Solicitação</span>
                    <div class="tabela-container" style="border: 1px solid var(--borda);">
                        <table class="tabela" id="tabItensVisualizar">
                            <thead>
                                <tr>
                                    <th style="width: 50px; text-align: center; padding: 8px 12px;">Nº Item</th>
                                    <th style="padding: 8px 12px;">Produto</th>
                                    <th style="width: 120px; text-align: right; padding: 8px 12px;">Quantidade</th>
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
            <button class="btn btn-secundario" onclick="Scopi.fecharModal('modalSolicitacao')">Fechar</button>
        </div>
    </div>
</div>

<script>
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

    Scopi.confirmar(`Deseja realmente autorizar as ${ids.length} solicitações selecionadas?`, async () => {
        const formData = new FormData();
        formData.append('ids', JSON.stringify(ids));

        try {
            const resp = await fetch(Scopi.url('/solicitacoes/autorizar-lote'), {
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
            alert('Erro de comunicação com o servidor.');
        }
    });
}
</script>

