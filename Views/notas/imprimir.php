<?php
/**
 * Views/notas/imprimir.php
 * View simplificada para impressão de Nota Fiscal (Espelho)
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Nota Fiscal #<?= htmlspecialchars($nota['numero'] ?? '') ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .cabecalho { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .cabecalho h1 { margin: 0; font-size: 24px; text-transform: uppercase; }
        .cabecalho p { margin: 5px 0 0 0; font-size: 14px; }
        
        .info-grid { display: flex; flex-wrap: wrap; margin-bottom: 20px; border: 1px solid #ccc; padding: 10px; }
        .info-item { width: 50%; margin-bottom: 10px; }
        .info-item strong { display: inline-block; width: 150px; }
        .info-item.full { width: 100%; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .totais { width: 300px; float: right; border: 1px solid #ccc; padding: 10px; margin-bottom: 30px; }
        .total-linha { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .total-linha.geral { font-weight: bold; font-size: 14px; border-top: 1px solid #ccc; padding-top: 5px; margin-top: 5px; }
        
        .clear { clear: both; }
        
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

    <button class="btn-imprimir" onclick="window.print()">Imprimir Espelho da NF</button>

    <div class="cabecalho">
        <h1>Espelho de Nota Fiscal</h1>
        <p><strong>Nº <?= htmlspecialchars($nota['numero'] ?? '') ?></strong> - Emitida em: <?= $nota['data_emissao'] ? date('d/m/Y', strtotime($nota['data_emissao'])) : '-' ?></p>
    </div>

    <div class="info-grid">
        <div class="info-item full"><strong>Chave de Acesso:</strong> <?= htmlspecialchars($nota['chave_acesso'] ?? 'N/A') ?></div>
        
        <div class="info-item"><strong>Fornecedor:</strong> <?= htmlspecialchars($nota['nome_fornecedor'] ?? '-') ?></div>
        <div class="info-item"><strong>CNPJ/CPF:</strong> <?= htmlspecialchars($nota['cnpj_cpf'] ?? '-') ?></div>
        
        <div class="info-item"><strong>Inscrição Estadual:</strong> <?= htmlspecialchars($nota['inscricao_estadual'] ?? 'Isento') ?></div>
        <div class="info-item"><strong>Cadastrado por:</strong> <?= htmlspecialchars($nota['nome_usuario'] ?? '-') ?></div>
    </div>

    <h3>Itens da Nota Fiscal</h3>
    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th style="width: 80px;" class="text-center">Qtd</th>
                <th style="width: 100px;" class="text-right">Preço Unit. (R$)</th>
                <th style="width: 100px;" class="text-right">Subtotal (R$)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $subtotalItens = 0;
            if (!empty($nota['itens'])):
                foreach ($nota['itens'] as $item): 
                    $sub = $item['quantidade'] * $item['preco_unitario'];
                    $subtotalItens += $sub;
            ?>
                <tr>
                    <td><?= htmlspecialchars($item['nome_produto'] ?? 'Produto não encontrado') ?></td>
                    <td class="text-center"><?= (float)$item['quantidade'] ?></td>
                    <td class="text-right"><?= number_format($item['preco_unitario'], 2, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($sub, 2, ',', '.') ?></td>
                </tr>
            <?php 
                endforeach;
            else:
            ?>
                <tr>
                    <td colspan="4" class="text-center">Nenhum item cadastrado nesta nota.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="totais">
        <div class="total-linha">
            <span>Valor dos Itens:</span>
            <span>R$ <?= number_format($subtotalItens, 2, ',', '.') ?></span>
        </div>
        <div class="total-linha geral">
            <span>Valor Total da NF:</span>
            <span>R$ <?= number_format((float)($nota['valor_total'] ?? 0), 2, ',', '.') ?></span>
        </div>
    </div>
    
    <div class="clear"></div>

</body>
</html>
