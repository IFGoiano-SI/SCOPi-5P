<?php
namespace Models;

class SolicitacaoModelo extends ModeloBase {
    protected string $tabela = 'solicitacoes';

    public function contarPorStatus(): array {
        $q = $this->bd->query("SELECT status, COUNT(*) AS total FROM solicitacoes GROUP BY status");
        return $q->fetchAll();
    }

    public function listarComFiltros(array $filtros = [], ?int $departamentoId = null): array {
        $sql = "SELECT s.*, d.nome AS nome_departamento, u.nome AS nome_solicitante
                FROM solicitacoes s
                LEFT JOIN departamentos d ON d.id = s.departamento_id
                LEFT JOIN usuarios u ON u.id = s.usuario_id
                WHERE 1=1";
        $p = [];
        if ($departamentoId) { $sql .= ' AND s.departamento_id=:dep'; $p[':dep']=$departamentoId; }
        if (!empty($filtros['numero']))                { $sql .= ' AND s.numero LIKE :num';              $p[':num']    = "%{$filtros['numero']}%"; }
        if (!empty($filtros['status']))                { $sql .= ' AND s.status = :status';              $p[':status'] = $filtros['status']; }
        if (!empty($filtros['periodo']))               { $sql .= ' AND DATE(s.criado_em) >= :per';       $p[':per']    = $filtros['periodo']; }
        if (!empty($filtros['data_inicial']))          { $sql .= ' AND DATE(s.criado_em) >= :dti';       $p[':dti']    = $filtros['data_inicial']; }
        if (!empty($filtros['data_final']))            { $sql .= ' AND DATE(s.criado_em) <= :dtf';       $p[':dtf']    = $filtros['data_final']; }
        if (!empty($filtros['departamento_codigo']))   { $sql .= ' AND d.codigo = :dcod';                $p[':dcod']   = $filtros['departamento_codigo']; }
        if (!empty($filtros['matricula_solicitante'])) { $sql .= ' AND u.matricula LIKE :mat';           $p[':mat']    = "%{$filtros['matricula_solicitante']}%"; }
        $sql .= ' ORDER BY s.criado_em DESC';
        $q = $this->bd->prepare($sql); $q->execute($p);
        return $q->fetchAll();
    }

