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
        $sql = "SELECT c.*,
                    (SELECT COUNT(*) FROM cotacao_fornecedores cf2 WHERE cf2.cotacao_id = c.id) AS total_fornecedores,
                    (SELECT COUNT(*) FROM cotacao_fornecedores cf2 WHERE cf2.cotacao_id = c.id AND cf2.status = 'respondido') AS total_respostas,
                    fv.razao_social AS fornecedor_vencedor,
                    fv.codigo AS codigo_fornecedor_vencedor
                FROM cotacoes c
                LEFT JOIN cotacao_fornecedores cfv ON cfv.cotacao_id = c.id AND cfv.vencedora = 1
                LEFT JOIN fornecedores fv ON fv.id = cfv.fornecedor_id
                WHERE 1=1";
        $p = [];
        if (!empty($filtros['status']))            { $sql .= ' AND c.status = :status';             $p[':status'] = $filtros['status']; }
        if (!empty($filtros['numero']))            { $sql .= ' AND c.numero LIKE :num';              $p[':num']    = "%{$filtros['numero']}%"; }
        if (!empty($filtros['data_abertura']))     { $sql .= ' AND c.data_abertura >= :dta';         $p[':dta']    = $filtros['data_abertura']; }
        if (!empty($filtros['data_encerramento'])) { $sql .= ' AND c.data_encerramento <= :dte';     $p[':dte']    = $filtros['data_encerramento']; }
        if (!empty($filtros['fornecedor_codigo'])) { $sql .= ' AND fv.codigo = :fcod';              $p[':fcod']   = $filtros['fornecedor_codigo']; }
        $sql .= ' ORDER BY c.criado_em DESC';
        $q = $this->bd->prepare($sql); $q->execute($p);
        return $q->fetchAll();
    }

    public function buscarComDetalhes(int $id): ?array {
        $cotacao = $this->buscarPorId($id);
        if (!$cotacao) return null;

        $qItens = $this->bd->prepare("
            SELECT ci.*, p.nome AS nome_produto, p.codigo AS codigo_produto, p.categoria_id
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

    public function criarCapa(int $usuarioId, string $dataAbertura, string $dataEncerramento): int {
        try {
            $this->bd->beginTransaction();

            $qMax = $this->bd->prepare("SELECT MAX(CAST(SUBSTRING(numero, 4) AS UNSIGNED)) FROM cotacoes WHERE numero LIKE 'cot%'");
            $qMax->execute();
            $maxNum = (int)$qMax->fetchColumn() ?? 0;
            $numero = 'cot' . str_pad($maxNum + 1, 5, '0', STR_PAD_LEFT);

            $q = $this->bd->prepare("
                INSERT INTO cotacoes (numero, solicitacao_id, usuario_id, status, data_abertura, data_encerramento, criado_em)
                VALUES (:num, NULL, :uid, 'aberta', :dta, :dte, NOW())
            ");
            $q->execute([
                ':num' => $numero,
                ':uid' => $usuarioId,
                ':dta' => $dataAbertura,
                ':dte' => $dataEncerramento
            ]);
            $cotacaoId = (int) $this->bd->lastInsertId();

            $this->registrarHistorico($this->tabela, $cotacaoId, [], ['status' => 'aberta'], $usuarioId, 'abertura da cotação');
            
            $this->bd->commit();
            return $cotacaoId;
        } catch (\Exception $e) {
            if ($this->bd->inTransaction()) {
                $this->bd->rollBack();
            }
            throw $e;
        }
    }

    public function criarCompleta(int $usuarioId, int $solicitacaoId, string $dataAbertura, string $dataEncerramento, array $fornecedorIds): int {
        try {
            $this->bd->beginTransaction();

            // 1. Gerar número sequencial da cotação
            $qMax = $this->bd->prepare("SELECT MAX(CAST(SUBSTRING(numero, 4) AS UNSIGNED)) FROM cotacoes WHERE numero LIKE 'cot%'");
            $qMax->execute();
            $maxNum = (int)$qMax->fetchColumn() ?? 0;
            $numero = 'cot' . str_pad($maxNum + 1, 5, '0', STR_PAD_LEFT);

            // 2. Inserir a capa da cotação
            $q = $this->bd->prepare("
                INSERT INTO cotacoes (numero, solicitacao_id, usuario_id, status, data_abertura, data_encerramento, criado_em)
                VALUES (:num, :sid, :uid, 'aberta', :dta, :dte, NOW())
            ");
            $q->execute([
                ':num' => $numero,
                ':sid' => $solicitacaoId,
                ':uid' => $usuarioId,
                ':dta' => $dataAbertura,
                ':dte' => $dataEncerramento
            ]);
            $cotacaoId = (int)$this->bd->lastInsertId();

            // 3. Buscar os itens da solicitação que estão 'aberto' ou 'autorizado' e não em cotação/concluídos
            $qItensSol = $this->bd->prepare("
                SELECT si.*, p.nome AS nome_produto, p.codigo AS codigo_produto
                FROM solicitacao_itens si
                JOIN produtos p ON p.id = si.produto_id
                WHERE si.solicitacao_id = :sid AND si.status NOT IN ('em_cotacao', 'concluido', 'cancelado')
            ");
            $qItensSol->execute([':sid' => $solicitacaoId]);
            $itensSol = $qItensSol->fetchAll();

            if (empty($itensSol)) {
                throw new \Exception("A solicitação não possui itens pendentes para cotação.");
            }

            // 4. Copiar os itens para cotacao_itens e atualizar o status em solicitacao_itens
            $numeroItem = 1;
            foreach ($itensSol as $item) {
                $this->bd->prepare("
                    INSERT INTO cotacao_itens (cotacao_id, numero_item, solicitacao_item_id, produto_id, quantidade)
                    VALUES (:cid, :num, :sid, :pid, :qtd)
                ")->execute([
                    ':cid' => $cotacaoId,
                    ':num' => $numeroItem,
                    ':sid' => $item['id'],
                    ':pid' => $item['produto_id'],
                    ':qtd' => $item['quantidade']
                ]);

                $this->bd->prepare("UPDATE solicitacao_itens SET status = 'em_cotacao' WHERE id = :sid")
                         ->execute([':sid' => $item['id']]);

                $numeroItem++;
            }

            // 5. Atualizar o status da solicitação para em_cotacao
            $this->bd->prepare("UPDATE solicitacoes SET status = 'em_cotacao' WHERE id = :sid")
                     ->execute([':sid' => $solicitacaoId]);

            // 6. Convidar os fornecedores selecionados
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

            // 7. Registrar histórico da cotação
            $this->registrarHistorico($this->tabela, $cotacaoId, [], ['status' => 'aberta'], $usuarioId, 'criação e convites da cotação');

            $this->bd->commit();
            return $cotacaoId;
        } catch (\Exception $e) {
            if ($this->bd->inTransaction()) {
                $this->bd->rollBack();
            }
            throw $e;
        }
    }
    
    public function salvarItensCotacao(int $cotacaoId, array $itens): void {
        try {
            $this->bd->beginTransaction();
            
            $qMax = $this->bd->prepare("SELECT MAX(numero_item) FROM cotacao_itens WHERE cotacao_id = :cid");
            $qMax->execute([':cid' => $cotacaoId]);
            $maxNum = (int)$qMax->fetchColumn();

            $idsManter = [];
            foreach ($itens as $item) {
                if (!empty($item['id'])) {
                    $idsManter[] = (int)$item['id'];
                }
            }

            if (empty($idsManter)) {
                // Ao deletar, precisamos voltar os itens da solicitação para em_aberto
                // O escopo de reversão completa é coberto abaixo, mas se deletar tudo de uma vez:
                $this->bd->prepare("
                    UPDATE solicitacao_itens si 
                    JOIN cotacao_itens ci ON ci.solicitacao_item_id = si.id 
                    SET si.status = 'aberto' 
                    WHERE ci.cotacao_id = :cid AND ci.solicitacao_item_id IS NOT NULL
                ")->execute([':cid' => $cotacaoId]);
                
                $this->bd->prepare("DELETE FROM cotacao_itens WHERE cotacao_id = :cid")->execute([':cid' => $cotacaoId]);
            } else {
                $placeholders = implode(',', array_fill(0, count($idsManter), '?'));
                $this->bd->prepare("
                    UPDATE solicitacao_itens si 
                    JOIN cotacao_itens ci ON ci.solicitacao_item_id = si.id 
                    SET si.status = 'aberto' 
                    WHERE ci.cotacao_id = ? AND ci.id NOT IN ($placeholders) AND ci.solicitacao_item_id IS NOT NULL
                ")->execute(array_merge([$cotacaoId], $idsManter));

                $qDel = $this->bd->prepare("DELETE FROM cotacao_itens WHERE cotacao_id = ? AND id NOT IN ($placeholders)");
                $qDel->execute(array_merge([$cotacaoId], $idsManter));
            }
            
            foreach ($itens as $item) {
                $solicitacaoItemId = !empty($item['solicitacao_item_id']) ? (int)$item['solicitacao_item_id'] : null;
                $prazoEntrega = trim($item['prazo_entrega'] ?? '');

                if (!empty($item['id'])) {
                    $this->bd->prepare("UPDATE cotacao_itens SET quantidade = :qtd, prazo_entrega = :prazo WHERE id = :id")
                        ->execute([':qtd' => $item['quantidade'], ':prazo' => $prazoEntrega ?: null, ':id' => $item['id']]);
                } else {
                    $maxNum++;
                    $this->bd->prepare("
                        INSERT INTO cotacao_itens (cotacao_id, numero_item, solicitacao_item_id, produto_id, quantidade, prazo_entrega)
                        VALUES (:cid, :num, :sid, :pid, :qtd, :prazo)
                    ")->execute([
                        ':cid' => $cotacaoId,
                        ':num' => $maxNum,
                        ':sid' => $solicitacaoItemId,
                        ':pid' => $item['produto_id'],
                        ':qtd' => $item['quantidade'],
                        ':prazo' => $prazoEntrega ?: null
                    ]);
                }

                if ($solicitacaoItemId) {
                    $this->bd->prepare("UPDATE solicitacao_itens SET status = 'em_cotacao' WHERE id = :sid")
                             ->execute([':sid' => $solicitacaoItemId]);
                }
            }
            
            $this->bd->commit();
        } catch (\Exception $e) {
            if ($this->bd->inTransaction()) $this->bd->rollBack();
            throw $e;
        }
    }

    public function vincularSolicitacao(int $cotacaoId, int $solicitacaoId, int $usuarioId): bool {
        try {
            $this->bd->beginTransaction();
            $this->bd->prepare("UPDATE cotacoes SET solicitacao_id = :sid WHERE id = :cid")->execute([':sid' => $solicitacaoId, ':cid' => $cotacaoId]);
            $this->bd->commit();
            
            // Log para histórico
            $this->registrarHistorico($this->tabela, $cotacaoId, [], ['acao'=>'Vínculo de Solicitação'], $usuarioId);
            return true;
        } catch (\Exception $e) {
            if ($this->bd->inTransaction()) $this->bd->rollBack();
            return false;
        }
    }

    public function excluirItem(int $itemId, int $usuarioId): bool {
        try {
            $this->bd->beginTransaction();
            $q = $this->bd->prepare("SELECT cotacao_id, solicitacao_item_id FROM cotacao_itens WHERE id = :id");
            $q->execute([':id' => $itemId]);
            $item = $q->fetch();
            if (!$item) {
                $this->bd->rollBack();
                return false;
            }

            $cotacaoId = (int)$item['cotacao_id'];
            $solItemId = !empty($item['solicitacao_item_id']) ? (int)$item['solicitacao_item_id'] : null;

            $cot = $this->buscarComDetalhes($cotacaoId);
            if ($cot['status'] !== 'rascunho' && $cot['status'] !== 'aberta') {
                $this->bd->rollBack();
                return false;
            }

            if ($solItemId) {
                $this->bd->prepare("UPDATE solicitacao_itens SET status = 'aberto' WHERE id = :sid")
                         ->execute([':sid' => $solItemId]);
            }

            $ok = $this->bd->prepare("DELETE FROM cotacao_itens WHERE id = :id")->execute([':id' => $itemId]);
            
            if ($ok) {
                $this->registrarHistorico($this->tabela, $cotacaoId, [], ['acao' => 'Exclusão de item ID ' . $itemId . ($solItemId ? ' (Desfez importação)' : '')], $usuarioId);
            }
            $this->bd->commit();
            return $ok;
        } catch (\Exception $e) {
            if ($this->bd->inTransaction()) $this->bd->rollBack();
            return false;
        }
    }
    
    public function convidarFornecedores(int $cotacaoId, array $fornecedorIds, int $usuarioId): int {
        try {
            $this->bd->beginTransaction();
            
            $qCot = $this->bd->prepare("SELECT numero FROM cotacoes WHERE id = :id");
            $qCot->execute([':id' => $cotacaoId]);
            $cotacao = $qCot->fetch();
            if (!$cotacao) throw new \Exception("Cotação não encontrada.");
            $numero = $cotacao['numero'];
            
            $enviados = 0;

            foreach ($fornecedorIds as $fornId) {
                // Check if already invited
                $qCheck = $this->bd->prepare("SELECT id FROM cotacao_fornecedores WHERE cotacao_id = :cid AND fornecedor_id = :fid");
                $qCheck->execute([':cid' => $cotacaoId, ':fid' => $fornId]);
                if ($qCheck->fetch()) {
                    continue; // Skip if already invited
                }

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
                $enviados++;
            }

            $this->bd->commit();
            return $enviados;
        } catch (\Exception $e) {
            if ($this->bd->inTransaction()) {
                $this->bd->rollBack();
            }
            throw $e;
        }
    }

    public function definirVencedoresPorItens(int $cotacaoId, array $propostaIds, int $usuarioId, bool $gerarOC = true): bool {
        try {
            $this->bd->beginTransaction();

            // 1. Verificar se a cotação existe e está aberta
            $qCot = $this->bd->prepare("SELECT * FROM cotacoes WHERE id = :cid");
            $qCot->execute([':cid' => $cotacaoId]);
            $cot = $qCot->fetch();
            if (!$cot || $cot['status'] === 'fechada') {
                $this->bd->rollBack();
                return false;
            }

            // 2. Zerar vencedora de todas as propostas desta cotação
            $this->bd->prepare("
                UPDATE cotacao_propostas cp
                JOIN cotacao_fornecedores cf ON cf.id = cp.cotacao_fornecedor_id
                SET cp.vencedora = 0
                WHERE cf.cotacao_id = :cid
            ")->execute([':cid' => $cotacaoId]);

            // 3. Marcar propostas selecionadas como vencedoras
            if (!empty($propostaIds)) {
                $placeholders = implode(',', array_fill(0, count($propostaIds), '?'));
                $qSetVenc = $this->bd->prepare("UPDATE cotacao_propostas SET vencedora = 1 WHERE id IN ($placeholders)");
                $qSetVenc->execute($propostaIds);
            }

            // 4. Resetar vencedora em cotacao_fornecedores
            $this->bd->prepare("UPDATE cotacao_fornecedores SET vencedora = 0 WHERE cotacao_id = :cid")
                     ->execute([':cid' => $cotacaoId]);

            // 5. Marcar como vencedores os fornecedores que possuem pelo menos uma proposta vencedora
            $this->bd->prepare("
                UPDATE cotacao_fornecedores cf
                JOIN (
                    SELECT DISTINCT cotacao_fornecedor_id 
                    FROM cotacao_propostas 
                    WHERE vencedora = 1
                ) sub ON sub.cotacao_fornecedor_id = cf.id
                SET cf.vencedora = 1
                WHERE cf.cotacao_id = :cid
            ")->execute([':cid' => $cotacaoId]);

            // 6. Fechar cotação
            $this->bd->prepare("
                UPDATE cotacoes 
                SET status = 'fechada', data_encerramento = CURDATE(), atualizado_em = NOW() 
                WHERE id = :cid
            ")->execute([':cid' => $cotacaoId]);

            // 7. Se gerarOC for verdadeiro, gerar as Ordens de Compra
            if ($gerarOC && !empty($propostaIds)) {
                // Obter todos os fornecedores vencedores
                $qWinners = $this->bd->prepare("
                    SELECT cf.*, f.razao_social, f.email
                    FROM cotacao_fornecedores cf
                    JOIN fornecedores f ON f.id = cf.fornecedor_id
                    WHERE cf.cotacao_id = :cid AND cf.vencedora = 1
                ");
                $qWinners->execute([':cid' => $cotacaoId]);
                $winners = $qWinners->fetchAll();

                foreach ($winners as $cf) {
                    // Buscar propostas vencedoras deste fornecedor específico
                    $qProps = $this->bd->prepare("
                        SELECT cp.*, ci.solicitacao_item_id, ci.quantidade AS cot_item_quantidade
                        FROM cotacao_itens ci
                        JOIN cotacao_propostas cp ON cp.produto_id = ci.produto_id
                        WHERE ci.cotacao_id = :cid AND cp.cotacao_fornecedor_id = :cfid AND cp.vencedora = 1
                    ");
                    $qProps->execute([':cid' => $cotacaoId, ':cfid' => $cf['id']]);
                    $propostasForn = $qProps->fetchAll();

                    if (empty($propostasForn)) {
                        continue;
                    }

                    $subtotalItens = 0.00;
                    $maxPrazo = 0;
                    $taxasItens = 0.00;

                    foreach ($propostasForn as $p) {
                        $subtotalItens += (float)$p['preco_unitario'] * (float)$p['cot_item_quantidade'];
                        $taxasItens += (float)($p['taxas'] ?? 0);
                        if ((int)$p['prazo_entrega'] > $maxPrazo) {
                            $maxPrazo = (int)$p['prazo_entrega'];
                        }
                    }

                    $taxasGlobais = (float)($cf['taxas_adicionais'] ?? 0.00);
                    $valorTotal = max(0.00, $subtotalItens + $taxasItens + $taxasGlobais);
                    
                    $numeroOC = 'OC-' . date('Ymd') . '-' . rand(1000, 9999);
                    $prazoTexto = $maxPrazo > 0 ? "{$maxPrazo} dias" : "Imediato";
                    $tokenOC = bin2hex(random_bytes(32));

                    $qOC = $this->bd->prepare("
                        INSERT INTO ordens_compra (
                            numero, cotacao_id, token, solicitacao_id, fornecedor_id, usuario_id,
                            condicao_pagamento, modalidade_frete, transportadora, cnpj_transportadora, prazo_entrega, valor_total,
                            status, emitido_em, observacao, criado_em
                        ) VALUES (
                            :num, :cid, :token, :sid, :fid, :uid,
                            :cond, :frete, :transp, :cnpj_transp, :prazo, :total,
                            'aberto', CURDATE(), :obs, NOW()
                        )
                    ");
                    
                    // Condição de pagamento sugerida
                    $condicaoPagamentoDesc = '';
                    if (!empty($propostasForn[0]['condicao_pagamento_id'])) {
                        $qCond = $this->bd->prepare("SELECT descricao FROM condicoes_pagamento WHERE id = :id");
                        $qCond->execute([':id' => $propostasForn[0]['condicao_pagamento_id']]);
                        $condicaoPagamentoDesc = $qCond->fetchColumn() ?: '';
                    }

                    $qOC->execute([
                        ':num' => $numeroOC,
                        ':cid' => $cotacaoId,
                        ':token' => $tokenOC,
                        ':sid' => $cot['solicitacao_id'] ?? null,
                        ':fid' => $cf['fornecedor_id'],
                        ':uid' => $usuarioId,
                        ':cond' => $condicaoPagamentoDesc,
                        ':frete' => $cf['modalidade_frete'],
                        ':transp' => $cf['transportadora'],
                        ':cnpj_transp' => $cf['cnpj_transportadora'],
                        ':prazo' => $prazoTexto,
                        ':total' => $valorTotal,
                        ':obs' => $cf['observacao']
                    ]);
                    
                    $ordemId = (int)$this->bd->lastInsertId();

                    // Inserir os itens da OC
                    $numeroItem = 1;
                    $dataEmissao = date('Y-m-d');
                    foreach ($propostasForn as $p) {
                        $diasEntrega = (int)($p['prazo_entrega'] ?? 0);
                        $prazoEntregaItem = null;
                        if ($diasEntrega > 0) {
                            $dataEntrega = new \DateTime($dataEmissao);
                            $dataEntrega->add(new \DateInterval("P{$diasEntrega}D"));
                            $prazoEntregaItem = $dataEntrega->format('Y-m-d');
                        }

                        $this->bd->prepare("
                            INSERT INTO ordem_compra_itens (
                                ordem_id, numero_item, solicitacao_item_id, produto_id, quantidade, preco_unitario, prazo_entrega, condicao_pagamento_id
                            ) VALUES (
                                :oid, :num_item, :sid, :pid, :qtd, :price, :prazo, :pagto_id
                            )
                        ")->execute([
                            ':oid' => $ordemId,
                            ':num_item' => $numeroItem,
                            ':sid' => $p['solicitacao_item_id'],
                            ':pid' => $p['produto_id'],
                            ':qtd' => $p['cot_item_quantidade'],
                            ':price' => $p['preco_unitario'],
                            ':prazo' => $prazoEntregaItem,
                            ':pagto_id' => $p['condicao_pagamento_id']
                        ]);

                        $numeroItem++;
                    }

                    // Enviar e-mail de notificação para o fornecedor com link de aceite
                    if (!empty($cf['email'])) {
                        $revisarUrl = base_url('login/fornecedor/ordem?token=' . $tokenOC);
                        $assunto = "Ordem de Compra Gerada - " . $numeroOC;
                        $mensagem = "
                            <h2>Olá, " . htmlspecialchars($cf['razao_social']) . "!</h2>
                            <p>Uma nova Ordem de Compra <strong>" . $numeroOC . "</strong> foi gerada a partir da cotação de preços.</p>
                            <p>Por favor, revise os detalhes da ordem de compra e confirme a sua aprovação e o envio dos produtos clicando no botão abaixo:</p>
                            <p><a href=\"" . $revisarUrl . "\" style=\"display:inline-block; padding:10px 20px; background-color:#510B76; color:#fff; text-decoration:none; border-radius:5px; font-weight:bold;\">Visualizar e Confirmar Ordem de Compra</a></p>
                            <p>Caso o botão não funcione, copie e cole o seguinte link no seu navegador:</p>
                            <p>" . $revisarUrl . "</p>
                            <br>
                            <p>Atenciosamente,<br>Departamento de Compras</p>
                        ";
                        
                        \Config\Notificador::enviarEmail($cf['email'], $assunto, $mensagem);
                    }
                }
            }

            // 8. Atualizar a solicitação vinculada
            if ($cot['solicitacao_id']) {
                $this->bd->prepare("UPDATE solicitacoes SET status = 'concluido' WHERE id = :sid")
                         ->execute([':sid' => $cot['solicitacao_id']]);

                require_once __DIR__ . '/SolicitacaoModelo.php';
                $solicitacaoModelo = new SolicitacaoModelo();
                $solicitacaoModelo->registrarHistorico('solicitacoes', (int)$cot['solicitacao_id'], ['status' => 'autorizado'], ['status' => 'concluido'], $usuarioId);
            }

            $this->bd->commit();

            $this->registrarHistorico($this->tabela, $cotacaoId, ['status' => 'aberta'], ['status' => 'fechada'], $usuarioId, 'fechamento');

            return true;
        } catch (\Exception $e) {
            if ($this->bd->inTransaction()) {
                $this->bd->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Buscar cotação para resposta usando token (acesso público fornecedor)
     * Valida token e retorna dados agrupados por produto
     */
    public function buscarParaRespostaComToken(int $cotacaoId, string $token): ?array {
        $qVal = $this->bd->prepare("
            SELECT cf.id, cf.cotacao_id
            FROM cotacao_fornecedores cf
            WHERE cf.cotacao_id = :cid AND cf.token = :token AND cf.token IS NOT NULL LIMIT 1
        ");
        $qVal->execute([':cid' => $cotacaoId, ':token' => $token]);
        $fornecedorLinha = $qVal->fetch();

        if (!$fornecedorLinha) {
            return null;
        }

        return $this->buscarParaResposta($fornecedorLinha['cotacao_id'], $fornecedorLinha['id']);
    }

    /**
     * Buscar cotação com itens e fornecedor para responder
     * Agrupa itens pelo produto_id e soma as quantidades
     */
    public function buscarParaResposta(int $cotacaoId, int $cotacaoFornecedorId): ?array {
        $qCot = $this->bd->prepare("
            SELECT c.*, u.nome AS nome_comprador
            FROM cotacoes c
            LEFT JOIN usuarios u ON u.id = c.usuario_id
            WHERE c.id = :id LIMIT 1
        ");
        $qCot->execute([':id' => $cotacaoId]);
        $cotacao = $qCot->fetch();
        if (!$cotacao) return null;

        $qForn = $this->bd->prepare("
            SELECT cf.*
            FROM cotacao_fornecedores cf
            WHERE cf.id = :cfid AND cf.cotacao_id = :cid LIMIT 1
        ");
        $qForn->execute([':cfid' => $cotacaoFornecedorId, ':cid' => $cotacaoId]);
        $fornecedor = $qForn->fetch();
        if (!$fornecedor) return null;

        // Buscar itens agrupando por produto_id
        $qItens = $this->bd->prepare("
            SELECT
                p.id AS produto_id,
                p.nome AS nome_produto,
                p.codigo AS codigo_produto,
                SUM(ci.quantidade) AS quantidade_total,
                MIN(ci.numero_item) AS primeiro_numero,
                GROUP_CONCAT(ci.id) AS item_ids,
                GROUP_CONCAT(ci.numero_item ORDER BY ci.numero_item) AS numeros_item
            FROM cotacao_itens ci
            JOIN produtos p ON p.id = ci.produto_id
            WHERE ci.cotacao_id = :cid
            GROUP BY p.id
            ORDER BY MIN(ci.numero_item) ASC
        ");
        $qItens->execute([':cid' => $cotacaoId]);
        $itens = $qItens->fetchAll();

        // Buscar resposta anterior (se houver) para preencher os campos
        $qResp = $this->bd->prepare("
            SELECT *
            FROM cotacao_propostas
            WHERE cotacao_fornecedor_id = :cfid
        ");
        $qResp->execute([':cfid' => $cotacaoFornecedorId]);
        $respostasAnteriores = [];
        foreach ($qResp->fetchAll() as $resp) {
            $respostasAnteriores[$resp['produto_id']] = $resp;
        }

        $totalEnvios = $this->contarEnviosFornecedor($cotacaoFornecedorId);

        return [
            'cotacao' => $cotacao,
            'fornecedor' => $fornecedor,
            'itens' => $itens,
            'respostasAnteriores' => $respostasAnteriores,
            'totalEnvios' => $totalEnvios
        ];
    }

    /**
     * Contar quantas vezes o fornecedor enviou resposta para esta cotação
     * Usa campo de controle de versão ou conta registros de histórico
     */
    public function contarEnviosFornecedor(int $cotacaoFornecedorId): int {
        $q = $this->bd->prepare("
            SELECT COALESCE(numero_envio, 0) AS envios
            FROM cotacao_fornecedores
            WHERE id = :cfid LIMIT 1
        ");
        $q->execute([':cfid' => $cotacaoFornecedorId]);
        $resultado = $q->fetch();
        return (int)($resultado['envios'] ?? 0);
    }

    /**
     * Salvar resposta do fornecedor (dados globais + itens)
     * Agrupa por produto_id - uma proposta por produto com quantidade somada
     * Incrementa contador de envios
     */
    public function salvarResposta(int $cotacaoFornecedorId, array $dados): bool {
        try {
            $this->bd->beginTransaction();

            // Atualizar dados globais em cotacao_fornecedores e incrementar envio
            $qUpd = $this->bd->prepare("
                UPDATE cotacao_fornecedores SET
                    transportadora = :transp,
                    cnpj_transportadora = :cnpj,
                    modalidade_frete = :frete,
                    observacao = :obs,
                    status = 'respondido',
                    numero_envio = numero_envio + 1,
                    respondido_em = NOW()
                WHERE id = :cfid
            ");
            $qUpd->execute([
                ':transp' => $dados['transportadora'] ?? null,
                ':cnpj' => $dados['cnpj_transportadora'] ?? null,
                ':frete' => $dados['modalidade_frete'] ?? null,
                ':obs' => $dados['observacao'] ?? null,
                ':cfid' => $cotacaoFornecedorId
            ]);

            // Deletar propostas antigas
            $this->bd->prepare("DELETE FROM cotacao_propostas WHERE cotacao_fornecedor_id = :cfid")
                ->execute([':cfid' => $cotacaoFornecedorId]);

            // Inserir nova proposta por produto (agrupada)
            foreach ($dados['itens'] ?? [] as $item) {
                $this->bd->prepare("
                    INSERT INTO cotacao_propostas (
                        cotacao_fornecedor_id, produto_id, modelo, quantidade,
                        preco_unitario, prazo_entrega, condicao_pagamento_id,
                        taxas, garantia, disponivel
                    ) VALUES (
                        :cfid, :pid, :modelo, :qtd,
                        :preco, :prazo, :cond_id,
                        :taxas, :garantia, :disponivel
                    )
                ")->execute([
                    ':cfid' => $cotacaoFornecedorId,
                    ':pid' => (int)$item['produto_id'],
                    ':modelo' => $item['modelo'] ?: null,
                    ':qtd' => (float)$item['quantidade_total'],
                    ':preco' => (float)$item['preco_unitario'],
                    ':prazo' => (int)($item['prazo_entrega'] ?? 0),
                    ':cond_id' => !empty($item['condicao_pagamento_id']) ? (int)$item['condicao_pagamento_id'] : null,
                    ':taxas' => (float)($item['taxas'] ?? 0),
                    ':garantia' => $item['garantia'] ?: null,
                    ':disponivel' => (int)($item['disponivel'] ?? 1)
                ]);
            }

            $this->bd->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->bd->inTransaction()) $this->bd->rollBack();
            return false;
        }
    }
}
