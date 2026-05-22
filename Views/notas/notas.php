<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Notas Fiscais';</script>
<div class="pagina-cabecalho"><h1 class="pagina-titulo">Notas de Entrada</h1><p class="pagina-subtitulo">Gerenciamento de notas fiscais de terceiros</p></div>
<div class="painel-filtros">
  <div class="filtros-cabecalho"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt=""><span>Filtros</span></div>
  <form method="GET" action="<?= BASE_URL ?>/notas"><div class="filtros-campos">
    <div class="campo-filtro"><label>Número NF</label><input type="text" name="numero" value="<?= Auxiliares::escapar($filtros['numero']??'') ?>" placeholder="Número..."></div>
    <div class="campo-filtro"><label>Chave de Acesso</label><input type="text" name="chave" value="<?= Auxiliares::escapar($filtros['chave']??'') ?>" placeholder="Chave NF-e..."></div>
    <div class="campo-filtro"><label>A partir de</label><input type="date" name="periodo" value="<?= Auxiliares::escapar($filtros['periodo']??'') ?>"></div>
    <div class="campo-filtro" style="flex:0;align-self:flex-end;"><button type="submit" class="btn btn-filtrar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt=""> Buscar</button></div>
  </div></form>
</div>
<div class="barra-acoes">
  <div class="grupo-botoes">
    <button class="btn btn-primario" onclick="Scopi.abrirCadastro('modalNota','formNota')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Lançar Nota</button>
    <button class="btn btn-secundario" onclick="Scopi.abrirModal('modalImportar')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Importar NF-e</button>
    <button class="btn btn-secundario"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Exportar</button>
  </div>
  <span style="font-size:.82rem;color:#888;"><?= count($notas) ?> registro(s)</span>
</div>
<div class="tabela-container">
  <table class="tabela">
    <thead><tr><th></th><th>Número</th><th>Fornecedor</th><th>Emissão</th><th>Valor Total</th><th class="coluna-acoes"></th></tr></thead>
    <tbody>
      <?php if(empty($notas)): ?><tr><td colspan="6" style="text-align:center;padding:32px;color:#888;">Nenhuma nota encontrada.</td></tr>
      <?php else: foreach($notas as $n): ?>
      <tr>
        <td></td>
        <td><span class="cod-clicavel" onclick="Scopi.abrirRegistro('modalNota','formNota','/notas/dados',<?= $n['id'] ?>,'visualizar')"><?= Auxiliares::escapar($n['numero']) ?></span></td>
        <td><?= Auxiliares::escapar($n['nome_fornecedor']??'—') ?></td>
        <td><?= !empty($n['data_emissao'])?date('d/m/Y',strtotime($n['data_emissao'])):'—' ?></td>
        <td>R$ <?= number_format($n['valor_total']??0,2,',','.') ?></td>
        <td class="coluna-acoes"><button class="btn-icone btn-editar-linha" onclick="Scopi.abrirRegistro('modalNota','formNota','/notas/dados',<?= $n['id'] ?>,'visualizar')" title="Ver"><img src="<?= BASE_URL ?>/public/assets/icons/iconeEditar.svg" alt=""></button></td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Nota -->
<div class="overlay-modal" id="modalNota">
  <div class="modal modal-largo">
    <div class="modal-cabecalho"><div class="modal-titulo"><img src="<?= BASE_URL ?>/public/assets/icons/iconeNF.svg" alt=""><span>Nota Fiscal</span></div><button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalNota')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt=""></button></div>
    <div class="modal-abas"><button class="aba-btn ativa" data-aba="visualizar" onclick="Scopi.ativarAba('modalNota','visualizar')">Visualizar</button><button class="aba-btn" data-aba="editar" onclick="Scopi.ativarAba('modalNota','editar')">Lançar</button></div>
    <div class="modal-corpo">
      <div class="conteudo-aba ativo" data-aba="visualizar">
        <div class="grade-visualizar">
          <div class="campo-visualizar"><span class="rotulo">Número</span><span class="valor" data-campo="numero">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Data de Emissão</span><span class="valor" data-campo="data_emissao">—</span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Chave de Acesso</span><span class="valor" data-campo="chave_acesso" style="font-size:.78rem;word-break:break-all;">—</span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Fornecedor</span><span class="valor" data-campo="nome_fornecedor">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Valor Total</span><span class="valor" data-campo="valor_total">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Natureza da Operação</span><span class="valor" data-campo="natureza_operacao">—</span></div>
        </div>
      </div>
      <div class="conteudo-aba" data-aba="editar">
        <form id="formNota" onsubmit="event.preventDefault();Scopi.enviarFormulario('formNota','modalNota','/notas/salvar')">
          <input type="hidden" name="id" value="0">
          <div class="grade-form">
            <div class="campo-form campo-completo"><label>Chave de Acesso NF-e *</label><input type="text" name="chave_acesso" required maxlength="44" placeholder="44 dígitos"></div>
            <div class="campo-form"><label>Número NF</label><input type="text" name="numero"></div>
            <div class="campo-form"><label>Data de Emissão</label><input type="date" name="data_emissao"></div>
            <div class="campo-form campo-completo"><label>Fornecedor *</label><select name="fornecedor_id" required><option value="">Selecione...</option></select></div>
            <div class="campo-form"><label>Valor Total</label><input type="number" name="valor_total" step="0.01"></div>
            <div class="campo-form"><label>Natureza da Operação</label><input type="text" name="natureza_operacao"></div>
            <div class="campo-form campo-completo"><label>Observações</label><textarea name="observacoes" rows="2"></textarea></div>
          </div>
        </form>
      </div>
    </div>
    <div class="modal-rodape"><button class="btn btn-secundario" onclick="Scopi.fecharModal('modalNota')">Fechar</button><button class="btn btn-primario btn-salvar" onclick="Scopi.enviarFormulario('formNota','modalNota','/notas/salvar')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Salvar</button></div>
  </div>
</div>

<!-- Modal Importar NF-e -->
<div class="overlay-modal" id="modalImportar">
  <div class="modal modal-estreito">
    <div class="modal-cabecalho"><div class="modal-titulo"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""><span>Importar NF-e</span></div><button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalImportar')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt=""></button></div>
    <div class="modal-corpo">
      <form id="formImportar" enctype="multipart/form-data" onsubmit="event.preventDefault();Scopi.enviarFormulario('formImportar','modalImportar','/notas/importar')">
        <div class="grade-form" style="grid-template-columns:1fr;">
          <div class="campo-form"><label>Arquivo XML (NF-e) *</label><input type="file" name="arquivo_xml" accept=".xml" required></div>
          <p style="font-size:.82rem;color:#888;">Selecione o arquivo XML da NF-e para importação automática dos dados.</p>
        </div>
      </form>
    </div>
    <div class="modal-rodape"><button class="btn btn-secundario" onclick="Scopi.fecharModal('modalImportar')">Cancelar</button><button class="btn btn-primario" onclick="Scopi.enviarFormulario('formImportar','modalImportar','/notas/importar')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Importar</button></div>
  </div>
</div>
