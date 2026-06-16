<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Fornecedores';</script>
<div class="pagina-cabecalho"><h1 class="pagina-titulo">Fornecedores</h1><p class="pagina-subtitulo">Cadastro e gerenciamento de fornecedores</p></div>
<div class="painel-filtros">
  <div class="filtros-cabecalho"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt=""><span>Filtros</span></div>
  <form method="GET" action="<?= BASE_URL ?>/fornecedores"><div class="filtros-campos">
    <div class="campo-filtro"><label>Código</label><input type="text" name="codigo" value="<?= Auxiliares::escapar($filtros['codigo']??'') ?>"></div>
    <div class="campo-filtro"><label>Razão Social</label><input type="text" name="razao_social" value="<?= Auxiliares::escapar($filtros['razao_social']??'') ?>"></div>
    <div class="campo-filtro"><label>Nome Fantasia</label><input type="text" name="nome_fantasia" value="<?= Auxiliares::escapar($filtros['nome_fantasia']??'') ?>"></div>
    <div class="campo-filtro">
      <label style="display:flex;gap:8px;align-items:center;justify-content:space-between;">
        <span>Cód. Categoria</span>
        <button type="button" class="btn btn-secundario" style="padding:4px 6px;margin:0;" onclick="Scopi.iconeBusca('categorias','filtroCatFornCodigo','filtroCatFornNome')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width:13px;margin:0;" alt="Buscar"></button>
      </label>
      <div style="display:flex;gap:8px;align-items:center;max-width:250px;">
        <input type="text" id="filtroCatFornCodigo" name="categoria_codigo" value="<?= Auxiliares::escapar($filtros['categoria_codigo']??'') ?>" class="campo-input" style="width:100px;text-transform:uppercase;" onblur="buscarCategoriaFiltroForn(this.value)">
        <span id="filtroCatFornNome" style="font-size:0.8rem;color:var(--texto-secundario);flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= empty($filtros['categoria_codigo']) ? 'Digite...' : 'Buscando...' ?></span>
      </div>
    </div>
    <div class="campo-filtro">
      <label style="display:flex;gap:8px;align-items:center;justify-content:space-between;">
        <span>Cód. Matriz</span>
        <button type="button" class="btn btn-secundario" style="padding:4px 6px;margin:0;" onclick="Scopi.iconeBusca('fornecedores','filtroMatrizCodigo','filtroMatrizNome')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width:13px;margin:0;" alt="Buscar"></button>
      </label>
      <div style="display:flex;gap:8px;align-items:center;max-width:250px;">
        <input type="text" id="filtroMatrizCodigo" name="matriz_codigo" value="<?= Auxiliares::escapar($filtros['matriz_codigo']??'') ?>" class="campo-input" style="width:100px;text-transform:uppercase;" onblur="buscarMatrizFiltro(this.value)">
        <span id="filtroMatrizNome" style="font-size:0.8rem;color:var(--texto-secundario);flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= empty($filtros['matriz_codigo']) ? 'Digite...' : 'Buscando...' ?></span>
      </div>
    </div>
    <div class="campo-filtro"><label>CNPJ</label><input type="text" name="cnpj" value="<?= Auxiliares::escapar($filtros['cnpj']??'') ?>"></div>
    <div class="campo-filtro"><label>Cidade/UF/País</label><input type="text" name="cidade_uf" value="<?= Auxiliares::escapar($filtros['cidade_uf']??'') ?>"></div>
    <div class="campo-filtro"><label>Status</label><select name="situacao"><option value="">Todas</option><option value="ativo" <?= ($filtros['situacao']??'')==='ativo'?'selected':'' ?>>Ativo</option><option value="inativo" <?= ($filtros['situacao']??'')==='inativo'?'selected':'' ?>>Inativo</option></select></div>
    <div class="campo-filtro" style="flex:0;align-self:flex-end;"><button type="submit" class="btn btn-filtrar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt=""> Filtrar</button></div>
  </div></form>
</div>
<div class="barra-acoes">
  <div class="grupo-botoes">
    <button class="btn btn-primario" onclick="Scopi.abrirCadastro('modalFornecedor','formFornecedor')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Novo Fornecedor</button>
    <button class="btn btn-secundario" onclick="window.open('<?= BASE_URL ?>/fornecedores/exportar' + window.location.search, '_blank')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Exportar</button>
  </div>
  <span style="font-size:.82rem;color:#888;"><?= count($fornecedores) ?> registro(s)</span>
