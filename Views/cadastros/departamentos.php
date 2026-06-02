<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Departamentos';</script>

<div class="pagina-cabecalho">
  <h1 class="pagina-titulo">Departamentos</h1>
  <p class="pagina-subtitulo">Cadastro e gerenciamento de departamentos</p>
</div>

<!-- Filtros -->
<div class="painel-filtros">
  <div class="filtros-cabecalho">
    <img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt=""><span>Filtros</span>
  </div>
  <form method="GET" action="<?= BASE_URL ?>/departamentos">
    <div class="filtros-campos">
      <div class="campo-filtro"><label>Código</label><input type="text" name="codigo" value="<?= Auxiliares::escapar($filtros['codigo']??'') ?>" placeholder="DEP-..."></div>
      <div class="campo-filtro"><label>Nome</label><input type="text" name="nome" value="<?= Auxiliares::escapar($filtros['nome']??'') ?>" placeholder="Nome do departamento..."></div>
      <div class="campo-filtro">
          <label>Matrícula do Gerente</label>
          <div style="display: flex; gap: 8px; align-items: center; max-width: 250px;">
              <input type="text" id="filtroGerMatricula" name="gerente_matricula" value="<?= Auxiliares::escapar($filtros['gerente_matricula'] ?? '') ?>" class="campo-input" style="width: 100px; text-transform: uppercase;" placeholder="Ex: 260000" onblur="buscarGerenteFiltro(this.value)">
              <span id="filtroGerNome" style="font-size: 0.8rem; color: var(--texto-secundario); flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= empty($filtros['gerente_matricula']) ? 'Digite...' : 'Buscando...' ?></span>
          </div>
      </div>
      <div class="campo-filtro"><label>Status</label>
        <select name="situacao">
          <option value="">Todas</option>
          <option value="ativo"   <?= ($filtros['situacao']??'')==='ativo'?'selected':'' ?>>Ativo</option>
          <option value="inativo" <?= ($filtros['situacao']??'')==='inativo'?'selected':'' ?>>Inativo</option>
        </select>
      </div>
      <div class="campo-filtro" style="flex:0;align-self:flex-end;">
        <button type="submit" class="btn btn-filtrar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt=""> Buscar</button>
      </div>
    </div>
  </form>
</div>

<!-- Ações -->
<div class="barra-acoes">
  <div class="grupo-botoes">
    <button class="btn btn-primario" onclick="Scopi.abrirCadastro('modalDepartamento','formDepartamento')">
      <img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Novo Departamento
    </button>
    <button class="btn btn-secundario" onclick="window.open('<?= BASE_URL ?>/departamentos/exportar' + window.location.search, '_blank')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Exportar</button>
  </div>
  <span style="font-size:.82rem;color:#888;"><?= count($departamentos) ?> registro(s)</span>
</div>

<!-- Tabela -->
<div class="tabela-container">
  <table class="tabela">
    <thead><tr>
      <th>Código</th><th>Nome</th><th>Gerente Responsável</th><th>Status</th><th class="coluna-acoes"></th>
    </tr></thead>
    <tbody>
      <?php if(empty($departamentos)): ?>
        <tr><td colspan="5" style="text-align:center;padding:32px;color:#888;">Nenhum departamento encontrado.</td></tr>
      <?php else: ?>
        <?php foreach($departamentos as $d): ?>
        <tr>
          <td><span class="cod-clicavel" onclick="Scopi.abrirRegistro('modalDepartamento','formDepartamento','/departamentos/dados',<?= $d['id'] ?>,'visualizar')"><?= Auxiliares::escapar($d['codigo']) ?></span></td>
          <td><?= Auxiliares::escapar($d['nome']) ?></td>
          <td><?= Auxiliares::escapar($d['nome_gerente']??'—') ?></td>
          <td><span class="badge badge-<?= $d['situacao'] ?>"><?= ucfirst($d['situacao']) ?></span></td>
          <td class="coluna-acoes"><button class="btn-icone btn-editar-linha" onclick="Scopi.abrirRegistro('modalDepartamento','formDepartamento','/departamentos/dados',<?= $d['id'] ?>,'editar')" title="Editar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeEditar.svg" alt=""></button></td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- MODAL Departamento -->
