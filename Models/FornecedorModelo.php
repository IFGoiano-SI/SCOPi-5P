<?php
namespace Models;

class FornecedorModelo extends ModeloBase {
    protected string $tabela = 'fornecedores';

    public function buscarPorId(int $id): ?array {
        $q = $this->bd->prepare("
            SELECT f.*, m.razao_social AS nome_matriz,
                   cid.nome AS nome_cidade, cid.id AS cidade_id,
                   est.nome AS nome_estado, est.sigla AS sigla_estado, est.id AS estado_id,
                   pai.nome AS nome_pais, pai.id AS pais_id
            FROM fornecedores f
            LEFT JOIN fornecedores m ON m.id = f.matriz_id 
            LEFT JOIN cidades cid ON cid.id = f.cidade_id
            LEFT JOIN estados est ON est.id = cid.estado_id
            LEFT JOIN paises pai ON pai.id = est.pais_id
            WHERE f.id = :id LIMIT 1
        ");
        $q->execute([':id' => $id]);
        return $q->fetch() ?: null;
    }

    public function listarComFiltros(array $filtros = []): array {
        $sql = "SELECT f.*, m.razao_social AS nome_matriz,
                       cid.nome AS nome_cidade, est.sigla AS sigla_estado
                FROM fornecedores f
                LEFT JOIN fornecedores m ON m.id = f.matriz_id 
                LEFT JOIN cidades cid ON cid.id = f.cidade_id
                LEFT JOIN estados est ON est.id = cid.estado_id
                WHERE 1=1";
        $p = [];
        if (!empty($filtros['nome']))        { $sql .= ' AND (f.razao_social LIKE :nome OR f.nome_fantasia LIKE :nome2)'; $p[':nome']=$p[':nome2']="%{$filtros['nome']}%"; }
        if (!empty($filtros['cnpj']))        { $sql .= ' AND f.cnpj LIKE :cnpj';           $p[':cnpj']       = "%{$filtros['cnpj']}%"; }
        if (!empty($filtros['categoria']))   { $sql .= ' AND f.categoria LIKE :categoria'; $p[':categoria']  = "%{$filtros['categoria']}%"; }
        if (!empty($filtros['situacao']))    { $sql .= ' AND f.situacao = :situacao';       $p[':situacao']   = $filtros['situacao']; }
        $sql .= ' ORDER BY f.razao_social ASC';
        $q = $this->bd->prepare($sql); $q->execute($p);
        return $q->fetchAll();
    }

    public function listarMatrizes(): array {
        $q = $this->bd->prepare("SELECT id, razao_social, cnpj FROM fornecedores WHERE tipo = 'matriz' AND situacao = 'ativo' ORDER BY razao_social");
        $q->execute();
        return $q->fetchAll();
    }

    public function garantirLocalidade(array $dadosLocalidade): int {
        $paisId = (int) ($dadosLocalidade['pais_id'] ?? 0);
        $paisNome = trim($dadosLocalidade['pais_nome'] ?? 'Brasil');

        if (empty($paisNome)) {
            return 0;
        }

        // 1. Garantir País
        if ($paisId > 0) {
            $q = $this->bd->prepare("SELECT id FROM paises WHERE id = :id");
            $q->execute([':id' => $paisId]);
            if (!$q->fetch()) {
                $this->bd->prepare("
                    INSERT INTO paises (id, nome, sigla_iso2, sigla_iso3)
                    VALUES (:id, :nome, :iso2, :iso3)
                ")->execute([
                    ':id' => $paisId,
                    ':nome' => $paisNome,
                    ':iso2' => $dadosLocalidade['pais_iso2'] ?? 'BR',
                    ':iso3' => $dadosLocalidade['pais_iso3'] ?? 'BRA'
                ]);
            }
        } else {
            $q = $this->bd->prepare("SELECT id FROM paises WHERE nome = :nome");
            $q->execute([':nome' => $paisNome]);
            $row = $q->fetch();
            if ($row) {
                $paisId = (int) $row['id'];
            } else {
                $this->bd->prepare("
                    INSERT INTO paises (nome, sigla_iso2, sigla_iso3)
                    VALUES (:nome, :iso2, :iso3)
                ")->execute([
                    ':nome' => $paisNome,
                    ':iso2' => $dadosLocalidade['pais_iso2'] ?? null,
                    ':iso3' => $dadosLocalidade['pais_iso3'] ?? null
                ]);
                $paisId = (int) $this->bd->lastInsertId();
            }
        }

        // 2. Garantir Estado
        $estadoId = (int) ($dadosLocalidade['estado_id'] ?? 0);
        $estadoNome = trim($dadosLocalidade['estado_nome'] ?? '');
        $estadoSigla = strtoupper(trim($dadosLocalidade['estado_sigla'] ?? ''));

        if (empty($estadoSigla) && empty($estadoNome)) {
            return 0;
        }

