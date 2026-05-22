<?php use Config\Auxiliares; ?>
<script>document.getElementById('topbarTitulo').textContent = 'Ordens de Compra';</script>
<div class="pagina-cabecalho"><h1 class="pagina-titulo">Ordens de Compra</h1><p class="pagina-subtitulo">Controle e acompanhamento das ordens de compra</p></div>
<div class="painel-filtros">
  <div class="filtros-cabecalho"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt=""><span>Filtros</span></div>
  <form method="GET" action="<?= BASE_URL ?>/ordens"><div class="filtros-campos">
    <div class="campo-filtro"><label>Número</label><input type="text" name="numero" value="<?= Auxiliares::escapar($filtros['numero']??'') ?>" placeholder="OC-..."></div>
    <div class="campo-filtro"><label>Status</label><select name="status"><option value="">Todos</option><option value="aberta">Aberta</option><option value="autorizada">Autorizada</option><option value="enviada">Enviada</option><option value="concluida">Concluída</option><option value="cancelada">Cancelada</option></select></div>
    <div class="campo-filtro" style="flex:0;align-self:flex-end;"><button type="submit" class="btn btn-filtrar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt=""> Buscar</button></div>
  </div></form>
</div>
<div class="barra-acoes">
  <div class="grupo-botoes"><button class="btn btn-primario" onclick="Scopi.abrirCadastro('modalOrdem','formOrdem')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Nova Ordem</button><button class="btn btn-secundario"><img src="<?= BASE_URL ?>/public/assets/icons/iconeDownload.svg" alt=""> Exportar</button></div>
  <span style="font-size:.82rem;color:#888;"><?= count($ordens) ?> registro(s)</span>
</div>
<div class="tabela-container">
  <table class="tabela">
    <thead><tr><th></th><th>Número</th><th>Fornecedor</th><th>Emissão</th><th>Valor Total</th><th>Status</th><th class="coluna-acoes"></th></tr></thead>
    <tbody>
      <?php if(empty($ordens)): ?><tr><td colspan="7" style="text-align:center;padding:32px;color:#888;">Nenhuma ordem encontrada.</td></tr>
      <?php else: foreach($ordens as $o): ?>
      <tr>
        <td></td>
        <td><span class="cod-clicavel" onclick="Scopi.abrirRegistro('modalOrdem','formOrdem','/ordens/dados',<?= $o['id'] ?>,'visualizar')"><?= Auxiliares::escapar($o['numero']??$o['id']) ?></span></td>
        <td><?= Auxiliares::escapar($o['nome_fornecedor']??'—') ?></td>
        <td><?= !empty($o['emitido_em'])?date('d/m/Y',strtotime($o['emitido_em'])):'—' ?></td>
        <td>R$ <?= number_format($o['valor_total']??0,2,',','.') ?></td>
        <td><span class="badge badge-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
        <td class="coluna-acoes"><button class="btn-icone btn-editar-linha" onclick="Scopi.abrirRegistro('modalOrdem','formOrdem','/ordens/dados',<?= $o['id'] ?>,'editar')" title="Editar"><img src="<?= BASE_URL ?>/public/assets/icons/iconeEditar.svg" alt=""></button></td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
<div class="overlay-modal" id="modalOrdem">
  <div class="modal modal-largo">
    <div class="modal-cabecalho"><div class="modal-titulo"><img src="<?= BASE_URL ?>/public/assets/icons/iconeOC.svg" alt=""><span>Ordem de Compra</span></div><button class="btn-fechar-modal" onclick="Scopi.fecharModal('modalOrdem')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeFechar.svg" alt=""></button></div>
    <div class="modal-abas"><button class="aba-btn ativa" data-aba="visualizar" onclick="Scopi.ativarAba('modalOrdem','visualizar')">Visualizar</button><button class="aba-btn" data-aba="editar" onclick="Scopi.ativarAba('modalOrdem','editar')">Editar</button></div>
    <div class="modal-corpo">
      <div class="conteudo-aba ativo" data-aba="visualizar">
        <div class="grade-visualizar">
          <div class="campo-visualizar"><span class="rotulo">Número</span><span class="valor" data-campo="numero">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Status</span><span class="valor"><span class="badge" data-badge="status">—</span></span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Fornecedor</span><span class="valor" data-campo="nome_fornecedor">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Condição de Pagamento</span><span class="valor" data-campo="condicao_pagamento">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Prazo de Entrega</span><span class="valor" data-campo="prazo_entrega">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Desconto (R$)</span><span class="valor" data-campo="desconto_valor">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Desconto (%)</span><span class="valor" data-campo="desconto_percentual">—</span></div>
          <div class="campo-visualizar"><span class="rotulo">Valor Total</span><span class="valor" data-campo="valor_total">—</span></div>
          <div class="campo-visualizar campo-completo"><span class="rotulo">Observação</span><span class="valor" data-campo="observacao">—</span></div>
        </div>
      </div>
      <div class="conteudo-aba" data-aba="editar">
        <form id="formOrdem" onsubmit="event.preventDefault();Scopi.enviarFormulario('formOrdem','modalOrdem','/ordens/salvar')">
          <input type="hidden" name="id" value="0">
          <div class="grade-form">
            <div class="campo-form campo-completo">
              <label>Fornecedor *</label>
              <select name="fornecedor_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($fornecedores as $f): ?>
                  <option value="<?= $f['id'] ?>"><?= Auxiliares::escapar($f['razao_social']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="campo-form">
              <label>Condição de Pagamento</label>
              <select name="condicao_pagamento_id">
                <option value="">Selecione...</option>
                <?php foreach ($condicoesPagamento as $cp): ?>
                  <option value="<?= $cp['id'] ?>"><?= Auxiliares::escapar($cp['descricao']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="campo-form"><label>Prazo de Entrega</label><input type="text" name="prazo_entrega"></div>
            <div class="campo-form"><label>Desconto (R$)</label><input type="number" step="0.01" name="desconto_valor" value="0.00"></div>
            <div class="campo-form"><label>Desconto (%)</label><input type="number" step="0.01" name="desconto_percentual" value="0.00"></div>
            <div class="campo-form"><label>Valor Total (R$)</label><input type="number" step="0.01" name="valor_total" value="0.00"></div>
            <div class="campo-form campo-completo"><label>Observação</label><textarea name="observacao" rows="3"></textarea></div>
          </div>
        </form>
      </div>
    </div>
    <div class="modal-rodape"><button class="btn btn-secundario" onclick="Scopi.fecharModal('modalOrdem')">Fechar</button><button class="btn btn-primario btn-salvar" onclick="Scopi.enviarFormulario('formOrdem','modalOrdem','/ordens/salvar')"><img src="<?= BASE_URL ?>/public/assets/icons/iconeInserir.svg" alt=""> Salvar</button></div>
  </div>
</div>