    public function buscarComItens(int $id): ?array {
        $q = $this->bd->prepare("
            SELECT s.*,
                   d.nome AS nome_departamento,
                   u.nome AS nome_solicitante,
                   aut.nome AS nome_autorizador
            FROM solicitacoes s
            LEFT JOIN departamentos d ON d.id = s.departamento_id
            LEFT JOIN usuarios u ON u.id = s.usuario_id
            LEFT JOIN usuarios aut ON aut.id = s.gerente_id
            WHERE s.id = :id LIMIT 1
        ");
        $q->execute([':id' => $id]);
        $solicitacao = $q->fetch();
        if (!$solicitacao) return null;

        $qi = $this->bd->prepare("SELECT si.*, p.nome AS nome_produto, p.codigo AS codigo_produto FROM solicitacao_itens si JOIN produtos p ON p.id=si.produto_id WHERE si.solicitacao_id=:id");
        $qi->execute([':id'=>$id]);
        $solicitacao['itens'] = $qi->fetchAll();
        return $solicitacao;
    }

    public function cadastrar(array $dados, int $usuarioId, int $departamentoId): int {
        $qMax = $this->bd->prepare("SELECT MAX(CAST(numero AS UNSIGNED)) FROM solicitacoes");
        $qMax->execute();
        $maxNum = (int)$qMax->fetchColumn() ?? 0;
        $numero = str_pad($maxNum + 1, 7, '0', STR_PAD_LEFT);
        $this->bd->prepare("\n            INSERT INTO solicitacoes (numero, departamento_id, usuario_id, justificativa, status, criado_em)\n            VALUES (:num, :dep, :usr, :just, 'aberto', NOW())\n        ")->execute([':num'=>$numero,':dep'=>$departamentoId,':usr'=>$usuarioId,':just'=>$dados['justificativa']]);
        $id = (int) $this->bd->lastInsertId();
        $numeroItem = 1;
        foreach ($dados['itens'] as $item) {
            $this->bd->prepare("INSERT INTO solicitacao_itens (solicitacao_id, numero_item, produto_id, quantidade) VALUES (:sid, :num, :pid,:qtd)")
                ->execute([':sid'=>$id, ':num'=>$numeroItem, ':pid'=>$item['produto_id'],':qtd'=>$item['quantidade']]);
            $numeroItem++;
        }
        $this->registrarHistorico($this->tabela, $id, [], $dados, $usuarioId);
        return $id;
    }

    public function autorizar(int $id, int $gerenteId): bool {
        $ok = $this->bd->prepare("UPDATE solicitacoes SET status='autorizado', gerente_id=:gid, autorizado_em=NOW() WHERE id=:id AND status='aberto'")
            ->execute([':gid'=>$gerenteId,':id'=>$id]);
        if ($ok) $this->registrarHistorico($this->tabela, $id, [], ['status'=>'autorizado'], $gerenteId);
        return $ok;
    }

    public function recusar(int $id, int $gerenteId): bool {
        $ok = $this->bd->prepare("UPDATE solicitacoes SET status='recusada', gerente_id=:gid, atualizado_em=NOW() WHERE id=:id AND status='aberto'")
            ->execute([':gid'=>$gerenteId,':id'=>$id]);
        if ($ok) $this->registrarHistorico($this->tabela, $id, [], ['status'=>'recusada'], $gerenteId);
        return $ok;
    }

    public function desautorizar(int $id, int $gerenteId): bool {
        $q = $this->bd->prepare("SELECT COUNT(*) FROM cotacoes WHERE solicitacao_id = :sid AND status != 'cancelado'");
        $q->execute([':sid' => $id]);
        if ((int)$q->fetchColumn() > 0) {
            return false;
        }
        $ok = $this->bd->prepare("UPDATE solicitacoes SET status='aberto', gerente_id=NULL, autorizado_em=NULL, atualizado_em=NOW() WHERE id=:id AND status='autorizado'")
            ->execute([':id'=>$id]);
        if ($ok) $this->registrarHistorico($this->tabela, $id, [], ['status'=>'aberto'], $gerenteId);
        return $ok;
    }

    public function atualizar(int $id, array $dados, int $usuarioId): bool {
        try {
            $this->bd->beginTransaction();
            $sol = $this->buscarPorId($id);
            if (!$sol) {
                $this->bd->rollBack();
                return false;
            }
            $q = $this->bd->prepare("UPDATE solicitacoes SET justificativa = :just, atualizado_em = NOW() WHERE id = :id");
            $q->execute([':just' => $dados['justificativa'], ':id' => $id]);

            $qMax = $this->bd->prepare("SELECT MAX(numero_item) FROM solicitacao_itens WHERE solicitacao_id = :sid");
            $qMax->execute([':sid' => $id]);
            $maxNum = (int)$qMax->fetchColumn();

            $idsManter = [];
            foreach ($dados['itens'] as $item) {
                if (!empty($item['id'])) {
                    $idsManter[] = (int)$item['id'];
                }
            }
            if (empty($idsManter)) {
                $this->bd->prepare("DELETE FROM solicitacao_itens WHERE solicitacao_id = :id")->execute([':id' => $id]);
            } else {
                $placeholders = implode(',', array_fill(0, count($idsManter), '?'));
                $qDel = $this->bd->prepare("DELETE FROM solicitacao_itens WHERE solicitacao_id = ? AND id NOT IN ($placeholders)");
                $params = array_merge([$id], $idsManter);
                $qDel->execute($params);
            }

            foreach ($dados['itens'] as $item) {
                if (!empty($item['id'])) {
                    $this->bd->prepare("UPDATE solicitacao_itens SET quantidade = :qtd WHERE id = :id_item")
                        ->execute([':qtd' => $item['quantidade'], ':id_item' => $item['id']]);
                } else {
                    $maxNum++;
                    $this->bd->prepare("INSERT INTO solicitacao_itens (solicitacao_id, numero_item, produto_id, quantidade) VALUES (:sid, :num, :pid, :qtd)")
                        ->execute([
                            ':sid' => $id,
                            ':num' => $maxNum,
                            ':pid' => $item['produto_id'],
                            ':qtd' => $item['quantidade']
                        ]);
                }
            }

            $this->bd->commit();
            $this->registrarHistorico($this->tabela, $id, $sol, ['justificativa' => $dados['justificativa'], 'itens' => $dados['itens']], $usuarioId);
            return true;
        } catch (\Exception $e) {
            if ($this->bd->inTransaction()) {
                $this->bd->rollBack();
            }
            return false;
        }
    }

    public function listarAutorizadas(): array {
        $sql = "SELECT s.*, d.nome AS nome_departamento, u.nome AS nome_solicitante
                FROM solicitacoes s
                LEFT JOIN departamentos d ON d.id = s.departamento_id
                LEFT JOIN usuarios u ON u.id = s.usuario_id
                WHERE s.status = 'autorizado'
                ORDER BY s.criado_em DESC";
        $q = $this->bd->query($sql);
        return $q->fetchAll();
    }

    public function cancelar(int $id, int $usuarioId): bool {
        // RF09: Não pode cancelar se houver cotação ativa vinculada
        $sol = $this->buscarPorId($id);
        if ($sol && $sol['status'] === 'autorizado') {
            $q = $this->bd->prepare("SELECT COUNT(*) FROM cotacoes WHERE solicitacao_id = :sid AND status != 'cancelado'");
            $q->execute([':sid' => $id]);
            if ((int)$q->fetchColumn() > 0) {
                return false; // Cotação ativa impede cancelamento
            }
        }

        $ok = $this->bd->prepare("UPDATE solicitacoes SET status='cancelado', atualizado_em=NOW() WHERE id=:id AND status IN ('aberto','autorizado')")
            ->execute([':id'=>$id]);
        if ($ok) $this->registrarHistorico($this->tabela, $id, [], ['status'=>'cancelado'], $usuarioId);
        return $ok;
    }

    public function excluirItem(int $itemId, int $usuarioId): bool {
        $q = $this->bd->prepare("SELECT solicitacao_id FROM solicitacao_itens WHERE id = :id");
        $q->execute([':id' => $itemId]);
        $solId = $q->fetchColumn();
        if (!$solId) return false;

        // Verificar status
        $sol = $this->buscarPorId((int)$solId);
        if ($sol['status'] !== 'aberto' && $sol['status'] !== 'recusada') {
            return false;
        }

        $ok = $this->bd->prepare("DELETE FROM solicitacao_itens WHERE id = :id")->execute([':id' => $itemId]);
        if ($ok) {
            $this->registrarHistorico($this->tabela, (int)$solId, [], ['acao' => 'Exclusão de item ID ' . $itemId], $usuarioId);
        }
        return $ok;
    }
}