        if ($estadoId > 0) {
            $q = $this->bd->prepare("SELECT id FROM estados WHERE id = :id");
            $q->execute([':id' => $estadoId]);
            if (!$q->fetch()) {
                $this->bd->prepare("
                    INSERT INTO estados (id, nome, sigla, pais_id)
                    VALUES (:id, :nome, :sigla, :pais_id)
                ")->execute([
                    ':id' => $estadoId,
                    ':nome' => $estadoNome ?: $estadoSigla,
                    ':sigla' => $estadoSigla,
                    ':pais_id' => $paisId
                ]);
            }
        } else {
            $q = $this->bd->prepare("SELECT id FROM estados WHERE sigla = :sigla AND pais_id = :pais_id");
            $q->execute([':sigla' => $estadoSigla, ':pais_id' => $paisId]);
            $row = $q->fetch();
            if ($row) {
                $estadoId = (int) $row['id'];
            } else {
                $this->bd->prepare("
                    INSERT INTO estados (nome, sigla, pais_id)
                    VALUES (:nome, :sigla, :pais_id)
                ")->execute([
                    ':nome' => $estadoNome ?: $estadoSigla,
                    ':sigla' => $estadoSigla,
                    ':pais_id' => $paisId
                ]);
                $estadoId = (int) $this->bd->lastInsertId();
            }
        }

        // 3. Garantir Cidade
        $cidadeId = (int) ($dadosLocalidade['cidade_id'] ?? 0);
        $cidadeNome = trim($dadosLocalidade['cidade_nome'] ?? '');

        if (empty($cidadeNome)) {
            return 0;
        }

        if ($cidadeId > 0) {
            $q = $this->bd->prepare("SELECT id FROM cidades WHERE id = :id");
            $q->execute([':id' => $cidadeId]);
            if (!$q->fetch()) {
                $this->bd->prepare("
                    INSERT INTO cidades (id, nome, estado_id)
                    VALUES (:id, :nome, :estado_id)
                ")->execute([
                    ':id' => $cidadeId,
                    ':nome' => $cidadeNome,
                    ':estado_id' => $estadoId
                ]);
            }
        } else {
            $q = $this->bd->prepare("SELECT id FROM cidades WHERE nome = :nome AND estado_id = :estado_id");
            $q->execute([':nome' => $cidadeNome, ':estado_id' => $estadoId]);
            $row = $q->fetch();
            if ($row) {
                $cidadeId = (int) $row['id'];
            } else {
                $this->bd->prepare("
                    INSERT INTO cidades (nome, estado_id)
                    VALUES (:nome, :estado_id)
                ")->execute([
                    ':nome' => $cidadeNome,
                    ':estado_id' => $estadoId
                ]);
                $cidadeId = (int) $this->bd->lastInsertId();
            }
        }

        return $cidadeId;
    }

    public function cadastrar(array $dados, ?int $responsavelId = null): int {
        $codigo = 'FOR-' . strtoupper(substr(md5(uniqid()), 0, 6));
        $this->bd->prepare("
            INSERT INTO fornecedores (razao_social, nome_fantasia, cnpj, inscricao_estadual, logradouro, numero, complemento, bairro, cep, cidade_id, email, contato, responsavel, categoria, codigo, tipo, matriz_id, situacao, criado_em)
            VALUES (:rs,:nf,:cnpj,:ie,:log,:num,:compl,:bairro,:cep,:cidade_id,:email,:contato,:resp,:cat,:cod,:tipo,:matriz_id,'ativo',NOW())
        ")->execute([
            ':rs' => $dados['razao_social'],
            ':nf' => $dados['nome_fantasia'] ?? null,
            ':cnpj' => $dados['cnpj'],
            ':ie' => $dados['inscricao_estadual'] ?? null,
            ':log' => $dados['logradouro'] ?? null,
            ':num' => $dados['numero'] ?? null,
            ':compl' => $dados['complemento'] ?? null,
            ':bairro' => $dados['bairro'] ?? null,
            ':cep' => $dados['cep'] ?? null,
            ':cidade_id' => !empty($dados['cidade_id']) ? (int) $dados['cidade_id'] : null,
            ':email' => $dados['email'] ?? null,
            ':contato' => $dados['contato'] ?? null,
            ':resp' => $dados['responsavel'] ?? null,
            ':cat' => $dados['categoria'] ?? null,
            ':cod' => $codigo,
            ':tipo' => $dados['tipo'] ?? 'matriz',
            ':matriz_id' => $dados['matriz_id'] ?? null
        ]);
        $novoId = (int) $this->bd->lastInsertId();
        if ($responsavelId) {
            $this->registrarHistorico($this->tabela, $novoId, [], $dados, $responsavelId);
        }
        return $novoId;
    }

    public function atualizar(int $id, array $dados, int $responsavelId): bool {
        $anterior = $this->buscarPorId($id);
        $ok = $this->bd->prepare("
            UPDATE fornecedores SET 
                razao_social=:rs, nome_fantasia=:nf, cnpj=:cnpj, inscricao_estadual=:ie,
                logradouro=:log, numero=:num, complemento=:compl, bairro=:bairro, cep=:cep, cidade_id=:cidade_id,
                email=:email, contato=:contato, responsavel=:resp, categoria=:cat, 
                tipo=:tipo, matriz_id=:matriz_id, atualizado_em=NOW() 
            WHERE id=:id
        ")->execute([
            ':rs' => $dados['razao_social'],
            ':nf' => $dados['nome_fantasia'] ?? null,
            ':cnpj' => $dados['cnpj'],
            ':ie' => $dados['inscricao_estadual'] ?? null,
            ':log' => $dados['logradouro'] ?? null,
            ':num' => $dados['numero'] ?? null,
            ':compl' => $dados['complemento'] ?? null,
            ':bairro' => $dados['bairro'] ?? null,
            ':cep' => $dados['cep'] ?? null,
            ':cidade_id' => !empty($dados['cidade_id']) ? (int) $dados['cidade_id'] : null,
            ':email' => $dados['email'] ?? null,
            ':contato' => $dados['contato'] ?? null,
            ':resp' => $dados['responsavel'] ?? null,
            ':cat' => $dados['categoria'] ?? null,
            ':tipo' => $dados['tipo'] ?? 'matriz',
            ':matriz_id' => $dados['matriz_id'] ?? null,
            ':id' => $id
        ]);
        if ($ok && $anterior) {
            $this->registrarHistorico($this->tabela, $id, $anterior, $dados, $responsavelId);
        }
        return $ok;
    }
}
