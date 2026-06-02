<?php
namespace Models;

class CotacaoModelo extends ModeloBase {
    protected string $tabela = 'cotacoes';

    public function contarPorMes(int $meses = 6): array {
        $q = $this->bd->prepare("SELECT DATE_FORMAT(criado_em,'%Y-%m') AS mes, COUNT(*) AS total FROM cotacoes WHERE criado_em >= DATE_SUB(NOW(), INTERVAL :m MONTH) GROUP BY mes ORDER BY mes");
        $q->execute([':m'=>$meses]);
        return $q->fetchAll();
    }

    public function listarComFiltros(array $filtros = []): array {
        $sql = "SELECT c.*, s.numero AS num_solicitacao FROM cotacoes c LEFT JOIN solicitacoes s ON s.id=c.solicitacao_id WHERE 1=1";
        $p = [];
        if (!empty($filtros['status']))  { $sql .= ' AND c.status=:status'; $p[':status']=$filtros['status']; }
        if (!empty($filtros['periodo'])) { $sql .= ' AND DATE(c.criado_em)>=:per'; $p[':per']=$filtros['periodo']; }
        $sql .= ' ORDER BY c.criado_em DESC';
        $q = $this->bd->prepare($sql); $q->execute($p);
        return $q->fetchAll();
    }

