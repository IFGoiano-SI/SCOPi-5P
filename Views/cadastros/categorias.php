<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Categorias';</script>

<div class="pagina-cabecalho">
  <h1 class="pagina-titulo">Categorias</h1>
  <p class="pagina-subtitulo">Cadastro e gerenciamento de categorias de produtos</p>
</div>

<!-- Filtros -->
<div class="painel-filtros">
  <div class="filtros-cabecalho">
    <img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt=""><span>Filtros</span>
  </div>
  <form method="GET" action="<?= BASE_URL ?>/categorias">
    <div class="filtros-campos">
      <div class="campo-filtro"><label>Nome</label><input type="text" name="nome" value="<?= Auxiliares::escapar($filtros['nome']??'') ?>" placeholder="Nome da categoria..."></div>
      <div class="campo-filtro"><label>Situação</label>
        <select name="situacao">
          <option value="">Todas</option>
          <option value="ativo"   <?= ($filtros['situacao']??'')==='ativo'?'selected':'' ?>>Ativa</option>
          <option value="inativo" <?= ($filtros['situacao']??'')==='inativo'?'selected':'' ?>>Inativa</option>
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
    <button class="btn btn-primario" onclick="Scopi.abrirCadastro('modalCategoria','formCategoria')">
      <img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Nova Categoria
    </button>
    <button class="btn btn-secundario" onclick="exportarTabela()"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Exportar</button>
  </div>
  <span style="font-size:.82rem;color:#888;"><?= count($categorias) ?> registro(s)</span>
</div>

<!-- Tabela -->
<div class="tabela-container">
  <table class="tabela" id="tabelaCategorias">
    <thead><tr>
      <th><label class="checkbox-custom"><input type="checkbox" onchange="Scopi.toggleCheckboxes(this)"><img src="<?= BASE_URL ?>/public/assets/icons/iconeCheckboxVazia.svg" class="check-icone" alt=""></label></th>
      <th>ID</th><th>Nome</th><th>Situação</th><th class="coluna-acoes"></th>
    </tr></thead>
    <tbody>
      <?php if(empty($categorias)): ?>
        <tr><td colspan="5" style="text-align:center;padding:32px;color:#888;">Nenhuma categoria encontrada.</td></tr>
      <?php else: ?>
        <?php foreach($categorias as $c): ?>
        <tr>
          <td><label class="checkbox-custom"><input type="checkbox" class="checkbox-linha" value="<?= $c['id'] ?>"><img src="<?= BASE_URL ?>/public/assets/icons/iconeCheckboxVazia.svg" class="check-icone" alt=""></label></td>
          <td><span class="cod-clicavel" onclick="Scopi.abrirRegistro('modalCategoria','formCategoria','/categorias/dados',<?= $c['id'] ?>,'visualizar')">#<?= $c['id'] ?></span></td>
          <td><?= Auxiliares::escapar($c['nome']) ?></td>
          <td><span class="badge badge-<?= $c['situacao'] ?>"><?= ucfirst($c['situacao'] === 'ativo' ? 'ativa' : 'inativa') ?></span></td>
          <td class="coluna-acoes">
            <button class="btn-icone btn-editar-linha" onclick="Scopi.abrirRegistro('modalCategoria','formCategoria','/categorias/dados',<?= $c['id'] ?>,'editar')" title="Editar">
              <img src="<?= BASE_URL ?>/public/assets/icons/iconeEditar.svg" alt="">
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- MODAL Categoria -->
<div class="overlay-modal" id="modalCategoria">
  <div class="modal modal-estreito">
    <div class="modal-cabecalho">
      <div class="modal-titulo"><img src="<?= BASE_URL ?>/public/assets/icons/iconeCadastro.svg" alt=""><span>Categoria</span></div>
      <button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalCategoria')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt=""></button>
    </div>
    <div class="modal-abas">
      <button class="aba-btn ativa" data-aba="visualizar" onclick="Scopi.ativarAba('modalCategoria','visualizar')">Visualizar</button>
      <button class="aba-btn" data-aba="editar" onclick="Scopi.ativarAba('modalCategoria','editar')">Editar</button>
    </div>
    <div class="modal-corpo">
      <div class="conteudo-aba ativo" data-aba="visualizar">
        <div class="grade-visualizar">
          <div class="campo-visualizar"><span class="rotulo">ID</span><span class="valor" data-campo="id">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Situação</span><span class="valor"><span class="badge" data-badge="situacao">—</span></span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Nome</span><span class="valor" data-campo="nome">—</span></div>
        </div>
      </div>
      <div class="conteudo-aba" data-aba="editar">
        <form id="formCategoria" onsubmit="event.preventDefault();Scopi.enviarFormulario('formCategoria','modalCategoria','/categorias/salvar')">
          <input type="hidden" name="id" value="0">
          <div class="grade-form" style="grid-template-columns:1fr;">
            <div class="campo-form"><label>Nome *</label><input type="text" name="nome" required placeholder="Nome da categoria"></div>
          </div>
        </form>
      </div>
    </div>
    <div class="modal-rodape">
      <button class="btn btn-secundario" id="btnInativar" style="margin-right:auto;" onclick="inativarCategoria()">Inativar</button>
      <button class="btn btn-secundario" id="btnReativar" style="margin-right:auto; display:none;" onclick="reativarCategoria()">Reativar</button>
      <button class="btn btn-secundario" onclick="Scopi.fecharModal('modalCategoria')">Fechar</button>
      <button class="btn btn-primario btn-salvar" onclick="Scopi.enviarFormulario('formCategoria','modalCategoria','/categorias/salvar')">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Salvar
      </button>
    </div>
  </div>