</div>
<div class="tabela-container">
  <table class="tabela">
    <thead><tr><th>Código</th><th>Razão Social</th><th>CNPJ</th><th>Cidade / UF / País</th><th>Nome Fantasia</th><th>Status</th><th class="coluna-acoes"></th></tr></thead>
    <tbody>
      <?php if(empty($fornecedores)): ?><tr><td colspan="6" style="text-align:center;padding:32px;color:#888;">Nenhum fornecedor encontrado.</td></tr>
      <?php else: foreach($fornecedores as $f): ?>
      <tr>
        <td><span class="cod-clicavel" onclick="Scopi.abrirRegistro('modalFornecedor','formFornecedor','/fornecedores/dados',<?= $f['id'] ?>,'visualizar')"><?= Auxiliares::escapar($f['codigo']) ?></span></td>
        <td><?= Auxiliares::escapar($f['razao_social']) ?></td>
        <td><?= Auxiliares::escapar($f['cnpj']) ?></td>
        <td><?= Auxiliares::escapar(($f['nome_cidade'] ?? '') . (($f['nome_cidade'] && $f['sigla_estado']) ? ' / ' : '') . ($f['sigla_estado'] ?? '') . (($f['sigla_estado'] && $f['nome_pais']) ? ' / ' : '') . ($f['nome_pais'] ?? '—')) ?></td>
        <td><?= Auxiliares::escapar($f['nome_fantasia'] ?? '—') ?></td>
        <td><span class="badge badge-<?= $f['situacao'] ?>"><?= ucfirst($f['situacao']) ?></span></td>
        <td class="coluna-acoes"><button class="btn-icone btn-editar-linha" onclick="Scopi.abrirRegistro('modalFornecedor','formFornecedor','/fornecedores/dados',<?= $f['id'] ?>,'editar')" title="Editar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeEditar.svg" alt=""></button></td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<div class="overlay-modal" id="modalFornecedor">
  <div class="modal modal-largo">
    <div class="modal-cabecalho">
        <div class="modal-titulo"><img src="<?= BASE_URL ?>/public/assets/icons/iconeCadastro.svg" alt=""><span id="tituloModalFornecedor">Cadastro de Fornecedor</span></div>
        <div style="display: flex; gap: 8px;">
            <button class="btn btn-secundario btn-historico" style="display:none; padding: 4px 8px; font-size: 0.8rem;" onclick="Scopi.abrirHistorico('fornecedores', _idFornAtual, 'Fornecedor')">Histórico</button>
            <button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalFornecedor')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt=""></button>
        </div>
    </div>
    <div class="modal-abas"><button class="aba-btn ativa" data-aba="visualizar" onclick="Scopi.ativarAba('modalFornecedor','visualizar')">Visualizar</button><button class="aba-btn" data-aba="editar" onclick="Scopi.ativarAba('modalFornecedor','editar')">Editar</button></div>
    <div class="modal-corpo">
      <div class="conteudo-aba ativo" data-aba="visualizar">
        <div class="grade-visualizar">
          <div class="campo-visualizar"><span class="rotulo">Código</span><span class="valor" data-campo="codigo">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Status</span><span class="valor"><span class="badge" data-badge="situacao">—</span></span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Razão Social</span><span class="valor" data-campo="razao_social">—</span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Nome Fantasia</span><span class="valor" data-campo="nome_fantasia">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">CNPJ</span><span class="valor" data-campo="cnpj">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Inscrição Estadual</span><span class="valor" data-campo="inscricao_estadual">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">CEP</span><span class="valor" data-campo="cep">—</span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Logradouro</span><span class="valor" data-campo="logradouro">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Número</span><span class="valor" data-campo="numero">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Complemento</span><span class="valor" data-campo="complemento">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Bairro</span><span class="valor" data-campo="bairro">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Cidade</span><span class="valor" data-campo="nome_cidade">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Estado/UF</span><span class="valor" data-campo="sigla_estado">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">País</span><span class="valor" data-campo="nome_pais">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">E-mail</span><span class="valor" data-campo="email">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Contato</span><span class="valor" data-campo="contato">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Responsável</span><span class="valor" data-campo="responsavel">—</span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Categorias</span><span class="valor" data-campo="categorias_nomes">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Tipo</span><span class="valor" data-campo="tipo">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Matriz Vinculada</span><span class="valor" data-campo="nome_matriz">—</span></div>
        </div>
      </div>
      <div class="conteudo-aba" data-aba="editar">
        <form id="formFornecedor" onsubmit="event.preventDefault();Scopi.enviarFormulario('formFornecedor','modalFornecedor','/fornecedores/salvar')">
          <input type="hidden" name="id" value="0">
          <div class="grade-form">
            <div class="grade-form" style="grid-template-columns: 1fr 1fr; margin-bottom: 10px; grid-column: 1 / -1;">
                <div class="campo-form">
                    <label>Código</label>
                    <input type="text" data-campo="codigo" readonly class="campo-input" style="cursor: not-allowed;" placeholder="">
                </div>
                <div class="campo-form">
                    <label>Status</label>
                    <input type="text" data-campo="situacao_texto" readonly class="campo-input" style="cursor: not-allowed;" value="Ativo">
                </div>
            </div>
            <div class="campo-form campo-completo"><label>Razão Social *</label><input type="text" name="razao_social" required></div>
            <div class="campo-form campo-completo"><label>Nome Fantasia</label><input type="text" name="nome_fantasia"></div>
            <div class="campo-form">
              <label>CNPJ *</label>
              <input type="text" name="cnpj" required onblur="consultarCnpjReceita(this)" oninput="mascararCnpj(this)" maxlength="18" placeholder="00.000.000/0000-00">
            </div>
            <div class="campo-form"><label>Inscrição Estadual</label><input type="text" name="inscricao_estadual"></div>
            
            <!-- Endereço -->
            <div class="campo-form"><label>CEP</label><input type="text" name="cep" placeholder="00000-000" onblur="consultarCEP(this)" oninput="mascararCEP(this)" maxlength="9"></div>
            <div class="campo-form campo-completo"><label>Logradouro</label><input type="text" name="logradouro"></div>
            <div class="campo-form"><label>Número</label>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <input type="text" name="numero" id="forn_numero" style="flex: 1;">
                    <label style="font-size: 0.8rem; font-weight: normal; margin-bottom: 0; display: flex; align-items: center; gap: 4px; white-space: nowrap;"><input type="checkbox" id="forn_sem_numero" onchange="toggleNumeroForn(this)"> Sem número</label>
                </div>
            </div>
            <div class="campo-form"><label>Complemento</label><input type="text" name="complemento"></div>
            <div class="campo-form"><label>Bairro</label><input type="text" name="bairro"></div>
            <div class="campo-form"><label>Cidade</label>
              <input type="text" name="nome_cidade" id="input_nome_cidade">
              <input type="hidden" name="cidade_id" id="input_cidade_id">
            </div>
            <div class="campo-form"><label>Estado (UF)</label>
              <input type="text" name="sigla_estado" id="input_sigla_estado" maxlength="2">
              <input type="hidden" name="estado_id" id="input_estado_id">
              <input type="hidden" name="nome_estado" id="input_nome_estado">
            </div>
            <div class="campo-form"><label>País</label>
              <input type="text" name="nome_pais" id="input_nome_pais" value="Brasil">
              <input type="hidden" name="pais_id" id="input_pais_id">
            </div>
            <div class="campo-form"><label>E-mail</label><input type="email" name="email"></div>
            <div class="campo-form"><label>Contato</label><input type="text" name="contato"></div>
            <div class="campo-form"><label>Responsável</label><input type="text" name="responsavel"></div>
            <div class="campo-form campo-completo">
              <label>Categorias Adicionais</label>
              <div style="border: 1px solid var(--borda); border-radius: 6px; padding: 10px; max-height: 150px; overflow-y: auto; background-color: var(--branco);" id="categoriasChecklist">
                <?php foreach($categorias as $cat): ?>
                  <div style="display: inline-flex; align-items: center; gap: 4px; margin: 4px 8px 4px 0;">
                    <input type="checkbox" name="categorias[]" value="<?= $cat['id'] ?>" id="cat_<?= $cat['id'] ?>" style="width: 14px; height: 14px; accent-color: var(--media);">
                    <label for="cat_<?= $cat['id'] ?>" style="font-size: 0.78rem; cursor: pointer; user-select: none;"><?= Auxiliares::escapar($cat['nome']) ?></label>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="campo-form"><label>Tipo *</label>
              <select name="tipo" onchange="toggleMatrizSelect(this.value)">
                <option value="matriz">Matriz</option>
                <option value="filial">Filial</option>
              </select>
            </div>
            <div class="campo-form" id="wrapperMatriz" style="display:none;">
                <label style="display:flex;gap:8px;align-items:center;justify-content:space-between;">
                    <span>Código Fornecedor Matriz</span>
                    <button type="button" class="btn btn-secundario" style="padding:4px 6px;margin:0;" title="Buscar matriz" onclick="Scopi.iconeBusca('fornecedores','fornMatrizCodigo','fornMatrizNome')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" style="width:13px;margin:0;" alt="Buscar"></button>
                </label>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <input type="text" id="fornMatrizCodigo" class="campo-input" style="width: 120px; text-transform: uppercase;" placeholder="Ex: forn000000" onblur="buscarFornecedorMatriz(this.value)">
                    <span id="fornMatrizNome" style="font-size: 0.9rem; color: var(--texto-secundario); font-style: italic;">Digite o código da matriz...</span>
                    <input type="hidden" name="matriz_id" id="fornMatrizId" value="">
                </div>
            </div>
          </div>
        </form>
      </div>
    </div>
    <div class="modal-rodape">
        <button class="btn btn-secundario" id="btnInativarForn" style="margin-right:auto; display:none;" onclick="inativarFornecedor()">Inativar</button>
        <button class="btn btn-secundario" id="btnReativarForn" style="margin-right:auto; display:none;" onclick="reativarFornecedor()">Reativar</button>
        <button class="btn btn-secundario" onclick="Scopi.fecharModal('modalFornecedor')">Fechar</button>
        <button class="btn btn-primario btn-salvar" onclick="Scopi.enviarFormulario('formFornecedor','modalFornecedor','/fornecedores/salvar')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Salvar</button>
    </div>
  </div>
