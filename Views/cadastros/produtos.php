<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Produtos';</script>
<div class="pagina-cabecalho"><h1 class="pagina-titulo">Produtos</h1><p class="pagina-subtitulo">Cadastro e gerenciamento de produtos</p></div>
<div class="painel-filtros">
  <div class="filtros-cabecalho"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt=""><span>Filtros</span></div>
  <form method="GET" action="<?= BASE_URL ?>/produtos"><div class="filtros-campos">
    <div class="campo-filtro"><label>Código</label><input type="text" name="codigo" value="<?= Auxiliares::escapar($filtros['codigo']??'') ?>"></div>
    <div class="campo-filtro"><label>Nome</label><input type="text" name="nome" value="<?= Auxiliares::escapar($filtros['nome']??'') ?>"></div>
    <div class="campo-filtro">
        <label style="display:flex;gap:8px;align-items:center;justify-content:space-between;">
          <span>Cód. Categoria</span>
          <button type="button" class="btn btn-secundario" style="padding:4px 6px;margin:0;" onclick="Scopi.iconeBusca('categorias', 'filtroCatCodigo', 'filtroCatNome')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width:13px;margin:0;" alt="Buscar"></button>
        </label>
        <div style="display: flex; gap: 8px; align-items: center; max-width: 250px;">
            <input type="text" id="filtroCatCodigo" name="categoria_codigo" value="<?= Auxiliares::escapar($filtros['categoria_codigo'] ?? '') ?>" class="campo-input" style="width: 100px; text-transform: uppercase;" placeholder="Ex: CAT" onblur="buscarCategoriaFiltro(this.value)">
            <span id="filtroCatNome" style="font-size: 0.8rem; color: var(--texto-secundario); flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= empty($filtros['categoria_codigo']) ? 'Digite...' : 'Buscando...' ?></span>
        </div>
    </div>
    <div class="campo-filtro"><label>Status</label><select name="situacao"><option value="">Todas</option><option value="ativo" <?= ($filtros['situacao']??'')==='ativo'?'selected':'' ?>>Ativo</option><option value="inativo" <?= ($filtros['situacao']??'')==='inativo'?'selected':'' ?>>Inativo</option></select></div>
    <div class="campo-filtro" style="flex:0;align-self:flex-end;"><button type="submit" class="btn btn-filtrar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt=""> Filtrar</button></div>
  </div></form>
</div>
<div class="barra-acoes">
  <div class="grupo-botoes">
    <button class="btn btn-primario" onclick="Scopi.abrirCadastro('modalProduto','formProduto')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Novo Produto</button>
    <button class="btn btn-secundario" onclick="window.open('<?= BASE_URL ?>/produtos/exportar' + window.location.search, '_blank')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Exportar</button>
  </div>
  <span style="font-size:.82rem;color:#888;"><?= count($produtos) ?> registro(s)</span>
</div>
<div class="tabela-container">
  <table class="tabela">
    <thead><tr><th>Código</th><th>Nome</th><th>Descrição</th><th>Categoria</th><th>Status</th><th class="coluna-acoes"></th></tr></thead>
    <tbody>
      <?php if(empty($produtos)): ?><tr><td colspan="6" style="text-align:center;padding:32px;color:#888;">Nenhum produto encontrado.</td></tr>
      <?php else: foreach($produtos as $p): ?>
      <tr>
        <td><span class="cod-clicavel" onclick="Scopi.abrirRegistro('modalProduto','formProduto','/produtos/dados',<?= $p['id'] ?>,'visualizar')"><?= Auxiliares::escapar($p['codigo']) ?></span></td>
        <td><?= Auxiliares::escapar($p['nome']) ?></td>
        <td style="font-size:0.8rem;color:#555;max-width:200px;"><?= Auxiliares::escapar(mb_strimwidth($p['descricao']??'—', 0, 60, '…')) ?></td>
        <td><?= Auxiliares::escapar($p['nome_categoria']??'—') ?></td>
        <td><span class="badge badge-<?= $p['situacao'] ?>"><?= ucfirst($p['situacao']) ?></span></td>
        <td class="coluna-acoes"><button class="btn-icone btn-editar-linha" onclick="Scopi.abrirRegistro('modalProduto','formProduto','/produtos/dados',<?= $p['id'] ?>,'editar')" title="Editar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeEditar.svg" alt=""></button></td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
