<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Solicitações';</script>
<div class="pagina-cabecalho"><h1 class="pagina-titulo">Solicitações</h1><p class="pagina-subtitulo">Registro e acompanhamento de solicitações de produto</p></div>
<div class="painel-filtros">
  <div class="filtros-cabecalho"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt=""><span>Filtros</span></div>
  <form method="GET" action="<?= BASE_URL ?>/solicitacoes"><div class="filtros-campos">
    <div class="campo-filtro"><label>Número</label><input type="text" name="numero" value="<?= Auxiliares::escapar($filtros['numero']??'') ?>"></div>
    <div class="campo-filtro">
      <label style="display:flex;gap:8px;align-items:center;justify-content:space-between;">
        <span>Cód. Departamento <?php if(!in_array($usuario['perfil'],['administrador','comprador'])): ?><span style="font-size:0.75rem;color:#888;">(fixo)</span><?php endif; ?></span>
        <?php if(in_array($usuario['perfil'],['administrador','comprador'])): ?>
        <button type="button" class="btn btn-secundario" style="padding:4px 6px;margin:0;" onclick="Scopi.iconeBusca('departamentos','filtroSolDepCodigo','filtroSolDepNome')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width:13px;margin:0;" alt="Buscar"></button>
        <?php endif; ?>
      </label>
      <div style="display:flex;gap:8px;align-items:center;max-width:260px;">
        <input type="text" id="filtroSolDepCodigo" name="departamento_codigo" value="<?= Auxiliares::escapar($filtros['departamento_codigo']??'') ?>" class="campo-input" style="width:90px;text-transform:uppercase;" onblur="buscarDepFiltroSol(this.value)" <?= !in_array($usuario['perfil'],['administrador','comprador']) ? 'readonly' : '' ?>>
        <span id="filtroSolDepNome" style="font-size:0.8rem;color:var(--texto-secundario);flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= empty($filtros['departamento_codigo']) ? 'Digite...' : 'Buscando...' ?></span>
      </div>
    </div>
    <div class="campo-filtro">
      <label style="display:flex;gap:8px;align-items:center;justify-content:space-between;">
        <span>Matrícula Solicitante</span>
        <button type="button" class="btn btn-secundario" style="padding:4px 6px;margin:0;" onclick="Scopi.iconeBusca('usuarios','filtroSolMatricula','filtroSolNomeSolic')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width:13px;margin:0;" alt="Buscar"></button>
      </label>
      <div style="display:flex;gap:8px;align-items:center;max-width:260px;">
        <input type="text" id="filtroSolMatricula" name="matricula_solicitante" value="<?= Auxiliares::escapar($filtros['matricula_solicitante']??'') ?>" class="campo-input" style="width:90px;" onblur="buscarMatriculaFiltroSol(this.value)">
        <span id="filtroSolNomeSolic" style="font-size:0.8rem;color:var(--texto-secundario);flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Digite...</span>
      </div>
    </div>
    <div class="campo-filtro"><label>Data Registro (De)</label><input type="date" name="data_inicial" value="<?= Auxiliares::escapar($filtros['data_inicial']??'') ?>"></div>
    <div class="campo-filtro"><label>Data Registro (Até)</label><input type="date" name="data_final" value="<?= Auxiliares::escapar($filtros['data_final']??'') ?>"></div>
    <div class="campo-filtro"><label>Status</label>
      <select name="status">
        <option value="">Todos</option>
        <option value="aberto"  <?= ($filtros['status']??'')==='aberto'?'selected':'' ?>>Aberto</option>
        <option value="autorizada" <?= ($filtros['status']??'')==='autorizado'?'selected':'' ?>>Autorizada</option>
        <option value="em_cotacao" <?= ($filtros['status']??'')==='em_cotacao'?'selected':'' ?>>Em Cotação</option>
        <option value="concluida"  <?= ($filtros['status']??'')==='concluido'?'selected':'' ?>>Concluída</option>
        <option value="cancelada"  <?= ($filtros['status']??'')==='cancelado'?'selected':'' ?>>Cancelada</option>
      </select>
    </div>
    <div class="campo-filtro" style="flex:0;align-self:flex-end;"><button type="submit" class="btn btn-filtrar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt=""> Filtrar</button></div>
  </div></form>
