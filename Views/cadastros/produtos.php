<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Produtos';</script>
<div class="pagina-cabecalho"><h1 class="pagina-titulo">Produtos</h1><p class="pagina-subtitulo">Cadastro e gerenciamento de produtos</p></div>
<div class="painel-filtros">
  <div class="filtros-cabecalho"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt=""><span>Filtros</span></div>
  <form method="GET" action="<?= BASE_URL ?>/produtos"><div class="filtros-campos">
    <div class="campo-filtro"><label>Nome</label><input type="text" name="nome" value="<?= Auxiliares::escapar($filtros['nome']??'') ?>" placeholder="Nome do produto..."></div>
    <div class="campo-filtro"><label>Código</label><input type="text" name="codigo" value="<?= Auxiliares::escapar($filtros['codigo']??'') ?>" placeholder="PRD-..."></div>
    <div class="campo-filtro"><label>Categoria</label><select name="categoria"><option value="">Todas</option><?php foreach($categorias as $c): ?><option value="<?= $c['id'] ?>" <?= ($filtros['categoria']??'')==$c['id']?'selected':'' ?>><?= Auxiliares::escapar($c['nome']) ?></option><?php endforeach; ?></select></div>
    <div class="campo-filtro"><label>Situação</label><select name="situacao"><option value="">Todas</option><option value="ativo" <?= ($filtros['situacao']??'')==='ativo'?'selected':'' ?>>Ativo</option><option value="inativo" <?= ($filtros['situacao']??'')==='inativo'?'selected':'' ?>>Inativo</option></select></div>
    <div class="campo-filtro" style="flex:0;align-self:flex-end;"><button type="submit" class="btn btn-filtrar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt=""> Buscar</button></div>
  </div></form>
</div>
<div class="barra-acoes">
  <div class="grupo-botoes">
    <button class="btn btn-primario" onclick="Scopi.abrirCadastro('modalProduto','formProduto')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Novo Produto</button>
    <button class="btn btn-secundario"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Exportar</button>
  </div>
  <span style="font-size:.82rem;color:#888;"><?= count($produtos) ?> registro(s)</span>
</div>
<div class="tabela-container">
  <table class="tabela">
    <thead><tr><th></th><th>Código</th><th>Nome</th><th>Categoria</th><th>Situação</th><th class="coluna-acoes"></th></tr></thead>
    <tbody>
      <?php if(empty($produtos)): ?><tr><td colspan="6" style="text-align:center;padding:32px;color:#888;">Nenhum produto encontrado.</td></tr>
      <?php else: foreach($produtos as $p): ?>
      <tr>
        <td></td>
        <td><span class="cod-clicavel" onclick="Scopi.abrirRegistro('modalProduto','formProduto','/produtos/dados',<?= $p['id'] ?>,'visualizar')"><?= Auxiliares::escapar($p['codigo']) ?></span></td>
        <td><?= Auxiliares::escapar($p['nome']) ?></td>
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
    <div class="modal-cabecalho"><div class="modal-titulo"><img src="<?= BASE_URL ?>/public/assets/icons/iconeCadastro.svg" alt=""><span>Produto</span></div><button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalProduto')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt=""></button></div>
    <div class="modal-abas"><button class="aba-btn ativa" data-aba="visualizar" onclick="Scopi.ativarAba('modalProduto','visualizar')">Visualizar</button><button class="aba-btn" data-aba="editar" onclick="Scopi.ativarAba('modalProduto','editar')">Editar</button></div>
    <div class="modal-corpo">
      <div class="conteudo-aba ativo" data-aba="visualizar">
        <div class="grade-visualizar">
          <div class="campo-visualizar"><span class="rotulo">Código</span><span class="valor" data-campo="codigo">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Situação</span><span class="valor"><span class="badge" data-badge="situacao">—</span></span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Nome</span><span class="valor" data-campo="nome">—</span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Categoria</span><span class="valor" data-campo="nome_categoria">—</span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Descrição</span><span class="valor" data-campo="descricao">—</span></div>
        </div>
      </div>
      <div class="conteudo-aba" data-aba="editar">
        <form id="formProduto" onsubmit="event.preventDefault();Scopi.enviarFormulario('formProduto','modalProduto','/produtos/salvar')">
          <input type="hidden" name="id" value="0">
          <div class="grade-form" style="grid-template-columns:1fr;">
            <div class="campo-form"><label>Nome *</label><input type="text" name="nome" required></div>
            <div class="campo-form"><label>Categoria</label><select name="categoria_id"><option value="">Selecione...</option><?php foreach($categorias as $c): ?><option value="<?= $c['id'] ?>"><?= Auxiliares::escapar($c['nome']) ?></option><?php endforeach; ?></select></div>
            <div class="campo-form"><label>Descrição</label><textarea name="descricao" rows="3"></textarea></div>
          </div>
        </form>
      </div>
    </div>
    <div class="modal-rodape"><button class="btn btn-secundario" onclick="Scopi.fecharModal('modalProduto')">Fechar</button><button class="btn btn-primario btn-salvar" onclick="Scopi.enviarFormulario('formProduto','modalProduto','/produtos/salvar')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Salvar</button></div>
  </div>
</div>
