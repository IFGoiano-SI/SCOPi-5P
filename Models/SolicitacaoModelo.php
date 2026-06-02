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
        if (!empty($filtros['numero']))    { $sql .= ' AND s.numero LIKE :num';          $p[':num']      = "%{$filtros['numero']}%"; }
        if (!empty($filtros['status']))    { $sql .= ' AND s.status = :status';          $p[':status']   = $filtros['status']; }
        if (!empty($filtros['periodo']))   { $sql .= ' AND DATE(s.criado_em) >= :per';   $p[':per']      = $filtros['periodo']; }
        $sql .= ' ORDER BY s.criado_em DESC';
        $q = $this->bd->prepare($sql); $q->execute($p);
        return $q->fetchAll();
    }

    public function buscarComItens(int $id): ?array {
        $solicitacao = $this->buscarPorId($id);
        if (!$solicitacao) return null;
        $q = $this->bd->prepare("SELECT si.*, p.nome AS nome_produto, p.codigo AS codigo_produto FROM solicitacao_itens si JOIN produtos p ON p.id=si.produto_id WHERE si.solicitacao_id=:id");
        $q->execute([':id'=>$id]);
        $solicitacao['itens'] = $q->fetchAll();
        return $solicitacao;
    }

    public function cadastrar(array $dados, int $usuarioId, int $departamentoId): int {
        $numero = 'SOL-' . date('Ymd') . '-' . rand(1000,9999);
        $this->bd->prepare("\n            INSERT INTO solicitacoes (numero, departamento_id, usuario_id, justificativa, status, criado_em)\n            VALUES (:num, :dep, :usr, :just, 'em_aberto', NOW())\n        ")->execute([':num'=>$numero,':dep'=>$departamentoId,':usr'=>$usuarioId,':just'=>$dados['justificativa']]);
        $id = (int) $this->bd->lastInsertId();
        foreach ($dados['itens'] as $item) {
            $this->bd->prepare("INSERT INTO solicitacao_itens (solicitacao_id, produto_id, quantidade) VALUES (:sid,:pid,:qtd)")
                ->execute([':sid'=>$id,':pid'=>$item['produto_id'],':qtd'=>$item['quantidade']]);
        }
        $this->registrarHistorico($this->tabela, $id, [], $dados, $usuarioId);
        return $id;
    }

    public function autorizar(int $id, int $gerenteId): bool {
        $ok = $this->bd->prepare("UPDATE solicitacoes SET status='autorizada', gerente_id=:gid, autorizado_em=NOW() WHERE id=:id AND status='em_aberto'")
            ->execute([':gid'=>$gerenteId,':id'=>$id]);
        if ($ok) $this->registrarHistorico($this->tabela, $id, [], ['status'=>'autorizada'], $gerenteId);
        return $ok;
    }

    public function recusar(int $id, int $gerenteId): bool {
        $ok = $this->bd->prepare("UPDATE solicitacoes SET status='recusada', gerente_id=:gid, atualizado_em=NOW() WHERE id=:id AND status='em_aberto'")
            ->execute([':gid'=>$gerenteId,':id'=>$id]);
        if ($ok) $this->registrarHistorico($this->tabela, $id, [], ['status'=>'recusada'], $gerenteId);
        return $ok;
    }

    public function desautorizar(int $id, int $gerenteId): bool {
        $q = $this->bd->prepare("SELECT COUNT(*) FROM cotacoes WHERE solicitacao_id = :sid AND status != 'cancelada'");
        $q->execute([':sid' => $id]);
        if ((int)$q->fetchColumn() > 0) {
            return false;
        }
        $ok = $this->bd->prepare("UPDATE solicitacoes SET status='em_aberto', gerente_id=NULL, autorizado_em=NULL, atualizado_em=NOW() WHERE id=:id AND status='autorizada'")
            ->execute([':id'=>$id]);
        if ($ok) $this->registrarHistorico($this->tabela, $id, [], ['status'=>'em_aberto'], $gerenteId);
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

            $this->bd->prepare("DELETE FROM solicitacao_itens WHERE solicitacao_id = :id")->execute([':id' => $id]);

            foreach ($dados['itens'] as $item) {
                $this->bd->prepare("INSERT INTO solicitacao_itens (solicitacao_id, produto_id, quantidade) VALUES (:sid, :pid, :qtd)")
                    ->execute([
                        ':sid' => $id,
                        ':pid' => $item['produto_id'],
                        ':qtd' => $item['quantidade']
                    ]);
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
                WHERE s.status = 'autorizada'
                ORDER BY s.criado_em DESC";
        $q = $this->bd->query($sql);
        return $q->fetchAll();
    }

    public function cancelar(int $id, int $usuarioId): bool {
        // RF09: Não pode cancelar se houver cotação ativa vinculada
        $sol = $this->buscarPorId($id);
        if ($sol && $sol['status'] === 'autorizada') {
            $q = $this->bd->prepare("SELECT COUNT(*) FROM cotacoes WHERE solicitacao_id = :sid AND status != 'cancelada'");
            $q->execute([':sid' => $id]);
            if ((int)$q->fetchColumn() > 0) {
                return false; // Cotação ativa impede cancelamento
            }
        }

        $ok = $this->bd->prepare("UPDATE solicitacoes SET status='cancelada', atualizado_em=NOW() WHERE id=:id AND status IN ('em_aberto','autorizada')")
            ->execute([':id'=>$id]);
        if ($ok) $this->registrarHistorico($this->tabela, $id, [], ['status'=>'cancelada'], $usuarioId);
        return $ok;
    }
}