</div>
<div class="barra-acoes">
  <div class="grupo-botoes">
    <button class="btn btn-primario" onclick="Scopi.abrirCadastro('modalSolicitacao','formSolicitacao')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Nova Solicitação</button>
    <button class="btn btn-secundario" onclick="window.open('<?= BASE_URL ?>/solicitacoes/exportar' + window.location.search, '_blank')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Exportar</button>
  </div>
  <span style="font-size:.82rem;color:#888;"><?= count($solicitacoes) ?> registro(s)</span>
</div>
<div class="tabela-container">
  <table class="tabela">
    <thead><tr><th>Número</th><th>Departamento</th><th>Solicitante</th><th>Data</th><th>Status</th><th class="coluna-acoes"></th></tr></thead>
    <tbody>
      <?php if(empty($solicitacoes)): ?><tr><td colspan="6" style="text-align:center;padding:32px;color:#888;">Nenhuma solicitação encontrada.</td></tr>
      <?php else: foreach($solicitacoes as $s): ?>
      <tr>
        <td><span class="cod-clicavel" onclick="Scopi.abrirRegistro('modalSolicitacao','formSolicitacao','/solicitacoes/dados',<?= $s['id'] ?>,'visualizar')"><?= Auxiliares::escapar($s['numero']) ?></span></td>
        <td><?= Auxiliares::escapar($s['nome_departamento']??'—') ?></td>
        <td><?= Auxiliares::escapar($s['nome_solicitante']??'—') ?></td>
        <td><?= date('d/m/Y', strtotime($s['criado_em'])) ?></td>
        <td><span class="badge badge-<?= str_replace('_','-',$s['status']) ?>"><?= Auxiliares::formatarStatus($s['status']) ?></span></td>
        <td class="coluna-acoes">
          <button class="btn-icone btn-editar-linha" onclick="Scopi.abrirRegistro('modalSolicitacao','formSolicitacao','/solicitacoes/dados',<?= $s['id'] ?>,'editar')" title="Editar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeEditar.svg" alt=""></button>
        </td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