<div class="overlay-modal" id="modalProduto">
  <div class="modal modal-estreito">
    <div class="modal-cabecalho">
        <div class="modal-titulo"><img src="<?= BASE_URL ?>/public/assets/icons/iconeCadastro.svg" alt=""><span id="tituloModalProduto">Cadastro de Produto</span></div>
        <div style="display: flex; gap: 8px;">
            <button class="btn btn-secundario btn-historico" style="display:none; padding: 4px 8px; font-size: 0.8rem;" onclick="Scopi.abrirHistorico('produtos', _idProdutoAtual, 'Produto')">Histórico</button>
            <button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalProduto')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt=""></button>
        </div>
    </div>
    <div class="modal-abas"><button class="aba-btn ativa" data-aba="visualizar" onclick="Scopi.ativarAba('modalProduto','visualizar')">Visualizar</button><button class="aba-btn" data-aba="editar" onclick="Scopi.ativarAba('modalProduto','editar')">Editar</button></div>
    <div class="modal-corpo">
      <div class="conteudo-aba ativo" data-aba="visualizar">
        <div class="grade-visualizar">
          <div class="campo-visualizar"><span class="rotulo">Código</span><span class="valor" data-campo="codigo">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Status</span><span class="valor"><span class="badge" data-badge="situacao">—</span></span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Nome</span><span class="valor" data-campo="nome">—</span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Categoria</span><span class="valor" data-campo="categoria_nome">—</span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Descrição</span><span class="valor" data-campo="descricao">—</span></div>
        </div>
      </div>
      <div class="conteudo-aba" data-aba="editar">
        <form id="formProduto" onsubmit="event.preventDefault();Scopi.enviarFormulario('formProduto','modalProduto','/produtos/salvar')">
          <input type="hidden" name="id" value="0">
          <div class="grade-form" style="grid-template-columns:1fr;">
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
            <div class="campo-form"><label>Nome *</label><input type="text" name="nome" required></div>
            <div class="campo-form">
              <label style="display:flex;gap:8px;align-items:center;justify-content:space-between;">
                <span>Cód. Categoria *</span>
                <button type="button" class="btn btn-secundario" style="padding:4px 6px;margin:0;" title="Buscar categoria" onclick="Scopi.iconeBusca('categorias','produtoCategoriaCodigo','produtoCategoriaNome')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width:13px;margin:0;" alt="Buscar"></button>
              </label>
              <div style="display: flex; gap: 8px; align-items: center;">
                <input type="text" id="produtoCategoriaCodigo" class="campo-input" style="width: 120px; text-transform: uppercase;" placeholder="Ex: CAT001" onblur="buscarCategoriaForm(this.value)" required>
                <span id="produtoCategoriaNome" style="font-size: 0.9rem; color: var(--texto-secundario); font-style: italic;">Digite o código da categoria...</span>
                <input type="hidden" name="categoria_id" id="produtoCategoriaId" value="">
              </div>
            </div>
            <div class="campo-form"><label>Descrição</label><textarea name="descricao" rows="3"></textarea></div>
          </div>
        </form>
      </div>
    </div>
    <div class="modal-rodape">
        <button class="btn btn-secundario" id="btnInativarProd" style="margin-right:auto; display:none;" onclick="inativarProduto()">Inativar</button>
        <button class="btn btn-secundario" id="btnReativarProd" style="margin-right:auto; display:none;" onclick="reativarProduto()">Reativar</button>
        <button class="btn btn-secundario" onclick="Scopi.fecharModal('modalProduto')">Fechar</button>
        <button class="btn btn-primario btn-salvar" onclick="Scopi.enviarFormulario('formProduto','modalProduto','/produtos/salvar')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Salvar</button>
    </div>
  </div>
</div>

<script>
var _idProdutoAtual = 0;

