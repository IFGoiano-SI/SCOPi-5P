<?php
namespace Controllers;

use Config\Auxiliares;

class CotacaoFornecedorControlador extends BaseController {

    public function exibir(): void {
        Auxiliares::iniciarSessao();
        $token = $_GET['token'] ?? $_SESSION['fornecedor_token'] ?? '';

        if (empty($_SESSION['fornecedor_logado']) || empty($_SESSION['cotacao_fornecedor_id']) || $_SESSION['fornecedor_token'] !== $token) {
            Auxiliares::redirecionar('login/fornecedor?token=' . urlencode($token));
            return;
        }

        $cfId = $_SESSION['cotacao_fornecedor_id'];
        $bd = \Config\BancoDados::obterInstancia()->obterConexao();

        $qCF = $bd->prepare("
            SELECT cf.*, c.numero AS numero_cotacao, c.status AS status_cotacao, f.razao_social, f.cnpj
            FROM cotacao_fornecedores cf
            JOIN cotacoes c ON c.id = cf.cotacao_id
            JOIN fornecedores f ON f.id = cf.fornecedor_id
            WHERE cf.id = :cfid
        ");
        $qCF->execute([':cfid' => $cfId]);
        $cf = $qCF->fetch();

        if (!$cf) {
            Auxiliares::encerrarSessao();
            Auxiliares::flash('erro', 'Sessão inválida. Por favor, faça login novamente.');
            Auxiliares::redirecionar('login/fornecedor?token=' . urlencode($token));
            return;
        }

        // Se cotação já está fechada ou cancelada, não permite alterar
        $fechada = in_array($cf['status_cotacao'], ['fechada', 'cancelada']);

        $qItens = $bd->prepare("
            SELECT ci.*, p.nome AS nome_produto, p.codigo AS codigo_produto
            FROM cotacao_itens ci
            JOIN produtos p ON p.id = ci.produto_id
            WHERE ci.cotacao_id = :cid
        ");
        $qItens->execute([':cid' => $cf['cotacao_id']]);
        $itens = $qItens->fetchAll();

        $qProp = $bd->prepare("
            SELECT cp.*
            FROM cotacao_propostas cp
            WHERE cp.cotacao_fornecedor_id = :cfid
        ");
        $qProp->execute([':cfid' => $cfId]);
        $propostas = $qProp->fetchAll();

        $propostasMapeadas = [];
        foreach ($propostas as $p) {
            $propostasMapeadas[$p['produto_id']] = $p;
        }

        $this->renderizarSemLayout('cotacao/responder', compact('cf', 'itens', 'propostasMapeadas', 'token', 'fechada'));
    }

    public function salvar(): void {
        Auxiliares::iniciarSessao();
        $token = $_POST['token'] ?? $_SESSION['fornecedor_token'] ?? '';

        if (empty($_SESSION['fornecedor_logado']) || empty($_SESSION['cotacao_fornecedor_id']) || $_SESSION['fornecedor_token'] !== $token) {
            $this->json(false, 'Sessão expirada. Por favor, recarregue a página.');
            return;
        }

        $cfId = $_SESSION['cotacao_fornecedor_id'];
        $bd = \Config\BancoDados::obterInstancia()->obterConexao();

        // Obter dados da cotação e fornecedor
        $qCF = $bd->prepare("
            SELECT cf.*, c.status AS status_cotacao, f.razao_social
            FROM cotacao_fornecedores cf
            JOIN cotacoes c ON c.id = cf.cotacao_id
            JOIN fornecedores f ON f.id = cf.fornecedor_id
            WHERE cf.id = :cfid
        ");
        $qCF->execute([':cfid' => $cfId]);
        $cf = $qCF->fetch();

        if (!$cf) {
            $this->json(false, 'Convite de cotação não encontrado.');
            return;
        }

        if (in_array($cf['status_cotacao'], ['fechada', 'cancelada'])) {
            $this->json(false, 'Esta cotação já se encontra encerrada ou cancelada. Não é possível enviar propostas.');
            return;
        }

        // Validar campos do cabeçalho
        $modalidadeFrete = trim($_POST['modalidade_frete'] ?? '');
        $transportadora = trim($_POST['transportadora'] ?? '');
        $condicaoPagamento = trim($_POST['condicao_pagamento'] ?? '');
        $impostos = (float)str_replace(',', '.', $_POST['impostos'] ?? '0');
        $taxasAdicionais = (float)str_replace(',', '.', $_POST['taxas_adicionais'] ?? '0');
        $validadeProposta = !empty($_POST['validade_proposta']) ? trim($_POST['validade_proposta']) : null;
        $garantia = trim($_POST['garantia'] ?? '');
        $prazoEntrega = (int)($_POST['prazo_entrega'] ?? 0);
        $observacao = trim($_POST['observacao'] ?? '');

        if (empty($modalidadeFrete)) {
            $this->json(false, 'A modalidade do frete é obrigatória.');
            return;
        }
        if (empty($condicaoPagamento)) {
            $this->json(false, 'A condição de pagamento é obrigatória.');
            return;
        }

        $itensForm = $_POST['itens'] ?? [];
        if (!is_array($itensForm) || empty($itensForm)) {
            $this->json(false, 'Nenhum item enviado na proposta.');
            return;
        }

        try {
            $bd->beginTransaction();

            // 1. Atualizar cabeçalho da proposta no convite
            $qUpCF = $bd->prepare("
                UPDATE cotacao_fornecedores 
                SET modalidade_frete = :frete,
                    transportadora = :transp,
                    condicao_pagamento = :pagto,
                    impostos = :impostos,
                    taxas_adicionais = :taxas,
                    validade_proposta = :validade,
                    garantia = :garantia,
                    prazo_entrega = :prazo,
                    observacao = :obs,
                    status = 'respondido',
                    respondido_em = NOW()
                WHERE id = :cfid
            ");
            $qUpCF->execute([
                ':frete' => $modalidadeFrete,
                ':transp' => $transportadora,
                ':pagto' => $condicaoPagamento,
                ':impostos' => $impostos,
                ':taxas' => $taxasAdicionais,
                ':validade' => $validadeProposta,
                ':garantia' => $garantia,
                ':prazo' => $prazoEntrega,
                ':obs' => $observacao,
                ':cfid' => $cfId
            ]);

            // 2. Salvar propostas de itens
            foreach ($itensForm as $prodId => $detalhes) {
                $precoUnitario = (float)str_replace(',', '.', $detalhes['preco_unitario'] ?? '0');
                $prazoEntrega = (int)($detalhes['prazo_entrega'] ?? 0);
                $obsItem = trim($detalhes['observacao'] ?? '');
                $modelo = trim($detalhes['modelo'] ?? '');
                $disponivel = isset($detalhes['disponivel']) ? 1 : 0;

                // RF12: Se o fornecedor não tem o item, preço pode ser 0
                if ($disponivel && $precoUnitario <= 0) {
                    throw new \Exception("O preço unitário de todos os itens disponíveis deve ser maior que zero.");
                }
                if ($prazoEntrega < 0) {
                    throw new \Exception("O prazo de entrega não pode ser negativo.");
                }

                // Obter quantidade do item da cotação
                $qQtd = $bd->prepare("SELECT quantidade FROM cotacao_itens WHERE cotacao_id = :cid AND produto_id = :pid");
                $qQtd->execute([':cid' => $cf['cotacao_id'], ':pid' => $prodId]);
                $qtdItem = $qQtd->fetchColumn();

                if ($qtdItem === false) {
                    throw new \Exception("Produto ID {$prodId} não faz parte desta cotação.");
                }

                // Verificar se já existe proposta para este item
                $qExist = $bd->prepare("SELECT id FROM cotacao_propostas WHERE cotacao_fornecedor_id = :cfid AND produto_id = :pid");
                $qExist->execute([':cfid' => $cfId, ':pid' => $prodId]);
                $propId = $qExist->fetchColumn();

                if ($propId !== false) {
                    // Update
                    $qUpProp = $bd->prepare("
                        UPDATE cotacao_propostas 
                        SET preco_unitario = :preco,
                            prazo_entrega = :prazo,
                            observacao = :obs,
                            modelo = :modelo,
                            disponivel = :disp
                        WHERE id = :id
                    ");
                    $qUpProp->execute([
                        ':preco' => $precoUnitario,
                        ':prazo' => $prazoEntrega,
                        ':obs' => $obsItem,
                        ':modelo' => $modelo,
                        ':disp' => $disponivel,
                        ':id' => $propId
                    ]);
                } else {
                    // Insert
                    $qInProp = $bd->prepare("
                        INSERT INTO cotacao_propostas (cotacao_fornecedor_id, produto_id, quantidade, preco_unitario, prazo_entrega, observacao, modelo, disponivel)
                        VALUES (:cfid, :pid, :qtd, :preco, :prazo, :obs, :modelo, :disp)
                    ");
                    $qInProp->execute([
                        ':cfid' => $cfId,
                        ':pid' => $prodId,
                        ':qtd' => $qtdItem,
                        ':preco' => $precoUnitario,
                        ':prazo' => $prazoEntrega,
                        ':obs' => $obsItem,
                        ':modelo' => $modelo,
                        ':disp' => $disponivel
                    ]);
                }
            }

            // Nota: A cotação permanece 'aberta' enquanto recebe propostas.
            // O comprador a 'fecha' ao selecionar o vencedor.

            // 4. Notificar comprador/gerente
            $qCot = $bd->prepare("SELECT usuario_id, numero FROM cotacoes WHERE id = :cid");
            $qCot->execute([':cid' => $cf['cotacao_id']]);
            $cot = $qCot->fetch();
            if ($cot) {
                \Config\Notificador::notificarUsuario(
                    (int)$cot['usuario_id'],
                    "Proposta enviada - Cotação {$cot['numero']}",
                    "O fornecedor {$cf['razao_social']} enviou a proposta comercial para a cotação {$cot['numero']}."
                );
            }

            $bd->commit();
            $this->json(true, 'Proposta comercial enviada com sucesso!');
        } catch (\Exception $e) {
            if ($bd->inTransaction()) {
                $bd->rollBack();
            }
            $this->json(false, $e->getMessage());
        }
    }
}