<div class="overlay-modal" id="modalSolicitacao">
  <div class="modal modal-largo">
    <div class="modal-cabecalho">
      <div class="modal-titulo"><img src="<?= BASE_URL ?>/public/assets/icons/iconeSolicitacao.svg" alt=""><span>Solicitação</span></div>
      <div style="display:flex;gap:8px;">
        <button class="btn btn-secundario" id="btnHistoricoSol" style="display:none;padding:4px 8px;font-size:0.8rem;" onclick="Scopi.abrirHistorico('solicitacoes',_idSol,'Solicitação')">Histórico</button>
        <button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalSolicitacao')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt=""></button>
      </div>
    </div>
    <div class="modal-abas"><button class="aba-btn ativa" data-aba="visualizar" onclick="Scopi.ativarAba('modalSolicitacao','visualizar')">Visualizar</button><button class="aba-btn" data-aba="editar" onclick="Scopi.ativarAba('modalSolicitacao','editar')">Nova / Editar</button></div>
    <div class="modal-corpo">
      <div class="conteudo-aba ativo" data-aba="visualizar">
        <div class="grade-visualizar">
          <div class="campo-visualizar"><span class="rotulo">Número</span><span class="valor" data-campo="numero">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Status</span><span class="valor"><span class="badge" data-badge="status">—</span></span></div>
          <div class="campo-visualizar"><span class="rotulo">Departamento</span><span class="valor" data-campo="nome_departamento">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Solicitante</span><span class="valor" data-campo="nome_solicitante">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Autorizador</span><span class="valor" data-campo="nome_autorizador">—</span></div>
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
                <!-- populated dynamically -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
      
      <div class="conteudo-aba" data-aba="editar">
        <form id="formSolicitacao" onsubmit="event.preventDefault();Scopi.enviarFormulario('formSolicitacao','modalSolicitacao','/solicitacoes/salvar')">
          <input type="hidden" name="id" value="0">

          <!-- CAMPOS READ-ONLY -->
          <div class="grade-form" style="grid-template-columns: 1fr 1fr; margin-bottom: 15px;">
            <div class="campo-form">
              <label>Número</label>
              <input type="text" readonly class="campo-input" style="cursor: not-allowed;" value="—" data-campo="numero">
            </div>
            <div class="campo-form">
              <label>Status</label>
              <input type="text" readonly class="campo-input" style="cursor: not-allowed;" value="Aberto" data-campo="situacao_texto">
            </div>
          </div>

          <div class="grade-form" style="grid-template-columns: 1fr 1fr; margin-bottom: 15px;">
            <div class="campo-form">
              <label>Departamento Solicitante</label>
              <input type="text" readonly class="campo-input" style="cursor: not-allowed;" value="<?= Auxiliares::escapar($usuario['departamento_nome'] ?? '') ?>" data-campo="nome_departamento">
            </div>
            <div class="campo-form">
              <label>Usuário Solicitante</label>
              <input type="text" readonly class="campo-input" style="cursor: not-allowed;" value="—" data-campo="nome_solicitante">
            </div>
          </div>

          <div class="grade-form" style="grid-template-columns: 1fr 1fr; margin-bottom: 15px;">
            <div class="campo-form">
              <label>Data de Abertura</label>
              <input type="text" readonly class="campo-input" style="cursor: not-allowed;" value="—" data-campo="data_abertura_fmt">
            </div>
            <div class="campo-form">
              <label>Usuário Autorizador</label>
              <input type="text" readonly class="campo-input" style="cursor: not-allowed;" value="—" data-campo="nome_autorizador">
            </div>
          </div>

          <div class="grade-form" style="grid-template-columns:1fr;">
            <div class="campo-form"><label>Justificativa *</label><textarea name="justificativa" required rows="2" ></textarea></div>
            
            <div id="blocoItensEdicao" class="campo-form" style="margin-top: 10px; border-top: 1px solid var(--borda); padding-top: 15px; display: none;">
              <label style="margin-bottom: 8px;">Adicionar Produtos</label>
              <div style="display: flex; gap: 8px; margin-bottom: 12px; align-items: center;">
                <div style="display: flex; flex: 1; gap: 8px; align-items: center;">
                    <input type="text" id="solicProdutoCodigo" class="campo-input" style="width: 120px; padding: 8px 10px; border: 1px solid var(--borda); border-radius: 6px; font-size: 0.78rem;" onblur="buscarProdutoPorCodigo(this.value)">
                    <button type="button" class="btn btn-secundario" style="padding:6px 8px;flex-shrink:0;" title="Buscar produto" onclick="Scopi.iconeBusca('produtos','solicProdutoCodigo','solicProdutoNome')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width:14px;margin:0;" alt="Buscar"></button>
                    <span id="solicProdutoNome" style="font-size: 0.85rem; color: var(--texto-secundario); font-style: italic;">Digite o código...</span>
                    <input type="hidden" id="solicProdutoId" value="">
                    <input type="hidden" id="solicProdutoNomeHidden" value="">
                </div>
                <input type="number" id="solicQtdInput" class="campo-input" style="width: 100px; padding: 8px 10px; border: 1px solid var(--borda); border-radius: 6px; font-size: 0.78rem;" min="0.01" step="any" >
                <button type="button" class="btn btn-primario" onclick="adicionarItemTabela()" style="height: 34px;">Adicionar</button>
              </div>
              
              <span class="rotulo" style="margin-bottom: 8px;">Lista de Itens</span>
              <div class="tabela-container" style="border: 1px solid var(--borda); max-height: 200px; overflow-y: auto;">
                <table class="tabela" id="tabItensEditar">
                  <thead>
                    <tr>
                      <th style="width: 50px; text-align: center; padding: 8px 12px;">Nº Item</th>
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
      <button class="btn btn-perigo" id="btnCancelarSol" style="margin-right:auto; display:none;" onclick="Scopi.confirmarAcao('Cancelar esta solicitação?','/solicitacoes/cancelar',{id:_idSol})">Cancelar Solicitação</button>
      <button class="btn btn-perigo" id="btnRetirarAutSol" style="margin-right:auto; display:none;" onclick="Scopi.confirmarAcao('Retirar autorização desta solicitação?','/solicitacoes/desautorizar',{id:_idSol})">Retirar Autorização</button>
      <button class="btn btn-secundario" onclick="Scopi.fecharModal('modalSolicitacao')">Fechar</button>
      <button class="btn btn-primario btn-salvar-capa" onclick="salvarCapaSolicitacao()" style="display:none;"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Confirmar Capa</button>
      <button class="btn btn-primario btn-salvar" onclick="Scopi.enviarFormulario('formSolicitacao','modalSolicitacao','/solicitacoes/salvar')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Salvar Itens</button>
    </div>
  </div>