</div>

<script>
function exportarTabela() {
  const rows = [...document.querySelectorAll('#tabelaCategorias tr')];
  const csv = rows.map(r =>
    [...r.querySelectorAll('th,td')].slice(1,-1).map(c => `"${c.innerText.trim()}"`).join(';')
  ).join('\n');
  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
  const a = document.createElement('a'); a.href = URL.createObjectURL(blob);
  a.download = 'categorias.csv'; a.click();
}

let _idAtual = 0;

const _abrirOriginal = Scopi.abrirRegistro.bind(Scopi);
Scopi.abrirRegistro = async function(idModal, idForm, url, id, aba) {
  _idAtual = id;
  await _abrirOriginal(idModal, idForm, url, id, aba);
  
  // Após abrir, ajustar botões inativar/reativar baseado na situação
  const badgeSituacao = document.querySelector(`#${idModal} [data-badge="situacao"]`);
  const btnInativar = document.getElementById('btnInativar');
  const btnReativar = document.getElementById('btnReativar');
  
  if (badgeSituacao && btnInativar && btnReativar) {
    const situacao = badgeSituacao.textContent.trim().toLowerCase();
    if (situacao === 'ativo' || situacao === 'ativa') {
      btnInativar.style.display = '';
      btnReativar.style.display = 'none';
    } else {
      btnInativar.style.display = 'none';
      btnReativar.style.display = '';
    }
  }
};

const _abrirCadastroOriginal = Scopi.abrirCadastro.bind(Scopi);
Scopi.abrirCadastro = function(idModal, idForm) {
  _idAtual = 0;
  const btnInativar = document.getElementById('btnInativar');
  const btnReativar = document.getElementById('btnReativar');
  if (btnInativar) btnInativar.style.display = 'none';
  if (btnReativar) btnReativar.style.display = 'none';
  _abrirCadastroOriginal(idModal, idForm);
};

function inativarCategoria() {
  if (!_idAtual) return;
  Scopi.confirmarAcao('Deseja inativar esta categoria?', '/categorias/inativar', { id: _idAtual });
}

function reativarCategoria() {
  if (!_idAtual) return;
  Scopi.confirmarAcao('Deseja reativar esta categoria?', '/categorias/reativar', { id: _idAtual });
}
</script>