</div>

<script>
    var _idFornAtual = 0;
    
    if (!window._hookedFornecedores) {
        window._hookedFornecedores = true;

        window._abrirOriginalForn = Scopi.abrirRegistro;
        Scopi.abrirRegistro = async function(idModal, idForm, url, id, aba) {
            _idFornAtual = 0;
            await window._abrirOriginalForn.call(Scopi, idModal, idForm, url, id, aba);

            if (idModal === 'modalFornecedor') {
                _idFornAtual = id;
                const titulo = document.getElementById('tituloModalFornecedor');
                const btnHistorico = document.querySelector(`#${idModal} .btn-historico`);
                const btnInativar = document.getElementById('btnInativarForn');
                const btnReativar = document.getElementById('btnReativarForn');
                const badgeSituacao = document.querySelector(`#${idModal} [data-badge="situacao"]`);
                
                if (titulo) titulo.textContent = 'Cadastro de Fornecedor';
                if (btnHistorico) btnHistorico.style.display = 'inline-flex';
                
                const inputCodigo = document.querySelector(`#${idModal} form [data-campo="codigo"]`);
                const inputSituacao = document.querySelector(`#${idModal} form [data-campo="situacao_texto"]`);
                const valCodigo = document.querySelector(`#${idModal} .grade-visualizar [data-campo="codigo"]`).textContent;
                const valSit = badgeSituacao ? badgeSituacao.textContent : '';
                if (inputCodigo) inputCodigo.value = valCodigo;
                if (inputSituacao) inputSituacao.value = valSit;

                document.getElementById('forn_numero').disabled = false;
                document.getElementById('forn_sem_numero').checked = false;
                
                try {
                    const resp = await fetch(Scopi.url(`/fornecedores/dados?id=${id}`), {credentials:'include',headers:{'X-Requested-With':'XMLHttpRequest'}});
                    const json = await resp.json();
                    if (json.sucesso && json.dados) {
                        if (json.dados.matriz_id && parseInt(json.dados.matriz_id) > 0) {
                            document.getElementById('fornMatrizCodigo').value = json.dados.matriz_codigo || '';
                            document.getElementById('fornMatrizNome').textContent = json.dados.matriz_nome || 'Matriz vinculada';
                            document.getElementById('wrapperMatriz').style.display = 'block';
                        } else {
                            document.getElementById('fornMatrizCodigo').value = '';
                            document.getElementById('fornMatrizNome').textContent = 'Digite o código da matriz...';
                            document.getElementById('wrapperMatriz').style.display = 'none';
                        }

                        if (json.dados.sem_numero === 1 || json.dados.sem_numero === '1' || json.dados.sem_numero === true) {
                            document.getElementById('forn_sem_numero').checked = true;
                            document.getElementById('forn_numero').value = '';
                            document.getElementById('forn_numero').disabled = true;
                        } else {
                            document.getElementById('forn_numero').disabled = false;
                            document.getElementById('forn_sem_numero').checked = false;
                        }

                        document.querySelectorAll('#categoriasChecklist input[type="checkbox"]').forEach(cb => cb.checked = false);
                        if (json.dados.categorias && Array.isArray(json.dados.categorias)) {
                            json.dados.categorias.forEach(catId => {
                                const cb = document.getElementById('cat_' + catId);
                                if(cb) cb.checked = true;
                            });
                        }
                    }
                } catch(e) { }

                if (!window._listenerFornAba) {
                    window._listenerFornAba = true;
                    window.addEventListener('scopiAbaChange', function(e) {
                        if (e.detail.idModal !== idModal) return;
                        const abaAtual = e.detail.aba;
                        if (titulo) titulo.textContent = abaAtual === 'editar' ? 'Edição de Cadastro de Fornecedor' : 'Cadastro de Fornecedor';
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

        window._abrirCadastroOriginalForn = Scopi.abrirCadastro;
        Scopi.abrirCadastro = function(idModal, idForm) {
            _idFornAtual = 0;
            if (idModal === 'modalFornecedor') {
                const titulo = document.getElementById('tituloModalFornecedor');
                const btnHistorico = document.querySelector(`#${idModal} .btn-historico`);
                const btnInativar = document.getElementById('btnInativarForn');
                const btnReativar = document.getElementById('btnReativarForn');
                
                if (titulo) titulo.textContent = 'Cadastro de Fornecedor';
                if (btnInativar) btnInativar.style.display = 'none';
                if (btnReativar) btnReativar.style.display = 'none';
                if (btnHistorico) btnHistorico.style.display = 'none';
                
                document.getElementById('forn_numero').disabled = false;
                document.getElementById('forn_sem_numero').checked = false;

                document.getElementById('fornMatrizCodigo').value = '';
                document.getElementById('fornMatrizNome').textContent = 'Digite o código da matriz...';
                document.getElementById('wrapperMatriz').style.display = 'none';
                
                const inputSit = document.querySelector(`#${idModal} form [data-campo="situacao_texto"]`);
                if (inputSit) inputSit.value = 'Ativo';
                const inputCod = document.querySelector(`#${idModal} form [data-campo="codigo"]`);
                if (inputCod) inputCod.value = '';
                
                document.querySelectorAll('#categoriasChecklist input[type="checkbox"]').forEach(cb => cb.checked = false);
            }
            
            window._abrirCadastroOriginalForn.call(Scopi, idModal, idForm);
        };
    }

async function buscarFornecedorMatriz(codigo) {
    codigo = (codigo||'').trim();
    const spanNome = document.getElementById('fornMatrizNome');
    const inputId  = document.getElementById('fornMatrizId');
    if (!codigo) {
        if (spanNome) { spanNome.textContent = 'Digite o código da matriz...'; spanNome.style.color = 'var(--texto-secundario)'; }
        if (inputId) inputId.value = '';
        return;
    }
    if (spanNome) { spanNome.textContent = 'Buscando...'; spanNome.style.color = 'var(--texto-secundario)'; }
    try {
        const resp = await fetch(Scopi.url(`/fornecedores/consultar-codigo?codigo=${encodeURIComponent(codigo)}`), {credentials:'include'});
        const data = await resp.json();
        if (data.sucesso && data.dados) {
            if (spanNome) { spanNome.textContent = data.dados.nome; spanNome.style.color = 'var(--sucesso)'; }
            if (inputId) inputId.value = data.dados.id;
        } else {
            if (spanNome) { spanNome.textContent = 'Fornecedor não encontrado ou inativo'; spanNome.style.color = 'var(--alerta)'; }
            if (inputId) inputId.value = '';
        }
    } catch(e) {
        if (spanNome) { spanNome.textContent = 'Erro ao buscar'; spanNome.style.color = 'var(--alerta)'; }
    }
}

async function buscarCategoriaFiltroForn(codigo) {
    codigo = (codigo||'').trim();
    const span = document.getElementById('filtroCatFornNome');
    if (!span) return;
    if (!codigo) { span.textContent = 'Digite...'; span.style.color = 'var(--texto-secundario)'; return; }
    span.textContent = 'Buscando...'; span.style.color = 'var(--texto-secundario)';
    try {
        const resp = await fetch(Scopi.url(`/categorias/consultar-codigo?codigo=${encodeURIComponent(codigo)}`), {credentials:'include'});
        const data = await resp.json();
        if (data.sucesso && data.dados) { span.textContent = data.dados.nome; span.style.color = 'var(--sucesso)'; }
        else { span.textContent = 'Não encontrada'; span.style.color = 'var(--alerta)'; }
    } catch(e) { span.textContent = 'Erro'; span.style.color = 'var(--alerta)'; }
}

async function buscarMatrizFiltro(codigo) {
    codigo = (codigo||'').trim();
    const span = document.getElementById('filtroMatrizNome');
    if (!span) return;
    if (!codigo) { span.textContent = 'Digite...'; span.style.color = 'var(--texto-secundario)'; return; }
    span.textContent = 'Buscando...'; span.style.color = 'var(--texto-secundario)';
    try {
        const resp = await fetch(Scopi.url(`/fornecedores/consultar-codigo?codigo=${encodeURIComponent(codigo)}`), {credentials:'include'});
        const data = await resp.json();
        if (data.sucesso && data.dados) { span.textContent = data.dados.nome; span.style.color = 'var(--sucesso)'; }
        else { span.textContent = 'Não encontrada'; span.style.color = 'var(--alerta)'; }
    } catch(e) { span.textContent = 'Erro'; span.style.color = 'var(--alerta)'; }
}

function toggleMatrizSelect(tipo) {
    const wrapper = document.getElementById('wrapperMatriz');
    if (wrapper) wrapper.style.display = (tipo === 'filial') ? 'block' : 'none';
}

function toggleNumeroForn(cb) {
    const input = document.getElementById('forn_numero');
    if (input) { input.disabled = cb.checked; if (cb.checked) input.value = ''; }
}

function mascararCEP(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 8);
    if (v.length > 5) {
        v = v.slice(0, 5) + '-' + v.slice(5);
    }
    input.value = v;
}

async function consultarCEP(input) {
    const cep = input.value.replace(/\D/g, '');
    if (cep.length !== 8) {
        return;
    }
    
    const form = input.form;
    if (!form) return;
    
    const logradouroInput = form.querySelector('input[name="logradouro"]');
    const complementoInput = form.querySelector('input[name="complemento"]');
    const bairroInput = form.querySelector('input[name="bairro"]');
    const nomeCidadeInput = form.querySelector('input[name="nome_cidade"]');
    const cidadeIdInput = form.querySelector('input[name="cidade_id"]');
    const siglaEstadoInput = form.querySelector('input[name="sigla_estado"]');
    const estadoIdInput = form.querySelector('input[name="estado_id"]');
    const nomeEstadoInput = form.querySelector('input[name="nome_estado"]');
    const nomePaisInput = form.querySelector('input[name="nome_pais"]');
    const paisIdInput = form.querySelector('input[name="pais_id"]');
    const numeroInput = form.querySelector('input[name="numero"]');

    const inputsToBlock = [logradouroInput, complementoInput, bairroInput, nomeCidadeInput, siglaEstadoInput].filter(Boolean);
    inputsToBlock.forEach(el => el.disabled = true);
    
    try {
        const resp = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const data = await resp.json();
        
        if (data && !data.erro) {
            if (logradouroInput) logradouroInput.value = data.logradouro || '';
            if (complementoInput) complementoInput.value = data.complemento || '';
            if (bairroInput) bairroInput.value = data.bairro || '';
            if (nomeCidadeInput) nomeCidadeInput.value = data.localidade || '';
            if (cidadeIdInput) cidadeIdInput.value = '';
            if (siglaEstadoInput) siglaEstadoInput.value = data.uf || '';
            if (estadoIdInput) estadoIdInput.value = '';
            if (nomeEstadoInput) nomeEstadoInput.value = data.estado || '';
            if (nomePaisInput) nomePaisInput.value = 'Brasil';
            if (paisIdInput) paisIdInput.value = '';
            
            if (numeroInput) numeroInput.focus();
        } else {
            Scopi.toast('alerta', 'CEP não encontrado.');
        }
    } catch (e) {
        console.error(e);
        Scopi.toast('erro', 'Erro ao consultar CEP na ViaCEP.');
    } finally {
        inputsToBlock.forEach(el => el.disabled = false);
    }
}

function mascararCnpj(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 14);
    if (v.length <= 2) v = v;
    else if (v.length <= 5) v = v.slice(0, 2) + '.' + v.slice(2);
    else if (v.length <= 8) v = v.slice(0, 2) + '.' + v.slice(2, 5) + '.' + v.slice(5);
    else if (v.length <= 12) v = v.slice(0, 2) + '.' + v.slice(2, 5) + '.' + v.slice(5, 8) + '/' + v.slice(8);
    else v = v.slice(0, 2) + '.' + v.slice(2, 5) + '.' + v.slice(5, 8) + '/' + v.slice(8, 12) + '-' + v.slice(12);
    input.value = v;
}

async function consultarCnpjReceita(input) {
    const cnpj = input.value.replace(/\D/g, '');
    if (cnpj.length !== 14) {
        return;
    }
    
    const form = input.form;
    if (!form) return;
    
    const inputsToBlock = Array.from(form.querySelectorAll('input:not([readonly]), select:not([readonly]), textarea:not([readonly])'));
    inputsToBlock.forEach(el => el.disabled = true);
    
    try {
        const resp = await fetch(`https://publica.cnpj.ws/cnpj/${cnpj}`);
        
        if (resp.status === 429) {
            Scopi.toast('alerta', 'Limite de consultas atingido (3/min). Aguarde 60 segundos.');
            return;
        }
        
        if (!resp.ok) {
            Scopi.toast('alerta', 'Erro ao consultar CNPJ. Verifique os dados ou tente novamente mais tarde.');
            return;
        }
        
        const data = await resp.json();
        
        if (data && (data.razao_social || data.estabelecimento)) {
            const razaoSocialInput = form.querySelector('input[name="razao_social"]');
            if (razaoSocialInput) razaoSocialInput.value = data.razao_social || '';
            
            const nomeFantasiaInput = form.querySelector('input[name="nome_fantasia"]');
            if (nomeFantasiaInput) {
                nomeFantasiaInput.value = (data.estabelecimento && data.estabelecimento.nome_fantasia) || '';
            }
            
            let ie = '';
            if (data.estabelecimento && Array.isArray(data.estabelecimento.inscricoes_estaduais)) {
                const ativa = data.estabelecimento.inscricoes_estaduais.find(x => x.ativo);
                if (ativa) {
                    ie = ativa.inscricao_estadual;
                } else if (data.estabelecimento.inscricoes_estaduais.length > 0) {
                    ie = data.estabelecimento.inscricoes_estaduais[0].inscricao_estadual;
                }
            }
            const ieInput = form.querySelector('input[name="inscricao_estadual"]');
            if (ieInput) ieInput.value = ie;
            
            let cep = (data.estabelecimento && data.estabelecimento.cep) || '';
            if (cep && cep.length === 8) {
                cep = cep.slice(0, 5) + '-' + cep.slice(5);
            }
            const cepInput = form.querySelector('input[name="cep"]');
            if (cepInput) cepInput.value = cep;
            
            let logradouro = '';
            if (data.estabelecimento) {
                logradouro = (data.estabelecimento.tipo_logradouro ? data.estabelecimento.tipo_logradouro + ' ' : '') + (data.estabelecimento.logradouro || '');
                logradouro = logradouro.trim();
            }
            const logradouroInput = form.querySelector('input[name="logradouro"]');
            if (logradouroInput) logradouroInput.value = logradouro;
            
            const numeroInput = form.querySelector('input[name="numero"]');
            const semNumeroCheckbox = document.getElementById('forn_sem_numero');
            const numeroVal = data.estabelecimento && data.estabelecimento.numero ? data.estabelecimento.numero.trim() : '';
            
            if (numeroVal.toLowerCase() === 's/n' || numeroVal.toLowerCase() === 'sn' || numeroVal.toLowerCase() === 'sem numero' || numeroVal.toLowerCase() === 'sem número') {
                if (semNumeroCheckbox) semNumeroCheckbox.checked = true;
                if (numeroInput) {
                    numeroInput.value = '';
                    numeroInput.disabled = true;
                }
            } else {
                if (semNumeroCheckbox) semNumeroCheckbox.checked = false;
                if (numeroInput) {
                    numeroInput.value = numeroVal;
                    numeroInput.disabled = false;
                }
            }
            
            const compInput = form.querySelector('input[name="complemento"]');
            if (compInput) compInput.value = (data.estabelecimento && data.estabelecimento.complemento) || '';
            
            const bairroInput = form.querySelector('input[name="bairro"]');
            if (bairroInput) bairroInput.value = (data.estabelecimento && data.estabelecimento.bairro) || '';
            
            const cidadeInput = form.querySelector('input[name="nome_cidade"]');
            if (cidadeInput) cidadeInput.value = (data.estabelecimento && data.estabelecimento.cidade && data.estabelecimento.cidade.nome) || '';
            
            const cidadeIdInput = form.querySelector('input[name="cidade_id"]');
            if (cidadeIdInput) cidadeIdInput.value = '';
            
            const siglaEstadoInput = form.querySelector('input[name="sigla_estado"]');
            if (siglaEstadoInput) siglaEstadoInput.value = (data.estabelecimento && data.estabelecimento.estado && data.estabelecimento.estado.sigla) || '';
            
            const estadoIdInput = form.querySelector('input[name="estado_id"]');
            if (estadoIdInput) estadoIdInput.value = '';
            
            const nomeEstadoInput = form.querySelector('input[name="nome_estado"]');
            if (nomeEstadoInput) nomeEstadoInput.value = (data.estabelecimento && data.estabelecimento.estado && data.estabelecimento.estado.nome) || '';
            
            const nomePaisInput = form.querySelector('input[name="nome_pais"]');
            if (nomePaisInput) nomePaisInput.value = (data.estabelecimento && data.estabelecimento.pais && data.estabelecimento.pais.nome) || 'Brasil';
            
            const paisIdInput = form.querySelector('input[name="pais_id"]');
            if (paisIdInput) paisIdInput.value = '';
            
            const emailInput = form.querySelector('input[name="email"]');
            if (emailInput) emailInput.value = (data.estabelecimento && data.estabelecimento.email) || '';
            
            let contato = '';
            if (data.estabelecimento) {
                if (data.estabelecimento.ddd1 && data.estabelecimento.telefone1) {
                    contato = '(' + data.estabelecimento.ddd1 + ') ' + data.estabelecimento.telefone1;
                } else if (data.estabelecimento.telefone1) {
                    contato = data.estabelecimento.telefone1;
                }
            }
            const contatoInput = form.querySelector('input[name="contato"]');
            if (contatoInput) contatoInput.value = contato;
            
            let responsavel = '';
            if (data.socios && data.socios.length > 0) {
                responsavel = data.socios[0].nome || '';
            }
            const respInput = form.querySelector('input[name="responsavel"]');
            if (respInput) respInput.value = responsavel;
            
            const tipoSelect = form.querySelector('select[name="tipo"]');
            if (tipoSelect && data.estabelecimento && data.estabelecimento.tipo) {
                const tipoLower = data.estabelecimento.tipo.toLowerCase();
                if (tipoLower === 'matriz' || tipoLower === 'filial') {
                    tipoSelect.value = tipoLower;
                    if (typeof toggleMatrizSelect === 'function') {
                        toggleMatrizSelect(tipoLower);
                    }
                }
            }
            
            Scopi.toast('sucesso', 'Dados do CNPJ importados com sucesso.');
        } else {
            Scopi.toast('alerta', 'CNPJ não encontrado ou resposta inválida.');
        }
    } catch (e) {
        console.error(e);
        Scopi.toast('erro', 'Erro ao consultar CNPJ.');
    } finally {
        inputsToBlock.forEach(el => el.disabled = false);
        const semNumeroCheckbox = document.getElementById('forn_sem_numero');
        const numeroInput = document.getElementById('forn_numero');
        if (semNumeroCheckbox && semNumeroCheckbox.checked && numeroInput) {
            numeroInput.disabled = true;
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const catFiltro = document.getElementById('filtroCatFornCodigo')?.value?.trim();
    if (catFiltro) buscarCategoriaFiltroForn(catFiltro);

    const matFiltro = document.getElementById('filtroMatrizCodigo')?.value?.trim();
    if (matFiltro) buscarMatrizFiltro(matFiltro);
});
</script>
