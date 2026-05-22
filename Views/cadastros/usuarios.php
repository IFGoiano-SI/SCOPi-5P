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
    <div class="filtros-campos">
      <div class="campo-filtro"><label>Nome</label><input type="text" name="nome" value="<?= Auxiliares::escapar($filtros['nome'] ?? '') ?>" placeholder="Buscar por nome..."></div>
      <div class="campo-filtro"><label>E-mail</label><input type="text" name="email" value="<?= Auxiliares::escapar($filtros['email'] ?? '') ?>" placeholder="Buscar por e-mail..."></div>
      <div class="campo-filtro"><label>Perfil</label><select name="perfil"><option value="">Todos</option><option value="administrador" <?= ($filtros['perfil'] ?? '') === 'administrador' ? 'selected' : '' ?>>Administrador</option><option value="cadastrador"   <?= ($filtros['perfil'] ?? '') === 'cadastrador'   ? 'selected' : '' ?>>Cadastrador</option><option value="comprador"     <?= ($filtros['perfil'] ?? '') === 'comprador'     ? 'selected' : '' ?>>Comprador</option><option value="gerente"       <?= ($filtros['perfil'] ?? '') === 'gerente'       ? 'selected' : '' ?>>Gerente</option><option value="contabilidade" <?= ($filtros['perfil'] ?? '') === 'contabilidade' ? 'selected' : '' ?>>Contabilidade</option><option value="usuario"       <?= ($filtros['perfil'] ?? '') === 'usuario'       ? 'selected' : '' ?>>Usuário</option></select></div>
      <div class="campo-filtro"><label>Situação</label><select name="situacao"><option value="">Todas</option><option value="ativo"   <?= ($filtros['situacao'] ?? '') === 'ativo'   ? 'selected' : '' ?>>Ativo</option><option value="inativo" <?= ($filtros['situacao'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option></select></div>
      <div class="campo-filtro" style="flex:0; align-self:flex-end;"><button type="submit" class="btn btn-filtrar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt=""> Buscar</button></div>
    </div>
  </form>
</div>
<div class="barra-acoes">
  <div class="grupo-botoes">
    <button class="btn btn-primario" onclick="Scopi.abrirCadastro('modalUsuario', 'formUsuario')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Novo Usuário</button>
    <button class="btn btn-secundario" onclick="exportarTabela()"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Exportar</button>
  </div>
  <span style="font-size:.82rem; color:#888;"><?= count($usuarios ?? []) ?> registro(s) encontrado(s)</span>
</div>

<script>document.getElementById('topbarTitulo').textContent = 'Usuários';</script>


<!-- ── Tabela ── -->
<div class="tabela-container">
  <table class="tabela" id="tabelaUsuarios">
    <thead>
      <tr>
        <th><label class="checkbox-custom">
          <input type="checkbox" onchange="Scopi.toggleCheckboxes(this)">
          <img src="<?= BASE_URL ?>/public/assets/icons/iconeCheckboxVazia.svg" class="check-icone" alt="">
        </label></th>
        <th>Matrícula</th>
        <th>Nome</th>
        <th>E-mail</th>
        <th>Departamento</th>
        <th>Perfil</th>
        <th>Situação</th>
        <th class="coluna-acoes"></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($usuarios)): ?>
        <tr><td colspan="8" style="text-align:center; padding:32px; color:#888;">Nenhum usuário encontrado.</td></tr>
      <?php else: ?>
        <?php foreach ($usuarios as $u): ?>
          <tr>
            <td><label class="checkbox-custom">
              <input type="checkbox" class="checkbox-linha" value="<?= $u['id'] ?>">
              <img src="<?= BASE_URL ?>/public/assets/icons/iconeCheckboxVazia.svg" class="check-icone" alt="">
            </label></td>
            <td>
              <span class="cod-clicavel"
                    onclick="Scopi.abrirRegistro('modalUsuario','formUsuario','/usuarios/dados',<?= $u['id'] ?>,'visualizar')"
                    title="Clique para visualizar">
                <?= Auxiliares::escapar($u['matricula']) ?>
              </span>
            </td>
            <td><?= Auxiliares::escapar($u['nome']) ?></td>
            <td><?= Auxiliares::escapar($u['email']) ?></td>
            <td><?= Auxiliares::escapar($u['nome_departamento'] ?? '—') ?></td>
            <td><?= ucfirst(Auxiliares::escapar($u['perfil'])) ?></td>
            <td>
              <span class="badge badge-<?= $u['situacao'] ?>">
                <?= ucfirst($u['situacao']) ?>
              </span>
            </td>
            <td class="coluna-acoes">
              <button class="btn-icone btn-editar-linha"
                      onclick="Scopi.abrirRegistro('modalUsuario','formUsuario','/usuarios/dados',<?= $u['id'] ?>,'editar')"
                      title="Editar">
                <img src="<?= BASE_URL ?>/public/assets/icons/iconeEditar.svg" alt="Editar">
              </button>
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

    <!-- Cabeçalho -->
    <div class="modal-cabecalho">
      <div class="modal-titulo">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeUser.svg" alt="">
        <span>Usuário</span>
      </div>
      <button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalUsuario')">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt="Fechar">
      </button>
    </div>

    <!-- Abas -->
    <div class="modal-abas">
      <button class="aba-btn ativa" data-aba="visualizar" onclick="Scopi.ativarAba('modalUsuario','visualizar')">Visualizar</button>
      <button class="aba-btn"       data-aba="editar"     onclick="Scopi.ativarAba('modalUsuario','editar')">Editar / Cadastrar</button>
    </div>

    <!-- Corpo -->
    <div class="modal-corpo">

      <!-- Aba: Visualizar (somente leitura) -->
      <div class="conteudo-aba ativo" data-aba="visualizar">
        <div class="grade-visualizar">
          <div class="campo-visualizar">
            <span class="rotulo">Matrícula</span>
            <span class="valor" data-campo="matricula">—</span>
          </div>
          <div class="campo-visualizar">
            <span class="rotulo">Situação</span>
            <span class="valor"><span class="badge" data-badge="situacao">—</span></span>
          </div>
          <div class="campo-visualizar campo-completo">
            <span class="rotulo">Nome Completo</span>
            <span class="valor" data-campo="nome">—</span>
          </div>
          <div class="campo-visualizar campo-completo">
            <span class="rotulo">E-mail</span>
            <span class="valor" data-campo="email">—</span>
          </div>
          <div class="campo-visualizar">
            <span class="rotulo">Departamento</span>
            <span class="valor" data-campo="nome_departamento">—</span>
          </div>
          <div class="campo-visualizar">
            <span class="rotulo">Perfil de Acesso</span>
            <span class="valor" data-campo="perfil">—</span>
          </div>
          <div class="campo-visualizar">
            <span class="rotulo">Contato</span>
            <span class="valor" data-campo="contato">—</span>
          </div>
          <div class="campo-visualizar">
            <span class="rotulo">Cadastrado em</span>
            <span class="valor" data-campo="criado_em">—</span>
          </div>
        </div>
      </div>

      <!-- Aba: Editar / Cadastrar -->
      <div class="conteudo-aba" data-aba="editar">
        <form id="formUsuario" onsubmit="event.preventDefault(); Scopi.enviarFormulario('formUsuario','modalUsuario','/usuarios/salvar')">
          <input type="hidden" name="id" value="0">

          <div class="grade-form">
            <div class="campo-form campo-completo">
              <label>Nome Completo *</label>
              <input type="text" name="nome" required placeholder="Nome do funcionário">
            </div>
            <div class="campo-form campo-completo">
              <label>E-mail *</label>
              <input type="email" name="email" required placeholder="email@empresa.com">
            </div>
            <div class="campo-form">
              <label>Matrícula</label>
              <input type="text" name="matricula" placeholder="Ex.: 00123">
            </div>
            <div class="campo-form">
              <label>Contato</label>
              <input type="text" name="contato" placeholder="(00) 00000-0000">
            </div>
            <div class="campo-form">
              <label>Departamento *</label>
              <select name="departamento_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($departamentos as $dep): ?>
                  <option value="<?= $dep['id'] ?>"><?= Auxiliares::escapar($dep['nome']) ?></option>
                <?php endforeach; ?>
              </select>
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
            <div class="campo-form campo-completo">
              <label>Senha <small style="font-weight:400;text-transform:none;">(deixe em branco para manter a atual na edição)</small></label>
              <input type="password" name="senha" placeholder="Mínimo 8 caracteres" autocomplete="new-password">
            </div>
          </div>
        </form>
      </div>

    </div><!-- /modal-corpo -->

    <!-- Rodapé -->
    <div class="modal-rodape">
      <!-- Botões que aparecem na aba visualizar -->
      <button class="btn btn-secundario" id="btnInativar" style="margin-right:auto;"
              onclick="inativarUsuario()">Inativar</button>
      <button class="btn btn-secundario" onclick="Scopi.fecharModal('modalUsuario')">Fechar</button>
      <button class="btn btn-primario btn-salvar" onclick="Scopi.enviarFormulario('formUsuario','modalUsuario','/usuarios/salvar')">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Salvar
      </button>
    </div>

  </div>
</div>
<!-- /MODAL -->

<script>
// Exporta a tabela para CSV simples
function exportarTabela() {
  const rows = [...document.querySelectorAll('#tabelaUsuarios tr')];
  const csv = rows.map(r =>
    [...r.querySelectorAll('th,td')].slice(1,-1).map(c => `"${c.innerText.trim()}"`).join(';')
  ).join('\n');
  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
  const a = document.createElement('a'); a.href = URL.createObjectURL(blob);
  a.download = 'usuarios.csv'; a.click();
}

// Botão inativar no modal
let _idAtual = 0;
const _abrirOriginal = Scopi.abrirRegistro.bind(Scopi);
Scopi.abrirRegistro = async function(idModal, idForm, url, id, aba) {
  _idAtual = id;
  await _abrirOriginal(idModal, idForm, url, id, aba);
};

function inativarUsuario() {
  if (!_idAtual) return;
  Scopi.confirmarAcao('Deseja inativar este usuário?', '/usuarios/inativar', { id: _idAtual });
}
</script>