<div class="overlay-modal" id="modalDepartamento">
  <div class="modal modal-estreito">
    <div class="modal-cabecalho">
      <div class="modal-titulo"><img src="<?= BASE_URL ?>/public/assets/icons/iconeCadastro.svg" alt=""><span id="tituloModalDepartamento">Cadastro de Departamento</span></div>
      <div style="display: flex; gap: 8px;">
          <button class="btn btn-secundario btn-historico" style="display:none; padding: 4px 8px; font-size: 0.8rem;" onclick="abrirHistorico('departamentos', _idDepAtual)">Histórico</button>
          <button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalDepartamento')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt=""></button>
      </div>
    </div>
    <div class="modal-abas">
      <button class="aba-btn ativa" data-aba="visualizar" onclick="Scopi.ativarAba('modalDepartamento','visualizar')">Visualizar</button>
      <button class="aba-btn" data-aba="editar" onclick="Scopi.ativarAba('modalDepartamento','editar')">Editar</button>
    </div>
    <div class="modal-corpo">
      <div class="conteudo-aba ativo" data-aba="visualizar">
        <div class="grade-visualizar">
          <div class="campo-visualizar"><span class="rotulo">Código</span><span class="valor" data-campo="codigo">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Status</span><span class="valor"><span class="badge" data-badge="situacao">—</span></span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Nome</span><span class="valor" data-campo="nome">—</span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Gerente</span><span class="valor" data-campo="nome_gerente">—</span></div>
        </div>
      </div>
      <div class="conteudo-aba" data-aba="editar">
        <form id="formDepartamento" onsubmit="event.preventDefault();Scopi.enviarFormulario('formDepartamento','modalDepartamento','/departamentos/salvar')">
          <input type="hidden" name="id" value="0">
          <div class="grade-form" style="grid-template-columns:1fr;">
            <div class="campo-form campo-leitura" id="blocoLeituraDep" style="display:none; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 10px;">
                <div><label>Código</label><input type="text" data-campo="codigo" readonly class="campo-input" style="background-color: #f5f5f5;"></div>
                <div><label>Status</label><input type="text" data-campo="situacao_texto" readonly class="campo-input" style="background-color: #f5f5f5;"></div>
            </div>
            <div class="campo-form"><label>Nome *</label><input type="text" name="nome" required placeholder="Nome do departamento"></div>
            <div class="campo-form"><label>Gerente Responsável (Matrícula)</label>
              <div style="display: flex; gap: 8px; align-items: center;">
                  <input type="text" id="depGerenteMatricula" class="campo-input" style="width: 150px; text-transform: uppercase;" placeholder="Ex: 26000000" onblur="buscarGerente(this.value)">
                  <span id="depGerenteNome" style="font-size: 0.9rem; color: var(--texto-secundario); font-style: italic;">Digite a matrícula do gerente...</span>
                  <input type="hidden" name="gerente_id" id="depGerenteId" value="">
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
    <div class="modal-rodape">
      <button class="btn btn-secundario" id="btnInativarDep" style="margin-right:auto; display:none;" onclick="inativarDepartamento()">Inativar</button>
      <button class="btn btn-secundario" id="btnReativarDep" style="margin-right:auto; display:none;" onclick="reativarDepartamento()">Reativar</button>
      <button class="btn btn-secundario" onclick="Scopi.fecharModal('modalDepartamento')">Fechar</button>
      <button class="btn btn-primario btn-salvar" onclick="Scopi.enviarFormulario('formDepartamento','modalDepartamento','/departamentos/salvar')">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Salvar
      </button>
    </div>
  </div>
</div>

<script>
let _idDepAtual = 0;

const _abrirOriginalDep = Scopi.abrirRegistro.bind(Scopi);
Scopi.abrirRegistro = async function(idModal, idForm, url, id, aba) {
  _idDepAtual = id;
  await _abrirOriginalDep(idModal, idForm, url, id, aba);
  
  const badgeSituacao = document.querySelector(`#${idModal} [data-badge="situacao"]`);
  const btnInativar = document.getElementById('btnInativarDep');
  const btnReativar = document.getElementById('btnReativarDep');
  const titulo = document.getElementById('tituloModalDepartamento');
  const btnHistorico = document.querySelector(`#${idModal} .btn-historico`);
  const blocoLeitura = document.getElementById('blocoLeituraDep');
  
  if (titulo) titulo.textContent = aba === 'editar' ? 'Edição de Cadastro de Departamento' : 'Cadastro de Departamento';
  if (btnHistorico) btnHistorico.style.display = 'inline-flex';
  if (blocoLeitura) blocoLeitura.style.display = 'grid';
  
  if (aba === 'editar') {
      const inputCodigo = document.querySelector(`#${idModal} form [data-campo="codigo"]`);
      const inputSituacao = document.querySelector(`#${idModal} form [data-campo="situacao_texto"]`);
      const valCodigo = document.querySelector(`#${idModal} .grade-visualizar [data-campo="codigo"]`).textContent;
      const valSit = badgeSituacao ? badgeSituacao.textContent : '';
      if (inputCodigo) inputCodigo.value = valCodigo;
      if (inputSituacao) inputSituacao.value = valSit;
  }
  
  if (badgeSituacao && btnInativar && btnReativar) {
    const situacao = badgeSituacao.textContent.trim().toLowerCase();
    if (situacao === 'ativo' || situacao === 'ativa') {
      if (aba === 'editar') btnInativar.style.display = '';
      else btnInativar.style.display = 'none';
      btnReativar.style.display = 'none';
    } else {
      btnInativar.style.display = 'none';
      if (aba === 'editar') btnReativar.style.display = '';
      else btnReativar.style.display = 'none';
    }
  }
};

