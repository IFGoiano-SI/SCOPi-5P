<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Condições de Pagamento';</script>

<div class="pagina-cabecalho">
  <h1 class="pagina-titulo">Condições de Pagamento</h1>
  <p class="pagina-subtitulo">Cadastro e gerenciamento de condições de pagamento</p>
</div>

<div class="painel-filtros">
  <div class="filtros-cabecalho">
    <img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt=""><span>Filtros</span>
  </div>
  <form method="GET" action="<?= BASE_URL ?>/condicoes-pagamento">
    <div class="filtros-campos">
      <div class="campo-filtro"><label>Código</label><input type="text" name="codigo" id="filtroCodigoCond" value="<?= Auxiliares::escapar($filtros['codigo']??'') ?>" maxlength="2"></div>
      <div class="campo-filtro"><label>Descrição</label><input type="text" name="descricao" id="filtroDescCond" value="<?= Auxiliares::escapar($filtros['descricao']??'') ?>"></div>
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
    <button class="btn btn-primario" onclick="Scopi.abrirCadastro('modalCondicao','formCondicao')">
      <img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Nova Condição de Pagamento
    </button>
    <button class="btn btn-secundario" onclick="window.open('<?= BASE_URL ?>/condicoes-pagamento/exportar' + window.location.search, '_blank')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Exportar</button>
  </div>
  <span style="font-size:.82rem;color:#888;"><?= count($condicoes) ?> registro(s)</span>
</div>

<div class="tabela-container">
  <table class="tabela" id="tabelaCondicoes">
    <thead><tr>
      <th>Código</th><th>Descrição</th><th>Status</th><th class="coluna-acoes"></th>
    </tr></thead>
    <tbody>
      <?php if(empty($condicoes)): ?>
        <tr><td colspan="4" style="text-align:center;padding:32px;color:#888;">Nenhuma condição de pagamento encontrada.</td></tr>
      <?php else: ?>
        <?php foreach($condicoes as $cp): ?>
        <tr>
          <td><span class="cod-clicavel" onclick="Scopi.abrirRegistro('modalCondicao','formCondicao','/condicoes-pagamento/dados',<?= $cp['id'] ?>,'visualizar')"><?= Auxiliares::escapar($cp['codigo']) ?></span></td>
          <td><?= Auxiliares::escapar($cp['descricao']) ?></td>
          <td><span class="badge badge-<?= $cp['situacao'] ?>"><?= ucfirst($cp['situacao']) ?></span></td>
          <td class="coluna-acoes">
            <button class="btn-icone btn-editar-linha" onclick="Scopi.abrirRegistro('modalCondicao','formCondicao','/condicoes-pagamento/dados',<?= $cp['id'] ?>,'editar')" title="Editar">
              <img src="<?= BASE_URL ?>/public/assets/icons/iconeEditar.svg" alt="">
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="overlay-modal" id="modalCondicao">
  <div class="modal modal-estreito">
    <div class="modal-cabecalho">
      <div class="modal-titulo"><img src="<?= BASE_URL ?>/public/assets/icons/iconeCadastro.svg" alt=""><span id="tituloModalCondicao">Cadastro de Condição de Pagamento</span></div>
      <div style="display:flex;gap:8px;">
        <button class="btn btn-secundario btn-historico" style="display:none;padding:4px 8px;font-size:0.8rem;" onclick="Scopi.abrirHistorico('condicoes_pagamento', _idCondAtual, 'Condição de Pagamento')">Histórico</button>
        <button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalCondicao')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt=""></button>
      </div>
    </div>
    <div class="modal-abas">
      <button class="aba-btn ativa" data-aba="visualizar" onclick="Scopi.ativarAba('modalCondicao','visualizar')">Visualizar</button>
      <button class="aba-btn" data-aba="editar" onclick="Scopi.ativarAba('modalCondicao','editar')">Editar</button>
    </div>
    <div class="modal-corpo">
      <div class="conteudo-aba ativo" data-aba="visualizar">
        <div class="grade-visualizar">
          <div class="campo-visualizar"><span class="rotulo">Código</span><span class="valor" data-campo="codigo">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Status</span><span class="valor"><span class="badge" data-badge="situacao">—</span></span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Descrição</span><span class="valor" data-campo="descricao">—</span></div>
        </div>
      </div>
      <div class="conteudo-aba" data-aba="editar">
        <form id="formCondicao" onsubmit="event.preventDefault();Scopi.enviarFormulario('formCondicao','modalCondicao','/condicoes-pagamento/salvar')">
          <input type="hidden" name="id" value="0">
          <div class="grade-form" style="grid-template-columns:1fr;">
            <div id="blocoLeituraCondicao" style="display:none;grid-column:1/-1;">
              <div class="grade-form" style="grid-template-columns:1fr 1fr;margin-bottom:10px;">
                <div class="campo-form">
                  <label>Código</label>
                  <input type="text" data-campo="codigo" readonly class="campo-input" style="cursor:not-allowed;">
                </div>
                <div class="campo-form">
                  <label>Status</label>
                  <input type="text" data-campo="situacao_texto" readonly class="campo-input" style="cursor:not-allowed;">
                </div>
              </div>
            </div>
            <div class="campo-form"><label>Descrição *</label><input type="text" name="descricao" required></div>
          </div>
        </form>
      </div>
    </div>
    <div class="modal-rodape">
      <button class="btn btn-secundario" id="btnInativarCondicao" style="margin-right:auto;display:none;" onclick="inativarCondicao()">Inativar</button>
      <button class="btn btn-secundario" id="btnReativarCondicao" style="margin-right:auto;display:none;" onclick="reativarCondicao()">Reativar</button>
      <button class="btn btn-secundario" onclick="Scopi.fecharModal('modalCondicao')">Fechar</button>
      <button class="btn btn-primario btn-salvar" onclick="Scopi.enviarFormulario('formCondicao','modalCondicao','/condicoes-pagamento/salvar')">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Salvar
      </button>
    </div>
  </div>