</div>
<script>
const USER_PERFIL = "<?= $usuario['perfil'] ?>";
const USER_NOME = "<?= Auxiliares::escapar($usuario['nome']) ?>";
const USER_ID = <?= (int)$usuario['id'] ?>;
const USER_DEPTO_ID = <?= (int)($usuario['departamento_id'] ?? 0) ?>;
const USER_DEPTO_NOME = "<?= Auxiliares::escapar($usuario['departamento_nome'] ?? '') ?>";

async function buscarDepFiltroSol(codigo) {
    codigo = (codigo||'').trim();
    const span = document.getElementById('filtroSolDepNome');
    if (!span) return;
    if (!codigo) { span.textContent = 'Digite...'; span.style.color = 'var(--texto-secundario)'; return; }
    span.textContent = 'Buscando...'; span.style.color = 'var(--texto-secundario)';
    try {
        const resp = await fetch(Scopi.url(`/departamentos/consultar-codigo?codigo=${encodeURIComponent(codigo)}`), {credentials:'include'});
        const data = await resp.json();
        if (data.sucesso && data.dados) { span.textContent = data.dados.nome; span.style.color = 'var(--sucesso)'; }
        else { span.textContent = 'Não encontrado'; span.style.color = 'var(--alerta)'; }
    } catch(e) { span.textContent = 'Erro'; span.style.color = 'var(--alerta)'; }
}

async function buscarMatriculaFiltroSol(matricula) {
    matricula = (matricula||'').trim();
    const span = document.getElementById('filtroSolNomeSolic');
    if (!span) return;
    if (!matricula) { span.textContent = 'Digite...'; span.style.color = 'var(--texto-secundario)'; return; }
    span.textContent = 'Buscando...'; span.style.color = 'var(--texto-secundario)';
    try {
        const resp = await fetch(Scopi.url(`/usuarios/consultar-matricula?matricula=${encodeURIComponent(matricula)}`), {credentials:'include'});
        const data = await resp.json();
        if (data.sucesso && data.dados) { span.textContent = data.dados.nome; span.style.color = 'var(--sucesso)'; }
        else { span.textContent = 'Não encontrado'; span.style.color = 'var(--alerta)'; }
    } catch(e) { span.textContent = 'Erro'; span.style.color = 'var(--alerta)'; }
}

document.addEventListener('DOMContentLoaded', () => {
    const codDep = document.getElementById('filtroSolDepCodigo')?.value?.trim();
    if (codDep) buscarDepFiltroSol(codDep);
    const mat = document.getElementById('filtroSolMatricula')?.value?.trim();
    if (mat) buscarMatriculaFiltroSol(mat);
});
let _idSol = 0;
let _statusSol = 'aberto';
let _itensSolicitacao = [];

