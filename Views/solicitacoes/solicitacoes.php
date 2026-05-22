<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Solicitações';</script>
<div class="pagina-cabecalho"><h1 class="pagina-titulo">Solicitações</h1><p class="pagina-subtitulo">Registro e acompanhamento de solicitações de produto</p></div>
<div class="painel-filtros">
  <div class="filtros-cabecalho"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt=""><span>Filtros</span></div>
  <form method="GET" action="<?= BASE_URL ?>/solicitacoes"><div class="filtros-campos">
    <div class="campo-filtro"><label>Número</label><input type="text" name="numero" value="<?= Auxiliares::escapar($filtros['numero']??'') ?>" placeholder="SOL-..."></div>
    <div class="campo-filtro"><label>Status</label>
      <select name="status">
        <option value="">Todos</option>
        <option value="em_aberto"  <?= ($filtros['status']??'')==='em_aberto'?'selected':'' ?>>Em Aberto</option>
        <option value="autorizada" <?= ($filtros['status']??'')==='autorizada'?'selected':'' ?>>Autorizada</option>
        <option value="em_cotacao" <?= ($filtros['status']??'')==='em_cotacao'?'selected':'' ?>>Em Cotação</option>
        <option value="recusada"   <?= ($filtros['status']??'')==='recusada'?'selected':'' ?>>Recusada</option>
        <option value="concluida"  <?= ($filtros['status']??'')==='concluida'?'selected':'' ?>>Concluída</option>
        <option value="cancelada"  <?= ($filtros['status']??'')==='cancelada'?'selected':'' ?>>Cancelada</option>
      </select>
    </div>
    <div class="campo-filtro"><label>A partir de</label><input type="date" name="periodo" value="<?= Auxiliares::escapar($filtros['periodo']??'') ?>"></div>
    <div class="campo-filtro" style="flex:0;align-self:flex-end;"><button type="submit" class="btn btn-filtrar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt=""> Buscar</button></div>
  </div></form>
</div>
<div class="barra-acoes">
  <div class="grupo-botoes">
    <button class="btn btn-primario" onclick="Scopi.abrirCadastro('modalSolicitacao','formSolicitacao')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Nova Solicitação</button>
    <button class="btn btn-secundario"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Exportar</button>
  </div>
  <span style="font-size:.82rem;color:#888;"><?= count($solicitacoes) ?> registro(s)</span>
</div>
<div class="tabela-container">
  <table class="tabela">
    <thead><tr><th></th><th>Número</th><th>Departamento</th><th>Solicitante</th><th>Data</th><th>Status</th><th class="coluna-acoes"></th></tr></thead>
    <tbody>
      <?php if(empty($solicitacoes)): ?><tr><td colspan="7" style="text-align:center;padding:32px;color:#888;">Nenhuma solicitação encontrada.</td></tr>
      <?php else: foreach($solicitacoes as $s): ?>
      <tr>
        <td></td>
        <td><span class="cod-clicavel" onclick="Scopi.abrirRegistro('modalSolicitacao','formSolicitacao','/solicitacoes/dados',<?= $s['id'] ?>,'visualizar')"><?= Auxiliares::escapar($s['numero']) ?></span></td>
        <td><?= Auxiliares::escapar($s['nome_departamento']??'—') ?></td>
        <td><?= Auxiliares::escapar($s['nome_solicitante']??'—') ?></td>
        <td><?= date('d/m/Y', strtotime($s['criado_em'])) ?></td>
        <td><span class="badge badge-<?= str_replace('_','-',$s['status']) ?>"><?= str_replace('_',' ', ucfirst($s['status'])) ?></span></td>
        <td class="coluna-acoes">
          <?php if($s['status'] === 'em_aberto' || $s['status'] === 'recusada'): ?>
            <button class="btn-icone btn-editar-linha" onclick="Scopi.abrirRegistro('modalSolicitacao','formSolicitacao','/solicitacoes/dados',<?= $s['id'] ?>,'editar')" title="Editar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeEditar.svg" alt=""></button>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
