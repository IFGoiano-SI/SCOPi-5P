<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Autorizações de Solicitações';</script>
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
                <input type="text" name="numero" value="<?= Auxiliares::escapar($filtros['numero']??'') ?>" placeholder="SOL-...">
            </div>
            <div class="campo-filtro">
                <label>A partir de</label>
                <input type="date" name="periodo" value="<?= Auxiliares::escapar($filtros['periodo']??'') ?>">
            </div>
            <div class="campo-filtro">
                <label>Departamento</label>
                <select name="departamento_id" <?= ($usuario['perfil'] === 'gerente') ? 'disabled' : '' ?>>
                    <option value="">Todos</option>
                    <?php foreach ($departamentos as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= (($filtros['departamento_id']??'') == $d['id'] || ($usuario['perfil'] === 'gerente' && $usuario['departamento_id'] == $d['id'])) ? 'selected' : '' ?>>
                            <?= Auxiliares::escapar($d['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
                    <td><span class="badge badge-em-aberto">Em Aberto</span></td>
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
        <div class="modal-corpo">
            <div class="grade-visualizar">
                <div class="campo-visualizar"><span class="rotulo">Número</span><span class="valor" id="visNumero">—</span></div>
                <div class="campo-visualizar"><span class="rotulo">Status</span><span class="valor"><span class="badge badge-em-aberto">Em Aberto</span></span></div>
                <div class="campo-visualizar"><span class="rotulo">Departamento</span><span class="valor" id="visDepartamento">—</span></div>
                <div class="campo-visualizar"><span class="rotulo">Solicitante</span><span class="valor" id="visSolicitante">—</span></div>
                <div class="campo-visualizar campo-completo"><span class="rotulo">Justificativa</span><span class="valor" id="visJustificativa">—</span></div>
            </div>
            
            <div class="campo-visualizar campo-completo" style="margin-top: 20px;">
                <span class="rotulo" style="margin-bottom: 8px;">Itens da Solicitação</span>
                <div class="tabela-container" style="border: 1px solid var(--borda);">
                    <table class="tabela" id="tabItensVisualizar">
                        <thead>
                            <tr>
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
    } else {
        btn.disabled = true;
        btn.style.opacity = '0.5';
    }
}

async function autorizarEmLote() {
    const checkboxes = document.querySelectorAll('.check-item:checked');
    if (checkboxes.length === 0) return;
    
    const ids = Array.from(checkboxes).map(cb => cb.value);
    
    if (confirm(`Deseja realmente autorizar as ${ids.length} solicitações selecionadas?`)) {
        const formData = new FormData();
        formData.append('ids', JSON.stringify(ids));
        
        try {
            const resp = await fetch(`${SCOPI_BASE}/solicitacoes/autorizar-lote`, {
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
        const resp = await fetch(`${SCOPI_BASE}/solicitacoes/dados?id=${id}`);
        const data = await resp.json();
        if (data.sucesso) {
            const r = data.dados;
            document.getElementById('visNumero').textContent = r.numero || '—';
            document.getElementById('visDepartamento').textContent = r.nome_departamento || '—';
            document.getElementById('visSolicitante').textContent = r.nome_solicitante || '—';
            document.getElementById('visJustificativa').textContent = r.justificativa || '—';
            
            const tbody = document.querySelector('#tabItensVisualizar tbody');
            tbody.innerHTML = '';
            if (r.itens && r.itens.length > 0) {
                r.itens.forEach(it => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${it.produto_nome}</td>
                            <td style="text-align: right;">${it.quantidade}</td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="2" style="text-align:center;color:#888;">Nenhum item</td></tr>`;
            }
            
            Scopi.abrirModal('modalSolicitacao');
        } else {
            alert('Erro ao carregar dados da solicitação.');
        }
    } catch (err) {
        alert('Erro ao comunicar com o servidor.');
    }
}
</script>
