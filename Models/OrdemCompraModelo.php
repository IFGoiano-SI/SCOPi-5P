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
        $sql = "SELECT oc.*, f.razao_social AS nome_fornecedor
                FROM ordens_compra oc
                LEFT JOIN fornecedores f ON f.id = oc.fornecedor_id
                WHERE 1=1";
        $p = [];
        if (!empty($filtros['numero']))  { $sql .= ' AND oc.numero LIKE :num';    $p[':num']    = "%{$filtros['numero']}%"; }
        if (!empty($filtros['status']))  { $sql .= ' AND oc.status = :status';    $p[':status'] = $filtros['status']; }
        if (!empty($filtros['periodo'])) { $sql .= ' AND DATE(oc.emitido_em)>=:per'; $p[':per'] = $filtros['periodo']; }
        $sql .= ' ORDER BY oc.criado_em DESC';
        $q = $this->bd->prepare($sql); $q->execute($p);
        return $q->fetchAll();
    }

    public function salvar(array $dados, int $usuarioId): int {
        $id = (int)($dados['id'] ?? 0);
        $fornecedorId = (int)$dados['fornecedor_id'];
        $condicaoPagamento = trim($dados['condicao_pagamento'] ?? '');
        $modalidadeFrete = trim($dados['modalidade_frete'] ?? '');
        $prazoEntrega = trim($dados['prazo_entrega'] ?? '');
        $observacao = trim($dados['observacao'] ?? '');
        $valorTotal = (float)($dados['valor_total'] ?? 0);

        if ($id > 0) {
            $anterior = $this->buscarPorId($id);
            $q = $this->bd->prepare("
                UPDATE ordens_compra SET
                    fornecedor_id = :fid,
                    condicao_pagamento = :cond,
                    modalidade_frete = :frete,
                    prazo_entrega = :prazo,
                    observacao = :obs,
                    valor_total = :total,
                    atualizado_em = NOW()
                WHERE id = :id AND status = 'aberta'
            ");
            $q->execute([
                ':fid' => $fornecedorId,
                ':cond' => $condicaoPagamento,
                ':frete' => $modalidadeFrete,
                ':prazo' => $prazoEntrega,
                ':obs' => $observacao,
                ':total' => $valorTotal,
                ':id' => $id
            ]);
            $this->registrarHistorico($this->tabela, $id, $anterior, $dados, $usuarioId);
            return $id;
        } else {
            $numero = 'OC-' . date('Ymd') . '-' . rand(1000, 9999);
            $solicitacaoId = !empty($dados['solicitacao_id']) ? (int)$dados['solicitacao_id'] : null;
            $cotacaoId = !empty($dados['cotacao_id']) ? (int)$dados['cotacao_id'] : null;

            $q = $this->bd->prepare("
                INSERT INTO ordens_compra (
                    numero, cotacao_id, solicitacao_id, fornecedor_id,
                    condicao_pagamento, modalidade_frete, prazo_entrega,
                    observacao, valor_total, usuario_id, status, emitido_em, criado_em
                ) VALUES (
                    :num, :cot_id, :sol_id, :fid,
                    :cond, :frete, :prazo,
                    :obs, :total, :uid, 'aberta', CURDATE(), NOW()
                )
            ");
            $q->execute([
                ':num' => $numero,
                ':cot_id' => $cotacaoId,
                ':sol_id' => $solicitacaoId,
                ':fid' => $fornecedorId,
                ':cond' => $condicaoPagamento,
                ':frete' => $modalidadeFrete,
                ':prazo' => $prazoEntrega,
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
            UPDATE ordens_compra SET status = 'autorizada', aprovador_id = :aid, autorizado_em = NOW(), atualizado_em = NOW()
            WHERE id = :id AND status = 'aberta'
        ");
        $q->execute([':aid' => $aprovadorId, ':id' => $id]);
        $ok = $q->rowCount() > 0;
        if ($ok) $this->registrarHistorico($this->tabela, $id, [], ['status' => 'autorizada'], $aprovadorId);
        return $ok;
    }

    /**
     * RF13: Remover autorização (se ainda não foi enviada)
     */
    public function desautorizar(int $id): bool {
        $q = $this->bd->prepare("
            UPDATE ordens_compra SET status = 'aberta', aprovador_id = NULL, autorizado_em = NULL, atualizado_em = NOW()
            WHERE id = :id AND status = 'autorizada'
        ");
        $q->execute([':id' => $id]);
        $ok = $q->rowCount() > 0;
        if ($ok) {
            $usuarioId = \Config\Auxiliares::usuarioLogado()['id'] ?? 0;
            $this->registrarHistorico($this->tabela, $id, [], ['status' => 'aberta', 'acao' => 'desautorizou'], $usuarioId);
        }
        return $ok;
    }

    /**
     * RF13: Enviar ordem ao fornecedor
     */
    public function enviar(int $id): bool {
        $q = $this->bd->prepare("
            UPDATE ordens_compra SET status = 'enviada', enviado_em = NOW(), atualizado_em = NOW()
            WHERE id = :id AND status = 'autorizada'
        ");
        $q->execute([':id' => $id]);
        $ok = $q->rowCount() > 0;
        if ($ok) {
            $usuarioId = \Config\Auxiliares::usuarioLogado()['id'] ?? 0;
            $this->registrarHistorico($this->tabela, $id, [], ['status' => 'enviada'], $usuarioId);
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
            $novoStatus = 'cancelada';
        } elseif (($atendidos + $cancelados) === $total && $atendidos > 0) {
            $novoStatus = 'concluida';
        } elseif ($atendidos > 0 || $parciais > 0) {
            $novoStatus = 'parcialmente_atendida';
        } else {
            return; // Nenhuma mudança necessária
        }

        $qUp = $this->bd->prepare("UPDATE ordens_compra SET status = :st, atualizado_em = NOW() WHERE id = :oid");
        $qUp->execute([':st' => $novoStatus, ':oid' => $ordemId]);
    }

    /**
     * Salvar itens da ordem de compra
     */
    public function salvarItens(int $ordemId, array $itens): void {
        // Remove itens antigos
        $this->bd->prepare("DELETE FROM ordem_compra_itens WHERE ordem_id = :oid")->execute([':oid' => $ordemId]);
        
        $q = $this->bd->prepare("
            INSERT INTO ordem_compra_itens (ordem_id, produto_id, quantidade, preco_unitario)
            VALUES (:oid, :pid, :qtd, :preco)
        ");
        foreach ($itens as $item) {
            $q->execute([
                ':oid' => $ordemId,
                ':pid' => (int)$item['produto_id'],
                ':qtd' => (float)$item['quantidade'],
                ':preco' => (float)$item['preco_unitario']
            ]);
        }
    }

    /**
     * Buscar itens de uma ordem
     */
    public function buscarItens(int $ordemId): array {
        $q = $this->bd->prepare("
            SELECT oci.*, p.nome AS produto_nome, p.codigo AS produto_codigo
            FROM ordem_compra_itens oci
            LEFT JOIN produtos p ON p.id = oci.produto_id
            WHERE oci.ordem_id = :oid
        ");
        $q->execute([':oid' => $ordemId]);
        return $q->fetchAll();
    }
}
