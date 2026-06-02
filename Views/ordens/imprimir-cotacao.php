<?php
/**
 * Views/ordens/imprimir-cotacao.php
 * View simplificada para impressão de Cotação (Mapa Comparativo)
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cotação #<?= htmlspecialchars($cotacao['numero'] ?? '') ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; margin: 0; padding: 20px; }
        .cabecalho { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .cabecalho h1 { margin: 0; font-size: 20px; text-transform: uppercase; }
        .cabecalho p { margin: 5px 0 0 0; font-size: 13px; }
        
        .info-grid { display: flex; flex-wrap: wrap; margin-bottom: 20px; border: 1px solid #ccc; padding: 10px; }
        .info-item { width: 50%; margin-bottom: 5px; }
        .info-item strong { display: inline-block; width: 130px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .fornecedor-header { background-color: #e6e6e6; font-weight: bold; }
        .vencedor { background-color: #d4edda !important; }
        .vencedor-text { color: #155724; font-weight: bold; }
        
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

    <button class="btn-imprimir" onclick="window.print()">Imprimir Mapa Comparativo</button>

    <div class="cabecalho">
        <h1>Mapa Comparativo de Cotação</h1>
        <p><strong>Cotação Nº <?= htmlspecialchars($cotacao['numero'] ?? '') ?></strong> | Solicitação: <?= htmlspecialchars($cotacao['numero_solicitacao'] ?? '-') ?></p>
    </div>

    <div class="info-grid">
        <div class="info-item"><strong>Status:</strong> <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $cotacao['status'] ?? ''))) ?></div>
        <div class="info-item"><strong>Data Criação:</strong> <?= $cotacao['criado_em'] ? date('d/m/Y H:i', strtotime($cotacao['criado_em'])) : '-' ?></div>
        
        <div class="info-item"><strong>Vencedor:</strong> <span class="vencedor-text"><?= htmlspecialchars($cotacao['fornecedor_vencedor'] ?? 'Nenhum definido') ?></span></div>
        <div class="info-item"><strong>Criado por:</strong> <?= htmlspecialchars($cotacao['nome_usuario'] ?? '-') ?></div>
    </div>

    <h3>Fornecedores Convidados e Propostas</h3>
    
    <?php if (empty($cotacao['fornecedores'])): ?>
        <p>Nenhum fornecedor vinculado a esta cotação.</p>
    <?php else: ?>
        <?php foreach ($cotacao['fornecedores'] as $f): 
            $isVencedor = ($f['vencedora'] == 1);
            $hasProposta = ($f['status'] === 'respondido');
        ?>
            <table>
                <thead>
                    <tr class="fornecedor-header <?= $isVencedor ? 'vencedor' : '' ?>">
                        <th colspan="6">
                            Fornecedor: <?= htmlspecialchars($f['razao_social']) ?> 
                            (<?= htmlspecialchars($f['cnpj']) ?>) 
                            - Status: <?= ucfirst($f['status']) ?>
                            <?= $isVencedor ? ' [VENCEDOR]' : '' ?>
                        </th>
                    </tr>
                    <?php if ($hasProposta): ?>
                    <tr>
                        <th colspan="6" style="background:#f9f9f9; font-weight:normal; font-size:10px;">
                            <strong>Condição Pagto:</strong> <?= htmlspecialchars($f['condicao_pagamento'] ?? '-') ?> | 
                            <strong>Prazo Entrega:</strong> <?= htmlspecialchars($f['prazo_entrega'] ?? '-') ?> dias | 
                            <strong>Frete:</strong> <?= htmlspecialchars($f['modalidade_frete'] ?? '-') ?> | 
                            <strong>Impostos:</strong> R$ <?= number_format($f['impostos'] ?? 0, 2, ',', '.') ?> | 
                            <strong>Taxas Adic.:</strong> R$ <?= number_format($f['taxas_adicionais'] ?? 0, 2, ',', '.') ?>
                        </th>
                    </tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php if ($hasProposta && !empty($f['itens'])): ?>
                        <tr>
                            <th style="width:10%;">Código</th>
                            <th style="width:30%;">Produto</th>
                            <th style="width:10%;" class="text-center">Qtd</th>
                            <th style="width:15%;" class="text-right">Unitário (R$)</th>
                            <th style="width:15%;" class="text-right">Subtotal (R$)</th>
                            <th style="width:20%;">Modelo/Obs</th>
                        </tr>
                        <?php 
                        $sub = 0;
                        foreach ($f['itens'] as $item): 
                            if ($item['disponivel']) {
                                $linhaSub = $item['quantidade'] * $item['preco_unitario'];
                                $sub += $linhaSub;
                            } else {
                                $linhaSub = 0;
                            }
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($item['codigo_produto'] ?? '') ?></td>
                                <td>
                                    <?= htmlspecialchars($item['nome_produto'] ?? '') ?>
                                    <?= !$item['disponivel'] ? ' <strong>(Indisponível)</strong>' : '' ?>
                                </td>
                                <td class="text-center"><?= (float)$item['quantidade'] ?></td>
                                <td class="text-right"><?= $item['disponivel'] ? number_format($item['preco_unitario'], 2, ',', '.') : '-' ?></td>
                                <td class="text-right"><?= $item['disponivel'] ? number_format($linhaSub, 2, ',', '.') : '-' ?></td>
                                <td>
                                    <?php
                                    $obs = [];
                                    if (!empty($item['modelo'])) $obs[] = "Mod: " . $item['modelo'];
                                    if (!empty($item['observacao'])) $obs[] = "Obs: " . $item['observacao'];
                                    echo htmlspecialchars(implode(" | ", $obs));
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="background:#f9f9f9; font-weight:bold;">
                            <td colspan="4" class="text-right">Total da Proposta (Subtotal + Impostos + Taxas):</td>
                            <td class="text-right">R$ <?= number_format($sub + ($f['impostos'] ?? 0) + ($f['taxas_adicionais'] ?? 0), 2, ',', '.') ?></td>
                            <td></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Aguardando resposta do fornecedor.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>
