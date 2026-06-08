<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Categorias';</script>

<div class="pagina-cabecalho">
  <h1 class="pagina-titulo">Categorias</h1>
  <p class="pagina-subtitulo">Cadastro e gerenciamento de categorias de produtos</p>
</div>

<div class="painel-filtros">
  <div class="filtros-cabecalho">
    <img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt=""><span>Filtros</span>
  </div>
  <form method="GET" action="<?= BASE_URL ?>/categorias">
    <div class="filtros-campos">
      <div class="campo-filtro"><label>Código</label><input type="text" name="codigo" value="<?= Auxiliares::escapar($filtros['codigo']??'') ?>"></div>
      <div class="campo-filtro"><label>Nome</label><input type="text" name="nome" value="<?= Auxiliares::escapar($filtros['nome']??'') ?>"></div>
      <div class="campo-filtro"><label>Status</label>
        <select name="situacao">
          <option value="">Todos</option>
          <option value="ativo"   <?= ($filtros['situacao']??'')==='ativo'?'selected':'' ?>>Ativo</option>
          <option value="inativo" <?= ($filtros['situacao']??'')==='inativo'?'selected':'' ?>>Inativo</option>
        </select>
      </div>
      <div class="campo-filtro" style="flex:0;align-self:flex-end;">
        <button type="submit" class="btn btn-filtrar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt=""> Filtrar</button>
      </div>
    </div>
  </form>
</div>

<div class="barra-acoes">
  <div class="grupo-botoes">
    <button class="btn btn-primario" onclick="Scopi.abrirCadastro('modalCategoria','formCategoria')">
      <img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Nova Categoria
    </button>
    <button class="btn btn-secundario" onclick="window.open('<?= BASE_URL ?>/categorias/exportar' + window.location.search, '_blank')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Exportar</button>
  </div>
  <span style="font-size:.82rem;color:#888;"><?= count($categorias) ?> registro(s)</span>
</div>

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
          <td><span class="badge badge-<?= $c['situacao'] ?>"><?= ucfirst($c['situacao']) ?></span></td>
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

<div class="overlay-modal" id="modalCategoria">
  <div class="modal modal-estreito">
    <div class="modal-cabecalho">
      <div class="modal-titulo"><img src="<?= BASE_URL ?>/public/assets/icons/iconeCadastro.svg" alt=""><span id="tituloModalCategoria">Cadastro de Categoria</span></div>
      <div style="display:flex;gap:8px;">
        <button class="btn btn-secundario btn-historico" style="display:none;padding:4px 8px;font-size:0.8rem;" onclick="Scopi.abrirHistorico('categorias', _idCatAtual, 'Categoria')">Histórico</button>
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
            <div class="grade-form" style="grid-template-columns:1fr 1fr;margin-bottom:10px;">
              <div class="campo-form">
                <label>Código</label>
                <input type="text" data-campo="codigo" readonly class="campo-input" style="cursor:not-allowed;">
              </div>
              <div class="campo-form">
                <label>Status</label>
                <input type="text" data-campo="situacao_texto" readonly class="campo-input" style="cursor:not-allowed;" value="Ativo">
              </div>
            </div>
            <div class="campo-form"><label>Nome *</label><input type="text" name="nome" required></div>
          </div>
        </form>
      </div>
    </div>
    <div class="modal-rodape">
      <button class="btn btn-secundario" id="btnInativarCat" style="margin-right:auto;display:none;" onclick="inativarCategoria()">Inativar</button>
      <button class="btn btn-secundario" id="btnReativarCat" style="margin-right:auto;display:none;" onclick="reativarCategoria()">Reativar</button>
      <button class="btn btn-secundario" onclick="Scopi.fecharModal('modalCategoria')">Fechar</button>
      <button class="btn btn-primario btn-salvar" onclick="Scopi.enviarFormulario('formCategoria','modalCategoria','/categorias/salvar')">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Salvar
      </button>
    </div>
  </div>
</div>

<script>
var _idCatAtual = 0;

if (!window._hookedCategorias) {
    window._hookedCategorias = true;

    const _origAbrirRegCat = Scopi.abrirRegistro;
    Scopi.abrirRegistro = async function(idModal, idForm, url, id, aba) {
        if (idModal === 'modalCategoria') _idCatAtual = id;
        await _origAbrirRegCat.call(Scopi, idModal, idForm, url, id, aba);
        if (idModal !== 'modalCategoria') return;

        const titulo      = document.getElementById('tituloModalCategoria');
        const btnHistorico= document.querySelector('#modalCategoria .btn-historico');
        const btnInativar = document.getElementById('btnInativarCat');
        const btnReativar = document.getElementById('btnReativarCat');
        const badge       = document.querySelector('#modalCategoria [data-badge="situacao"]');

        if (titulo) titulo.textContent = aba === 'editar' ? 'Edição de Cadastro de Categoria' : 'Cadastro de Categoria';
        if (btnHistorico) btnHistorico.style.display = 'inline-flex';

        if (aba === 'editar') {
            const inputCod = document.querySelector('#modalCategoria form [data-campo="codigo"]');
            const inputSit = document.querySelector('#modalCategoria form [data-campo="situacao_texto"]');
            const valCod   = document.querySelector('#modalCategoria .grade-visualizar [data-campo="codigo"]')?.textContent || '';
            const valSit   = badge ? badge.textContent : '';
            if (inputCod) inputCod.value = valCod;
            if (inputSit) inputSit.value = valSit;
        }

        if (badge && btnInativar && btnReativar) {
            const sit = badge.textContent.trim().toLowerCase();
            if (sit === 'ativo') {
                if (aba === 'editar') btnInativar.style.display = ''; else btnInativar.style.display = 'none';
                btnReativar.style.display = 'none';
            } else {
                btnInativar.style.display = 'none';
                if (aba === 'editar') btnReativar.style.display = ''; else btnReativar.style.display = 'none';
            }
        }
    };

    const _origAbrirCadCat = Scopi.abrirCadastro;
    Scopi.abrirCadastro = function(idModal, idForm) {
        _origAbrirCadCat.call(Scopi, idModal, idForm);
        if (idModal !== 'modalCategoria') return;
        _idCatAtual = 0;
        const titulo       = document.getElementById('tituloModalCategoria');
        const btnHistorico = document.querySelector('#modalCategoria .btn-historico');
        const btnInativar  = document.getElementById('btnInativarCat');
        const btnReativar  = document.getElementById('btnReativarCat');
        if (titulo) titulo.textContent = 'Cadastro de Categoria';
        if (btnHistorico) btnHistorico.style.display = 'none';
        if (btnInativar)  btnInativar.style.display  = 'none';
        if (btnReativar)  btnReativar.style.display  = 'none';
        setTimeout(() => {
            const inputSit = document.querySelector('#modalCategoria form [data-campo="situacao_texto"]');
            if (inputSit) inputSit.value = 'Ativo';
            const inputCod = document.querySelector('#modalCategoria form [data-campo="codigo"]');
            if (inputCod) inputCod.value = '';
        }, 50);
    };
}

function inativarCategoria() {
    if (!_idCatAtual) return;
    Scopi.confirmarAcao('Deseja inativar esta categoria?', '/categorias/inativar', { id: _idCatAtual });
}
function reativarCategoria() {
    if (!_idCatAtual) return;
    Scopi.confirmarAcao('Deseja reativar esta categoria?', '/categorias/reativar', { id: _idCatAtual });
}
</script>
