<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Fornecedores';</script>
<div class="pagina-cabecalho"><h1 class="pagina-titulo">Fornecedores</h1><p class="pagina-subtitulo">Cadastro e gerenciamento de fornecedores</p></div>
<div class="painel-filtros">
  <div class="filtros-cabecalho"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt=""><span>Filtros</span></div>
  <form method="GET" action="<?= BASE_URL ?>/fornecedores"><div class="filtros-campos">
    <div class="campo-filtro"><label>Cód. Fornecedor</label><input type="text" name="codigo" value="<?= Auxiliares::escapar($filtros['codigo']??'') ?>" placeholder="FORN-..."></div>
    <div class="campo-filtro"><label>Razão Social</label><input type="text" name="razao_social" value="<?= Auxiliares::escapar($filtros['razao_social']??'') ?>" placeholder="Buscar..."></div>
    <div class="campo-filtro"><label>Nome Fantasia</label><input type="text" name="nome_fantasia" value="<?= Auxiliares::escapar($filtros['nome_fantasia']??'') ?>" placeholder="Buscar..."></div>
    <div class="campo-filtro"><label>CNPJ</label><input type="text" name="cnpj" value="<?= Auxiliares::escapar($filtros['cnpj']??'') ?>" placeholder="00.000.000/0000-00"></div>
    <div class="campo-filtro"><label>Cidade/UF/País</label><input type="text" name="cidade_uf" value="<?= Auxiliares::escapar($filtros['cidade_uf']??'') ?>" placeholder="Buscar..."></div>
    <div class="campo-filtro"><label>Status</label><select name="situacao"><option value="">Todas</option><option value="ativo" <?= ($filtros['situacao']??'')==='ativo'?'selected':'' ?>>Ativo</option><option value="inativo" <?= ($filtros['situacao']??'')==='inativo'?'selected':'' ?>>Inativo</option></select></div>
    <div class="campo-filtro" style="flex:0;align-self:flex-end;"><button type="submit" class="btn btn-filtrar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt=""> Buscar</button></div>
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
            <button class="btn btn-secundario btn-historico" style="display:none; padding: 4px 8px; font-size: 0.8rem;" onclick="abrirHistorico('fornecedores', _idFornAtual)">Histórico</button>
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
            <div class="campo-form campo-leitura campo-completo" id="blocoLeituraForn" style="display:none; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 10px;">
                <div><label>Código do Fornecedor</label><input type="text" data-campo="codigo" readonly class="campo-input" style="background-color: #f5f5f5;"></div>
                <div><label>Status</label><input type="text" data-campo="situacao_texto" readonly class="campo-input" style="background-color: #f5f5f5;"></div>
            </div>
            <div class="campo-form campo-completo"><label>Razão Social *</label><input type="text" name="razao_social" required></div>
            <div class="campo-form campo-completo"><label>Nome Fantasia</label><input type="text" name="nome_fantasia"></div>
            <div class="campo-form">
              <label>CNPJ *</label>
              <input type="text" name="cnpj" required placeholder="Digite os 14 números" onblur="consultarCnpjReceita(this)">
            </div>
            <div class="campo-form"><label>Inscrição Estadual</label><input type="text" name="inscricao_estadual"></div>
            
            <!-- Endereço -->
            <div class="campo-form"><label>CEP</label><input type="text" name="cep" placeholder="00000-000" onblur="consultarCEP(this)" oninput="mascararCEP(this)" maxlength="9"></div>
            <div class="campo-form campo-completo"><label>Logradouro</label><input type="text" name="logradouro" placeholder="Ex: Av. Paulista"></div>
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
              <input type="text" name="sigla_estado" id="input_sigla_estado" placeholder="Ex: SP" maxlength="2">
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
              <label>Categorias</label>
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
                <label>Código Fornecedor Matriz</label>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <input type="text" id="fornMatrizCodigo" class="campo-input" style="width: 120px; text-transform: uppercase;" placeholder="FORN..." onblur="buscarFornecedorMatriz(this.value)">
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
let _idFornAtual = 0;