const _origAbrirCadastro = Scopi.abrirCadastro;
Scopi.abrirCadastro = function(idModal, idForm) {
    _origAbrirCadastro(idModal, idForm);
    if (idModal === 'modalSolicitacao') {
        _idSol = 0;
        _statusSol = 'aberto';
        _itensSolicitacao = [];

        // Preencher campos read-only para novo cadastro com dados do usuário logado
        document.querySelectorAll('#formSolicitacao [data-campo]').forEach(el => {
            if (el.tagName === 'INPUT') {
                const campo = el.getAttribute('data-campo');
                if (campo === 'numero') el.value = '';
                else if (campo === 'status_texto') el.value = 'Aberto';
                else if (campo === 'nome_departamento') {
                    el.value = USER_DEPTO_NOME;
                }
                else if (campo === 'nome_solicitante') el.value = USER_NOME || '';
                else if (campo === 'data_abertura_fmt') el.value = new Date().toLocaleDateString('pt-BR');
                else if (campo === 'nome_autorizador') el.value = '';
            }
        });

        const tabEditarBtn = document.querySelector('#modalSolicitacao .aba-btn[data-aba="editar"]');
        if (tabEditarBtn) tabEditarBtn.style.display = 'inline-block';

        const blocoItens = document.getElementById('blocoItensEdicao');
        if (blocoItens) blocoItens.style.display = 'none';

        // Ativar a aba "editar" por padrão
        Scopi.ativarAba('modalSolicitacao', 'editar');

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
            const resp = await fetch(`${Scopi.url(urlDados)}?id=${id}`, {credentials:'include',headers:{'X-Requested-With':'XMLHttpRequest'}});
            const json = await resp.json();
            if (json.sucesso) {
                _itensSolicitacao = json.dados.itens || [];
                _statusSol = json.dados.status || 'aberto';

                // Preencher campos read-only na aba de edição
                const dados = json.dados;
                document.querySelectorAll('#formSolicitacao [data-campo]').forEach(el => {
                    if (el.tagName === 'INPUT') {
                        const campo = el.getAttribute('data-campo');
                        if (campo === 'numero') el.value = dados.numero || '—';
                        else if (campo === 'status_texto') el.value = dados.status ? Scopi.formatarStatus(dados.status) : '—';
                        else if (campo === 'nome_departamento') el.value = dados.nome_departamento || '—';
                        else if (campo === 'nome_solicitante') el.value = dados.nome_solicitante || '—';
                        else if (campo === 'data_abertura_fmt') el.value = dados.criado_em ? new Date(dados.criado_em).toLocaleDateString('pt-BR') : '—';
                        else if (campo === 'nome_autorizador') el.value = dados.nome_autorizador || '—';
                    }
                });
                
                const tabEditarBtn = document.querySelector('#modalSolicitacao .aba-btn[data-aba="editar"]');
                if (tabEditarBtn) {
                    if (_statusSol !== 'aberto' && _statusSol !== 'recusada') {
                        tabEditarBtn.style.display = 'none';
                    } else {
                        tabEditarBtn.style.display = 'inline-block';
                    }
                }
                
                const blocoItens = document.getElementById('blocoItensEdicao');
                if (blocoItens) blocoItens.style.display = 'block';
                
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
    const btnRetirarAut = document.getElementById('btnRetirarAutSol');
    const btnSalvar = document.querySelector('#modalSolicitacao .btn-salvar');
    const btnSalvarCapa = document.querySelector('#modalSolicitacao .btn-salvar-capa');

    const btnHistorico = document.getElementById('btnHistoricoSol');
    if (btnCancelar) btnCancelar.style.display = 'none';
    if (btnRetirarAut) btnRetirarAut.style.display = 'none';
    if (btnSalvar) btnSalvar.style.display = 'none';
    if (btnSalvarCapa) btnSalvarCapa.style.display = 'none';
    if (btnHistorico) btnHistorico.style.display = (_idSol > 0) ? 'inline-flex' : 'none';

    if (aba === 'visualizar') {
        // Apenas botões padrão (Fechar e Histórico)
    } else if (aba === 'editar') {
        if (_idSol === 0) {
            if (btnSalvarCapa) btnSalvarCapa.style.display = 'inline-flex';
        } else {
            if (btnSalvar && (_statusSol === 'aberto')) {
                btnSalvar.style.display = 'inline-flex';
            }
            if (btnCancelar && (_statusSol === 'aberto' || _statusSol === 'autorizado')) {
                btnCancelar.style.display = 'inline-flex';
            }
            if (btnRetirarAut && (_statusSol === 'autorizado')) {
                btnRetirarAut.style.display = 'inline-flex';
            }
        }
    }
}

async function salvarCapaSolicitacao() {
    const form = document.getElementById('formSolicitacao');
    if (!form.reportValidity()) return;
    
    document.getElementById('itensJsonInput').value = '[]';
    
    const btn = document.querySelector('#modalSolicitacao .btn-salvar-capa');
    if (btn) { btn.disabled = true; btn.textContent = 'Salvando...'; }
    
    try {
        const resp = await fetch(Scopi.url('/solicitacoes/salvar'), {credentials:'include',
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await resp.json();
        
        if (json.sucesso && json.dados && json.dados.id) {
            Scopi.toast('sucesso', 'Capa salva! Agora insira os itens.');
            _idSol = json.dados.id;
            form.querySelector('[name="id"]').value = _idSol;
            
            const blocoItens = document.getElementById('blocoItensEdicao');
            if (blocoItens) blocoItens.style.display = 'block';
            
            atualizarBotoesRodape('editar');
        } else {
            Scopi.toast('erro', json.mensagem || 'Erro ao salvar capa.');
        }
    } catch (e) {
        Scopi.toast('erro', 'Falha na comunicação.');
    } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = '<img src="'+SCOPI_BASE+'/public/assets/icons/iconeInserir.svg" alt=""> Confirmar Capa'; }
    }
}

async function buscarProdutoPorCodigo(codigo) {
    codigo = codigo.trim();
    const spanNome = document.getElementById('solicProdutoNome');
    const inputId = document.getElementById('solicProdutoId');
    const inputNomeHidden = document.getElementById('solicProdutoNomeHidden');
    
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
        const resp = await fetch(`${SCOPI_BASE}/produtos/consultar-codigo?codigo=${encodeURIComponent(codigo)}`, {credentials:'include'});
        const data = await resp.json();
        if (data.sucesso && data.dados) {
            spanNome.textContent = `${data.dados.nome} (${data.dados.categoria_nome || 'Sem categoria'})`;
            spanNome.style.color = 'var(--sucesso)';
            inputId.value = data.dados.id;
            inputNomeHidden.value = data.dados.nome;
        } else {
            spanNome.textContent = 'Produto não encontrado';
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

function adicionarItemTabela() {
    const inputId = document.getElementById('solicProdutoId');
    const inputCodigo = document.getElementById('solicProdutoCodigo');
    const inputNomeHidden = document.getElementById('solicProdutoNomeHidden');
    const qtdInput = document.getElementById('solicQtdInput');
    if (!inputId || !qtdInput) return;
    
    const prodId = parseInt(inputId.value);
    const qtd = parseFloat(qtdInput.value);
    
    if (!prodId) {
        Scopi.toast('erro', 'Busque e selecione um produto válido.');
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
    
    const nome = inputNomeHidden.value;
    const codigo = inputCodigo.value.trim();
    
    _itensSolicitacao.push({
        produto_id: prodId,
        quantidade: qtd,
        nome_produto: nome,
        codigo_produto: codigo
    });
    
    inputCodigo.value = '';
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
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#888;padding:12px;">Nenhum produto adicionado.</td></tr>';
    } else {
        _itensSolicitacao.forEach((item, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="text-align: center; padding: 8px 12px;">${item.numero_item || '-'}</td>
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
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;color:#888;padding:12px;">Nenhum produto adicionado.</td></tr>';
    } else {
        _itensSolicitacao.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="text-align: center; padding: 8px 12px;">${item.numero_item || '-'}</td>
                <td style="padding: 8px 12px;">${item.nome_produto} (${item.codigo_produto || ''})</td>
                <td style="text-align:right; padding: 8px 12px;">${parseFloat(item.quantidade).toFixed(2)}</td>
            `;
            tbody.appendChild(tr);
        });
    }
}
</script>
