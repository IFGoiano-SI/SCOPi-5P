<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Fornecedores';</script>
<div class="pagina-cabecalho"><h1 class="pagina-titulo">Fornecedores</h1><p class="pagina-subtitulo">Cadastro e gerenciamento de fornecedores</p></div>
<div class="painel-filtros">
  <div class="filtros-cabecalho"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt=""><span>Filtros</span></div>
  <form method="GET" action="<?= BASE_URL ?>/fornecedores"><div class="filtros-campos">
    <div class="campo-filtro"><label>Razão Social / Fantasia</label><input type="text" name="nome" value="<?= Auxiliares::escapar($filtros['nome']??'') ?>" placeholder="Buscar..."></div>
    <div class="campo-filtro"><label>CNPJ</label><input type="text" name="cnpj" value="<?= Auxiliares::escapar($filtros['cnpj']??'') ?>" placeholder="00.000.000/0000-00"></div>
    <div class="campo-filtro"><label>Situação</label><select name="situacao"><option value="">Todas</option><option value="ativo" <?= ($filtros['situacao']??'')==='ativo'?'selected':'' ?>>Ativo</option><option value="inativo" <?= ($filtros['situacao']??'')==='inativo'?'selected':'' ?>>Inativo</option></select></div>
    <div class="campo-filtro" style="flex:0;align-self:flex-end;"><button type="submit" class="btn btn-filtrar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt=""> Buscar</button></div>
  </div></form>
</div>
<div class="barra-acoes">
  <div class="grupo-botoes">
    <button class="btn btn-primario" onclick="Scopi.abrirCadastro('modalFornecedor','formFornecedor')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Novo Fornecedor</button>
    <button class="btn btn-secundario"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Exportar</button>
  </div>
  <span style="font-size:.82rem;color:#888;"><?= count($fornecedores) ?> registro(s)</span>
</div>
<div class="tabela-container">
  <table class="tabela">
    <thead><tr><th></th><th>Código</th><th>Razão Social</th><th>CNPJ</th><th>Responsável</th><th>Situação</th><th class="coluna-acoes"></th></tr></thead>
    <tbody>
      <?php if(empty($fornecedores)): ?><tr><td colspan="7" style="text-align:center;padding:32px;color:#888;">Nenhum fornecedor encontrado.</td></tr>
      <?php else: foreach($fornecedores as $f): ?>
      <tr>
        <td></td>
        <td><span class="cod-clicavel" onclick="Scopi.abrirRegistro('modalFornecedor','formFornecedor','/fornecedores/dados',<?= $f['id'] ?>,'visualizar')"><?= Auxiliares::escapar($f['codigo']) ?></span></td>
        <td><?= Auxiliares::escapar($f['razao_social']) ?></td>
        <td><?= Auxiliares::escapar($f['cnpj']) ?></td>
        <td><?= Auxiliares::escapar($f['responsavel']??'—') ?></td>
        <td><span class="badge badge-<?= $f['situacao'] ?>"><?= ucfirst($f['situacao']) ?></span></td>
        <td class="coluna-acoes"><button class="btn-icone btn-editar-linha" onclick="Scopi.abrirRegistro('modalFornecedor','formFornecedor','/fornecedores/dados',<?= $f['id'] ?>,'editar')" title="Editar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeEditar.svg" alt=""></button></td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<!-- MODAL Fornecedor -->