const _abrirForn = Scopi.abrirRegistro.bind(Scopi);
Scopi.abrirRegistro = async function(idModal, idForm, url, id, aba) {
    _idFornAtual = id;
    await _abrirForn(idModal, idForm, url, id, aba);
    if (idModal === 'modalFornecedor') {
        const form = document.getElementById(idForm);
        
        const badgeSituacao = document.querySelector(`#${idModal} [data-badge="situacao"]`);
        const btnInativar = document.getElementById('btnInativarForn');
        const btnReativar = document.getElementById('btnReativarForn');
        const titulo = document.getElementById('tituloModalFornecedor');
        const btnHistorico = document.querySelector(`#${idModal} .btn-historico`);
        const blocoLeitura = document.getElementById('blocoLeituraForn');
        
        if (titulo) titulo.textContent = aba === 'editar' ? 'Edição de Cadastro de Fornecedor' : 'Cadastro de Fornecedor';
        if (btnHistorico) btnHistorico.style.display = 'inline-flex';
        if (blocoLeitura) blocoLeitura.style.display = 'grid';
        
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
        
        if (form) {
            const tipo = form.querySelector('[name="tipo"]')?.value || 'matriz';
            toggleMatrizSelect(tipo);
            
            const numField = document.getElementById('forn_numero');
            const numCheck = document.getElementById('forn_sem_numero');
            if (numField && numCheck) {
                if (numField.value === 'S/N' || numField.value === 's/n') {
                    numCheck.checked = true;
                    numField.disabled = true;
                } else {
                    numCheck.checked = false;
                    numField.disabled = false;
                }
            }
            
            // RF05/RF08: Preencher categorias N:N e campo de visualização
            try {
                const resp = await fetch(Scopi.url(`/fornecedores/dados?id=${id}`), {headers:{'X-Requested-With':'XMLHttpRequest'}});
                const json = await resp.json();
                if (json.sucesso && json.dados && json.dados.categorias) {
                    // Desmarcar todas primeiro
                    document.querySelectorAll('#categoriasChecklist input[type="checkbox"]').forEach(cb => cb.checked = false);
                    // Marcar as vinculadas
                    json.dados.categorias.forEach(cat => {
                        const cb = document.getElementById('cat_' + cat.id);
                        if (cb) cb.checked = true;
                    });
                    // Atualizar campo de visualização
                    const nomesEl = document.querySelector('[data-campo="categorias_nomes"]');
                    if (nomesEl) {
                        nomesEl.textContent = json.dados.categorias.map(c => c.nome).join(', ') || '—';
                    }
                }
            } catch(e) { console.error(e); }
        }
    }
};

const _novoForn = Scopi.abrirCadastro.bind(Scopi);
Scopi.abrirCadastro = function(idModal, idForm) {
    _idFornAtual = 0;
    _novoForn(idModal, idForm);
    if (idModal === 'modalFornecedor') {
        const titulo = document.getElementById('tituloModalFornecedor');
        const btnHistorico = document.querySelector(`#${idModal} .btn-historico`);
        const blocoLeitura = document.getElementById('blocoLeituraForn');
        const btnInativar = document.getElementById('btnInativarForn');
        const btnReativar = document.getElementById('btnReativarForn');
        
        if (titulo) titulo.textContent = 'Cadastro de Fornecedor';
        if (btnInativar) btnInativar.style.display = 'none';
        if (btnReativar) btnReativar.style.display = 'none';
        if (btnHistorico) btnHistorico.style.display = 'none';
        if (blocoLeitura) blocoLeitura.style.display = 'none';
        
        document.getElementById('forn_numero').disabled = false;
        document.getElementById('forn_sem_numero').checked = false;
        
        document.getElementById('fornMatrizCodigo').value = '';
        document.getElementById('fornMatrizNome').textContent = 'Digite o código da matriz...';
        document.getElementById('fornMatrizId').value = '';

        toggleMatrizSelect('matriz');
        // Desmarcar todas as categorias ao abrir novo cadastro
        document.querySelectorAll('#categoriasChecklist input[type="checkbox"]').forEach(cb => cb.checked = false);
    }
};

function toggleMatrizSelect(tipo) {
    const wrapper = document.getElementById('wrapperMatriz');
    const inputCodigo = document.getElementById('fornMatrizCodigo');
    if (tipo === 'filial') {
        wrapper.style.display = 'block';
        if (inputCodigo) inputCodigo.required = true;
    } else {
        wrapper.style.display = 'none';
        if (inputCodigo) {
            inputCodigo.required = false;
            inputCodigo.value = '';
            document.getElementById('fornMatrizNome').textContent = 'Digite o código da matriz...';
            document.getElementById('fornMatrizId').value = '';
        }
    }
}

