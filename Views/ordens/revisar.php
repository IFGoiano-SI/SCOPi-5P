<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SCOPi - Revisar Ordem de Compra #<?= htmlspecialchars($ordem['numero'] ?? '') ?></title>
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
      filter: brightness(0) invert(1);
    }

    .topbar-fornecedor .topbar-logo img.logotipo {
      height: 20px;
      filter: brightness(0) invert(1);
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

    /* Card da Ordem de Compra */
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
  </style>
</head>
<body>

  <!-- TOPBAR -->
  <header class="topbar-fornecedor">
    <div class="topbar-logo">
      <img src="<?= BASE_URL ?>/public/assets/icons/iconeSCOPi.svg" alt="SCOPi" class="icone-logo">
      <img src="<?= BASE_URL ?>/public/assets/icons/iconeLogotipo.svg" alt="SCOPi" class="logotipo" style="display:none;">
      <span class="badge-portal">Portal do Fornecedor</span>
    </div>
    
    <div class="topbar-usr-info">
      <div class="info-usr">
        <div class="nome-usr"><?= htmlspecialchars($ordem['nome_fornecedor'] ?? '') ?></div>
        <div class="perfil-usr">Acesso via Token</div>
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
    $flash = \Config\Auxiliares::obterFlash();
    if (!empty($flash)):
    ?>
      <div class="mensagem-flash flash-<?= $flash['color'] ?? 'info' ?>">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeAlerta.svg" alt="">
        <?= htmlspecialchars($flash['texto']) ?>
      </div>
    <?php endif; ?>

    <!-- Banner de Status -->
    <?php if ((int)($ordem['aceito_fornecedor'] ?? 0) === 1): ?>
      <div class="banner-status sucesso">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt="" style="filter: hue-rotate(90deg) brightness(0.8);">
        <span>Você aprovou e aceitou esta Ordem de Compra em <strong><?= date('d/m/Y \à\s H:i', strtotime($ordem['aceito_em'])) ?></strong>. A empresa compradora já foi notificada e os produtos estão aguardando envio conforme acordado.</span>
      </div>
    <?php else: ?>
      <div class="banner-status fechada">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeAlerta.svg" alt="">
        <span><strong>Confirmação Pendente:</strong> Por favor, revise os termos de entrega, preços e quantidades abaixo. Para aprovar e confirmar o envio dos produtos, clique no botão de aceite ao final da página.</span>
      </div>
    <?php endif; ?>

    <!-- Card de Cabeçalho da Ordem de Compra -->
    <section class="card-cotacao">
      <div class="card-cotacao-topo">
        <div>
          <h1 class="card-cotacao-titulo">Ordem de Compra #<?= htmlspecialchars($ordem['numero'] ?? '') ?></h1>
          <p class="card-cotacao-subtitulo">Por favor, revise as condições de fornecimento e os itens descritos.</p>
        </div>
        <div>
          <span class="badge" style="background: rgba(254,249,255,0.2); color: var(--branco); font-size: 0.72rem; padding: 4px 12px; border-radius: 12px; font-weight: 600;">
            Status: <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $ordem['status'] ?? ''))) ?>
          </span>
        </div>
      </div>
      
      <div class="card-cotacao-corpo">
        <div class="info-item">
          <span class="info-item-rotulo">Empresa Compradora</span>
          <span class="info-item-valor">SCOPi Tecnologia</span>
        </div>
        <div class="info-item">
          <span class="info-item-rotulo">Fornecedor</span>
          <span class="info-item-valor"><?= htmlspecialchars($ordem['nome_fornecedor'] ?? '') ?></span>
        </div>
        <div class="info-item">
          <span class="info-item-rotulo">Emitida Em</span>
          <span class="info-item-valor"><?= $ordem['emitido_em'] ? date('d/m/Y', strtotime($ordem['emitido_em'])) : '-' ?></span>
        </div>
        <div class="info-item">
          <span class="info-item-rotulo">Comprador Resp.</span>
          <span class="info-item-valor"><?= htmlspecialchars($ordem['nome_comprador'] ?? '-') ?></span>
        </div>
      </div>
    </section>

    <!-- Detalhes da Ordem de Compra -->
    <div class="painel-proposta">
      
      <!-- Condições Comerciais -->
      <h2 class="secao-titulo">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeFiltro.svg" alt="">
        Condições de Entrega & Pagamento
      </h2>
      
      <div class="grade-form" style="margin-bottom: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <div class="campo-form">
          <label>Prazo de Entrega</label>
          <input type="text" class="input-tabela" value="<?= htmlspecialchars($ordem['prazo_entrega'] ?? '-') ?>" readonly>
        </div>
        <div class="campo-form">
          <label>Condição de Pagamento</label>
          <input type="text" class="input-tabela" value="<?= htmlspecialchars($ordem['condicao_pagamento'] ?? '-') ?>" readonly>
        </div>
        <div class="campo-form">
          <label>Modalidade Frete</label>
          <input type="text" class="input-tabela" value="<?= htmlspecialchars($ordem['modalidade_frete'] ?? '-') ?>" readonly>
        </div>
        <?php if (!empty($ordem['transportadora'])): ?>
          <div class="campo-form">
            <label>Transportadora</label>
            <input type="text" class="input-tabela" value="<?= htmlspecialchars($ordem['transportadora'] ?? '-') ?> (CNPJ: <?= htmlspecialchars($ordem['cnpj_transportadora'] ?? '-') ?>)" readonly>
          </div>
        <?php endif; ?>
      </div>

      <?php if (!empty($ordem['observacao'])): ?>
        <div class="campo-form" style="margin-bottom: 30px;">
          <label>Observações gerais</label>
          <textarea class="input-tabela" readonly style="min-height: 80px; resize: none;"><?= htmlspecialchars($ordem['observacao']) ?></textarea>
        </div>
      <?php endif; ?>

      <!-- Itens da Ordem de Compra -->
      <h2 class="secao-titulo">
        <img src="<?= BASE_URL ?>/public/assets/icons/iconeCadastro.svg" alt="">
        Itens da Ordem de Compra
      </h2>

      <div style="overflow-x: auto;">
        <table class="tabela" style="width: 100%; border-collapse: collapse; font-size: 0.82rem;">
          <thead>
            <tr style="background: linear-gradient(135deg, var(--escura), var(--media)); color: var(--branco);">
              <th style="width: 70px; text-align: center; padding: 12px 15px;">Item</th>
              <th style="width: 120px; padding: 12px 15px;">Código</th>
              <th style="padding: 12px 15px;">Produto</th>
              <th style="width: 100px; text-align: center; padding: 12px 15px;">Quantidade</th>
              <th style="width: 135px; text-align: right; padding: 12px 15px;">Preço Unitário</th>
              <th style="width: 150px; text-align: right; padding: 12px 15px;">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $somaItens = 0;
            if (!empty($ordem['itens'])):
              foreach ($ordem['itens'] as $item):
                $sub = (float)$item['quantidade'] * (float)$item['preco_unitario'];
                $somaItens += $sub;
            ?>
              <tr style="border-bottom: 1px solid var(--borda);">
                <td style="text-align: center; padding: 12px 15px; font-weight: 600;"><?= htmlspecialchars($item['numero_item'] ?? '-') ?></td>
                <td style="padding: 12px 15px;"><?= htmlspecialchars($item['produto_codigo'] ?? '-') ?></td>
                <td style="padding: 12px 15px; font-weight: 500;"><?= htmlspecialchars($item['produto_name'] ?? $item['produto_nome'] ?? 'Produto não encontrado') ?></td>
                <td style="text-align: center; padding: 12px 15px;"><?= (float)$item['quantidade'] ?></td>
                <td style="text-align: right; padding: 12px 15px;">R$ <?= number_format((float)$item['preco_unitario'], 2, ',', '.') ?></td>
                <td style="text-align: right; padding: 12px 15px; font-weight: 600; color: var(--media);">R$ <?= number_format($sub, 2, ',', '.') ?></td>
              </tr>
            <?php 
              endforeach;
            else:
            ?>
              <tr>
                <td colspan="6" style="text-align: center; padding: 20px; color:#999;">Nenhum item encontrado nesta Ordem de Compra.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Resumo / Totais -->
      <div class="painel-totais">
        <div class="total-linha">
          <span>Subtotal dos Itens:</span>
          <span>R$ <?= number_format($somaItens, 2, ',', '.') ?></span>
        </div>
        <div class="total-linha geral">
          <span>Valor Total da OC:</span>
          <span>R$ <?= number_format((float)($ordem['valor_total'] ?? $somaItens), 2, ',', '.') ?></span>
        </div>
      </div>

      <!-- Botão de Ação -->
      <div class="acoes-fim">
        <span style="font-size: 0.78rem; color: #777;">* Revise cuidadosamente todos os termos antes de confirmar.</span>
        <div>
          <?php if ((int)($ordem['aceito_fornecedor'] ?? 0) === 0): ?>
            <button type="button" class="btn btn-primario btn-salvar" id="btn-confirmar-oc" onclick="confirmarOrdem()" style="padding: 10px 24px; font-size: 0.85rem;">
              <img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt="" style="filter: brightness(0) invert(1);">
              Confirmar e Enviar Produtos
            </button>
          <?php else: ?>
            <button type="button" class="btn btn-secundario" style="cursor: not-allowed; padding: 10px 24px; font-size: 0.85rem;" disabled>
              Ordem de Compra Confirmada
            </button>
          <?php endif; ?>
        </div>
      </div>

    </div>

  </main>

  <div class="toast" id="scopi-toast"></div>

  <script>
  function mostrarToast(mensagem, tipo = 'sucesso') {
    let el = document.getElementById('scopi-toast');
    if(!el) { el=document.createElement('div'); el.id='scopi-toast'; el.className='toast'; document.body.appendChild(el); }
    el.textContent=mensagem; el.className=`toast ${tipo}`;
    requestAnimationFrame(()=>el.classList.add('visivel'));
    clearTimeout(el._t); el._t=setTimeout(()=>el.classList.remove('visivel'),3500);
  }

  function confirmarOrdem() {
    const btn = document.getElementById('btn-confirmar-oc');
    btn.disabled = true;
    btn.innerText = 'Processando...';

    const formData = new FormData();
    formData.append('token', '<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>');

    fetch('<?= BASE_URL ?>/ordem/revisar/confirmar', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.sucesso) {
        mostrarToast(data.mensagem || 'Ordem de Compra confirmada com sucesso!', 'sucesso');
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      } else {
        mostrarToast(data.mensagem || 'Ocorreu um erro ao confirmar a Ordem de Compra.', 'erro');
        btn.disabled = false;
        btn.innerHTML = '<img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt="" style="filter: brightness(0) invert(1);"> Confirmar e Enviar Produtos';
      }
    })
    .catch(error => {
      console.error('Erro:', error);
      mostrarToast('Erro na conexão com o servidor.', 'erro');
      btn.disabled = false;
      btn.innerHTML = '<img src="<?= BASE_URL ?>/public/assets/icons/iconeBusca.svg" alt="" style="filter: brightness(0) invert(1);"> Confirmar e Enviar Produtos';
    });
  }
  </script>

</body>
</html>