const _abrirCadastroOriginalDep = Scopi.abrirCadastro.bind(Scopi);
Scopi.abrirCadastro = function(idModal, idForm) {
  _idDepAtual = 0;
  const titulo = document.getElementById('tituloModalDepartamento');
  const btnHistorico = document.querySelector(`#${idModal} .btn-historico`);
  const blocoLeitura = document.getElementById('blocoLeituraDep');
  const btnInativar = document.getElementById('btnInativarDep');
  const btnReativar = document.getElementById('btnReativarDep');
  
  if (titulo) titulo.textContent = 'Cadastro de Departamento';
  if (btnInativar) btnInativar.style.display = 'none';
  if (btnReativar) btnReativar.style.display = 'none';
  if (btnHistorico) btnHistorico.style.display = 'none';
  if (blocoLeitura) blocoLeitura.style.display = 'none';
  
  document.getElementById('depGerenteMatricula').value = '';
  document.getElementById('depGerenteNome').textContent = 'Digite a matrícula do gerente...';
  document.getElementById('depGerenteNome').style.color = 'var(--texto-secundario)';
  document.getElementById('depGerenteId').value = '';
  
  _abrirCadastroOriginalDep(idModal, idForm);
};

async function buscarGerente(matricula) {
    matricula = matricula.trim();
    const spanNome = document.getElementById('depGerenteNome');
    const inputId = document.getElementById('depGerenteId');
    
    if (!matricula) {
        spanNome.textContent = 'Digite a matrícula do gerente...';
        spanNome.style.color = 'var(--texto-secundario)';
        inputId.value = '';
        return;
    }
    
    spanNome.textContent = 'Buscando...';
    spanNome.style.color = 'var(--texto-secundario)';
    
    try {
        const resp = await fetch(`${SCOPI_BASE}/usuarios/consultar-matricula?matricula=${encodeURIComponent(matricula)}`);
        const data = await resp.json();
        if (data.sucesso && data.dados) {
            spanNome.textContent = data.dados.nome;
            spanNome.style.color = 'var(--sucesso)';
            inputId.value = data.dados.id;
        } else {
            spanNome.textContent = 'Gerente não encontrado ou inativo';
            spanNome.style.color = 'var(--alerta)';
            inputId.value = '';
        }
    } catch (err) {
        spanNome.textContent = 'Erro ao buscar';
        spanNome.style.color = 'var(--alerta)';
        inputId.value = '';
    }
}

function inativarDepartamento() {
    if (!_idDepAtual) return;
    Scopi.confirmarAcao('Inativar este departamento?','/departamentos/inativar',{id:_idDepAtual});
}
function reativarDepartamento() {
    if (!_idDepAtual) return;
    Scopi.confirmarAcao('Reativar este departamento?','/departamentos/reativar',{id:_idDepAtual});
}

async function buscarGerenteFiltro(matricula) {
    matricula = matricula.trim();
    const spanNome = document.getElementById('filtroGerNome');
    
    if (!matricula) {
        spanNome.textContent = 'Digite...';
        spanNome.style.color = 'var(--texto-secundario)';
        return;
    }
    
    spanNome.textContent = 'Buscando...';
    spanNome.style.color = 'var(--texto-secundario)';
    
    try {
        const resp = await fetch(`${SCOPI_BASE}/usuarios/consultar-matricula?matricula=${encodeURIComponent(matricula)}`);
        const data = await resp.json();
        if (data.sucesso && data.dados) {
            spanNome.textContent = data.dados.nome;
            spanNome.style.color = 'var(--sucesso)';
        } else {
            spanNome.textContent = 'Não encontrado';
            spanNome.style.color = 'var(--alerta)';
        }
    } catch (err) {
        spanNome.textContent = 'Erro';
        spanNome.style.color = 'var(--alerta)';
    }
}
</script>
