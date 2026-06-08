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
                VALUES (:num, NULL, :uid, 'aberto', :dta, :dte, NOW())
            ");
            $q->execute([
                ':num' => $numero,
                ':uid' => $usuarioId,
                ':dta' => $dataAbertura,
                ':dte' => $dataEncerramento
            ]);
            $cotacaoId = (int) $this->bd->lastInsertId();

            $this->registrarHistorico($this->tabela, $cotacaoId, [], ['status' => 'aberto'], $usuarioId, 'abertura da cotação');
            
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
            if ($cot['status'] !== 'rascunho' && $cot['status'] !== 'aberto') {
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

    public function definirVencedora(int $cotacaoId, int $cotacaoFornecedorId, int $usuarioId, bool $gerarOC = true): bool {
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

            $qProp = $this->bd->prepare("
                SELECT cp.*
                FROM cotacao_propostas cp
                WHERE cp.cotacao_fornecedor_id = :cfid
            ");
            $qProp->execute([':cfid' => $cotacaoFornecedorId]);
            $propostas = $qProp->fetchAll();

            if ($gerarOC) {
                $subtotalItens = 0.00;
                $maxPrazo = 0;
                // Dados globais da transportadora (do fornecedor, não por item)
                $transportadora = $cf['transportadora'] ?? null;
                $cnpjTransportadora = $cf['cnpj_transportadora'] ?? null;
                $modalidadeFrete = $cf['modalidade_frete'] ?? null;

                foreach ($propostas as $p) {
                    $subtotalLinha = (float)$p['preco_unitario'] * (float)$p['quantidade'];
                    $subtotalItens += $subtotalLinha;
                    if ((int)$p['prazo_entrega'] > $maxPrazo) {
                        $maxPrazo = (int)$p['prazo_entrega'];
                    }
                }

                $taxas = 0.00;
                foreach ($propostas as $p) {
                    $taxas += (float)($p['taxas'] ?? 0);
                }
                $valorTotal = max(0.00, $subtotalItens + $taxas);

                $numeroOC = 'OC-' . date('Ymd') . '-' . rand(1000, 9999);
                $prazoTexto = $maxPrazo > 0 ? "{$maxPrazo} dias" : "Imediato";

            $qOC = $this->bd->prepare("
                INSERT INTO ordens_compra (
                    numero, cotacao_id, solicitacao_id, fornecedor_id, usuario_id,
                    modalidade_frete, transportadora, cnpj_transportadora, prazo_entrega, valor_total,
                    status, emitido_em,
                    observacao, criado_em
                ) VALUES (
                    :num, :cid, :sid, :fid, :uid,
                    :frete, :transp, :cnpj_transp, :prazo, :total,
                    'aberto', CURDATE(),
                    :obs, NOW()
                )
            ");
            $qOC->execute([
                ':num' => $numeroOC,
                ':cid' => $cotacaoId,
                ':sid' => $cf['solicitacao_id'] ?? null,
                ':fid' => $cf['fornecedor_id'],
                ':uid' => $usuarioId,
                ':frete' => $modalidadeFrete,
                ':transp' => $transportadora,
                ':cnpj_transp' => $cnpjTransportadora,
                ':prazo' => $prazoTexto,
                ':total' => $valorTotal,
                ':obs' => $cf['observacao'] ?? null
            ]);
            $ordemId = (int) $this->bd->lastInsertId();

            // Inserir os itens baseados na cotacao_itens (preservando o solicitacao_item_id original)
            $qItensCotacao = $this->bd->prepare("SELECT * FROM cotacao_itens WHERE cotacao_id = :cid");
            $qItensCotacao->execute([':cid' => $cotacaoId]);
            $itensCotacao = $qItensCotacao->fetchAll();

            $numeroItem = 1;
            $dataEmissao = date('Y-m-d'); // Data de emissão da OC
            foreach ($itensCotacao as $itemCotacao) {
                // Encontrar o preço, prazo (dias) e condição de pagamento na proposta
                $precoUnitario = 0;
                $diasEntrega = null;
                $condPagamentoId = null;
                foreach ($propostas as $p) {
                    if ($p['produto_id'] == $itemCotacao['produto_id']) {
                        $precoUnitario = $p['preco_unitario'];
                        $diasEntrega = (int)($p['prazo_entrega'] ?? 0);
                        $condPagamentoId = !empty($p['condicao_pagamento_id']) ? (int)$p['condicao_pagamento_id'] : null;
                        break;
                    }
                }

                // Converter dias em data (emissão + dias)
                $prazoEntrega = null;
                if ($diasEntrega > 0) {
                    $dataEntrega = new \DateTime($dataEmissao);
                    $dataEntrega->add(new \DateInterval("P{$diasEntrega}D"));
                    $prazoEntrega = $dataEntrega->format('Y-m-d');
                }

                $this->bd->prepare("
                    INSERT INTO ordem_compra_itens (ordem_id, numero_item, solicitacao_item_id, produto_id, quantidade, preco_unitario, prazo_entrega, condicao_pagamento_id)
                    VALUES (:oid, :num_item, :sid, :pid, :qtd, :price, :prazo, :pagto_id)
                ")->execute([
                    ':oid' => $ordemId,
                    ':num_item' => $numeroItem,
                    ':sid' => $itemCotacao['solicitacao_item_id'],
                    ':pid' => $itemCotacao['produto_id'],
                    ':qtd' => $itemCotacao['quantidade'],
                    ':price' => $precoUnitario,
                    ':prazo' => $prazoEntrega,
                    ':pagto_id' => $condPagamentoId
                ]);

                $numeroItem++;
            }
            } // Fim if ($gerarOC)

            if ($cf['solicitacao_id']) {
                $this->bd->prepare("UPDATE solicitacoes SET status = 'concluido' WHERE id = :sid")
                         ->execute([':sid' => $cf['solicitacao_id']]);

                require_once __DIR__ . '/SolicitacaoModelo.php';
                $solicitacaoModelo = new SolicitacaoModelo();
                $solicitacaoModelo->atualizarStatusCapa($cf['solicitacao_id'], $usuarioId);
                $solicitacaoModelo->registrarHistorico('solicitacoes', (int)$cf['solicitacao_id'], ['status' => 'autorizado'], ['status' => 'concluido'], $usuarioId);
            }

            $this->bd->commit();

            $detalhes = $gerarOC ? "Proposta vencedora selecionada. Ordem de compra gerada." : "Proposta vencedora selecionada sem OC.";
            $this->registrarHistorico($this->tabela, $cotacaoId, ['status' => 'aberto'], ['status' => 'fechada'], $usuarioId, $detalhes);

            return true;
        } catch (\Exception $e) {
            if ($this->bd->inTransaction()) $this->bd->rollBack();
            return false;
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
