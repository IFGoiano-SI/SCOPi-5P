<?php
use Config\Auxiliares;
$cotacao = $cotacao ?? [];
$fornecedor = $fornecedor ?? [];
$itens = $itens ?? [];
$respostasAnteriores = $respostasAnteriores ?? [];
$totalEnvios = $totalEnvios ?? 0;
?>

<div class="container-form" style="max-width:1200px;">

  <div style="margin-bottom:30px;">
    <h1 style="margin:0 0 10px 0;">Responder Cotação - <?= Auxiliares::escapar($cotacao['numero'] ?? '') ?></h1>
  </div>

  <!-- CONTEXTO (Read-only) -->
  <div style="background:#f8f9fa; border:1px solid #dee2e6; border-radius:8px; padding:20px; margin-bottom:30px;">
    <h3 style="margin-top:0; margin-bottom:15px; font-size:0.95rem; text-transform:uppercase; color:#555;">Informações da Cotação</h3>

    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px;">
      <div>
        <label style="display:block; font-size:0.75rem; color:#666; margin-bottom:5px;">Número Cotação</label>
        <div style="font-size:0.95rem; font-weight:600;"><?= Auxiliares::escapar($cotacao['numero'] ?? '—') ?></div>
      </div>
      <div>
        <label style="display:block; font-size:0.75rem; color:#666; margin-bottom:5px;">Comprador</label>
        <div style="font-size:0.95rem;"><?= Auxiliares::escapar($cotacao['nome_comprador'] ?? '—') ?></div>
      </div>
      <div>
        <label style="display:block; font-size:0.75rem; color:#666; margin-bottom:5px;">Data Abertura</label>
        <div style="font-size:0.95rem;"><?= !empty($cotacao['data_abertura']) ? date('d/m/Y', strtotime($cotacao['data_abertura'])) : '—' ?></div>
      </div>
      <div>
        <label style="display:block; font-size:0.75rem; color:#666; margin-bottom:5px;">Data Encerramento</label>
        <div style="font-size:0.95rem;"><?= !empty($cotacao['data_encerramento']) ? date('d/m/Y', strtotime($cotacao['data_encerramento'])) : '—' ?></div>
      </div>
    </div>
  </div>

  <!-- FORMULÁRIO DE RESPOSTA -->
  <form id="formResposta" method="POST" action="<?= BASE_URL ?>/cotacoes/salvar-resposta" style="display:none;">
    <input type="hidden" name="cotacao_id" value="<?= (int)($cotacao['id'] ?? 0) ?>">
    <input type="hidden" name="cotacao_fornecedor_id" value="<?= (int)($fornecedor['id'] ?? 0) ?>">
    <input type="hidden" name="itens_json" id="itensJson" value="">
  </form>

  <!-- DADOS GLOBAIS -->
  <div style="background:#fff; border:1px solid #dee2e6; border-radius:8px; padding:20px; margin-bottom:30px;">
    <h3 style="margin-top:0; margin-bottom:20px; font-size:0.95rem; text-transform:uppercase; color:#555;">Dados Globais (Preencha uma única vez)</h3>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
      <div class="campo-form">
        <label style="font-size:0.75rem;">Transportadora</label>
        <input type="text" id="transportadora" class="campo-input" placeholder="Ex: XYZ Transportes"
               value="<?= Auxiliares::escapar($fornecedor['transportadora'] ?? '') ?>">
      </div>
      <div class="campo-form">
        <label style="font-size:0.75rem;">CNPJ Transportadora</label>
        <input type="text" id="cnpj_transportadora" class="campo-input" placeholder="00.000.000/0000-00"
               value="<?= Auxiliares::escapar($fornecedor['cnpj_transportadora'] ?? '') ?>">
      </div>
      <div class="campo-form">
        <label style="font-size:0.75rem;">Modalidade Frete</label>
        <select id="modalidade_frete" class="campo-input">
          <option value="">Selecione...</option>
          <option value="CIF" <?= ($fornecedor['modalidade_frete'] ?? '') === 'CIF' ? 'selected' : '' ?>>CIF</option>
          <option value="FOB" <?= ($fornecedor['modalidade_frete'] ?? '') === 'FOB' ? 'selected' : '' ?>>FOB</option>
        </select>
      </div>
    </div>

    <div class="campo-form">
      <label style="font-size:0.75rem;">Observação Geral</label>
      <textarea id="observacao_geral" class="campo-input" style="min-height:100px; resize:vertical;" placeholder="Observações gerais da resposta"><?= Auxiliares::escapar($fornecedor['observacao'] ?? '') ?></textarea>
    </div>
  </div>

  <!-- ITENS DA COTAÇÃO -->
  <div style="background:#fff; border:1px solid #dee2e6; border-radius:8px; padding:20px; margin-bottom:30px;">
    <h3 style="margin-top:0; margin-bottom:20px; font-size:0.95rem; text-transform:uppercase; color:#555;">Itens da Cotação</h3>

    <div style="overflow-x:auto;">
      <table class="tabela" style="width:100%; font-size:0.85rem;">
        <thead>
          <tr>
            <th style="width:60px; padding:8px 12px;">Item</th>
            <th style="padding:8px 12px;">Produto</th>
            <th style="width:80px; padding:8px 12px;">Modelo</th>
            <th style="width:80px; text-align:right; padding:8px 12px;">Qtd</th>
            <th style="width:100px; text-align:right; padding:8px 12px;">Valor Unit.</th>
            <th style="width:80px; text-align:center; padding:8px 12px;">Prazo (dias)</th>
            <th style="width:100px; padding:8px 12px;">Garantia</th>
            <th style="width:80px; text-align:right; padding:8px 12px;">Taxas</th>
            <th style="width:100px; padding:8px 12px;">Cond. Pag.</th>
            <th style="width:60px; text-align:center; padding:8px 12px;">Disp.</th>
          </tr>
        </thead>
        <tbody id="tabelaItens">
        </tbody>
      </table>
    </div>

    <div style="margin-top:20px;">
      <p style="font-size:0.8rem; color:#666; margin:0 0 15px 0;">* Campos obrigatórios: Valor Unitário, Prazo</p>
    </div>
  </div>

  <!-- BOTÃO ENVIAR -->
  <div style="text-align:center; margin-bottom:30px;">
    <button type="button" class="btn btn-primario" onclick="confirmarEEnviar()" style="padding:12px 40px; font-size:1rem;">
      Confirmar e Enviar
    </button>
  </div>

  <!-- RODAPÉ COM CONTADOR -->
  <div style="background:#f8f9fa; border-top:1px solid #dee2e6; padding:15px 20px; margin-left:-20px; margin-right:-20px; margin-bottom:-20px; border-radius:0 0 8px 8px;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
      <div>
        <strong>Envios Realizados:</strong> <span id="contadorEnvios"><?= (int)$totalEnvios ?></span>
      </div>
      <div style="font-size:0.85rem; color:#666;">
        <?php if (!empty($fornecedor['respondido_em'])): ?>
          Última atualização: <?= date('d/m/Y \à\s H:i', strtotime($fornecedor['respondido_em'])) ?>
        <?php else: ?>
          Nenhum envio ainda
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<script>
const _cotacaoId = <?= (int)($cotacao['id'] ?? 0) ?>;
const _cotacaoFornecedorId = <?= (int)($fornecedor['id'] ?? 0) ?>;
const _itensAgrupados = <?= json_encode($itens) ?>;
const _respostasAnteriores = <?= json_encode($respostasAnteriores) ?>;