if (!window._hookedProdutos) {
    window._hookedProdutos = true;

    window._abrirOriginalProd = Scopi.abrirRegistro;
    Scopi.abrirRegistro = async function(idModal, idForm, url, id, aba) {
        _idProdutoAtual = id;
        await window._abrirOriginalProd.call(Scopi, idModal, idForm, url, id, aba);

        if (idModal === 'modalProduto') {
            const titulo = document.getElementById('tituloModalProduto');
            const btnHistorico = document.querySelector(`#${idModal} .btn-historico`);
            const btnInativar = document.getElementById('btnInativarProd');
            const btnReativar = document.getElementById('btnReativarProd');
            const badgeSituacao = document.querySelector(`#${idModal} [data-badge="situacao"]`);

            if (titulo) titulo.textContent = 'Cadastro de Produto';
            if (btnHistorico) btnHistorico.style.display = 'inline-flex';

            const inputCodigo = document.querySelector(`#${idModal} form [data-campo="codigo"]`);
            const inputSituacao = document.querySelector(`#${idModal} form [data-campo="situacao_texto"]`);
            const valCodigo = document.querySelector(`#${idModal} .grade-visualizar [data-campo="codigo"]`).textContent;
            const valSit = badgeSituacao ? badgeSituacao.textContent : '';
            if (inputCodigo) inputCodigo.value = valCodigo;
            if (inputSituacao) inputSituacao.value = valSit;

            try {
                const resp = await fetch(Scopi.url(`/produtos/dados?id=${id}`), {credentials:'include',headers:{'X-Requested-With':'XMLHttpRequest'}});
                const json = await resp.json();
                if (json.sucesso && json.dados) {
                    const catNome = document.getElementById("produtoCategoriaNome");
                    const catCodigo = document.getElementById("produtoCategoriaCodigo");
                    const catId = document.getElementById("produtoCategoriaId");
                    if (catCodigo && json.dados.categoria_codigo) {
                        catCodigo.value = json.dados.categoria_codigo;
                        catNome.textContent = json.dados.categoria_nome || "Categoria vinculada";
                        catNome.style.color = "var(--sucesso)";
                        if(catId) catId.value = json.dados.categoria_id;
                    } else {
                        if (catCodigo) catCodigo.value = '';
                        if (catNome) {
                            catNome.textContent = 'Digite o código da categoria...';
                            catNome.style.color = 'var(--texto-secundario)';
                        }
                        if (catId) catId.value = '';
                    }
                }
            } catch(e) {}

            if (!window._listenerProdAba) {
                window._listenerProdAba = true;
                window.addEventListener('scopiAbaChange', function(e) {
                    if (e.detail.idModal !== idModal) return;
                    const abaAtual = e.detail.aba;
                    if (titulo) titulo.textContent = abaAtual === 'editar' ? 'Edição de Cadastro de Produto' : 'Cadastro de Produto';
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
        }
    };

    window._abrirCadastroOriginalProd = Scopi.abrirCadastro;
    Scopi.abrirCadastro = function(idModal, idForm) {
        _idProdutoAtual = 0;
        if (idModal === 'modalProduto') {
            const titulo = document.getElementById('tituloModalProduto');
            const btnHistorico = document.querySelector(`#${idModal} .btn-historico`);
            const btnInativar = document.getElementById('btnInativarProd');
            const btnReativar = document.getElementById('btnReativarProd');
            
            if (titulo) titulo.textContent = 'Cadastro de Produto';
            if (btnInativar) btnInativar.style.display = 'none';
            if (btnReativar) btnReativar.style.display = 'none';
            if (btnHistorico) btnHistorico.style.display = 'none';
            
            document.getElementById('produtoCategoriaCodigo').value = '';
            document.getElementById('produtoCategoriaNome').textContent = 'Digite o código da categoria...';
            document.getElementById('produtoCategoriaNome').style.color = 'var(--texto-secundario)';
            document.getElementById('produtoCategoriaId').value = '';
            
            const inputSit = document.querySelector(`#${idModal} form [data-campo="situacao_texto"]`);
            if (inputSit) inputSit.value = 'Ativo';
            const inputCod = document.querySelector(`#${idModal} form [data-campo="codigo"]`);
            if (inputCod) inputCod.value = '';
        }
        window._abrirCadastroOriginalProd.call(Scopi, idModal, idForm);
    };
}

async function buscarCategoriaForm(codigo) {
    codigo = codigo.trim();
    const spanNome = document.getElementById('produtoCategoriaNome');
    const inputId = document.getElementById('produtoCategoriaId');
    
    if (!codigo) {
        spanNome.textContent = 'Digite o código da categoria...';
        spanNome.style.color = 'var(--texto-secundario)';
        inputId.value = '';
        return;
    }
    
    spanNome.textContent = 'Buscando...';
    spanNome.style.color = 'var(--texto-secundario)';
    
    try {
        const resp = await fetch(Scopi.url(`/categorias/consultar-codigo?codigo=${encodeURIComponent(codigo)}`), {credentials:'include'});
        const data = await resp.json();
        if (data.sucesso && data.dados) {
            spanNome.textContent = data.dados.nome;
            spanNome.style.color = 'var(--sucesso)';
            inputId.value = data.dados.id;
        } else {
            spanNome.textContent = 'Categoria não encontrada ou inativa';
            spanNome.style.color = 'var(--alerta)';
            inputId.value = '';
        }
    } catch (err) {
        spanNome.textContent = 'Erro ao buscar';
        spanNome.style.color = 'var(--alerta)';
        inputId.value = '';
    }
}

async function buscarCategoriaFiltro(codigo) {
    codigo = codigo.trim();
    const spanNome = document.getElementById('filtroCatNome');
    if (!spanNome) return;
    if (!codigo) { spanNome.textContent = 'Digite...'; spanNome.style.color = 'var(--texto-secundario)'; return; }
    spanNome.textContent = 'Buscando...'; spanNome.style.color = 'var(--texto-secundario)';
    try {
        const resp = await fetch(Scopi.url(`/categorias/consultar-codigo?codigo=${encodeURIComponent(codigo)}`), {credentials:'include'});
        const data = await resp.json();
        if (data.sucesso && data.dados) {
            spanNome.textContent = data.dados.nome; spanNome.style.color = 'var(--sucesso)';
        } else {
            spanNome.textContent = 'Não encontrada'; spanNome.style.color = 'var(--alerta)';
        }
    } catch(e) { spanNome.textContent = 'Erro'; spanNome.style.color = 'var(--alerta)'; }
}

function inativarProduto() {
    if (!_idProdutoAtual) return;
    Scopi.confirmarAcao('Inativar este produto?','/produtos/inativar',{id:_idProdutoAtual});
}
function reativarProduto() {
    if (!_idProdutoAtual) return;
    Scopi.confirmarAcao('Reativar este produto?','/produtos/reativar',{id:_idProdutoAtual});
}
</script>
