<?php
use Config\Auxiliares;
?>
<script>document.getElementById('topbarTitulo').textContent = 'Usuários';</script>
<div class="pagina-cabecalho">
  <h1 class="pagina-titulo">Usuários</h1>
  <p class="pagina-subtitulo">Gerenciamento de usuários e perfis de acesso</p>
</div>
<div class="painel-filtros">
  <div class="filtros-cabecalho">
    <img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt="">
    <span>Filtros</span>
  </div>
  <form method="GET" action="<?= BASE_URL ?>/usuarios">
    <?php $isGerente = ($usuario['perfil'] ?? '') === 'gerente'; ?>
    <div class="filtros-campos">
      <div class="campo-filtro">
        <label>Matrícula</label>
        <input type="text" name="matricula" id="filtroMatricula" value="<?= Auxiliares::escapar($filtros['matricula'] ?? '') ?>">
      </div>
      <div class="campo-filtro">
        <label>Nome do Usuário</label>
        <input type="text" name="nome" id="filtroNome" value="<?= Auxiliares::escapar($filtros['nome'] ?? '') ?>">
      </div>
      <div class="campo-filtro" style="min-width:250px;">
        <label style="display:flex;gap:8px;align-items:center;justify-content:space-between;">
          <span>Cód. Departamento</span>
          <?php if (!$isGerente): ?><button type="button" class="btn btn-secundario" style="padding:4px 6px;margin:0;" onclick="Scopi.iconeBusca('departamentos','filtroDepCodigo','filtroDepNome')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width:13px;margin:0;" alt="Buscar"></button><?php endif; ?>
        </label>
        <div style="display:flex;gap:8px;align-items:center;">
          <input type="text" name="departamento_codigo" id="filtroDepCodigo" value="<?= Auxiliares::escapar($filtros['departamento_codigo'] ?? '') ?>" style="width:80px;text-transform:uppercase;" onblur="buscarDepartamentoFiltro(this.value)" <?= $isGerente ? 'readonly style="cursor:not-allowed;"' : '' ?>>
          <span id="filtroDepNome" style="font-size:0.8rem;color:var(--texto-secundario);font-style:italic;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Digite o código...</span>
        </div>
      </div>
      <div class="campo-filtro"><label>Perfil</label><select name="perfil"><option value="">Todos</option><option value="administrador" <?= ($filtros['perfil'] ?? '') === 'administrador' ? 'selected' : '' ?>>Administrador</option><option value="cadastrador" <?= ($filtros['perfil'] ?? '') === 'cadastrador' ? 'selected' : '' ?>>Cadastrador</option><option value="comprador" <?= ($filtros['perfil'] ?? '') === 'comprador' ? 'selected' : '' ?>>Comprador</option><option value="gerente" <?= ($filtros['perfil'] ?? '') === 'gerente' ? 'selected' : '' ?>>Gerente</option><option value="contabilidade" <?= ($filtros['perfil'] ?? '') === 'contabilidade' ? 'selected' : '' ?>>Contabilidade</option><option value="usuario" <?= ($filtros['perfil'] ?? '') === 'usuario' ? 'selected' : '' ?>>Usuário</option></select></div>
      <div class="campo-filtro"><label>Status</label><select name="situacao"><option value="">Todos</option><option value="ativo" <?= ($filtros['situacao'] ?? '') === 'ativo' ? 'selected' : '' ?>>Ativo</option><option value="inativo" <?= ($filtros['situacao'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option></select></div>
      <div class="campo-filtro" style="flex:0;align-self:flex-end;"><button type="submit" class="btn btn-filtrar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt=""> Filtrar</button></div>
    </div>
  </form>
</div>
<div class="barra-acoes">
  <div class="grupo-botoes">
    <?php if (($usuario['perfil'] ?? '') === 'administrador'): ?>
    <button class="btn btn-primario" onclick="Scopi.abrirCadastro('modalUsuario','formUsuario')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Novo Usuário</button>
    <?php endif; ?>
    <button class="btn btn-secundario" onclick="exportarTabela()"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Exportar</button>
  </div>
  <span style="font-size:.82rem;color:#888;"><?= count($usuarios ?? []) ?> registro(s) encontrado(s)</span>
</div>

<!-- ── Tabela ── -->
<div class="tabela-container">
  <table class="tabela" id="tabelaUsuarios">
    <thead>
      <tr>
        <th>Matrícula</th>
        <th>Nome</th>
        <th>E-mail Corporativo</th>
        <th>Departamento</th>
        <th>Perfil</th>
        <th>Status</th>
        <th class="coluna-acoes"></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($usuarios)): ?>
        <tr><td colspan="7" style="text-align:center;padding:32px;color:#888;">Nenhum usuário encontrado.</td></tr>
      <?php else: ?>
        <?php foreach ($usuarios as $u): ?>
          <tr>
            <td>
              <span class="cod-clicavel" onclick="Scopi.abrirRegistro('modalUsuario','formUsuario','/usuarios/dados',<?= $u['id'] ?>,'visualizar')" title="Clique para visualizar">
                <?= Auxiliares::escapar($u['matricula']) ?>
              </span>
            </td>
            <td><?= Auxiliares::escapar($u['nome']) ?></td>
            <td><?= Auxiliares::escapar($u['email']) ?></td>
            <td><?= Auxiliares::escapar($u['nome_departamento'] ?? '—') ?></td>
            <td><?= ucfirst(Auxiliares::escapar($u['perfil'])) ?></td>
            <td><span class="badge badge-<?= $u['situacao'] ?>"><?= ucfirst($u['situacao']) ?></span></td>
            <td class="coluna-acoes">
              <?php if (($usuario['perfil'] ?? '') === 'administrador'): ?>
              <button class="btn-icone btn-editar-linha" onclick="Scopi.abrirRegistro('modalUsuario','formUsuario','/usuarios/dados',<?= $u['id'] ?>,'editar')" title="Editar">
                <img src="<?= BASE_URL ?>/public/assets/icons/iconeEditar.svg" alt="Editar">
              </button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- ══════════════════════════════════════════════════
     MODAL — Usuário (Visualizar / Editar / Cadastrar)
══════════════════════════════════════════════════ -->
<div class="overlay-modal" id="modalUsuario">
  <div class="modal">
    <div class="modal-cabecalho">
      <div class="modal-titulo">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeUser.svg" alt="">
        <span>Cadastro de Usuário</span>
      </div>
      <div style="display:flex;gap:8px;">
        <button class="btn btn-secundario btn-historico-usu" id="btnHistoricoUsu" style="display:none;padding:4px 8px;font-size:0.8rem;" onclick="Scopi.abrirHistorico('usuarios',_idAtual,'Usuário')">Histórico</button>
        <button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalUsuario')">
          <img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt="Fechar">
        </button>
      </div>
    </div>
    <div class="modal-abas">
      <button class="aba-btn ativa" data-aba="visualizar" onclick="Scopi.ativarAba('modalUsuario','visualizar')">Visualizar</button>
      <?php if (($usuario['perfil'] ?? '') === 'administrador'): ?>
      <button class="aba-btn" data-aba="editar" onclick="Scopi.ativarAba('modalUsuario','editar')">Editar / Cadastrar</button>
      <?php endif; ?>
    </div>
    <div class="modal-corpo">

      <!-- Aba: Visualizar -->
      <div class="conteudo-aba ativo" data-aba="visualizar">
        <div class="grade-visualizar">
          <div class="campo-visualizar"><span class="rotulo">Matrícula</span><span class="valor" data-campo="matricula">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Status</span><span class="valor"><span class="badge" data-badge="situacao">—</span></span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Nome Completo</span><span class="valor" data-campo="nome">—</span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">E-mail Corporativo</span><span class="valor" data-campo="email">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Departamento</span><span class="valor" data-campo="nome_departamento">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Perfil de Acesso</span><span class="valor" data-campo="perfil">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Contato</span><span class="valor" data-campo="contato">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Cadastrado em</span><span class="valor" data-campo="criado_em">—</span></div>
        </div>
      </div>

      <!-- Aba: Editar / Cadastrar -->
      <div class="conteudo-aba" data-aba="editar">
        <form id="formUsuario" onsubmit="event.preventDefault();Scopi.enviarFormulario('formUsuario','modalUsuario','/usuarios/salvar')">
          <input type="hidden" name="id" value="0">
          <div class="grade-form">
            <div class="campo-form">
              <label>Matrícula *</label>
              <input type="text" name="matricula" required>
            </div>
            <div class="campo-form">
              <label>Status</label>
              <input type="text" data-campo="situacao_texto" value="Ativo" readonly class="campo-input" style="cursor:not-allowed;">
            </div>
            <div class="campo-form campo-completo">
              <label>Nome Completo *</label>
              <input type="text" name="nome" required>
            </div>
            <div class="campo-form campo-completo">
              <label>E-mail Corporativo *</label>
              <input type="email" name="email" required>
            </div>
            <div class="campo-form">
              <label>Número de Contato</label>
              <input type="text" name="contato">
            </div>
            <div class="campo-form">
              <label style="display:flex;gap:8px;align-items:center;justify-content:space-between;">
                <span>Departamento *</span>
                <button type="button" class="btn btn-secundario" style="padding:4px 6px;margin:0;" title="Buscar departamento" onclick="Scopi.iconeBusca('departamentos','usuDepCodigo','usuDepNome')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width:13px;margin:0;" alt="Buscar"></button>
              </label>
              <div style="display:flex;gap:8px;align-items:center;">
                <input type="text" id="usuDepCodigo" class="campo-input" style="width:100px;text-transform:uppercase;" onblur="buscarDepartamentoForm(this.value)" required>
                <span id="usuDepNome" style="font-size:0.9rem;color:var(--texto-secundario);font-style:italic;">Digite o código...</span>
                <input type="hidden" name="departamento_id" id="usuDepId" value="">
              </div>
            </div>
            <div class="campo-form">
              <label>Perfil de Acesso *</label>
              <select name="perfil" required>
                <option value="usuario">Usuário</option>
                <option value="cadastrador">Cadastrador</option>
                <option value="comprador">Comprador</option>
                <option value="gerente">Gerente</option>
                <option value="contabilidade">Contabilidade</option>
                <option value="administrador">Administrador</option>
              </select>
            </div>
            <div class="campo-form campo-completo" id="blocoSenha">
              <label>Senha Padrão <small style="font-weight:400;text-transform:none;" id="lblSenhaInfo">(Pode ser alterada ao cadastrar)</small></label>
              <div style="display:flex;gap:8px;align-items:center;">
                <input type="text" name="senha" id="campoSenhaUsuario" value="SCOPi2026*" autocomplete="new-password" style="flex:1;">
                <button type="button" class="btn btn-secundario" id="btnRedefinirSenha" style="display:none;white-space:nowrap;" onclick="redefinirSenhaPadrao()">Redefinir Senha</button>
              </div>
            </div>
          </div>
        </form>
      </div>

    </div><!-- /modal-corpo -->

    <!-- Rodapé -->
    <div class="modal-rodape">
      <button class="btn btn-secundario" id="btnInativar" style="margin-right:auto;display:none;" onclick="inativarUsuario()">Inativar</button>
      <button class="btn btn-secundario" id="btnReativar" style="margin-right:auto;display:none;" onclick="reativarUsuario()">Reativar</button>
      <button class="btn btn-secundario" onclick="Scopi.fecharModal('modalUsuario')">Fechar</button>
      <button class="btn btn-primario btn-salvar" onclick="Scopi.enviarFormulario('formUsuario','modalUsuario','/usuarios/salvar')">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Salvar
      </button>
    </div>

  </div>
</div>
<!-- /MODAL -->

<script>
const USER_PERFIL = "<?= $usuario['perfil'] ?>";
var _idAtual = 0;

/* ── Ações do rodapé ── */
function inativarUsuario() {
    if (!_idAtual) return;
    Scopi.confirmarAcao('Deseja inativar este usuário?', '/usuarios/inativar', { id: _idAtual });
}
function reativarUsuario() {
    if (!_idAtual) return;
    Scopi.confirmarAcao('Deseja reativar este usuário?', '/usuarios/reativar', { id: _idAtual });
}
function redefinirSenhaPadrao() {
    Scopi.confirmar('Deseja realmente redefinir a senha deste usuário para a senha informada no campo?', async () => {
        const fd = new FormData();
        fd.append('id', _idAtual);
        fd.append('senhaPadrao', document.getElementById('campoSenhaUsuario').value);
        fetch(Scopi.url('/usuarios/redefinirSenha'), { method: 'POST', credentials: 'include', body: fd, headers: {'X-Requested-With': 'XMLHttpRequest'} })
        .then(r => r.json())
        .then(res => Scopi.toast(res.sucesso ? 'sucesso' : 'erro', res.mensagem))
        .catch(() => Scopi.toast('erro', 'Erro na requisição.'));
    });
}

/* ── Ajusta visibilidade dos botões conforme o modo ── */
function _ajustarRodapeUsuario(aba, situacao) {
    const btnInativar  = document.getElementById('btnInativar');
    const btnReativar  = document.getElementById('btnReativar');
    const btnRedefinir = document.getElementById('btnRedefinirSenha');
    const blocoSenha   = document.getElementById('blocoSenha');
    const btnHistorico = document.getElementById('btnHistoricoUsu');

    // Histórico sempre visível se for um registro existente
    if (btnHistorico) btnHistorico.style.display = (_idAtual > 0) ? 'inline-flex' : 'none';

    if (aba === 'editar') {
        const isAdmin = (USER_PERFIL === 'administrador');
        if (btnRedefinir) btnRedefinir.style.display = (_idAtual > 0 && isAdmin) ? 'inline-flex' : 'none';
        if (blocoSenha)   blocoSenha.style.display   = isAdmin ? 'block' : 'none';
        const sit = (situacao || '').trim().toLowerCase();
        if (btnInativar) btnInativar.style.display = (sit === 'ativo' && isAdmin)   ? '' : 'none';
        if (btnReativar) btnReativar.style.display = (sit === 'inativo' && isAdmin) ? '' : 'none';
    } else {
        if (btnInativar)  btnInativar.style.display  = 'none';
        if (btnReativar)  btnReativar.style.display  = 'none';
        if (btnRedefinir) btnRedefinir.style.display = 'none';
        if (blocoSenha)   blocoSenha.style.display   = 'none';
    }
}

if (!window._hookedUsuarios) {
    window._hookedUsuarios = true;

    const _origAbrirRegUsu = Scopi.abrirRegistro;
    Scopi.abrirRegistro = async function(idModal, idForm, url, id, aba) {
        if (idModal === 'modalUsuario') _idAtual = id;
        await _origAbrirRegUsu.call(Scopi, idModal, idForm, url, id, aba);
        if (idModal !== 'modalUsuario') return;

        const btnSalvar = document.querySelector('#modalUsuario .btn-salvar');
        if (btnSalvar) btnSalvar.style.display = (aba === 'editar' && USER_PERFIL === 'administrador') ? 'inline-flex' : 'none';

        if (aba === 'editar') {
            try {
                const resp = await fetch(Scopi.url(`/usuarios/dados?id=${id}`), { credentials: 'include', headers: {'X-Requested-With': 'XMLHttpRequest'} });
                const json = await resp.json();
                if (json.sucesso && json.dados) {
                    const depCodigo = document.getElementById('usuDepCodigo');
                    const depNome   = document.getElementById('usuDepNome');
                    if (depCodigo && json.dados.departamento_codigo) {
                        depCodigo.value     = json.dados.departamento_codigo;
                        depNome.textContent = json.dados.nome_departamento || '';
                        depNome.style.color = 'var(--sucesso)';
                    }
                    // Em edição, o campo é informativo (não submete no salvar)
                    // Para redefinir, usar o botão "Redefinir Senha"
                    const inputSenha = document.getElementById('campoSenhaUsuario');
                    if (inputSenha) { inputSenha.removeAttribute('name'); inputSenha.value = 'SCOPi2026*'; }
                }
            } catch(e) {}
        }

        const badgeSit = document.querySelector('#modalUsuario .grade-visualizar [data-badge="situacao"]');
        _ajustarRodapeUsuario(aba, badgeSit ? badgeSit.textContent : '');
    };

    const _origAbrirCadUsu = Scopi.abrirCadastro;
    Scopi.abrirCadastro = function(idModal, idForm) {
        _origAbrirCadUsu.call(Scopi, idModal, idForm);
        if (idModal !== 'modalUsuario') return;
        _idAtual = 0;
        setTimeout(() => {
            const depCodigo = document.getElementById('usuDepCodigo');
            const depNome   = document.getElementById('usuDepNome');
            const depId     = document.getElementById('usuDepId');
            if (depCodigo) depCodigo.value = '';
            if (depNome)   { depNome.textContent = 'Digite o código...'; depNome.style.color = 'var(--texto-secundario)'; }
            if (depId)     depId.value = '';
            const inputSenha = document.getElementById('campoSenhaUsuario');
            if (inputSenha) { inputSenha.setAttribute('name', 'senha'); inputSenha.value = 'SCOPi2026*'; }
            _ajustarRodapeUsuario('editar', '');
        }, 50);
    };
}

/* ── Busca de departamento no formulário ── */
async function buscarDepartamentoForm(codigo) {
    codigo = codigo.trim();
    const spanNome = document.getElementById('usuDepNome');
    const inputId  = document.getElementById('usuDepId');
    if (!codigo) {
        if (spanNome) { spanNome.textContent = 'Digite o código...'; spanNome.style.color = 'var(--texto-secundario)'; }
        if (inputId) inputId.value = '';
        return;
    }
    if (spanNome) { spanNome.textContent = 'Buscando...'; spanNome.style.color = 'var(--texto-secundario)'; }
    try {
        const resp = await fetch(Scopi.url(`/departamentos/consultar-codigo?codigo=${encodeURIComponent(codigo)}`), {credentials:'include'});
        const data = await resp.json();
        if (data.sucesso && data.dados) {
            if (spanNome) { spanNome.textContent = data.dados.nome; spanNome.style.color = 'var(--sucesso)'; }
            if (inputId) inputId.value = data.dados.id;
        } else {
            if (spanNome) { spanNome.textContent = 'Não encontrado'; spanNome.style.color = 'var(--alerta)'; }
            if (inputId) inputId.value = '';
        }
    } catch(e) {
        if (spanNome) { spanNome.textContent = 'Erro ao buscar'; spanNome.style.color = 'var(--alerta)'; }
    }
}

/* ── Busca de departamento no filtro ── */
async function buscarDepartamentoFiltro(codigo) {
    codigo = codigo.trim();
    const spanNome = document.getElementById('filtroDepNome');
    if (!spanNome) return;
    if (!codigo) { spanNome.textContent = 'Digite o código...'; spanNome.style.color = 'var(--texto-secundario)'; return; }
    spanNome.textContent = 'Buscando...'; spanNome.style.color = 'var(--texto-secundario)';
    try {
        const resp = await fetch(Scopi.url(`/departamentos/consultar-codigo?codigo=${encodeURIComponent(codigo)}`), {credentials:'include'});
        const data = await resp.json();
        if (data.sucesso && data.dados) {
            spanNome.textContent = data.dados.nome; spanNome.style.color = 'var(--sucesso)';
        } else {
            spanNome.textContent = 'Não encontrado'; spanNome.style.color = 'var(--alerta)';
        }
    } catch(e) { spanNome.textContent = 'Erro ao buscar'; spanNome.style.color = 'var(--alerta)'; }
}

/* ── Exporta tabela para CSV ── */
function exportarTabela() {
    const rows = [...document.querySelectorAll('#tabelaUsuarios tr')];
    const csv  = rows.map(r => [...r.querySelectorAll('th,td')].slice(1,-1).map(c => `"${c.innerText.trim()}"`).join(';')).join('\n');
    const blob = new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a'); a.href = URL.createObjectURL(blob);
    a.download = 'usuarios.csv'; a.click();
}

document.addEventListener('DOMContentLoaded', () => {
    const codFiltro = document.getElementById('filtroDepCodigo')?.value;
    if (codFiltro) buscarDepartamentoFiltro(codFiltro);
});
</script>
