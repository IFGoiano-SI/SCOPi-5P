<?php
namespace Models;

class CategoriaModelo extends ModeloBase {
    protected string $tabela = 'categorias';

    public function listarAtivas(): array {
        $q = $this->bd->prepare("SELECT id, nome FROM categorias WHERE situacao='ativo' ORDER BY nome");
        $q->execute();
        return $q->fetchAll();
    }

    public function listarComFiltros(array $filtros = []): array {
        $sql = "SELECT * FROM categorias WHERE 1=1";
        $p = [];
        if (!empty($filtros['nome']))     { $sql .= ' AND nome LIKE :nome';         $p[':nome']     = "%{$filtros['nome']}%"; }
        if (!empty($filtros['situacao'])) { $sql .= ' AND situacao = :situacao';    $p[':situacao'] = $filtros['situacao']; }
        $sql .= ' ORDER BY nome ASC';
        $q = $this->bd->prepare($sql);
        $q->execute($p);
        return $q->fetchAll();
    }

    public function cadastrar(array $dados, ?int $responsavelId = null): int {
        $q = $this->bd->prepare("INSERT INTO categorias (nome, situacao) VALUES (:nome, 'ativo')");
        $q->execute([':nome' => $dados['nome']]);
        $novoId = (int) $this->bd->lastInsertId();
        if ($responsavelId) {
            $this->registrarHistorico($this->tabela, $novoId, [], $dados, $responsavelId);
        }
        return $novoId;
    }

    public function atualizar(int $id, array $dados, int $responsavelId): bool {
        $anterior = $this->buscarPorId($id);
        $q = $this->bd->prepare("UPDATE categorias SET nome = :nome WHERE id = :id");
        $ok = $q->execute([':nome' => $dados['nome'], ':id' => $id]);
        if ($ok && $anterior) {
            $this->registrarHistorico($this->tabela, $id, $anterior, $dados, $responsavelId);
        }
        return $ok;
    }
}