    public function buscarComDetalhes(int $id): ?array {
        $cotacao = $this->buscarPorId($id);
        if (!$cotacao) return null;

        $qItens = $this->bd->prepare("
            SELECT ci.*, p.nome AS nome_produto, p.codigo AS codigo_produto
            FROM cotacao_itens ci
            JOIN produtos p ON p.id = ci.produto_id
            WHERE ci.cotacao_id = :id
        ");
        $qItens->execute([':id' => $id]);
        $cotacao['itens'] = $qItens->fetchAll();

        $qForn = $this->bd->prepare("
            SELECT cf.*, f.razao_social, f.cnpj, f.codigo AS codigo_fornecedor
            FROM cotacao_fornecedores cf
            JOIN fornecedores f ON f.id = cf.fornecedor_id
            WHERE cf.cotacao_id = :id
        ");
        $qForn->execute([':id' => $id]);
        $fornecedores = $qForn->fetchAll();

        foreach ($fornecedores as &$f) {
            $qProp = $this->bd->prepare("
                SELECT cp.*
                FROM cotacao_propostas cp
                WHERE cp.cotacao_fornecedor_id = :cfid
            ");
            $qProp->execute([':cfid' => $f['id']]);
            $propostas = $qProp->fetchAll();
            
            $f['propostas'] = [];
            foreach ($propostas as $p) {
                $f['propostas'][$p['produto_id']] = $p;
            }
        }
        $cotacao['fornecedores'] = $fornecedores;

        return $cotacao;
    }

    public function criarCotacao(int $solicitacaoId, array $fornecedorIds, int $usuarioId): int {
        try {
            $this->bd->beginTransaction();

            $numero = 'COT-' . date('Ymd') . '-' . rand(1000, 9999);
            
            $q = $this->bd->prepare("
                INSERT INTO cotacoes (numero, solicitacao_id, usuario_id, status, data_abertura, criado_em)
                VALUES (:num, :sid, :uid, 'aberta', CURDATE(), NOW())
            ");
            $q->execute([
                ':num' => $numero,
                ':sid' => $solicitacaoId,
                ':uid' => $usuarioId
            ]);
            $cotacaoId = (int) $this->bd->lastInsertId();

            $this->bd->prepare("UPDATE solicitacoes SET status = 'em_cotacao', atualizado_em = NOW() WHERE id = :sid")
                ->execute([':sid' => $solicitacaoId]);

            $qItens = $this->bd->prepare("SELECT produto_id, quantidade FROM solicitacao_itens WHERE solicitacao_id = :sid");
            $qItens->execute([':sid' => $solicitacaoId]);
            $itens = $qItens->fetchAll();

            foreach ($itens as $item) {
                $this->bd->prepare("
                    INSERT INTO cotacao_itens (cotacao_id, produto_id, quantidade)
                    VALUES (:cid, :pid, :qtd)
                ")->execute([
                    ':cid' => $cotacaoId,
                    ':pid' => $item['produto_id'],
                    ':qtd' => $item['quantidade']
                ]);
            }

            foreach ($fornecedorIds as $fornId) {
                $token = bin2hex(random_bytes(32));
                
                $this->bd->prepare("
                    INSERT INTO cotacao_fornecedores (cotacao_id, fornecedor_id, token, status, enviado_em)
                    VALUES (:cid, :fid, :tok, 'pendente', NOW())
                ")->execute([
                    ':cid' => $cotacaoId,
                    ':fid' => $fornId,
                    ':tok' => $token
                ]);

                $qForn = $this->bd->prepare("SELECT email, razao_social FROM fornecedores WHERE id = :fid");
                $qForn->execute([':fid' => $fornId]);
                $supplier = $qForn->fetch();

                if ($supplier && !empty($supplier['email'])) {
                    $responderUrl = base_url('cotacao/responder?token=' . $token);
                    $assunto = "Convite de Cotação - " . $numero;
                    $mensagem = "
                        <h2>Olá, " . htmlspecialchars($supplier['razao_social']) . "!</h2>
                        <p>Você foi convidado a participar da cotação de preços <strong>" . $numero . "</strong>.</p>
                        <p>Para enviar sua proposta comercial, por favor clique no link abaixo e autentique-se usando seu CNPJ:</p>
                        <p><a href=\"" . $responderUrl . "\" style=\"display:inline-block; padding:10px 20px; background-color:#510B76; color:#fff; text-decoration:none; border-radius:5px;\">Responder Cotação</a></p>
                        <p>Caso o botão não funcione, copie e cole o seguinte link no seu navegador:</p>
                        <p>" . $responderUrl . "</p>
                        <br>
                        <p>Atenciosamente,<br>Departamento de Compras</p>
                    ";
                    
                    \Config\Notificador::enviarEmail($supplier['email'], $assunto, $mensagem);
                }
            }

            $this->registrarHistorico($this->tabela, $cotacaoId, [], ['status' => 'aberta', 'solicitacao_id' => $solicitacaoId], $usuarioId);
            
            $this->bd->commit();
            return $cotacaoId;
        } catch (\Exception $e) {
            if ($this->bd->inTransaction()) {
                $this->bd->rollBack();
            }
            throw $e;
        }
    }

    public function definirVencedora(int $cotacaoId, int $cotacaoFornecedorId, int $usuarioId): bool {
        try {
            $this->bd->beginTransaction();

            $qCF = $this->bd->prepare("
                SELECT cf.*, c.solicitacao_id
                FROM cotacao_fornecedores cf
                JOIN cotacoes c ON c.id = cf.cotacao_id
                WHERE cf.id = :cfid AND cf.cotacao_id = :cid
            ");
            $qCF->execute([':cfid' => $cotacaoFornecedorId, ':cid' => $cotacaoId]);
            $cf = $qCF->fetch();
            if (!$cf) {
                $this->bd->rollBack();
                return false;
            }

            $this->bd->prepare("UPDATE cotacao_fornecedores SET vencedora = 1 WHERE id = :cfid")
                ->execute([':cfid' => $cotacaoFornecedorId]);

            $this->bd->prepare("UPDATE cotacao_fornecedores SET vencedora = 0 WHERE cotacao_id = :cid AND id != :cfid")
                ->execute([':cid' => $cotacaoId, ':cfid' => $cotacaoFornecedorId]);

            $this->bd->prepare("
                UPDATE cotacoes 
                SET status = 'fechada', data_encerramento = CURDATE(), atualizado_em = NOW() 
                WHERE id = :cid
            ")->execute([':cid' => $cotacaoId]);

            $this->bd->prepare("
                UPDATE solicitacoes 
                SET status = 'concluida', atualizado_em = NOW() 
                WHERE id = :sid
            ")->execute([':sid' => $cf['solicitacao_id']]);

            $qProp = $this->bd->prepare("
                SELECT cp.*
                FROM cotacao_propostas cp
                WHERE cp.cotacao_fornecedor_id = :cfid
            ");
            $qProp->execute([':cfid' => $cotacaoFornecedorId]);
            $propostas = $qProp->fetchAll();

            $subtotalItens = 0.00;
            $maxPrazo = 0;
            foreach ($propostas as $p) {
                $subtotalLinha = (float)$p['preco_unitario'] * (float)$p['quantidade'];
                $subtotalItens += $subtotalLinha;
                if ((int)$p['prazo_entrega'] > $maxPrazo) {
                    $maxPrazo = (int)$p['prazo_entrega'];
                }
            }

            $impostos = (float)($cf['impostos'] ?? 0);
            $taxas = (float)($cf['taxas_adicionais'] ?? 0);
            $valorTotal = max(0.00, $subtotalItens + $impostos + $taxas);

            $numeroOC = 'OC-' . date('Ymd') . '-' . rand(1000, 9999);
            $prazoTexto = $maxPrazo > 0 ? "{$maxPrazo} dias" : "Imediato";

            $condicaoPagamentoDesc = $cf['condicao_pagamento'] ?? '';
            $modalidadeFrete = $cf['modalidade_frete'] ?? '';

            $qOC = $this->bd->prepare("
                INSERT INTO ordens_compra (
                    numero, cotacao_id, solicitacao_id, fornecedor_id, usuario_id,
                    condicao_pagamento, modalidade_frete, prazo_entrega, valor_total,
                    status, emitido_em,
                    observacao, criado_em
                ) VALUES (
                    :num, :cid, :sid, :fid, :uid,
                    :cond, :frete, :prazo, :total,
                    'aberta', CURDATE(),
                    :obs, NOW()
                )
            ");
            $qOC->execute([
                ':num' => $numeroOC,
                ':cid' => $cotacaoId,
                ':sid' => $cf['solicitacao_id'] ?? null,
                ':fid' => $cf['fornecedor_id'],
                ':uid' => $usuarioId,
                ':cond' => $condicaoPagamentoDesc,
                ':frete' => $modalidadeFrete,
                ':prazo' => $prazoTexto,
                ':total' => $valorTotal,
                ':obs' => $cf['observacao']
            ]);
            $ordemId = (int) $this->bd->lastInsertId();

            foreach ($propostas as $p) {
                $this->bd->prepare("
                    INSERT INTO ordem_compra_itens (ordem_id, produto_id, quantidade, preco_unitario)
                    VALUES (:oid, :pid, :qtd, :price)
                ")->execute([
                    ':oid' => $ordemId,
                    ':pid' => $p['produto_id'],
                    ':qtd' => $p['quantidade'],
                    ':price' => $p['preco_unitario']
                ]);
            }

            $this->registrarHistorico($this->tabela, $cotacaoId, [], ['vencedora_id' => $cotacaoFornecedorId, 'status' => 'fechada'], $usuarioId);
            $this->registrarHistorico('ordens_compra', $ordemId, [], ['numero' => $numeroOC, 'status' => 'aberta'], $usuarioId);

            $this->bd->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->bd->inTransaction()) {
                $this->bd->rollBack();
            }
            throw $e;
        }
    }
}
