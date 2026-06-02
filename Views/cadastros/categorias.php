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
      <div class="campo-filtro"><label>Cód. Categoria</label><input type="text" name="codigo" value="<?= Auxiliares::escapar($filtros['codigo']??'') ?>" placeholder="CAT-..."></div>
      <div class="campo-filtro"><label>Nome</label><input type="text" name="nome" value="<?= Auxiliares::escapar($filtros['nome']??'') ?>" placeholder="Nome da categoria..."></div>
      <div class="campo-filtro"><label>Status</label>
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
    <button class="btn btn-secundario" onclick="window.open('<?= BASE_URL ?>/categorias/exportar' + window.location.search, '_blank')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Exportar</button>
  </div>
  <span style="font-size:.82rem;color:#888;"><?= count($categorias) ?> registro(s)</span>
</div>

<!-- Tabela -->
<div class="tabela-container">
  <table class="tabela" id="tabelaCategorias">
    <thead><tr>
      <th>Código</th><th>Nome</th><th>Status</th><th class="coluna-acoes"></th>
    </tr></thead>
    <tbody>
      <?php if(empty($categorias)): ?>
        <tr><td colspan="4" style="text-align:center;padding:32px;color:#888;">Nenhuma categoria encontrada.</td></tr>
      <?php else: ?>
        <?php foreach($categorias as $c): ?>
        <tr>
          <td><span class="cod-clicavel" onclick="Scopi.abrirRegistro('modalCategoria','formCategoria','/categorias/dados',<?= $c['id'] ?>,'visualizar')"><?= Auxiliares::escapar($c['codigo']) ?></span></td>
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
      <div class="modal-titulo"><img src="<?= BASE_URL ?>/public/assets/icons/iconeCadastro.svg" alt=""><span id="tituloModalCategoria">Cadastro de Categoria</span></div>
      <div style="display: flex; gap: 8px;">
          <button class="btn btn-secundario btn-historico" style="display:none; padding: 4px 8px; font-size: 0.8rem;" onclick="abrirHistorico('categorias', _idAtual)">Histórico</button>
          <button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalCategoria')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt=""></button>
      </div>
    </div>
    <div class="modal-abas">
      <button class="aba-btn ativa" data-aba="visualizar" onclick="Scopi.ativarAba('modalCategoria','visualizar')">Visualizar</button>
      <button class="aba-btn" data-aba="editar" onclick="Scopi.ativarAba('modalCategoria','editar')">Editar</button>
    </div>
    <div class="modal-corpo">
      <div class="conteudo-aba ativo" data-aba="visualizar">
        <div class="grade-visualizar">
          <div class="campo-visualizar"><span class="rotulo">Código</span><span class="valor" data-campo="codigo">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Status</span><span class="valor"><span class="badge" data-badge="situacao">—</span></span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Nome</span><span class="valor" data-campo="nome">—</span></div>
        </div>
      </div>
      <div class="conteudo-aba" data-aba="editar">
        <form id="formCategoria" onsubmit="event.preventDefault();Scopi.enviarFormulario('formCategoria','modalCategoria','/categorias/salvar')">
          <input type="hidden" name="id" value="0">
          <div class="grade-form" style="grid-template-columns:1fr;">
            <div class="campo-form campo-leitura" id="blocoLeituraCategoria" style="display:none; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 10px;">
                <div><label>Código</label><input type="text" data-campo="codigo" readonly class="campo-input" style="background-color: #f5f5f5;"></div>
                <div><label>Status</label><input type="text" data-campo="situacao_texto" readonly class="campo-input" style="background-color: #f5f5f5;"></div>
            </div>
            <div class="campo-form"><label>Nome *</label><input type="text" name="nome" required placeholder="Nome da categoria"></div>
          </div>
        </form>
      </div>
    </div>
    <div class="modal-rodape">
      <button class="btn btn-secundario" id="btnInativar" style="margin-right:auto; display:none;" onclick="inativarCategoria()">Inativar</button>
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
  const titulo = document.getElementById('tituloModalCategoria');
  const btnHistorico = document.querySelector(`#${idModal} .btn-historico`);
  const blocoLeitura = document.getElementById('blocoLeituraCategoria');
  
  if (titulo) titulo.textContent = aba === 'editar' ? 'Edição de Cadastro de Categoria' : 'Cadastro de Categoria';
  if (btnHistorico) btnHistorico.style.display = 'inline-flex';
  if (blocoLeitura) blocoLeitura.style.display = 'grid';
  
  // Preencher campos readonly do editar
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

const _abrirCadastroOriginal = Scopi.abrirCadastro.bind(Scopi);
Scopi.abrirCadastro = function(idModal, idForm) {
  _idAtual = 0;
  const titulo = document.getElementById('tituloModalCategoria');
  const btnHistorico = document.querySelector(`#${idModal} .btn-historico`);
  const blocoLeitura = document.getElementById('blocoLeituraCategoria');
  
  if (titulo) titulo.textContent = 'Cadastro de Categoria';
  if (btnInativar) btnInativar.style.display = 'none';
  if (btnReativar) btnReativar.style.display = 'none';
  if (btnHistorico) btnHistorico.style.display = 'none';
  if (blocoLeitura) blocoLeitura.style.display = 'none';
  
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