</div>

<script>
var _idCondAtual = 0;

if (!window._hookedCondicoes) {
    window._hookedCondicoes = true;

    const _origAbrirRegCond = Scopi.abrirRegistro;
    Scopi.abrirRegistro = async function(idModal, idForm, url, id, aba) {
        if (idModal === 'modalCondicao') _idCondAtual = id;
        await _origAbrirRegCond.call(Scopi, idModal, idForm, url, id, aba);
        if (idModal !== 'modalCondicao') return;

        const titulo       = document.getElementById('tituloModalCondicao');
        const btnHistorico = document.querySelector('#modalCondicao .btn-historico');
        const btnInativar  = document.getElementById('btnInativarCondicao');
        const btnReativar  = document.getElementById('btnReativarCondicao');
        const badge        = document.querySelector('#modalCondicao [data-badge="situacao"]');
        const blocoLeitura = document.getElementById('blocoLeituraCondicao');

        if (titulo) titulo.textContent = aba === 'editar' ? 'Edição de Cadastro de Condição de Pagamento' : 'Cadastro de Condição de Pagamento';
        if (btnHistorico) btnHistorico.style.display = 'inline-flex';
        if (blocoLeitura) blocoLeitura.style.display = 'block';

        if (aba === 'editar') {
            const inputCod = document.querySelector('#modalCondicao form [data-campo="codigo"]');
            const inputSit = document.querySelector('#modalCondicao form [data-campo="situacao_texto"]');
            const valCod   = document.querySelector('#modalCondicao .grade-visualizar [data-campo="codigo"]')?.textContent || '';
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

    const _origAbrirCadCond = Scopi.abrirCadastro;
    Scopi.abrirCadastro = function(idModal, idForm) {
        _origAbrirCadCond.call(Scopi, idModal, idForm);
        if (idModal !== 'modalCondicao') return;
        _idCondAtual = 0;
        const titulo       = document.getElementById('tituloModalCondicao');
        const btnHistorico = document.querySelector('#modalCondicao .btn-historico');
        const blocoLeitura = document.getElementById('blocoLeituraCondicao');
        const btnInativar  = document.getElementById('btnInativarCondicao');
        const btnReativar  = document.getElementById('btnReativarCondicao');
        if (titulo)       titulo.textContent          = 'Cadastro de Condição de Pagamento';
        if (btnHistorico) btnHistorico.style.display  = 'none';
        if (btnInativar)  btnInativar.style.display   = 'none';
        if (btnReativar)  btnReativar.style.display   = 'none';
        if (blocoLeitura) {
            blocoLeitura.style.display = 'block';
            const inputCod = blocoLeitura.querySelector('[data-campo="codigo"]');
            const inputSit = blocoLeitura.querySelector('[data-campo="situacao_texto"]');
            if (inputCod) inputCod.value = '';
            if (inputSit) inputSit.value = 'Ativo';
        }
    };
}

function inativarCondicao() {
    if (!_idCondAtual) return;
    Scopi.confirmarAcao('Deseja inativar esta condição de pagamento?', '/condicoes-pagamento/inativar', { id: _idCondAtual });
}
function reativarCondicao() {
    if (!_idCondAtual) return;
    Scopi.confirmarAcao('Deseja reativar esta condição de pagamento?', '/condicoes-pagamento/reativar', { id: _idCondAtual });
}
</script>