<div class="overlay-modal" id="modalSolicitacao">
  <div class="modal modal-largo">
    <div class="modal-cabecalho"><div class="modal-titulo"><img src="<?= BASE_URL ?>/public/assets/icons/iconeSolicitacao.svg" alt=""><span>Solicitação</span></div><button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalSolicitacao')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt=""></button></div>
    <div class="modal-abas"><button class="aba-btn ativa" data-aba="visualizar" onclick="Scopi.ativarAba('modalSolicitacao','visualizar')">Visualizar</button><button class="aba-btn" data-aba="editar" onclick="Scopi.ativarAba('modalSolicitacao','editar')">Nova / Editar</button></div>
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
                  <th style="padding: 8px 12px;">Produto</th>
                  <th style="width: 120px; text-align: right; padding: 8px 12px;">Quantidade</th>
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
        <form id="formSolicitacao" onsubmit="event.preventDefault();Scopi.enviarFormulario('formSolicitacao','modalSolicitacao','/solicitacoes/salvar')">
          <input type="hidden" name="id" value="0">
          <div class="grade-form" style="grid-template-columns:1fr;">
            <div class="campo-form"><label>Justificativa *</label><textarea name="justificativa" required rows="2" placeholder="Descreva a necessidade..."></textarea></div>
            
            <div class="campo-form" style="margin-top: 10px; border-top: 1px solid var(--borda); padding-top: 15px;">
              <label style="margin-bottom: 8px;">Adicionar Produtos</label>
              <div style="display: flex; gap: 8px; margin-bottom: 12px; align-items: center;">
                <select id="solicProdutoSel" class="campo-select" style="flex: 1; padding: 8px 10px; border: 1px solid var(--borda); border-radius: 6px; font-size: 0.78rem;">
                  <option value="">Selecione um produto...</option>
                  <?php foreach($produtosAtivos as $p): ?>
                    <option value="<?= $p['id'] ?>" data-nome="<?= Auxiliares::escapar($p['nome']) ?>" data-codigo="<?= Auxiliares::escapar($p['codigo']) ?>">
                      <?= Auxiliares::escapar($p['nome']) ?> (<?= Auxiliares::escapar($p['codigo']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
                <input type="number" id="solicQtdInput" class="campo-input" style="width: 100px; padding: 8px 10px; border: 1px solid var(--borda); border-radius: 6px; font-size: 0.78rem;" min="0.01" step="any" placeholder="Qtd.">
                <button type="button" class="btn btn-primario" onclick="adicionarItemTabela()" style="height: 34px;">Adicionar</button>
              </div>
              
              <span class="rotulo" style="margin-bottom: 8px;">Lista de Itens</span>
              <div class="tabela-container" style="border: 1px solid var(--borda); max-height: 200px; overflow-y: auto;">
                <table class="tabela" id="tabItensEditar">
                  <thead>
                    <tr>
                      <th style="padding: 8px 12px;">Produto</th>
                      <th style="width: 120px; text-align: right; padding: 8px 12px;">Quantidade</th>
                      <th style="width: 50px; padding: 8px 12px;"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- populated dynamically -->
                  </tbody>
                </table>
              </div>
              <input type="hidden" name="itens_json" id="itensJsonInput" value="[]">
            </div>
          </div>
        </form>
      </div>
    </div>
    <div class="modal-rodape">
      <button class="btn btn-perigo" id="btnCancelarSol" style="margin-right:auto;" onclick="Scopi.confirmarAcao('Cancelar esta solicitação?','/solicitacoes/cancelar',{id:_idSol})">Cancelar Solicitação</button>
      <button class="btn" id="btnAutorizarSol" style="background-color: var(--sucesso); color: var(--branco);" onclick="Scopi.confirmarAcao('Autorizar esta solicitação?','/solicitacoes/autorizar',{id:_idSol})">Autorizar</button>
      <button class="btn" id="btnRecusarSol" style="background-color: var(--alerta); color: var(--branco);" onclick="Scopi.confirmarAcao('Recusar esta solicitação?','/solicitacoes/recusar',{id:_idSol})">Recusar</button>
      <button class="btn" id="btnDesautorizarSol" style="background-color: #E65100; color: var(--branco);" onclick="Scopi.confirmarAcao('Retirar autorização desta solicitação?','/solicitacoes/desautorizar',{id:_idSol})">Retirar Autorização</button>
      <button class="btn btn-secundario" onclick="Scopi.fecharModal('modalSolicitacao')">Fechar</button>
      <button class="btn btn-primario btn-salvar" onclick="Scopi.enviarFormulario('formSolicitacao','modalSolicitacao','/solicitacoes/salvar')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Salvar</button>
    </div>
  </div>
</div>
<script>
const USER_PERFIL = "<?= $usuario['perfil'] ?>";
let _idSol = 0;
let _statusSol = 'em_aberto';
let _itensSolicitacao = [];

const _origAbrirCadastro = Scopi.abrirCadastro;
Scopi.abrirCadastro = function(idModal, idForm) {
    _origAbrirCadastro(idModal, idForm);
    if (idModal === 'modalSolicitacao') {
        _idSol = 0;
        _statusSol = 'em_aberto';
        _itensSolicitacao = [];
        const tabEditarBtn = document.querySelector('#modalSolicitacao .aba-btn[data-aba="editar"]');
        if (tabEditarBtn) tabEditarBtn.style.display = 'inline-block';
        renderItensEditar();
        atualizarBotoesRodape('editar');
    }
};

const _origAbrirRegistro = Scopi.abrirRegistro;
Scopi.abrirRegistro = async function(idModal, idForm, urlDados, id, abaInicial='visualizar') {
    if (idModal === 'modalSolicitacao') {
        _idSol = id;
        await _origAbrirRegistro(idModal, idForm, urlDados, id, abaInicial);
        try {
            const resp = await fetch(`${Scopi.url(urlDados)}?id=${id}`, {headers:{'X-Requested-With':'XMLHttpRequest'}});
            const json = await resp.json();
            if (json.sucesso) {
                _itensSolicitacao = json.dados.itens || [];
                _statusSol = json.dados.status || 'em_aberto';
                
                const tabEditarBtn = document.querySelector('#modalSolicitacao .aba-btn[data-aba="editar"]');
                if (tabEditarBtn) {
                    if (_statusSol !== 'em_aberto' && _statusSol !== 'recusada') {
                        tabEditarBtn.style.display = 'none';
                    } else {
                        tabEditarBtn.style.display = 'inline-block';
                    }
                }
                
                renderItensVisualizar();
                renderItensEditar();
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
    if (modalId === 'modalSolicitacao') {
        atualizarBotoesRodape(aba);
    }
};

function atualizarBotoesRodape(aba) {
    const btnCancelar = document.getElementById('btnCancelarSol');
    const btnAutorizar = document.getElementById('btnAutorizarSol');
    const btnRecusar = document.getElementById('btnRecusarSol');
    const btnDesautorizar = document.getElementById('btnDesautorizarSol');
    const btnSalvar = document.querySelector('#modalSolicitacao .btn-salvar');

    if (btnCancelar) btnCancelar.style.display = 'none';
    if (btnAutorizar) btnAutorizar.style.display = 'none';
    if (btnRecusar) btnRecusar.style.display = 'none';
    if (btnDesautorizar) btnDesautorizar.style.display = 'none';
    if (btnSalvar) btnSalvar.style.display = 'none';

    if (aba === 'visualizar') {
        if (btnCancelar && (_statusSol === 'em_aberto' || _statusSol === 'autorizada')) {
            btnCancelar.style.display = 'inline-flex';
        }
        if (btnAutorizar && btnRecusar && _statusSol === 'em_aberto' && (USER_PERFIL === 'gerente' || USER_PERFIL === 'administrador')) {
            btnAutorizar.style.display = 'inline-flex';
            btnRecusar.style.display = 'inline-flex';
        }
        if (btnDesautorizar && _statusSol === 'autorizada' && (USER_PERFIL === 'gerente' || USER_PERFIL === 'administrador')) {
            btnDesautorizar.style.display = 'inline-flex';
        }
    } else if (aba === 'editar') {
        if (btnSalvar) {
            if (_idSol === 0 || _statusSol === 'em_aberto' || _statusSol === 'recusada') {
                btnSalvar.style.display = 'inline-flex';
            }
        }
    }
}

function adicionarItemTabela() {
    const sel = document.getElementById('solicProdutoSel');
    const qtdInput = document.getElementById('solicQtdInput');
    if (!sel || !qtdInput) return;
    
    const prodId = parseInt(sel.value);
    const qtd = parseFloat(qtdInput.value);
    
    if (!prodId) {
        Scopi.toast('erro', 'Selecione um produto.');
        return;
    }
    if (isNaN(qtd) || qtd <= 0) {
        Scopi.toast('erro', 'Informe uma quantidade válida.');
        return;
    }
    
    if (_itensSolicitacao.some(item => parseInt(item.produto_id) === prodId)) {
        Scopi.toast('erro', 'Este produto já foi adicionado.');
        return;
    }
    
    const option = sel.options[sel.selectedIndex];
    const nome = option.dataset.nome;
    const codigo = option.dataset.codigo;
    
    _itensSolicitacao.push({
        produto_id: prodId,
        quantidade: qtd,
        nome_produto: nome,
        codigo_produto: codigo
    });
    
    sel.value = '';
    qtdInput.value = '';
    
    renderItensEditar();
}

function removerItemTabela(idx) {
    _itensSolicitacao.splice(idx, 1);
    renderItensEditar();
}

function renderItensEditar() {
    const tbody = document.querySelector('#tabItensEditar tbody');
    if (!tbody) return;
    tbody.innerHTML = '';
    
    if (_itensSolicitacao.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;color:#888;padding:12px;">Nenhum produto adicionado.</td></tr>';
    } else {
        _itensSolicitacao.forEach((item, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="padding: 8px 12px;">${item.nome_produto} (${item.codigo_produto || ''})</td>
                <td style="text-align:right; padding: 8px 12px;">${parseFloat(item.quantidade).toFixed(2)}</td>
                <td style="text-align:center; padding: 8px 12px;">
                    <button type="button" class="btn-icone" onclick="removerItemTabela(${idx})" title="Remover">
                        <img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" style="width:12px;filter:brightness(0) saturate(100%) invert(18%) sepia(85%) saturate(2200%) hue-rotate(330deg);" alt="">
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }
    
    const input = document.getElementById('itensJsonInput');
    if (input) {
        input.value = JSON.stringify(_itensSolicitacao);
    }
}

function renderItensVisualizar() {
    const tbody = document.querySelector('#tabItensVisualizar tbody');
    if (!tbody) return;
    tbody.innerHTML = '';
    
    if (_itensSolicitacao.length === 0) {
        tbody.innerHTML = '<tr><td colspan="2" style="text-align:center;color:#888;padding:12px;">Nenhum produto adicionado.</td></tr>';
    } else {
        _itensSolicitacao.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="padding: 8px 12px;">${item.nome_produto} (${item.codigo_produto || ''})</td>
                <td style="text-align:right; padding: 8px 12px;">${parseFloat(item.quantidade).toFixed(2)}</td>
            `;
            tbody.appendChild(tr);
        });
    }
}
</script>
