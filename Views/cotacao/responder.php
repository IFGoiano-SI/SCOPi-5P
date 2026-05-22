<?php
/**
 * Views/cotacao/responder.php
 * Portal do Fornecedor — Responder Cotação
 */
use Config\Auxiliares;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SCOPi - Responder Cotação #<?= htmlspecialchars($cf['numero_cotacao'] ?? '') ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/estilo.css">
  <style>
    body {
      background: var(--fundo-app);
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
    
    .topbar-fornecedor {
      height: var(--topbar-h);
      background: linear-gradient(135deg, var(--escura), var(--media));
      color: var(--branco);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 30px;
      box-shadow: 0 4px 12px rgba(35,0,60,.1);
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .topbar-fornecedor .topbar-logo {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .topbar-fornecedor .topbar-logo img.icone-logo {
      width: 32px;
      height: 32px;
    }

    .topbar-fornecedor .topbar-logo img.logotipo {
      height: 20px;
    }

    .topbar-fornecedor .badge-portal {
      background: rgba(254,249,255,0.15);
      color: var(--branco);
      padding: 3px 10px;
      border-radius: 12px;
      font-size: 0.72rem;
      font-weight: 600;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      margin-left: 8px;
    }

    .topbar-fornecedor .topbar-usr-info {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .topbar-fornecedor .topbar-usr-info img.avatar {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      border: 2px solid var(--suave);
      background: var(--suave);
    }

    .topbar-fornecedor .topbar-usr-info .info-usr {
      display: flex;
      flex-direction: column;
      text-align: right;
    }

    .topbar-fornecedor .topbar-usr-info .nome-usr {
      font-size: 0.85rem;
      font-weight: 600;
    }

    .topbar-fornecedor .topbar-usr-info .perfil-usr {
      font-size: 0.72rem;
      color: var(--suave);
    }

    .btn-sair-fornecedor {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(254,249,255,0.1);
      border: 1px solid rgba(254,249,255,0.2);
      color: var(--branco);
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 0.78rem;
      font-weight: 500;
      cursor: pointer;
      transition: all var(--trans);
      text-decoration: none;
      margin-left: 15px;
      gap: 6px;
    }

    .btn-sair-fornecedor:hover {
      background: var(--alerta);
      border-color: var(--alerta);
      color: var(--branco);
    }

    .btn-sair-fornecedor img {
      width: 14px;
      filter: brightness(0) invert(1);
    }

    .pagina-responder {
      max-width: 1100px;
      width: 100%;
      margin: 30px auto;
      padding: 0 20px 60px;
      display: flex;
      flex-direction: column;
      gap: 25px;
    }

    /* Banner Informativo */
    .banner-status {
      border-radius: var(--raio);
      padding: 16px 20px;
      display: flex;
      align-items: center;
      gap: 15px;
      font-weight: 500;
      font-size: 0.85rem;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .banner-status.fechada {
      background: #FFF3E0;
      color: #E65100;
      border-left: 5px solid #FF9800;
    }

    .banner-status.sucesso {
      background: #E8F5E9;
      color: #1B5E20;
      border-left: 5px solid #4CAF50;
    }

    .banner-status img {
      width: 24px;
      height: 24px;
    }

    /* Card da Cotação */
    .card-cotacao {
      background: linear-gradient(135deg, var(--escura) 0%, var(--destaque-alt) 100%);
      color: var(--branco);
      padding: 24px 28px;
      border-radius: var(--raio);
      position: relative;
      box-shadow: var(--sombra);
      overflow: hidden;
    }

    .card-cotacao::before {
      content: '';
      position: absolute;
      right: -50px;
      bottom: -50px;
      width: 200px;
      height: 200px;
      border-radius: 50%;
      background: rgba(254, 249, 255, 0.04);
      pointer-events: none;
    }

    .card-cotacao-topo {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      border-bottom: 1px solid rgba(254,249,255,0.15);
      padding-bottom: 15px;
      margin-bottom: 15px;
    }

    .card-cotacao-titulo {
      font-size: 1.5rem;
      font-weight: 600;
      line-height: 1.2;
    }

    .card-cotacao-subtitulo {
      font-size: 0.85rem;
      color: var(--suave);
      margin-top: 3px;
    }

    .card-cotacao-corpo {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
    }

    .info-item {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .info-item-rotulo {
      font-size: 0.68rem;
      font-weight: 600;
      color: var(--suave);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .info-item-valor {
      font-size: 0.95rem;
      font-weight: 500;
    }

    /* Painel do Formulário */
    .painel-proposta {
      background: var(--fundo);
      border-radius: var(--raio);
      box-shadow: 0 4px 15px rgba(35,0,60,0.06);
      padding: 28px;
    }

    .secao-titulo {
      font-size: 1.05rem;
      font-weight: 600;
      color: var(--media);
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
      border-bottom: 2px solid var(--suave);
      padding-bottom: 8px;
    }

    .secao-titulo img {
      width: 18px;
      height: 18px;
    }

    /* Ajustes na tabela */
    .tabela-responder td {
      vertical-align: middle;
    }

    .input-tabela {
      padding: 6px 10px;
      border: 1px solid var(--borda);
      border-radius: 5px;
      font-family: 'Poppins', sans-serif;
      font-size: 0.78rem;
      color: var(--texto);
      width: 100%;
      background: var(--fundo);
      transition: all var(--trans);
    }

    .input-tabela:focus {
      outline: none;
      border-color: var(--destaque);
      box-shadow: 0 0 0 3px rgba(145,51,210,.1);
    }

    .input-tabela[readonly] {
      background: #f1ebf5;
      color: #777;
      border-color: var(--borda);
      cursor: not-allowed;
    }

    /* Painel de Totais */
    .painel-totais {
      background: #f8f3fc;
      border: 1px solid var(--borda);
      border-radius: var(--raio);
      padding: 20px;
      margin-top: 20px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      max-width: 450px;
      margin-left: auto;
    }

    .total-linha {
      display: flex;
      justify-content: space-between;
      font-size: 0.82rem;
      color: #555;
    }

    .total-linha.geral {
      border-top: 1px solid var(--borda);
      padding-top: 10px;
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--media);
    }

    .acoes-fim {
      margin-top: 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    /* Estilo de desabilitado geral */
    .form-desabilitado input, 
    .form-desabilitado select, 
    .form-desabilitado textarea {
      background: #f1ebf5 !important;
      color: #666 !important;
      cursor: not-allowed !important;
      border-color: var(--borda) !important;
    }
  </style>
</head>
<body>

  <!-- TOPBAR -->
  <header class="topbar-fornecedor">
    <div class="topbar-logo">
      <img src="<?= BASE_URL ?>/public/assets/icons/iconeSCOPi.svg" alt="SCOPi" class="icone-logo">
      <img src="<?= BASE_URL ?>/public/assets/icons/iconeLogotipo.svg" alt="SCOPi" class="logotipo">
      <span class="badge-portal">Portal do Fornecedor</span>
    </div>
    
    <div class="topbar-usr-info">
      <div class="info-usr">
        <div class="nome-usr"><?= htmlspecialchars($cf['razao_social'] ?? '') ?></div>
        <div class="perfil-usr">CNPJ: <?= htmlspecialchars($cf['cnpj'] ?? '') ?></div>
      </div>
      <img src="<?= BASE_URL ?>/public/assets/icons/iconeUser.svg" alt="" class="avatar">
      
      <a href="<?= BASE_URL ?>/login/sair" class="btn-sair-fornecedor" title="Sair do Portal">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeSair.svg" alt="">
        Sair
      </a>
    </div>
  </header>

  <!-- CONTEUDO PRINCIPAL -->
  <main class="pagina-responder">

    <!-- Mensagens Flash -->
    <?php
    $flash = Auxiliares::obterFlash();
    if (!empty($flash)):
    ?>
      <div class="mensagem-flash flash-<?= $flash['color'] ?? 'info' ?>">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeAlerta.svg" alt="">
        <?= htmlspecialchars($flash['texto']) ?>
      </div>
    <?php endif; ?>

    <!-- Banner de Cotação Encerrada -->
    <?php if ($fechada): ?>
      <div class="banner-status fechada">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeAlerta.svg" alt="">
        <span><strong>Atenção:</strong> Esta cotação já está encerrada, concluída ou cancelada. Esta proposta é apenas para visualização e não pode ser editada.</span>
      </div>
    <?php elseif ($cf['status'] === 'respondido'): ?>
      <div class="banner-status sucesso">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeVerificado.svg" alt="" style="filter: hue-rotate(90deg) brightness(0.8);">
        <span>Você já enviou uma proposta para esta cotação em <?= date('d/m/Y \à\s H:i', strtotime($cf['respondido_em'])) ?>. Se necessário, você pode alterar os dados e reenviar a proposta até o encerramento.</span>
      </div>
    <?php endif; ?>

    <!-- Card de Cabeçalho da Cotação -->
    <section class="card-cotacao">
      <div class="card-cotacao-topo">
        <div>
          <h1 class="card-cotacao-titulo">Cotação #<?= htmlspecialchars($cf['numero_cotacao'] ?? '') ?></h1>
          <p class="card-cotacao-subtitulo">Por favor, preencha as condições comerciais e os valores de cada item abaixo.</p>
        </div>
        <div>
          <span class="badge" style="background: rgba(254,249,255,0.2); color: var(--branco); font-size: 0.72rem; padding: 4px 12px; border-radius: 12px; font-weight: 600;">
            Status: <?= htmlspecialchars(ucfirst($cf['status_cotacao'] ?? '')) ?>
          </span>
        </div>
      </div>
      
      <div class="card-cotacao-corpo">
        <div class="info-item">
          <span class="info-item-rotulo">Empresa Solicitante</span>
          <span class="info-item-valor">SCOPi Tecnologia</span>
        </div>
        <div class="info-item">
          <span class="info-item-rotulo">Participante</span>
          <span class="info-item-valor"><?= htmlspecialchars($cf['razao_social'] ?? '') ?></span>
        </div>
        <div class="info-item">
          <span class="info-item-rotulo">CNPJ Fornecedor</span>
          <span class="info-item-valor"><?= htmlspecialchars($cf['cnpj'] ?? '') ?></span>
        </div>
        <div class="info-item">
          <span class="info-item-rotulo">Situação do Convite</span>
          <span class="info-item-valor" style="text-transform: capitalize;"><?= htmlspecialchars($cf['status'] ?? '') ?></span>
        </div>
      </div>
    </section>

    <!-- Formulário da Proposta -->
    <form id="formProposta" class="<?= $fechada ? 'form-desabilitado' : '' ?>" onsubmit="enviarProposta(event)">
      <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
      
      <div class="painel-proposta">
        
        <!-- Condições Comerciais -->
        <h2 class="secao-titulo">
          <img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt="">
          Condições Comerciais
        </h2>
        
        <div class="grade-form" style="margin-bottom: 30px;">
          <div class="campo-form">
            <label for="modalidade_frete">Modalidade do Frete *</label>
            <select name="modalidade_frete" id="modalidade_frete" required <?= $fechada ? 'disabled' : '' ?>>
              <option value="">Selecione...</option>
              <option value="CIF" <?= ($cf['modalidade_frete'] ?? '') === 'CIF' ? 'selected' : '' ?>>CIF - Frete pago pelo Fornecedor</option>
              <option value="FOB" <?= ($cf['modalidade_frete'] ?? '') === 'FOB' ? 'selected' : '' ?>>FOB - Frete pago pelo Cliente</option>
            </select>
          </div>
          
          <div class="campo-form">
            <label for="transportadora">Transportadora Indicada</label>
            <input type="text" name="transportadora" id="transportadora" value="<?= htmlspecialchars($cf['transportadora'] ?? '') ?>" placeholder="Caso aplicável..." <?= $fechada ? 'readonly' : '' ?>>
          </div>

          <div class="campo-form">
            <label for="condicao_pagamento_id">Condição de Pagamento *</label>
            <select name="condicao_pagamento_id" id="condicao_pagamento_id" required <?= $fechada ? 'disabled' : '' ?>>
              <option value="">Selecione...</option>
              <?php foreach ($condicoesPagamento as $cp): ?>
                <option value="<?= $cp['id'] ?>" <?= (int)($cf['condicao_pagamento_id'] ?? 0) === (int)$cp['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cp['descricao']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="campo-form">
            <label for="validade_proposta">Validade da Proposta</label>
            <input type="date" name="validade_proposta" id="validade_proposta" value="<?= htmlspecialchars($cf['validade_proposta'] ?? '') ?>" <?= $fechada ? 'readonly' : '' ?>>
          </div>

          <div class="campo-form">
            <label for="impostos">Valor Estimado de Impostos (R$)</label>
            <input type="number" name="impostos" id="impostos" step="0.01" min="0" value="<?= htmlspecialchars($cf['impostos'] ?? '0.00') ?>" class="calcula-total" <?= $fechada ? 'readonly' : '' ?>>
          </div>

          <div class="campo-form">
            <label for="taxas_adicionais">Outras Despesas / Taxas (R$)</label>
            <input type="number" name="taxas_adicionais" id="taxas_adicionais" step="0.01" min="0" value="<?= htmlspecialchars($cf['taxas_adicionais'] ?? '0.00') ?>" class="calcula-total" <?= $fechada ? 'readonly' : '' ?>>
          </div>

          <div class="campo-form">
            <label for="desconto_valor">Desconto Header (R$)</label>
            <input type="number" name="desconto_valor" id="desconto_valor" step="0.01" min="0" value="<?= htmlspecialchars($cf['desconto_valor'] ?? '0.00') ?>" class="calcula-total" <?= $fechada ? 'readonly' : '' ?>>
          </div>

          <div class="campo-form">
            <label for="desconto_percentual">Desconto Header (%)</label>
            <input type="number" name="desconto_percentual" id="desconto_percentual" step="0.01" min="0" max="100" value="<?= htmlspecialchars($cf['desconto_percentual'] ?? '0.00') ?>" class="calcula-total" <?= $fechada ? 'readonly' : '' ?>>
          </div>

          <div class="campo-form">
            <label for="garantia">Garantia Oferecida</label>
            <input type="text" name="garantia" id="garantia" value="<?= htmlspecialchars($cf['garantia'] ?? '') ?>" placeholder="Ex: 12 meses do fabricante..." <?= $fechada ? 'readonly' : '' ?>>
          </div>

          <div class="campo-form campo-completo">
            <label for="observacao">Observações Gerais</label>
            <textarea name="observacao" id="observacao" rows="3" placeholder="Insira quaisquer observações adicionais sobre sua proposta comercial..." <?= $fechada ? 'readonly' : '' ?>><?= htmlspecialchars($cf['observacao'] ?? '') ?></textarea>
          </div>
        </div>

        <!-- Itens da Cotação -->
        <h2 class="secao-titulo" style="margin-top: 40px;">
          <img src="<?= BASE_URL ?>/public/assets/icons/iconeSolicitacao.svg" alt="">
          Itens Requisitados
        </h2>

        <div class="tabela-container" style="border: 1px solid var(--borda); margin-bottom: 20px;">
          <table class="tabela tabela-responder">
            <thead>
              <tr>
                <th style="width: 100px;">Código</th>
                <th>Nome do Produto</th>
                <th style="width: 90px; text-align: center;">Quantidade</th>
                <th style="width: 140px;">Preço Unitário (R$) *</th>
                <th style="width: 110px;">Prazo Entrega (dias) *</th>
                <th style="width: 120px;">Desconto (R$)</th>
                <th style="width: 120px; text-align: right;">Subtotal (R$)</th>
                <th style="width: 180px;">Observação do Item</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($itens as $item): 
                $prop = $propostasMapeadas[$item['produto_id']] ?? null;
                $precoUnit = $prop ? $prop['preco_unitario'] : '';
                $prazo = $prop ? $prop['prazo_entrega'] : '';
                $descontoItem = $prop ? $prop['desconto_valor'] : '0.00';
                $obsItem = $prop ? $prop['observacao'] : '';
                
                $subtotal = $prop ? (($item['quantidade'] * $prop['preco_unitario']) - $prop['desconto_valor']) : 0;
                if ($subtotal < 0) $subtotal = 0;
              ?>
                <tr class="item-linha" data-produto-id="<?= $item['produto_id'] ?>">
                  <td style="font-weight: 500;"><?= htmlspecialchars($item['codigo_produto']) ?></td>
                  <td><?= htmlspecialchars($item['nome_produto']) ?></td>
                  <td style="text-align: center; font-weight: 600;" class="item-qtd"><?= (int)$item['quantidade'] ?></td>
                  <td>
                    <input type="number" 
                           name="itens[<?= $item['produto_id'] ?>][preco_unitario]" 
                           class="input-tabela item-preco calcula-total" 
                           step="0.01" 
                           min="0.01" 
                           value="<?= $precoUnit ?>" 
                           required 
                           <?= $fechada ? 'readonly' : '' ?> 
                           placeholder="0.00">
                  </td>
                  <td>
                    <input type="number" 
                           name="itens[<?= $item['produto_id'] ?>][prazo_entrega]" 
                           class="input-tabela item-prazo" 
                           min="0" 
                           value="<?= $prazo ?>" 
                           required 
                           <?= $fechada ? 'readonly' : '' ?> 
                           placeholder="dias">
                  </td>
                  <td>
                    <input type="number" 
                           name="itens[<?= $item['produto_id'] ?>][desconto_valor]" 
                           class="input-tabela item-desconto calcula-total" 
                           step="0.01" 
                           min="0" 
                           value="<?= $descontoItem ?>" 
                           <?= $fechada ? 'readonly' : '' ?> 
                           placeholder="0.00">
                  </td>
                  <td style="text-align: right; font-weight: 600; color: var(--media);" class="item-subtotal">
                    R$ <?= number_format($subtotal, 2, ',', '.') ?>
                  </td>
                  <td>
                    <input type="text" 
                           name="itens[<?= $item['produto_id'] ?>][observacao]" 
                           class="input-tabela" 
                           value="<?= htmlspecialchars($obsItem) ?>" 
                           <?= $fechada ? 'readonly' : '' ?> 
                           placeholder="Observações sobre o item...">
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Painel de Resumo / Totais -->
        <div class="painel-totais">
          <div class="total-linha">
            <span>Subtotal dos Itens (c/ desc. item):</span>
            <span id="tot-subtotal">R$ 0,00</span>
          </div>
          <div class="total-linha">
            <span>Impostos:</span>
            <span id="tot-impostos">R$ 0,00</span>
          </div>
          <div class="total-linha">
            <span>Outras Taxas/Despesas:</span>
            <span id="tot-taxas">R$ 0,00</span>
          </div>
          <div class="total-linha">
            <span>Desconto Header (R$):</span>
            <span id="tot-desconto-valor">R$ 0,00</span>
          </div>
          <div class="total-linha">
            <span>Desconto Header (%):</span>
            <span id="tot-desconto-percentual">0,00%</span>
          </div>
          <div class="total-linha geral">
            <span>Total da Proposta:</span>
            <span id="tot-geral">R$ 0,00</span>
          </div>
        </div>

        <!-- Botões de Ação -->
        <div class="acoes-fim">
          <span style="font-size: 0.78rem; color: #777;">* Campos marcados com asterisco são de preenchimento obrigatório.</span>
          <div>
            <?php if (!$fechada): ?>
              <button type="submit" class="btn btn-primario btn-salvar" style="padding: 10px 24px; font-size: 0.85rem;">
                <img src="<?= BASE_URL ?>/public/assets/icons/iconeVerificado.svg" alt="" style="filter: brightness(0) invert(1);">
                Enviar Proposta Comercial
              </button>
            <?php else: ?>
              <button type="button" class="btn btn-secundario" style="cursor: not-allowed;" disabled>
                Cotação Encerrada
              </button>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </form>

  </main>

  <script>
    const SCOPI_BASE = "<?= BASE_URL ?>";
  </script>
  <script src="<?= BASE_URL ?>/public/assets/js/scopi.js"></script>
  
  <script>
    // Inicia e executa o cálculo inicial dos totais
    document.addEventListener('DOMContentLoaded', () => {
      calcularTotais();
      
      // Vincula o evento input em todos os campos com a classe calcula-total
      document.querySelectorAll('.calcula-total').forEach(el => {
        el.addEventListener('input', calcularTotais);
      });
    });

    /**
     * Calcula o subtotal e o total da proposta comercial em tempo real
     */
    function calcularTotais() {
      let subtotalItens = 0;

      // Calcular o subtotal de cada linha de produto
      document.querySelectorAll('.item-linha').forEach(linha => {
        const qtd = parseFloat(linha.querySelector('.item-qtd').textContent) || 0;
        const precoInput = linha.querySelector('.item-preco');
        const preco = parseFloat(precoInput.value) || 0;
        const descInput = linha.querySelector('.item-desconto');
        const desconto = parseFloat(descInput.value) || 0;
        
        const sub = Math.max(0, (qtd * preco) - desconto);
        subtotalItens += sub;

        // Atualiza a coluna de subtotal formatado da linha
        const cellSubtotal = linha.querySelector('.item-subtotal');
        if (cellSubtotal) {
          cellSubtotal.textContent = formatarMoeda(sub);
        }
      });

      // Ler impostos, taxas adicionais, desconto header valor e desconto header percentual
      const impostos = parseFloat(document.getElementById('impostos').value) || 0;
      const taxas = parseFloat(document.getElementById('taxas_adicionais').value) || 0;
      const descVal = parseFloat(document.getElementById('desconto_valor').value) || 0;
      const descPct = parseFloat(document.getElementById('desconto_percentual').value) || 0;

      const totalBruto = subtotalItens + impostos + taxas;
      let totalGeral = totalBruto - descVal;
      if (descPct > 0) {
        totalGeral -= totalBruto * (descPct / 100);
      }
      totalGeral = Math.max(0, totalGeral);

      // Atualizar o painel de resumo
      document.getElementById('tot-subtotal').textContent = formatarMoeda(subtotalItens);
      document.getElementById('tot-impostos').textContent = formatarMoeda(impostos);
      document.getElementById('tot-taxas').textContent = formatarMoeda(taxas);
      
      const descValEl = document.getElementById('tot-desconto-valor');
      if (descValEl) descValEl.textContent = formatarMoeda(descVal);
      const descPctEl = document.getElementById('tot-desconto-percentual');
      if (descPctEl) descPctEl.textContent = descPct.toFixed(2) + '%';
      
      document.getElementById('tot-geral').textContent = formatarMoeda(totalGeral);
    }

    /**
     * Auxiliar para formatar números como Real Brasileiro (R$)
     */
    function formatarMoeda(valor) {
      return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
      }).format(valor);
    }

    /**
     * AJAX submit do formulário da proposta
     */
    async function enviarProposta(event) {
      event.preventDefault();
      
      const form = document.getElementById('formProposta');
      const btn = form.querySelector('.btn-salvar');
      
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<div class="spinner" style="width:14px;height:14px;margin-right:8px;border-width:1px;"></div> Enviando...';
      }

      try {
        const formData = new FormData(form);
        const url = `${SCOPI_BASE}/cotacao/responder/salvar`;
        
        const resp = await fetch(url, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        const json = await resp.json();

        if (json.sucesso) {
          Scopi.toast('sucesso', json.mensagem || 'Proposta comercial enviada com sucesso!');
          // Aguarda um pequeno intervalo e recarrega a página para atualizar o status e carregar dados gravados
          setTimeout(() => {
            window.location.reload();
          }, 1500);
        } else {
          Scopi.toast('erro', json.mensagem || 'Ocorreu um erro ao salvar a proposta.');
          if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<img src="<?= BASE_URL ?>/public/assets/icons/iconeVerificado.svg" alt="" style="filter: brightness(0) invert(1);"> Enviar Proposta Comercial';
          }
        }
      } catch (e) {
        Scopi.toast('erro', 'Falha na comunicação com o servidor.');
        if (btn) {
          btn.disabled = false;
          btn.innerHTML = '<img src="<?= BASE_URL ?>/public/assets/icons/iconeVerificado.svg" alt="" style="filter: brightness(0) invert(1);"> Enviar Proposta Comercial';
        }
      }
    }
  </script>

</body>
</html>
