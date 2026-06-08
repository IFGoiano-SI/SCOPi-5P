<?php
namespace Models;

class DepartamentoModelo extends ModeloBase {

    protected string $tabela = 'departamentos';

    public function listarAtivos(): array {
        $q = $this->bd->prepare("SELECT id, nome FROM departamentos WHERE situacao='ativo' ORDER BY nome");
        $q->execute();
        return $q->fetchAll();
    }

    public function listarComFiltros(array $filtros = [], ?int $departamentoId = null): array {
        $sql = "SELECT d.*, u.nome AS nome_gerente FROM departamentos d
                LEFT JOIN usuarios u ON u.id = d.gerente_id WHERE 1=1";
        $p = [];
        if ($departamentoId !== null) {
            $sql .= ' AND d.id = :depId';
            $p[':depId'] = $departamentoId;
        }
        if (!empty($filtros['nome']))     { $sql .= ' AND d.nome LIKE :nome';         $p[':nome']     = "%{$filtros['nome']}%"; }
        if (!empty($filtros['codigo']))   { $sql .= ' AND d.codigo LIKE :codigo';     $p[':codigo']   = "%{$filtros['codigo']}%"; }
        if (!empty($filtros['gerente_matricula'])) { $sql .= ' AND u.matricula LIKE :gerente_matricula'; $p[':gerente_matricula'] = "%{$filtros['gerente_matricula']}%"; }
        if (!empty($filtros['situacao'])) { $sql .= ' AND d.situacao = :situacao';    $p[':situacao'] = $filtros['situacao']; }
        $sql .= ' ORDER BY d.nome ASC';
        $q = $this->bd->prepare($sql);
        $q->execute($p);
        return $q->fetchAll();
    }

    public function cadastrar(array $dados, ?int $responsavelId = null): int {
        $qMax = $this->bd->query("SELECT MAX(CAST(SUBSTRING(codigo, 4) AS UNSIGNED)) FROM departamentos");
        $maxVal = (int) $qMax->fetchColumn();
        $dados['codigo'] = 'dep' . sprintf('%04d', $maxVal + 1);

        $this->bd->prepare("
            INSERT INTO departamentos (nome, codigo, gerente_id, situacao, criado_em)
            VALUES (:nome, :codigo, :gerente_id, 'ativo', NOW())
        ")->execute([':nome'=>$dados['nome'], ':codigo'=>$dados['codigo'], ':gerente_id'=>$dados['gerente_id']]);
        $novoId = (int) $this->bd->lastInsertId();
        if ($responsavelId) {
            $this->registrarHistorico($this->tabela, $novoId, [], $dados, $responsavelId);
        }
        return $novoId;
    }

    public function atualizar(int $id, array $dados, int $responsavelId): bool {
        $anterior = $this->buscarPorId($id);
        $ok = $this->bd->prepare("\n            UPDATE departamentos SET nome=:nome, gerente_id=:gerente_id, atualizado_em=NOW() WHERE id=:id\n        ")->execute([':nome'=>$dados['nome'], ':gerente_id'=>$dados['gerente_id'], ':id'=>$id]);
        if ($ok && $anterior) $this->registrarHistorico($this->tabela, $id, $anterior, $dados, $responsavelId);
        return $ok;
    }

    public function buscarComGerente(int $id): ?array {
        $q = $this->bd->prepare("
            SELECT d.*, u.nome AS nome_gerente, u.matricula AS gerente_matricula, u.id AS gerente_id
            FROM departamentos d
            LEFT JOIN usuarios u ON u.id = d.gerente_id
            WHERE d.id = :id LIMIT 1
        ");
        $q->execute([':id' => $id]);
        return $q->fetch() ?: null;
    }
}