function toggleNumeroForn(checkbox) {
    const num = document.getElementById('forn_numero');
    if (checkbox.checked) {
        num.value = 'S/N';
        num.disabled = true;
    } else {
        num.value = '';
        num.disabled = false;
        num.focus();
    }
}

function inativarFornecedor() {
    if (!_idFornAtual) return;
    Scopi.confirmarAcao('Inativar este fornecedor?','/fornecedores/inativar',{id:_idFornAtual});
}

function reativarFornecedor() {
    if (!_idFornAtual) return;
    Scopi.confirmarAcao('Reativar este fornecedor?','/fornecedores/reativar',{id:_idFornAtual});
}

async function buscarFornecedorMatriz(codigo) {
    codigo = codigo.trim();
    const spanNome = document.getElementById('fornMatrizNome');
    const inputId = document.getElementById('fornMatrizId');
    
    if (!codigo) {
        spanNome.textContent = 'Digite o código da matriz...';
        spanNome.style.color = 'var(--texto-secundario)';
        inputId.value = '';
        return;
    }
    
    spanNome.textContent = 'Buscando...';
    spanNome.style.color = 'var(--texto-secundario)';
    
    try {
        const resp = await fetch(`${SCOPI_BASE}/fornecedores/consultar-codigo?codigo=${encodeURIComponent(codigo)}`);
        const data = await resp.json();
        if (data.sucesso && data.dados) {
            spanNome.textContent = data.dados.razao_social;
            spanNome.style.color = 'var(--sucesso)';
            inputId.value = data.dados.id;
        } else {
            spanNome.textContent = 'Matriz não encontrada ou inativa';
            spanNome.style.color = 'var(--alerta)';
            inputId.value = '';
        }
    } catch (err) {
        spanNome.textContent = 'Erro ao buscar';
        spanNome.style.color = 'var(--alerta)';
        inputId.value = '';
    }
}

async function consultarCnpjReceita(input) {
    if (!input) return;
    
    // Limpa os caracteres especiais do input
    const cnpj = input.value.replace(/\D/g, '');
    input.value = cnpj;

    if (cnpj.length === 0) return;

    if (cnpj.length !== 14) {
        Scopi.toast('erro', 'Por favor, insira um CNPJ com 14 dígitos numéricos.');
        return;
    }

    // Evita consultas repetidas para o mesmo CNPJ
    if (input.dataset.ultimoConsultado === cnpj) {
        return;
    }
    input.dataset.ultimoConsultado = cnpj;

    const originalPlaceholder = input.placeholder;
    input.disabled = true;
    input.placeholder = 'Consultando...';

    try {
        const resp = await fetch(Scopi.url(`/fornecedores/consultar-cnpj?cnpj=${cnpj}`), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const res = await resp.json();
        
        if (res.sucesso && res.dados) {
            const data = res.dados;
            const form = document.getElementById('formFornecedor');
            
            // Preencher campos
            form.querySelector('[name="razao_social"]').value = data.razao_social || '';
            
            const est = data.estabelecimento || {};
            form.querySelector('[name="nome_fantasia"]').value = est.nome_fantasia || '';
            form.querySelector('[name="email"]').value = est.email || '';
            
            // Format phone number
            const ddd = est.ddd1 || '';
            const tel = est.telefone1 || '';
            form.querySelector('[name="contato"]').value = ddd && tel ? `(${ddd}) ${tel}` : (tel || '');
            
            // Fill normalized address fields
            form.querySelector('[name="cep"]').value = est.cep || '';
            form.querySelector('[name="logradouro"]').value = est.tipo_logradouro ? est.tipo_logradouro + ' ' + est.logradouro : (est.logradouro || '');
            form.querySelector('[name="numero"]').value = est.numero || '';
            form.querySelector('[name="complemento"]').value = est.complemento || '';
            form.querySelector('[name="bairro"]').value = est.bairro || '';
            
            form.querySelector('[name="cidade_id"]').value = est.cidade ? (est.cidade.id || '') : '';
            form.querySelector('[name="nome_cidade"]').value = est.cidade ? (est.cidade.nome || '') : '';
            
            form.querySelector('[name="estado_id"]').value = est.estado ? (est.estado.id || '') : '';
            form.querySelector('[name="nome_estado"]').value = est.estado ? (est.estado.nome || '') : '';
            form.querySelector('[name="sigla_estado"]').value = est.estado ? (est.estado.sigla || '') : '';
            
            form.querySelector('[name="pais_id"]').value = est.pais ? (est.pais.id || '') : '';
            form.querySelector('[name="nome_pais"]').value = est.pais ? (est.pais.nome || '') : '';
            
            // Category (activity principal) - não mais usado, categorias são N:N
            // Manter como referência: est.atividade_principal.descricao

            // Responsible (first socio if exists)
            if (data.socios && data.socios.length > 0) {
                form.querySelector('[name="responsavel"]').value = data.socios[0].nome || '';
            }
            
            // Tipo
            const tipo = est.tipo ? est.tipo.toLowerCase() : 'matriz';
            const selectTipo = form.querySelector('[name="tipo"]');
            if (selectTipo) {
                selectTipo.value = tipo;
                toggleMatrizSelect(tipo);
            }
            
            Scopi.toast('sucesso', 'Dados do CNPJ importados com sucesso!');
        } else {
            Scopi.toast('erro', res.mensagem || 'Não foi possível encontrar este CNPJ.');
            input.dataset.ultimoConsultado = '';
        }
    } catch(e) {
        console.error(e);
        Scopi.toast('erro', 'Falha na comunicação com o servidor.');
        input.dataset.ultimoConsultado = '';
    } finally {
        input.disabled = false;
        input.placeholder = originalPlaceholder;
    }
}

function mascararCEP(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 8);
    if (v.length > 5) v = v.slice(0, 5) + '-' + v.slice(5);
    input.value = v;
}

