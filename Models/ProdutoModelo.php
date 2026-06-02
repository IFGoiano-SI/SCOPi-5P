<?php
namespace Models;

class ProdutoModelo extends ModeloBase {
    protected string $tabela = 'produtos';

    public function listarComFiltros(array $filtros = []): array {
        $sql = "SELECT p.*, c.nome AS nome_categoria FROM produtos p LEFT JOIN categorias c ON c.id=p.categoria_id WHERE 1=1";
        $p = [];
        if (!empty($filtros['nome']))      { $sql .= ' AND p.nome LIKE :nome';         $p[':nome']     = "%{$filtros['nome']}%"; }
        if (!empty($filtros['codigo']))    { $sql .= ' AND p.codigo LIKE :codigo';     $p[':codigo']   = "%{$filtros['codigo']}%"; }
        if (!empty($filtros['categoria_codigo'])) { $sql .= ' AND c.codigo LIKE :cat_cod'; $p[':cat_cod'] = "%{$filtros['categoria_codigo']}%"; }
        if (!empty($filtros['situacao']))  { $sql .= ' AND p.situacao = :situacao';    $p[':situacao'] = $filtros['situacao']; }
        $sql .= ' ORDER BY p.nome ASC';
        $q = $this->bd->prepare($sql); $q->execute($p);
        return $q->fetchAll();
    }

    public function cadastrar(array $dados, ?int $responsavelId = null): int {
        $codigo = 'PRD-' . strtoupper(substr(md5(uniqid()), 0, 6));
        $this->bd->prepare("
            INSERT INTO produtos (nome, descricao, codigo, categoria_id, situacao, criado_em)
            VALUES (:nome, :desc, :cod, :cat, 'ativo', NOW())
        ")->execute([':nome'=>$dados['nome'],':desc'=>$dados['descricao'],':cod'=>$codigo,':cat'=>$dados['categoria_id']]);
        $novoId = (int) $this->bd->lastInsertId();
        if ($responsavelId) {
            $this->registrarHistorico($this->tabela, $novoId, [], $dados, $responsavelId);
        }
        return $novoId;
    }

    public function atualizar(int $id, array $dados, int $responsavelId): bool {
        $anterior = $this->buscarPorId($id);
        $ok = $this->bd->prepare("\n            UPDATE produtos SET nome=:nome, descricao=:desc, categoria_id=:cat, atualizado_em=NOW() WHERE id=:id\n        ")->execute([':nome'=>$dados['nome'],':desc'=>$dados['descricao'],':cat'=>$dados['categoria_id'],':id'=>$id]);
        if ($ok && $anterior) $this->registrarHistorico($this->tabela, $id, $anterior, $dados, $responsavelId);
        return $ok;
    }

    public function listarAtivos(): array {
        $q = $this->bd->query("SELECT id, nome, codigo FROM produtos WHERE situacao = 'ativo' ORDER BY nome ASC");
        return $q->fetchAll();
    }
}
