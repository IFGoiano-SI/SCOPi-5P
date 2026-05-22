<?php
namespace Models;

class CondicaoPagamentoModelo extends ModeloBase {
    protected string $tabela = 'condicoes_pagamento';

    public function listarAtivas(): array {
        $q = $this->bd->prepare("SELECT id, descricao FROM condicoes_pagamento WHERE situacao='ativo' ORDER BY descricao");
        $q->execute();
        return $q->fetchAll();
    }

    public function listarComFiltros(array $filtros = []): array {
        $sql = "SELECT * FROM condicoes_pagamento WHERE 1=1";
        $p = [];
        if (!empty($filtros['descricao'])) {
            $sql .= ' AND descricao LIKE :descricao';
            $p[':descricao'] = "%{$filtros['descricao']}%";
        }
        if (!empty($filtros['situacao'])) {
            $sql .= ' AND situacao = :situacao';
            $p[':situacao'] = $filtros['situacao'];
        }
        $sql .= ' ORDER BY descricao ASC';
        $q = $this->bd->prepare($sql);
        $q->execute($p);
        return $q->fetchAll();
    }

    public function cadastrar(array $dados, ?int $responsavelId = null): int {
        $q = $this->bd->prepare("INSERT INTO condicoes_pagamento (descricao, situacao) VALUES (:descricao, 'ativo')");
        $q->execute([':descricao' => $dados['descricao']]);
        $novoId = (int) $this->bd->lastInsertId();
        if ($responsavelId) {
            $this->registrarHistorico($this->tabela, $novoId, [], $dados, $responsavelId);
        }
        return $novoId;
    }

    public function atualizar(int $id, array $dados, int $responsavelId): bool {
        $anterior = $this->buscarPorId($id);
        $q = $this->bd->prepare("UPDATE condicoes_pagamento SET descricao = :descricao WHERE id = :id");
        $ok = $q->execute([':descricao' => $dados['descricao'], ':id' => $id]);
        if ($ok && $anterior) {
            $this->registrarHistorico($this->tabela, $id, $anterior, $dados, $responsavelId);
        }
        return $ok;
    }
}
