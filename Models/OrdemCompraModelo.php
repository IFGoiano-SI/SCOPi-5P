<?php
namespace Models;

class OrdemCompraModelo extends ModeloBase {
    protected string $tabela = 'ordens_compra';

    public function buscarPorId(int $id): ?array {
        $q = $this->bd->prepare("
            SELECT oc.*, f.razao_social AS nome_fornecedor,
                   u.nome AS nome_comprador, a.nome AS nome_aprovador
            FROM ordens_compra oc
            LEFT JOIN fornecedores f ON f.id = oc.fornecedor_id
            LEFT JOIN usuarios u ON u.id = oc.usuario_id
            LEFT JOIN usuarios a ON a.id = oc.aprovador_id
            WHERE oc.id = :id LIMIT 1
        ");
        $q->execute([':id' => $id]);
        return $q->fetch() ?: null;
    }

    public function contarPorStatus(): array {
        $q = $this->bd->query("SELECT status, COUNT(*) AS total FROM ordens_compra GROUP BY status");
        return $q->fetchAll();
    }

    public function listarComFiltros(array $filtros = []): array {
        $sql = "SELECT oc.*, f.razao_social AS nome_fornecedor, f.codigo AS codigo_fornecedor
                FROM ordens_compra oc
                LEFT JOIN fornecedores f ON f.id = oc.fornecedor_id
                WHERE 1=1";
        $p = [];
        if (!empty($filtros['numero']))           { $sql .= ' AND oc.numero LIKE :num';           $p[':num']    = "%{$filtros['numero']}%"; }
        if (!empty($filtros['status']))           { $sql .= ' AND oc.status = :status';           $p[':status'] = $filtros['status']; }
        if (!empty($filtros['periodo']))          { $sql .= ' AND DATE(oc.emitido_em) >= :per';   $p[':per']    = $filtros['periodo']; }
        if (!empty($filtros['data_inicial']))     { $sql .= ' AND DATE(oc.emitido_em) >= :dti';   $p[':dti']    = $filtros['data_inicial']; }
        if (!empty($filtros['data_final']))       { $sql .= ' AND DATE(oc.emitido_em) <= :dtf';   $p[':dtf']    = $filtros['data_final']; }
        if (!empty($filtros['fornecedor_codigo'])) { $sql .= ' AND f.codigo = :fcod';             $p[':fcod']   = $filtros['fornecedor_codigo']; }
        $sql .= ' ORDER BY oc.criado_em DESC';
        $q = $this->bd->prepare($sql); $q->execute($p);
        return $q->fetchAll();
    }

    public function salvar(array $dados, int $usuarioId): int {
        $id = (int)($dados['id'] ?? 0);
        $fornecedorId = (int)$dados['fornecedor_id'];
        $condicaoPagamento = trim($dados['condicao_pagamento'] ?? '');
        $modalidadeFrete = trim($dados['modalidade_frete'] ?? '');
        $observacao = trim($dados['observacao'] ?? '');
        $valorTotal = (float)($dados['valor_total'] ?? 0);

        if ($id > 0) {
            $anterior = $this->buscarPorId($id);
            $q = $this->bd->prepare("
                UPDATE ordens_compra SET
                    fornecedor_id = :fid,
                    condicao_pagamento = :cond,
                    modalidade_frete = :frete,
                    observacao = :obs,
                    valor_total = :total,
                    atualizado_em = NOW()
                WHERE id = :id AND status = 'aberto'
            ");
            $q->execute([
                ':fid' => $fornecedorId,
                ':cond' => $condicaoPagamento,
                ':frete' => $modalidadeFrete,
                ':obs' => $observacao,
                ':total' => $valorTotal,
                ':id' => $id
            ]);
            $this->registrarHistorico($this->tabela, $id, $anterior, $dados, $usuarioId);
            return $id;
        } else {
            $qMax = $this->bd->prepare("SELECT MAX(CAST(numero AS UNSIGNED)) FROM ordens_compra");
            $qMax->execute();
            $maxNum = (int)$qMax->fetchColumn() ?? 0;
            $numero = str_pad($maxNum + 1, 6, '0', STR_PAD_LEFT);
            $solicitacaoId = !empty($dados['solicitacao_id']) ? (int)$dados['solicitacao_id'] : null;
            $cotacaoId = !empty($dados['cotacao_id']) ? (int)$dados['cotacao_id'] : null;
            $tokenOC = bin2hex(random_bytes(32));

            $q = $this->bd->prepare("
                INSERT INTO ordens_compra (
                    numero, cotacao_id, token, solicitacao_id, fornecedor_id,
                    condicao_pagamento, modalidade_frete,
                    observacao, valor_total, usuario_id, status, emitido_em, criado_em
                ) VALUES (
                    :num, :cot_id, :token, :sol_id, :fid,
                    :cond, :frete,
                    :obs, :total, :uid, 'aberto', CURDATE(), NOW()
                )
            ");
            $q->execute([
                ':num' => $numero,
                ':cot_id' => $cotacaoId,
                ':token' => $tokenOC,
                ':sol_id' => $solicitacaoId,
                ':fid' => $fornecedorId,
                ':cond' => $condicaoPagamento,
                ':frete' => $modalidadeFrete,
                ':obs' => $observacao,
                ':total' => $valorTotal,
                ':uid' => $usuarioId
            ]);
            $newId = (int)$this->bd->lastInsertId();
            $this->registrarHistorico($this->tabela, $newId, [], $dados, $usuarioId);
            return $newId;
        }
    }

    /**
     * RF13: Autorizar ordem de compra (gerente de compras)
     */
    public function autorizar(int $id, int $aprovadorId): bool {
        $q = $this->bd->prepare("
            UPDATE ordens_compra SET status = 'autorizado', aprovador_id = :aid, autorizado_em = NOW(), atualizado_em = NOW()
            WHERE id = :id AND status = 'aberto'
        ");
        $q->execute([':aid' => $aprovadorId, ':id' => $id]);
        $ok = $q->rowCount() > 0;
        if ($ok) $this->registrarHistorico($this->tabela, $id, [], ['status' => 'autorizado'], $aprovadorId);
        return $ok;
    }

    /**
     * RF13: Remover autorização (se ainda não foi enviado)
     */
    public function desautorizar(int $id): bool {
        $q = $this->bd->prepare("
            UPDATE ordens_compra SET status = 'aberto', aprovador_id = NULL, autorizado_em = NULL, atualizado_em = NOW()
            WHERE id = :id AND status = 'autorizado'
        ");
        $q->execute([':id' => $id]);
        $ok = $q->rowCount() > 0;
        if ($ok) {
            $usuarioId = \Config\Auxiliares::usuarioLogado()['id'] ?? 0;
            $this->registrarHistorico($this->tabela, $id, [], ['status' => 'aberto', 'acao' => 'desautorizou'], $usuarioId);
        }
        return $ok;
    }

    public function cancelar(int $id, int $usuarioId): bool {
        $q = $this->bd->prepare("
            UPDATE ordens_compra SET status = 'cancelado', atualizado_em = NOW()
            WHERE id = :id AND status IN ('aberto', 'autorizado')
        ");
        $q->execute([':id' => $id]);
        $ok = $q->rowCount() > 0;
        if ($ok) {
            $this->registrarHistorico($this->tabela, $id, [], ['status' => 'cancelado'], $usuarioId);
        }
        return $ok;
    }

    /**
     * RF13: Enviar ordem ao fornecedor
     */
    public function enviar(int $id): bool {
        $q = $this->bd->prepare("
            UPDATE ordens_compra SET status = 'enviado', enviado_em = NOW(), atualizado_em = NOW()
            WHERE id = :id AND status = 'autorizado'
        ");
        $q->execute([':id' => $id]);
        $ok = $q->rowCount() > 0;
        if ($ok) {
            $usuarioId = \Config\Auxiliares::usuarioLogado()['id'] ?? 0;
            $this->registrarHistorico($this->tabela, $id, [], ['status' => 'enviado'], $usuarioId);
        }
        return $ok;
    }

    /**
     * RF13: Cancelar item individual da ordem
     */
    public function cancelarItem(int $itemId): bool {
        $q = $this->bd->prepare("
            UPDATE ordem_compra_itens SET status_item = 'cancelado' WHERE id = :id AND status_item != 'cancelado'
        ");
        $q->execute([':id' => $itemId]);

        if ($q->rowCount() > 0) {
            // Buscar ordem para verificar se TODOS os itens estão cancelados
            $qOrdem = $this->bd->prepare("SELECT ordem_id FROM ordem_compra_itens WHERE id = :id");
            $qOrdem->execute([':id' => $itemId]);
            $ordemId = $qOrdem->fetchColumn();

            if ($ordemId) {
                $this->atualizarStatusPorItens((int)$ordemId);
            }
            return true;
        }
        return false;
    }

    /**
     * RF13: Atualizar status da ordem com base nos itens atendidos/cancelados
     * Usado quando NFs são vinculadas ou itens são cancelados
     */
    public function atualizarStatusPorItens(int $ordemId): void {
        $q = $this->bd->prepare("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status_item = 'atendido' THEN 1 ELSE 0 END) AS atendidos,
                SUM(CASE WHEN status_item = 'cancelado' THEN 1 ELSE 0 END) AS cancelados,
                SUM(CASE WHEN status_item = 'parcial' THEN 1 ELSE 0 END) AS parciais
            FROM ordem_compra_itens WHERE ordem_id = :oid
        ");
        $q->execute([':oid' => $ordemId]);
        $r = $q->fetch();

        $total = (int)$r['total'];
        $atendidos = (int)$r['atendidos'];
        $cancelados = (int)$r['cancelados'];
        $parciais = (int)$r['parciais'];

        if ($cancelados === $total) {
            $novoStatus = 'cancelado';
        } elseif (($atendidos + $cancelados) === $total && $atendidos > 0) {
            $novoStatus = 'concluido';
        } elseif ($atendidos > 0 || $parciais > 0) {
            $novoStatus = 'parcialmente_atendido';
        } else {
            // Se o status atual for concluído ou parcialmente atendido, mas não há mais itens atendidos/parciais, volta para enviado
            $qCurrent = $this->bd->prepare("SELECT status FROM ordens_compra WHERE id = :oid");
            $qCurrent->execute([':oid' => $ordemId]);
            $currentStatus = $qCurrent->fetchColumn();
            if (in_array($currentStatus, ['parcialmente_atendido', 'concluido'])) {
                $novoStatus = 'enviado';
            } else {
                return;
            }
        }

        $qUp = $this->bd->prepare("UPDATE ordens_compra SET status = :st, atualizado_em = NOW() WHERE id = :oid");
        $qUp->execute([':st' => $novoStatus, ':oid' => $ordemId]);
    }

    /**
     * Salvar itens da ordem de compra
     */
    public function salvarItens(int $ordemId, array $itens): void {
        $qMax = $this->bd->prepare("SELECT MAX(numero_item) FROM ordem_compra_itens WHERE ordem_id = :oid");
        $qMax->execute([':oid' => $ordemId]);
        $maxNum = (int)$qMax->fetchColumn();

        $idsManter = [];
        foreach ($itens as $item) {
            if (!empty($item['id'])) {
                $idsManter[] = (int)$item['id'];
            }
        }

        if (empty($idsManter)) {
            $this->bd->prepare("DELETE FROM ordem_compra_itens WHERE ordem_id = :oid")->execute([':oid' => $ordemId]);
        } else {
            $placeholders = implode(',', array_fill(0, count($idsManter), '?'));
            $qDel = $this->bd->prepare("DELETE FROM ordem_compra_itens WHERE ordem_id = ? AND id NOT IN ($placeholders)");
            $qDel->execute(array_merge([$ordemId], $idsManter));
        }

        foreach ($itens as $item) {
            $solItemId = !empty($item['solicitacao_item_id']) ? (int)$item['solicitacao_item_id'] : null;
            $prazoEntrega = !empty($item['prazo_entrega']) ? $item['prazo_entrega'] : null;
            $condPagId = !empty($item['condicao_pagamento_id']) ? (int)$item['condicao_pagamento_id'] : null;

            if (!empty($item['id'])) {
                $this->bd->prepare("UPDATE ordem_compra_itens SET quantidade = :qtd, preco_unitario = :preco, prazo_entrega = :prazo, condicao_pagamento_id = :cond_id WHERE id = :id")
                    ->execute([
                        ':qtd' => (float)$item['quantidade'],
                        ':preco' => (float)$item['preco_unitario'],
                        ':prazo' => $prazoEntrega,
                        ':cond_id' => $condPagId,
                        ':id' => $item['id']
                    ]);
            } else {
                $maxNum++;
                $this->bd->prepare("
                    INSERT INTO ordem_compra_itens (ordem_id, numero_item, solicitacao_item_id, produto_id, quantidade, preco_unitario, prazo_entrega, condicao_pagamento_id)
                    VALUES (:oid, :num, :sid, :pid, :qtd, :preco, :prazo, :cond_id)
                ")->execute([
                    ':oid' => $ordemId,
                    ':num' => $maxNum,
                    ':sid' => $solItemId,
                    ':pid' => (int)$item['produto_id'],
                    ':qtd' => (float)$item['quantidade'],
                    ':preco' => (float)$item['preco_unitario'],
                    ':prazo' => $prazoEntrega,
                    ':cond_id' => $condPagId
                ]);
            }
        }
    }

    public function excluirItem(int $itemId, int $usuarioId): bool {
        try {
            $this->bd->beginTransaction();
            $q = $this->bd->prepare("SELECT ordem_id, solicitacao_item_id FROM ordem_compra_itens WHERE id = :id");
            $q->execute([':id' => $itemId]);
            $item = $q->fetch();
            if (!$item) {
                $this->bd->rollBack();
                return false;
            }

            $ordemId = (int)$item['ordem_id'];
            $solItemId = !empty($item['solicitacao_item_id']) ? (int)$item['solicitacao_item_id'] : null;

            $qOrd = $this->bd->prepare("SELECT status FROM ordens_compra WHERE id = :oid");
            $qOrd->execute([':oid' => $ordemId]);
            $ordem = $qOrd->fetch();

            if ($ordem['status'] !== 'aberto') {
                $this->bd->rollBack();
                return false;
            }

            $ok = $this->bd->prepare("DELETE FROM ordem_compra_itens WHERE id = :id")->execute([':id' => $itemId]);
            
            if ($ok) {
                $this->registrarHistorico($this->tabela, $ordemId, [], ['acao' => 'Exclusão de item ID ' . $itemId], $usuarioId);
            }
            $this->bd->commit();
            return $ok;
        } catch (\Exception $e) {
            if ($this->bd->inTransaction()) $this->bd->rollBack();
            return false;
        }
    }

    /**
     * Buscar itens de uma ordem (com prazo_entrega por item e condição de pagamento)
     */
    public function buscarItens(int $ordemId): array {
        $q = $this->bd->prepare("
            SELECT oci.*, p.nome AS produto_nome, p.codigo AS produto_codigo,
                   cp.descricao AS condicao_pagamento_descricao
            FROM ordem_compra_itens oci
            LEFT JOIN produtos p ON p.id = oci.produto_id
            LEFT JOIN condicoes_pagamento cp ON cp.id = oci.condicao_pagamento_id
            WHERE oci.ordem_id = :oid
            ORDER BY oci.numero_item ASC
        ");
        $q->execute([':oid' => $ordemId]);
        return $q->fetchAll();
    }
}
