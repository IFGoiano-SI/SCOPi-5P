<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Condições de Pagamento';</script>

<div class="pagina-cabecalho">
  <h1 class="pagina-titulo">Condições de Pagamento</h1>
  <p class="pagina-subtitulo">Cadastro e gerenciamento de condições de pagamento para cotações e compras</p>
</div>

<!-- Filtros -->
<div class="painel-filtros">
  <div class="filtros-cabecalho">
    <img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt=""><span>Filtros</span>
  </div>
  <form method="GET" action="<?= BASE_URL ?>/condicoes-pagamento">
    <div class="filtros-campos">
      <div class="campo-filtro"><label>Descrição</label><input type="text" name="descricao" value="<?= Auxiliares::escapar($filtros['descricao']??'') ?>" placeholder="Descrição da condição..."></div>
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
    <button class="btn btn-primario" onclick="Scopi.abrirCadastro('modalCondicao','formCondicao')">
      <img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Nova Condição
    </button>
    <button class="btn btn-secundario" onclick="exportarTabela()"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Exportar</button>
  </div>
  <span style="font-size:.82rem;color:#888;"><?= count($condicoes) ?> registro(s)</span>
</div>

<!-- Tabela -->
<div class="tabela-container">
  <table class="tabela" id="tabelaCondicoes">
    <thead><tr>
      <th><label class="checkbox-custom"><input type="checkbox" onchange="Scopi.toggleCheckboxes(this)"><img src="<?= BASE_URL ?>/public/assets/icons/iconeCheckboxVazia.svg" class="check-icone" alt=""></label></th>
      <th>ID</th><th>Descrição</th><th>Situação</th><th class="coluna-acoes"></th>
    </tr></thead>
    <tbody>
      <?php if(empty($condicoes)): ?>
        <tr><td colspan="5" style="text-align:center;padding:32px;color:#888;">Nenhuma condição de pagamento encontrada.</td></tr>
      <?php else: ?>
        <?php foreach($condicoes as $c): ?>
        <tr>
          <td><label class="checkbox-custom"><input type="checkbox" class="checkbox-linha" value="<?= $c['id'] ?>"><img src="<?= BASE_URL ?>/public/assets/icons/iconeCheckboxVazia.svg" class="check-icone" alt=""></label></td>
          <td><span class="cod-clicavel" onclick="Scopi.abrirRegistro('modalCondicao','formCondicao','/condicoes-pagamento/dados',<?= $c['id'] ?>,'visualizar')">#<?= $c['id'] ?></span></td>
          <td><?= Auxiliares::escapar($c['descricao']) ?></td>
          <td><span class="badge badge-<?= $c['situacao'] ?>"><?= ucfirst($c['situacao'] === 'ativo' ? 'ativa' : 'inativa') ?></span></td>
          <td class="coluna-acoes">
            <button class="btn-icone btn-editar-linha" onclick="Scopi.abrirRegistro('modalCondicao','formCondicao','/condicoes-pagamento/dados',<?= $c['id'] ?>,'editar')" title="Editar">
              <img src="<?= BASE_URL ?>/public/assets/icons/iconeEditar.svg" alt="">
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- MODAL Condição de Pagamento -->
<div class="overlay-modal" id="modalCondicao">
  <div class="modal modal-estreito">
    <div class="modal-cabecalho">
      <div class="modal-titulo"><img src="<?= BASE_URL ?>/public/assets/icons/iconeCadastro.svg" alt=""><span>Condição de Pagamento</span></div>
      <button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalCondicao')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt=""></button>
    </div>
    <div class="modal-abas">
      <button class="aba-btn ativa" data-aba="visualizar" onclick="Scopi.ativarAba('modalCondicao','visualizar')">Visualizar</button>
      <button class="aba-btn" data-aba="editar" onclick="Scopi.ativarAba('modalCondicao','editar')">Editar</button>
    </div>
    <div class="modal-corpo">
      <div class="conteudo-aba ativo" data-aba="visualizar">
        <div class="grade-visualizar">
          <div class="campo-visualizar"><span class="rotulo">ID</span><span class="valor" data-campo="id">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Situação</span><span class="valor"><span class="badge" data-badge="situacao">—</span></span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Descrição</span><span class="valor" data-campo="descricao">—</span></div>
        </div>
      </div>
      <div class="conteudo-aba" data-aba="editar">
        <form id="formCondicao" onsubmit="event.preventDefault();Scopi.enviarFormulario('formCondicao','modalCondicao','/condicoes-pagamento/salvar')">
          <input type="hidden" name="id" value="0">
          <div class="grade-form" style="grid-template-columns:1fr;">
            <div class="campo-form"><label>Descrição *</label><input type="text" name="descricao" required placeholder="Ex: 30/60/90 dias"></div>
          </div>
        </form>
      </div>
    </div>
    <div class="modal-rodape">
      <button class="btn btn-secundario" id="btnInativarCondicao" style="margin-right:auto;" onclick="inativarCondicao()">Inativar</button>
      <button class="btn btn-secundario" id="btnReativarCondicao" style="margin-right:auto; display:none;" onclick="reativarCondicao()">Reativar</button>
      <button class="btn btn-secundario" onclick="Scopi.fecharModal('modalCondicao')">Fechar</button>
      <button class="btn btn-primario btn-salvar" onclick="Scopi.enviarFormulario('formCondicao','modalCondicao','/condicoes-pagamento/salvar')">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Salvar
      </button>
    </div>
  </div>
</div>

<script>
function exportarTabela() {
  const rows = [...document.querySelectorAll('#tabelaCondicoes tr')];
  const csv = rows.map(r =>
    [...r.querySelectorAll('th,td')].slice(1,-1).map(c => `"${c.innerText.trim()}"`).join(';')
  ).join('\n');
  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
  const a = document.createElement('a'); a.href = URL.createObjectURL(blob);
  a.download = 'condicoes_pagamento.csv'; a.click();
}

let _idAtual = 0;

const _abrirOriginal = Scopi.abrirRegistro.bind(Scopi);
Scopi.abrirRegistro = async function(idModal, idForm, url, id, aba) {
  _idAtual = id;
  await _abrirOriginal(idModal, idForm, url, id, aba);
  
  if (idModal === 'modalCondicao') {
    const badgeSituacao = document.querySelector(`#${idModal} [data-badge="situacao"]`);
    const btnInativar = document.getElementById('btnInativarCondicao');
    const btnReativar = document.getElementById('btnReativarCondicao');
    
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
  }
};

const _abrirCadastroOriginal = Scopi.abrirCadastro.bind(Scopi);
Scopi.abrirCadastro = function(idModal, idForm) {
  _idAtual = 0;
  if (idModal === 'modalCondicao') {
    const btnInativar = document.getElementById('btnInativarCondicao');
    const btnReativar = document.getElementById('btnReativarCondicao');
    if (btnInativar) btnInativar.style.display = 'none';
    if (btnReativar) btnReativar.style.display = 'none';
  }
  _abrirCadastroOriginal(idModal, idForm);
};

function inativarCondicao() {
  if (!_idAtual) return;
  Scopi.confirmarAcao('Deseja inativar esta condição de pagamento?', '/condicoes-pagamento/inativar', { id: _idAtual });
}

function reativarCondicao() {
  if (!_idAtual) return;
  Scopi.confirmarAcao('Deseja reativar esta condição de pagamento?', '/condicoes-pagamento/reativar', { id: _idAtual });
}
</script>
