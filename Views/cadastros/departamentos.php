<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Departamentos';</script>

<div class="pagina-cabecalho">
  <h1 class="pagina-titulo">Departamentos</h1>
  <p class="pagina-subtitulo">Cadastro e gerenciamento de departamentos</p>
</div>

<!-- Filtros -->
<div class="painel-filtros">
  <div class="filtros-cabecalho">
    <img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt="">
    <span>Filtros</span>
  </div>
  <form method="GET" action="<?= BASE_URL ?>/departamentos">
    <div class="filtros-campos">
      <div class="campo-filtro">
        <label>Código</label>
        <input type="text" name="codigo" value="<?= Auxiliares::escapar($filtros['codigo'] ?? '') ?>">
      </div>
      <div class="campo-filtro">
        <label>Nome</label>
        <input type="text" name="nome" value="<?= Auxiliares::escapar($filtros['nome'] ?? '') ?>">
      </div>
      <div class="campo-filtro">
        <label style="display:flex;gap:8px;align-items:center;justify-content:space-between;">
          <span>Matrícula do Gerente</span>
          <button type="button" class="btn btn-secundario" style="padding:4px 6px;margin:0;" onclick="Scopi.iconeBusca('usuarios','filtroDepGerenteMatricula','filtroDepGerenteNome')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width:13px;margin:0;" alt="Buscar"></button>
        </label>
        <div style="display:flex;gap:8px;align-items:center;max-width:260px;">
          <input type="text" id="filtroDepGerenteMatricula" name="gerente_matricula" value="<?= Auxiliares::escapar($filtros['gerente_matricula']??'') ?>" class="campo-input" style="width:90px;" onblur="buscarGerenteFiltro(this.value)">
          <span id="filtroDepGerenteNome" style="font-size:0.8rem;color:var(--texto-secundario);flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Digite...</span>
        </div>
      </div>
      <div class="campo-filtro">
        <label>Status</label>
        <select name="situacao">
          <option value="">Todos</option>
          <option value="ativo" <?= ($filtros['situacao'] ?? '') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
          <option value="inativo" <?= ($filtros['situacao'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
        </select>
      </div>
      <div class="campo-filtro" style="flex:0;align-self:flex-end;">
        <button type="submit" class="btn btn-filtrar">
          <img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt=""> Filtrar
        </button>
      </div>
    </div>
  </form>
</div>

<!-- Barra de Ações -->
<div class="barra-acoes">
  <div class="grupo-botoes">
    <button class="btn btn-primario" onclick="Scopi.abrirCadastro('modalDepartamento','formDepartamento')">
      <img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Novo Departamento
    </button>
    <button class="btn btn-secundario" onclick="window.open('<?= BASE_URL ?>/departamentos/exportar' + window.location.search, '_blank')">
      <img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Exportar
    </button>
  </div>
  <span style="font-size: 0.82rem; color: #888;"><?= count($departamentos) ?> registro(s)</span>
</div>

<!-- Tabela de Listagem -->
<div class="tabela-container">
  <table class="tabela">
    <thead>
      <tr>
        <th>Código</th>
        <th>Nome</th>
        <th>Gerente</th>
        <th>Status</th>
        <th class="coluna-acoes"></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($departamentos)): ?>
        <tr>
          <td colspan="5" style="text-align: center; padding: 32px; color: #888;">
            Nenhum departamento encontrado.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($departamentos as $d): ?>
          <tr>
            <td>
              <span class="cod-clicavel" onclick="Scopi.abrirRegistro('modalDepartamento','formDepartamento','/departamentos/dados', <?= $d['id'] ?>, 'visualizar')">
                <?= Auxiliares::escapar($d['codigo']) ?>
              </span>
            </td>
            <td><?= Auxiliares::escapar($d['nome']) ?></td>
            <td><?= Auxiliares::escapar($d['nome_gerente'] ?? '—') ?></td>
            <td>
              <span class="badge badge-<?= $d['situacao'] ?>">
                <?= ucfirst($d['situacao']) ?>
              </span>
            </td>
            <td class="coluna-acoes">
              <button class="btn-icone btn-editar-linha" onclick="Scopi.abrirRegistro('modalDepartamento','formDepartamento','/departamentos/dados', <?= $d['id'] ?>, 'editar')" title="Editar">
                <img src="<?= BASE_URL ?>/public/assets/icons/iconeEditar.svg" alt="">
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Departamento -->
<div class="overlay-modal" id="modalDepartamento">
  <div class="modal modal-estreito">
    <div class="modal-cabecalho">
      <div class="modal-titulo">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeCadastro.svg" alt="">
        <span id="tituloModalDepartamento">Cadastro de Departamento</span>
      </div>
      <div style="display: flex; gap: 8px;">
          <button class="btn btn-secundario btn-historico" style="display:none; padding: 4px 8px; font-size: 0.8rem;" onclick="Scopi.abrirHistorico('departamentos', _idDepAtual, 'Departamento')">Histórico</button>
          <button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalDepartamento')">
            <img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt="">
          </button>
      </div>
    </div>
    <div class="modal-abas">
      <button class="aba-btn ativa" data-aba="visualizar" onclick="Scopi.ativarAba('modalDepartamento', 'visualizar')">Visualizar</button>
      <button class="aba-btn" data-aba="editar" onclick="Scopi.ativarAba('modalDepartamento', 'editar')">Editar</button>
    </div>
    
    <div class="modal-corpo">
      <div class="conteudo-aba ativo" data-aba="visualizar">
        <div class="grade-visualizar">
          <div class="campo-visualizar">
            <span class="rotulo">Código</span>
            <span class="valor" data-campo="codigo">—</span>
          </div>
          <div class="campo-visualizar">
            <span class="rotulo">Status</span>
            <span class="valor">
              <span class="badge" data-badge="situacao">—</span>
            </span>
          </div>
          <div class="campo-visualizar campo-completo">
            <span class="rotulo">Nome</span>
            <span class="valor" data-campo="nome">—</span>
          </div>
          <div class="campo-visualizar campo-completo">
            <span class="rotulo">Gerente Vinculado</span>
            <span class="valor" data-campo="nome_gerente">—</span>
          </div>
          <div class="campo-visualizar campo-completo">
            <span class="rotulo">Descrição</span>
            <span class="valor" data-campo="descricao">—</span>
          </div>
        </div>
      </div>
      
      <div class="conteudo-aba" data-aba="editar">
        <form id="formDepartamento" onsubmit="event.preventDefault(); Scopi.enviarFormulario('formDepartamento','modalDepartamento','/departamentos/salvar')">
          <input type="hidden" name="id" value="0">
          <div class="grade-form" style="grid-template-columns: 1fr;">
            
            <div class="grade-form" style="grid-template-columns: 1fr 1fr; margin-bottom: 10px;">
                <div class="campo-form">
                    <label>Código</label>
                    <input type="text" data-campo="codigo" readonly class="campo-input" style="cursor: not-allowed;" placeholder="">
                </div>
                <div class="campo-form">
                    <label>Status</label>
                    <input type="text" data-campo="situacao_texto" readonly class="campo-input" style="cursor: not-allowed;" value="Ativo">
                </div>
            </div>

            <div class="campo-form">
              <label>Nome *</label>
              <input type="text" name="nome" required placeholder="Nome do departamento">
            </div>
            <div class="campo-form">
                <label style="display:flex;gap:8px;align-items:center;justify-content:space-between;">
                    <span>Matrícula do Gerente</span>
                    <button type="button" class="btn btn-secundario" style="padding:4px 6px;margin:0;" title="Buscar gerente" onclick="Scopi.iconeBusca('usuarios','depGerenteMatricula','depGerenteNome')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width:13px;margin:0;" alt="Buscar"></button>
                </label>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <input type="text" id="depGerenteMatricula" class="campo-input" style="width: 120px;" placeholder="Ex: 25000000" onblur="buscarGerente(this.value)">
                    <span id="depGerenteNome" style="font-size: 0.9rem; color: var(--texto-secundario); font-style: italic;">Digite a matrícula do gerente...</span>
                    <input type="hidden" name="gerente_id" id="depGerenteId" value="">
                </div>
            </div>
            <div class="campo-form">
              <label>Descrição</label>
              <textarea name="descricao" rows="3" placeholder="Opcional"></textarea>
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
  var _idDepAtual = 0;
  
  if (!window._hookedDepartamentos) {
      window._hookedDepartamentos = true;
  
      window._abrirOriginalDep = Scopi.abrirRegistro;
      Scopi.abrirRegistro = async function(idModal, idForm, url, id, aba) {
          _idDepAtual = id;
          await window._abrirOriginalDep.call(Scopi, idModal, idForm, url, id, aba);

          const badgeSituacao = document.querySelector(`#${idModal} [data-badge="situacao"]`);
          const btnInativar = document.getElementById('btnInativarDep');
          const btnReativar = document.getElementById('btnReativarDep');
          const titulo = document.getElementById('tituloModalDepartamento');
          const btnHistorico = document.querySelector(`#${idModal} .btn-historico`);
          if (titulo) titulo.textContent = 'Cadastro de Departamento';
          if (btnHistorico) btnHistorico.style.display = 'inline-flex';

          const inputCodigo = document.querySelector(`#${idModal} form [data-campo="codigo"]`);
          const inputSituacao = document.querySelector(`#${idModal} form [data-campo="situacao_texto"]`);
          const elCodigo = document.querySelector(`#${idModal} .grade-visualizar [data-campo="codigo"]`);
          const valCodigo = elCodigo ? elCodigo.textContent : '';
          const valSit = badgeSituacao ? badgeSituacao.textContent : '';
          if (inputCodigo) inputCodigo.value = valCodigo;
          if (inputSituacao) inputSituacao.value = valSit;


          try {
              const resp = await fetch(Scopi.url(`/departamentos/dados?id=${id}`), {credentials:'include',headers:{'X-Requested-With':'XMLHttpRequest'}});
              const json = await resp.json();
              if (json.sucesso && json.dados) {
                  const spanNome = document.getElementById("depGerenteNome");
                  const inputMatricula = document.getElementById("depGerenteMatricula");
                  const inputId = document.getElementById("depGerenteId");
                  if (inputMatricula && json.dados.gerente_matricula) {
                      inputMatricula.value = json.dados.gerente_matricula;
                      spanNome.textContent = json.dados.nome_gerente || "Gerente vinculado";
                      spanNome.style.color = "var(--sucesso)";
                      if(inputId) inputId.value = json.dados.gerente_id;
                  } else {
                      if (inputMatricula) inputMatricula.value = '';
                      if (spanNome) {
                          spanNome.textContent = 'Digite a matrícula do gerente...';
                          spanNome.style.color = 'var(--texto-secundario)';
                      }
                      if (inputId) inputId.value = '';
                  }
              }
          } catch(e) {}
    
    if (!window._listenerDepAba) {
        window._listenerDepAba = true;
        window.addEventListener('scopiAbaChange', function(e) {
            if (e.detail.idModal !== idModal) return;
            const abaAtual = e.detail.aba;
            if (titulo) titulo.textContent = abaAtual === 'editar' ? 'Edição de Cadastro de Departamento' : 'Cadastro de Departamento';
            if (badgeSituacao && btnInativar && btnReativar) {
              const situacao = badgeSituacao.textContent.trim().toLowerCase();
              if (situacao === 'ativo' || situacao === 'ativa') {
                if (abaAtual === 'editar') btnInativar.style.display = '';
                else btnInativar.style.display = 'none';
                btnReativar.style.display = 'none';
              } else {
                btnInativar.style.display = 'none';
                if (abaAtual === 'editar') btnReativar.style.display = '';
                else btnReativar.style.display = 'none';
              }
            }
        });
    }
    window.dispatchEvent(new CustomEvent('scopiAbaChange', { detail: { idModal, aba } }));
  };
  
      window._abrirCadastroOriginalDep = Scopi.abrirCadastro;
      Scopi.abrirCadastro = function(idModal, idForm) {
          _idDepAtual = 0;
          const titulo = document.getElementById('tituloModalDepartamento');
    const btnHistorico = document.querySelector(`#${idModal} .btn-historico`);
    const btnInativar = document.getElementById('btnInativarDep');
    const btnReativar = document.getElementById('btnReativarDep');
    
    if (titulo) titulo.textContent = 'Cadastro de Departamento';
    if (btnInativar) btnInativar.style.display = 'none';
    if (btnReativar) btnReativar.style.display = 'none';
    if (btnHistorico) btnHistorico.style.display = 'none';
    
    document.getElementById('depGerenteMatricula').value = '';
    document.getElementById('depGerenteNome').textContent = 'Digite a matrícula do gerente...';
    document.getElementById('depGerenteNome').style.color = 'var(--texto-secundario)';
    document.getElementById('depGerenteId').value = '';
    
    const inputSit = document.querySelector(`#${idModal} form [data-campo="situacao_texto"]`);
    if (inputSit) inputSit.value = 'Ativo';
    const inputCod = document.querySelector(`#${idModal} form [data-campo="codigo"]`);
    if (inputCod) inputCod.value = '';
    
        window._abrirCadastroOriginalDep.call(Scopi, idModal, idForm);
  };
  }

  async function buscarGerenteFiltro(matricula) {
      matricula = (matricula||'').trim();
      const span = document.getElementById('filtroDepGerenteNome');
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
          const resp = await fetch(Scopi.url(`/usuarios/consultar-matricula?matricula=${encodeURIComponent(matricula)}`), {credentials:'include'});
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

  document.addEventListener('DOMContentLoaded', () => {
      const matGerente = document.getElementById('filtroDepGerenteMatricula')?.value?.trim();
      if (matGerente) buscarGerenteFiltro(matGerente);
  });

  function inativarDepartamento() {
      if (!_idDepAtual) return;
      Scopi.confirmarAcao('Inativar este departamento?', '/departamentos/inativar', { id: _idDepAtual });
  }

  function reativarDepartamento() {
      if (!_idDepAtual) return;
      Scopi.confirmarAcao('Reativar este departamento?', '/departamentos/reativar', { id: _idDepAtual });
  }
</script>
