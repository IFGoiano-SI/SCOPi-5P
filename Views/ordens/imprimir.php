<?php
/**
 * Views/ordens/imprimir.php
 * View simplificada para impressão de Ordem de Compra
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ordem de Compra #<?= htmlspecialchars($ordem['numero'] ?? '') ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .cabecalho { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .cabecalho h1 { margin: 0; font-size: 24px; text-transform: uppercase; }
        .cabecalho p { margin: 5px 0 0 0; font-size: 14px; }
        
        .info-grid { display: flex; flex-wrap: wrap; margin-bottom: 20px; border: 1px solid #ccc; padding: 10px; }
        .info-item { width: 50%; margin-bottom: 10px; }
        .info-item strong { display: inline-block; width: 150px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .totais { width: 300px; float: right; border: 1px solid #ccc; padding: 10px; margin-bottom: 30px; }
        .total-linha { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .total-linha.geral { font-weight: bold; font-size: 14px; border-top: 1px solid #ccc; padding-top: 5px; margin-top: 5px; }
        
        .clear { clear: both; }
        
        .assinaturas { margin-top: 50px; display: flex; justify-content: space-around; }
        .assinatura-box { text-align: center; width: 250px; }
        .linha-assinatura { border-top: 1px solid #000; margin-bottom: 5px; }
        
        @media print {
            body { padding: 0; }
            button { display: none; }
        }
        
        .btn-imprimir {
            display: inline-block; padding: 10px 20px; background: #510B76; color: #fff; text-decoration: none;
            border: none; border-radius: 4px; cursor: pointer; font-size: 14px; margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <button class="btn-imprimir" onclick="window.print()">Imprimir Ordem de Compra</button>

    <div class="cabecalho">
        <h1>Ordem de Compra</h1>
        <p><strong>Nº <?= htmlspecialchars($ordem['numero'] ?? '') ?></strong> - Emitida em: <?= $ordem['emitido_em'] ? date('d/m/Y', strtotime($ordem['emitido_em'])) : '-' ?></p>
    </div>

    <div class="info-grid">
        <div class="info-item"><strong>Fornecedor:</strong> <?= htmlspecialchars($ordem['nome_fornecedor'] ?? '-') ?></div>
        <div class="info-item"><strong>Status:</strong> <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $ordem['status'] ?? ''))) ?></div>
        
        <div class="info-item"><strong>Condição de Pagto:</strong> <?= htmlspecialchars($ordem['condicao_pagamento'] ?? '-') ?></div>
        <div class="info-item"><strong>Prazo de Entrega:</strong> <?= htmlspecialchars($ordem['prazo_entrega'] ?? '-') ?></div>
        
        <div class="info-item"><strong>Modalidade Frete:</strong> <?= htmlspecialchars($ordem['modalidade_frete'] ?? '-') ?></div>
        <div class="info-item"><strong>Comprador Resp.:</strong> <?= htmlspecialchars($ordem['nome_comprador'] ?? '-') ?></div>
        
        <?php if (!empty($ordem['observacao'])): ?>
        <div class="info-item" style="width: 100%;"><strong>Observações:</strong> <?= nl2br(htmlspecialchars($ordem['observacao'])) ?></div>
        <?php endif; ?>
    </div>

    <h3>Itens da Ordem de Compra</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 80px;">Código</th>
                <th>Produto</th>
                <th style="width: 80px;" class="text-center">Qtd</th>
                <th style="width: 100px;" class="text-right">Preço Unit. (R$)</th>
                <th style="width: 100px;" class="text-right">Subtotal (R$)</th>
                <th style="width: 100px;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $subtotalGeral = 0;
            if (!empty($ordem['itens'])):
                foreach ($ordem['itens'] as $item): 
                    $sub = $item['quantidade'] * $item['preco_unitario'];
                    $subtotalGeral += $sub;
            ?>
                <tr>
                    <td><?= htmlspecialchars($item['produto_codigo'] ?? '') ?></td>
                    <td><?= htmlspecialchars($item['produto_nome'] ?? 'Produto não encontrado') ?></td>
                    <td class="text-center"><?= (float)$item['quantidade'] ?></td>
                    <td class="text-right"><?= number_format($item['preco_unitario'], 2, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($sub, 2, ',', '.') ?></td>
                    <td class="text-center"><?= ucfirst(htmlspecialchars($item['status_item'])) ?></td>
                </tr>
            <?php 
                endforeach;
            else:
            ?>
                <tr>
                    <td colspan="6" class="text-center">Nenhum item encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="totais">
        <div class="total-linha">
            <span>Subtotal Itens:</span>
            <span>R$ <?= number_format($subtotalGeral, 2, ',', '.') ?></span>
        </div>
        <div class="total-linha geral">
            <span>Valor Total da OC:</span>
            <span>R$ <?= number_format((float)($ordem['valor_total'] ?? 0), 2, ',', '.') ?></span>
        </div>
    </div>
    
    <div class="clear"></div>

    <div class="assinaturas">
        <div class="assinatura-box">
            <div class="linha-assinatura"></div>
            Comprador Responsável<br>
            <?= htmlspecialchars($ordem['nome_comprador'] ?? '') ?>
        </div>
        <div class="assinatura-box">
            <div class="linha-assinatura"></div>
            Gerente de Compras<br>
            <?= htmlspecialchars($ordem['nome_aprovador'] ?? '') ?>
        </div>
    </div>

    <script>
        // Imprime automaticamente se o usuário quiser
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