document.addEventListener('DOMContentLoaded', function() {
  carregarItens();
});

function carregarItens() {
  const tbody = document.getElementById('tabelaItens');
  tbody.innerHTML = '';

  _itensAgrupados.forEach((item, idx) => {
    const respostaAnterior = _respostasAnteriores[item.produto_id] || {};

    const tr = document.createElement('tr');
    tr.dataset.produtoId = item.produto_id;
    tr.dataset.itemIds = item.item_ids;
    tr.innerHTML = `
      <td style="padding:10px 12px; text-align:center;">${item.numeros_item}</td>
      <td style="padding:10px 12px;"><strong>${Auxiliares.escapar(item.nome_produto)}</strong></td>
      <td style="padding:10px 12px;">
        <input type="text" class="campo-input campo-modelo" style="width:100%;"
               value="${Auxiliares.escapar(respostaAnterior.modelo || '')}"
               placeholder="Ex: v2.1">
      </td>
      <td style="padding:10px 12px; text-align:right;">
        <span style="font-size:0.9rem;">${parseFloat(item.quantidade_total).toFixed(2)}</span>
      </td>
      <td style="padding:10px 12px;">
        <input type="number" class="campo-input campo-valor" style="width:100%;"
               step="0.01" min="0" placeholder="0.00"
               value="${respostaAnterior.preco_unitario || ''}" required>
      </td>
      <td style="padding:10px 12px;">
        <input type="number" class="campo-input campo-prazo" style="width:100%;"
               min="1" placeholder="Dias"
               value="${respostaAnterior.prazo_entrega || ''}" required>
      </td>
      <td style="padding:10px 12px;">
        <input type="text" class="campo-input campo-garantia" style="width:100%;"
               placeholder="Ex: 12 meses"
               value="${Auxiliares.escapar(respostaAnterior.garantia || '')}">
      </td>
      <td style="padding:10px 12px;">
        <input type="number" class="campo-input campo-taxas" style="width:100%;"
               step="0.01" min="0" placeholder="0.00"
               value="${respostaAnterior.taxas || ''}">
      </td>
      <td style="padding:10px 12px;">
        <select class="campo-input campo-cond-pag" style="width:100%;">
          <option value="">—</option>
          ${window.condicoesPagamento ? window.condicoesPagamento.map(cp =>
            `<option value="${cp.id}" ${respostaAnterior.condicao_pagamento_id == cp.id ? 'selected' : ''}>${Auxiliares.escapar(cp.descricao)}</option>`
          ).join('') : ''}
        </select>
      </td>
      <td style="padding:10px 12px; text-align:center;">
        <input type="checkbox" class="campo-disponivel"
               ${respostaAnterior.disponivel !== false ? 'checked' : ''}>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function confirmarEEnviar() {
  if (!validarFormulario()) return;

  const mensagem = `Tem certeza que deseja enviar a resposta? Esta é a submissão nº ${document.getElementById('contadorEnvios').textContent + 1}.`;

  Scopi.confirmar(mensagem, () => {
    enviarResposta();
  });
}

function validarFormulario() {
  const campos = document.querySelectorAll('.campo-valor, .campo-prazo');
  let valido = true;

  campos.forEach(campo => {
    if (!campo.value.trim()) {
      campo.style.borderColor = '#dc3545';
      valido = false;
    } else {
      campo.style.borderColor = '';
    }
  });

  if (!valido) {
    Scopi.toast('alerta', 'Preencha todos os campos obrigatórios (Valor Unitário e Prazo).');
    return false;
  }

  return true;
}

function enviarResposta() {
  const itens = [];
  document.querySelectorAll('#tabelaItens tr').forEach(tr => {
    const produtoId = tr.dataset.produtoId;
    const itemAgrupado = _itensAgrupados.find(i => i.produto_id == produtoId);

    itens.push({
      produto_id: produtoId,
      item_ids: tr.dataset.itemIds.split(',').map(id => parseInt(id)),
      modelo: tr.querySelector('.campo-modelo').value.trim(),
      quantidade_total: parseFloat(itemAgrupado.quantidade_total),
      preco_unitario: parseFloat(tr.querySelector('.campo-valor').value),
      prazo_entrega: parseInt(tr.querySelector('.campo-prazo').value),
      garantia: tr.querySelector('.campo-garantia').value.trim(),
      taxas: parseFloat(tr.querySelector('.campo-taxas').value) || 0,
      condicao_pagamento_id: parseInt(tr.querySelector('.campo-cond-pag').value) || null,
      disponivel: tr.querySelector('.campo-disponivel').checked ? 1 : 0
    });
  });

  const dados = {
    cotacao_id: _cotacaoId,
    cotacao_fornecedor_id: _cotacaoFornecedorId,
    transportadora: document.getElementById('transportadora').value.trim(),
    cnpj_transportadora: document.getElementById('cnpj_transportadora').value.trim(),
    modalidade_frete: document.getElementById('modalidade_frete').value,
    observacao: document.getElementById('observacao_geral').value.trim(),
    itens: itens
  };

  fetch(Scopi.url('/cotacoes/salvar-resposta'), {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    body: JSON.stringify(dados)
  })
  .then(r => r.json())
  .then(json => {
    if (json.sucesso) {
      Scopi.toast('sucesso', 'Resposta enviada com sucesso!');
      setTimeout(() => window.location.href = Scopi.url('/cotacoes'), 1500);
    } else {
      Scopi.toast('alerta', json.mensagem || 'Erro ao enviar resposta.');
    }
  })
  .catch(e => {
    console.error(e);
    Scopi.toast('alerta', 'Erro ao enviar resposta.');
  });
}

// Objeto auxiliar (será carregado do servidor)
const Auxiliares = {
  escapar: function(texto) {
    const div = document.createElement('div');
    div.textContent = texto;
    return div.innerHTML;
  }
};
</script>