<div class="overlay-modal" id="modalFornecedor">
  <div class="modal modal-largo">
    <div class="modal-cabecalho"><div class="modal-titulo"><img src="<?= BASE_URL ?>/public/assets/icons/iconeCadastro.svg" alt=""><span>Fornecedor</span></div><button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalFornecedor')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt=""></button></div>
    <div class="modal-abas"><button class="aba-btn ativa" data-aba="visualizar" onclick="Scopi.ativarAba('modalFornecedor','visualizar')">Visualizar</button><button class="aba-btn" data-aba="editar" onclick="Scopi.ativarAba('modalFornecedor','editar')">Editar</button></div>
    <div class="modal-corpo">
      <div class="conteudo-aba ativo" data-aba="visualizar">
        <div class="grade-visualizar">
          <div class="campo-visualizar"><span class="rotulo">Código</span><span class="valor" data-campo="codigo">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Situação</span><span class="valor"><span class="badge" data-badge="situacao">—</span></span></div>
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
          <div class="campo-visualizar"><span class="rotulo">Categoria</span><span class="valor" data-campo="categoria">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Tipo</span><span class="valor" data-campo="tipo">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Matriz Vinculada</span><span class="valor" data-campo="nome_matriz">—</span></div>
        </div>
      </div>
      <div class="conteudo-aba" data-aba="editar">
        <form id="formFornecedor" onsubmit="event.preventDefault();Scopi.enviarFormulario('formFornecedor','modalFornecedor','/fornecedores/salvar')">
          <input type="hidden" name="id" value="0">
          <div class="grade-form">
            <div class="campo-form campo-completo"><label>Razão Social *</label><input type="text" name="razao_social" required></div>
            <div class="campo-form campo-completo"><label>Nome Fantasia</label><input type="text" name="nome_fantasia"></div>
            <div class="campo-form">
              <label>CNPJ *</label>
              <input type="text" name="cnpj" required placeholder="Digite os 14 números" onblur="consultarCnpjReceita(this)">
            </div>
            <div class="campo-form"><label>Inscrição Estadual</label><input type="text" name="inscricao_estadual"></div>
            <!-- Campos de Endereço Normalizados -->
            <div class="campo-form"><label>CEP</label><input type="text" name="cep" placeholder="00000-000"></div>
            <div class="campo-form campo-completo"><label>Logradouro</label><input type="text" name="logradouro" placeholder="Ex: Av. Paulista"></div>
            <div class="campo-form"><label>Número</label><input type="text" name="numero"></div>
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
            <div class="campo-form"><label>Categoria</label><input type="text" name="categoria"></div>
            <div class="campo-form"><label>Tipo *</label>
              <select name="tipo" onchange="toggleMatrizSelect(this.value)">
                <option value="matriz">Matriz</option>
                <option value="filial">Filial</option>
              </select>
            </div>
            <div class="campo-form" id="wrapperMatriz" style="display:none;"><label>Matriz Vinculada</label>
              <select name="matriz_id">
                <option value="">Selecione a Matriz...</option>
                <?php foreach($matrizes as $mz): ?>
                  <option value="<?= $mz['id'] ?>"><?= Auxiliares::escapar($mz['razao_social']) ?> (<?= Auxiliares::escapar($mz['cnpj']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </form>
      </div>
    </div>
    <div class="modal-rodape"><button class="btn btn-secundario" onclick="Scopi.fecharModal('modalFornecedor')">Fechar</button><button class="btn btn-primario btn-salvar" onclick="Scopi.enviarFormulario('formFornecedor','modalFornecedor','/fornecedores/salvar')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Salvar</button></div>
  </div>
</div>

<script>
const _abrirForn = Scopi.abrirRegistro.bind(Scopi);
Scopi.abrirRegistro = async function(idModal, idForm, url, id, aba) {
    await _abrirForn(idModal, idForm, url, id, aba);
    if (idModal === 'modalFornecedor') {
        const form = document.getElementById(idForm);
        if (form) {
            const tipo = form.querySelector('[name="tipo"]')?.value || 'matriz';
            toggleMatrizSelect(tipo);
        }
    }
};

const _novoForn = Scopi.abrirCadastro.bind(Scopi);
Scopi.abrirCadastro = function(idModal, idForm) {
    _novoForn(idModal, idForm);
    if (idModal === 'modalFornecedor') {
        toggleMatrizSelect('matriz');
    }
};

function toggleMatrizSelect(tipo) {
    const wrapper = document.getElementById('wrapperMatriz');
    const select = document.querySelector('[name="matriz_id"]');
    if (tipo === 'filial') {
        wrapper.style.display = 'block';
        select.required = true;
    } else {
        wrapper.style.display = 'none';
        select.required = false;
        select.value = '';
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
            
            // Category (activity principal)
            if (est.atividade_principal && est.atividade_principal.descricao) {
                form.querySelector('[name="categoria"]').value = est.atividade_principal.descricao;
            }

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

