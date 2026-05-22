<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Departamentos';</script>

<div class="pagina-cabecalho">
  <h1 class="pagina-titulo">Departamentos</h1>
  <p class="pagina-subtitulo">Cadastro e gerenciamento de departamentos</p>
</div>

<!-- Filtros -->
<div class="painel-filtros">
  <div class="filtros-cabecalho">
    <img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt=""><span>Filtros</span>
  </div>
  <form method="GET" action="<?= BASE_URL ?>/departamentos">
    <div class="filtros-campos">
      <div class="campo-filtro"><label>Nome</label><input type="text" name="nome" value="<?= Auxiliares::escapar($filtros['nome']??'') ?>" placeholder="Nome do departamento..."></div>
      <div class="campo-filtro"><label>Código</label><input type="text" name="codigo" value="<?= Auxiliares::escapar($filtros['codigo']??'') ?>" placeholder="DEP-..."></div>
      <div class="campo-filtro"><label>Situação</label>
        <select name="situacao">
          <option value="">Todas</option>
          <option value="ativo"   <?= ($filtros['situacao']??'')==='ativo'?'selected':'' ?>>Ativo</option>
          <option value="inativo" <?= ($filtros['situacao']??'')==='inativo'?'selected':'' ?>>Inativo</option>
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
    <button class="btn btn-primario" onclick="Scopi.abrirCadastro('modalDepartamento','formDepartamento')">
      <img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Novo Departamento
    </button>
    <button class="btn btn-secundario"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Exportar</button>
  </div>
  <span style="font-size:.82rem;color:#888;"><?= count($departamentos) ?> registro(s)</span>
</div>

<!-- Tabela -->
<div class="tabela-container">
  <table class="tabela">
    <thead><tr>
      <th><label class="checkbox-custom"><input type="checkbox" onchange="Scopi.toggleCheckboxes(this)"><img src="<?= BASE_URL ?>/public/assets/icons/iconeCheckboxVazia.svg" class="check-icone" alt=""></label></th>
      <th>Código</th><th>Nome</th><th>Gerente Responsável</th><th>Situação</th><th class="coluna-acoes"></th>
    </tr></thead>
    <tbody>
      <?php if(empty($departamentos)): ?>
        <tr><td colspan="6" style="text-align:center;padding:32px;color:#888;">Nenhum departamento encontrado.</td></tr>
      <?php else: ?>
        <?php foreach($departamentos as $d): ?>
        <tr>
          <td><label class="checkbox-custom"><input type="checkbox" class="checkbox-linha" value="<?= $d['id'] ?>"><img src="<?= BASE_URL ?>/public/assets/icons/iconeCheckboxVazia.svg" class="check-icone" alt=""></label></td>
          <td><span class="cod-clicavel" onclick="Scopi.abrirRegistro('modalDepartamento','formDepartamento','/departamentos/dados',<?= $d['id'] ?>,'visualizar')"><?= Auxiliares::escapar($d['codigo']) ?></span></td>
          <td><?= Auxiliares::escapar($d['nome']) ?></td>
          <td><?= Auxiliares::escapar($d['nome_gerente']??'—') ?></td>
          <td><span class="badge badge-<?= $d['situacao'] ?>"><?= ucfirst($d['situacao']) ?></span></td>
          <td class="coluna-acoes"><button class="btn-icone btn-editar-linha" onclick="Scopi.abrirRegistro('modalDepartamento','formDepartamento','/departamentos/dados',<?= $d['id'] ?>,'editar')" title="Editar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeEditar.svg" alt=""></button></td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- MODAL Departamento -->
<div class="overlay-modal" id="modalDepartamento">
  <div class="modal modal-estreito">
    <div class="modal-cabecalho">
      <div class="modal-titulo"><img src="<?= BASE_URL ?>/public/assets/icons/iconeCadastro.svg" alt=""><span>Departamento</span></div>
      <button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalDepartamento')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt=""></button>
    </div>
    <div class="modal-abas">
      <button class="aba-btn ativa" data-aba="visualizar" onclick="Scopi.ativarAba('modalDepartamento','visualizar')">Visualizar</button>
      <button class="aba-btn" data-aba="editar" onclick="Scopi.ativarAba('modalDepartamento','editar')">Editar</button>
    </div>
    <div class="modal-corpo">
      <div class="conteudo-aba ativo" data-aba="visualizar">
        <div class="grade-visualizar">
          <div class="campo-visualizar"><span class="rotulo">Código</span><span class="valor" data-campo="codigo">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Situação</span><span class="valor"><span class="badge" data-badge="situacao">—</span></span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Nome</span><span class="valor" data-campo="nome">—</span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Gerente</span><span class="valor" data-campo="nome_gerente">—</span></div>
        </div>
      </div>
      <div class="conteudo-aba" data-aba="editar">
        <form id="formDepartamento" onsubmit="event.preventDefault();Scopi.enviarFormulario('formDepartamento','modalDepartamento','/departamentos/salvar')">
          <input type="hidden" name="id" value="0">
          <div class="grade-form" style="grid-template-columns:1fr;">
            <div class="campo-form"><label>Nome *</label><input type="text" name="nome" required placeholder="Nome do departamento"></div>
            <div class="campo-form"><label>Gerente Responsável</label>
              <select name="gerente_id">
                <option value="">Selecione...</option>
                <?php foreach($gerentes as $g): ?>
                  <option value="<?= $g['id'] ?>"><?= Auxiliares::escapar($g['nome']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </form>
      </div>
    </div>
    <div class="modal-rodape">
      <button class="btn btn-secundario" onclick="Scopi.fecharModal('modalDepartamento')">Fechar</button>
      <button class="btn btn-primario btn-salvar" onclick="Scopi.enviarFormulario('formDepartamento','modalDepartamento','/departamentos/salvar')">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Salvar
      </button>
    </div>
  </div>
</div>
