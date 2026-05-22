<?php
namespace Models;

class OrdemCompraModelo extends ModeloBase {
    protected string $tabela = 'ordens_compra';

    public function buscarPorId(int $id): ?array {
        $q = $this->bd->prepare("
            SELECT oc.*, f.razao_social AS nome_fornecedor, cp.descricao AS condicao_pagamento_desc
            FROM ordens_compra oc
            LEFT JOIN fornecedores f ON f.id = oc.fornecedor_id
            LEFT JOIN condicoes_pagamento cp ON cp.id = oc.condicao_pagamento_id
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
        $sql = "SELECT oc.*, f.razao_social AS nome_fornecedor, cp.descricao AS condicao_pagamento_desc
                FROM ordens_compra oc
                LEFT JOIN fornecedores f ON f.id = oc.fornecedor_id
                LEFT JOIN condicoes_pagamento cp ON cp.id = oc.condicao_pagamento_id
                WHERE 1=1";
        $p = [];
        if (!empty($filtros['numero']))  { $sql .= ' AND oc.numero LIKE :num';    $p[':num']    = "%{$filtros['numero']}%"; }
        if (!empty($filtros['status']))  { $sql .= ' AND oc.status = :status';    $p[':status'] = $filtros['status']; }
        if (!empty($filtros['periodo'])) { $sql .= ' AND DATE(oc.emitido_em)>=:per'; $p[':per'] = $filtros['periodo']; }
        $sql .= ' ORDER BY oc.emitido_em DESC';
        $q = $this->bd->prepare($sql); $q->execute($p);
        return $q->fetchAll();
    }

    public function salvar(array $dados, int $usuarioId): int {
        $id = (int)($dados['id'] ?? 0);
        $fornecedorId = (int)$dados['fornecedor_id'];
        $condicaoPagamentoId = !empty($dados['condicao_pagamento_id']) ? (int)$dados['condicao_pagamento_id'] : null;
        $condicaoPagamentoDesc = '';

        if ($condicaoPagamentoId) {
            $qCP = $this->bd->prepare("SELECT descricao FROM condicoes_pagamento WHERE id = :id");
            $qCP->execute([':id' => $condicaoPagamentoId]);
            $condicaoPagamentoDesc = $qCP->fetchColumn() ?: '';
        }

        $prazoEntrega = trim($dados['prazo_entrega'] ?? '');
        $descontoValor = (float)($dados['desconto_valor'] ?? 0);
        $descontoPercentual = (float)($dados['desconto_percentual'] ?? 0);
        $observacao = trim($dados['observacao'] ?? '');
        $valorTotal = (float)($dados['valor_total'] ?? 0);

        if ($id > 0) {
            // Update
            $anterior = $this->buscarPorId($id);
            $q = $this->bd->prepare("
                UPDATE ordens_compra SET
                    fornecedor_id = :fid,
                    condicao_pagamento_id = :cpid,
                    condicao_pagamento = :cond,
                    prazo_entrega = :prazo,
                    desconto_valor = :desc_val,
                    desconto_percentual = :desc_pct,
                    observacao = :obs,
                    valor_total = :total,
                    atualizado_em = NOW()
                WHERE id = :id
            ");
            $q->execute([
                ':fid' => $fornecedorId,
                ':cpid' => $condicaoPagamentoId,
                ':cond' => $condicaoPagamentoDesc,
                ':prazo' => $prazoEntrega,
                ':desc_val' => $descontoValor,
                ':desc_pct' => $descontoPercentual,
                ':obs' => $observacao,
                ':total' => $valorTotal,
                ':id' => $id
            ]);
            $this->registrarHistorico($this->tabela, $id, $anterior, $dados, $usuarioId);
            return $id;
        } else {
            // Insert new Purchase Order
            $numero = 'OC-' . date('Ymd') . '-' . rand(1000, 9999);
            $q = $this->bd->prepare("
                INSERT INTO ordens_compra (
                    numero, fornecedor_id, condicao_pagamento_id, condicao_pagamento,
                    prazo_entrega, desconto_valor, desconto_percentual, observacao,
                    valor_total, usuario_id, status, emitido_em, criado_em
                ) VALUES (
                    :num, :fid, :cpid, :cond,
                    :prazo, :desc_val, :desc_pct, :obs,
                    :total, :uid, 'rascunho', CURDATE(), NOW()
                )
            ");
            $q->execute([
                ':num' => $numero,
                ':fid' => $fornecedorId,
                ':cpid' => $condicaoPagamentoId,
                ':cond' => $condicaoPagamentoDesc,
                ':prazo' => $prazoEntrega,
                ':desc_val' => $descontoValor,
                ':desc_pct' => $descontoPercentual,
                ':obs' => $observacao,
                ':total' => $valorTotal,
                ':uid' => $usuarioId
            ]);
            $newId = (int)$this->bd->lastInsertId();
            $this->registrarHistorico($this->tabela, $newId, [], $dados, $usuarioId);
            return $newId;
        }
    }
}