async function consultarCEP(input) {
    if (!input) return;
    const cep = input.value.replace(/\D/g, '');
    if (cep.length !== 8) return;

    if (input.dataset.ultimoConsultado === cep) return;
    input.dataset.ultimoConsultado = cep;

    const originalPlaceholder = input.placeholder;
    input.disabled = true;
    input.placeholder = 'Buscando...';

    try {
        const resp = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const data = await resp.json();

        if (!data.erro) {
            const form = document.getElementById('formFornecedor');
            form.querySelector('[name="logradouro"]').value = data.logradouro || '';
            form.querySelector('[name="bairro"]').value = data.bairro || '';
            form.querySelector('[name="nome_cidade"]').value = data.localidade || '';
            form.querySelector('[name="sigla_estado"]').value = data.uf || '';
            // Reset IDs to force DB check since we are typing a new text location
            form.querySelector('[name="cidade_id"]').value = '';
            form.querySelector('[name="estado_id"]').value = '';
            form.querySelector('[name="nome_estado"]').value = '';
            form.querySelector('[name="pais_id"]').value = '';
            form.querySelector('[name="nome_pais"]').value = 'Brasil';
            
            form.querySelector('[name="numero"]').focus();
            Scopi.toast('sucesso', 'CEP encontrado!');
        } else {
            Scopi.toast('erro', 'CEP não encontrado.');
            input.dataset.ultimoConsultado = '';
        }
    } catch(e) {
        console.error(e);
        Scopi.toast('erro', 'Falha ao buscar CEP.');
        input.dataset.ultimoConsultado = '';
    } finally {
        input.disabled = false;
        input.placeholder = originalPlaceholder;
    }
}

// Clear IDs if geographic fields are manually updated by user using Event Delegation
document.addEventListener('input', function(e) {
    const target = e.target;
    if (!target) return;
    const form = document.getElementById('formFornecedor');
    if (!form || !form.contains(target)) return;

    if (target.name === 'nome_cidade') {
        const el = form.querySelector('[name="cidade_id"]');
        if (el) el.value = '';
    } else if (target.name === 'sigla_estado') {
        const elId = form.querySelector('[name="estado_id"]');
        if (elId) elId.value = '';
        const elName = form.querySelector('[name="nome_estado"]');
        if (elName) elName.value = '';
    } else if (target.name === 'nome_pais') {
        const el = form.querySelector('[name="pais_id"]');
        if (el) el.value = '';
    }
});
</script>

