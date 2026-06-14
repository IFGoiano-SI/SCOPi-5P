<?php
namespace Controllers;

use Config\Auxiliares;
use Config\BancoDados;

class BuscaGlobalControlador extends BaseController {

    public function dados(): void {
        Auxiliares::exigirAutenticacao();
        $tabela = $_GET['tabela'] ?? '';
        $termo  = $_GET['termo']  ?? '';

        if (empty($tabela)) {
            $this->json(false, 'Tabela não especificada.');
            return;
        }

        $bd = BancoDados::obterInstancia()->obterConexao();
        $resultados = [];
        $limite = ' LIMIT 30';

        try {
            switch ($tabela) {
                case 'usuarios':
                    $resultados = $this->buscarUsuarios($bd, $_GET, $termo, $limite);
                    break;

                case 'departamentos':
                    $resultados = $this->buscarDepartamentos($bd, $_GET, $termo, $limite);
                    break;

                case 'categorias':
                    $resultados = $this->buscarCategorias($bd, $_GET, $termo, $limite);
                    break;

                case 'condicoes_pagamento':
                    $resultados = $this->buscarCondicoesPagamento($bd, $_GET, $termo, $limite);
                    break;

                case 'produtos':
                    $resultados = $this->buscarProdutos($bd, $_GET, $termo, $limite);
                    break;

                case 'fornecedores':
                    $resultados = $this->buscarFornecedores($bd, $_GET, $termo, $limite);
                    break;

                case 'ordens':
                case 'ordens_compra':
                    $resultados = $this->buscarOrdens($bd, $termo, $limite);
                    break;

                case 'cotacoes':
                    $resultados = $this->buscarCotacoes($bd, $_GET, $termo, $limite);
                    break;

                default:
                    $this->json(false, 'Tabela não suportada para busca global.');
                    return;
            }

            $this->json(true, 'Busca concluída', $resultados);

        } catch (\Exception $e) {
            $this->json(false, 'Erro ao buscar dados: ' . $e->getMessage());
        }
    }

    private function buscarUsuarios($bd, $filtros, $termo, $limite) {
        $sql = "SELECT u.id, u.matricula AS identificador, u.nome AS descricao, d.nome AS extra1
                FROM usuarios u
                LEFT JOIN departamentos d ON d.id = u.departamento_id
                WHERE u.situacao = :status";
        $p = [':status' => 'ativo'];

        if (!empty($filtros['matricula'] ?? '')) {
            $sql .= " AND u.matricula LIKE :matricula";
            $p[':matricula'] = "%{$filtros['matricula']}%";
        }

        if (!empty($filtros['nome'] ?? '')) {
            $sql .= " AND u.nome LIKE :nome";
            $p[':nome'] = "%{$filtros['nome']}%";
        }

        if (!empty($filtros['departamento_id'] ?? '')) {
            $sql .= " AND u.departamento_id IN (SELECT id FROM departamentos WHERE codigo LIKE :depto_codigo)";
            $p[':depto_codigo'] = "%{$filtros['departamento_id']}%";
        }

        if (!empty($filtros['perfil'] ?? '')) {
            $sql .= " AND u.perfil = :perfil";
            $p[':perfil'] = $filtros['perfil'];
        }

        // Se não tem filtros específicos, busca por termo
        if (empty($filtros['matricula']) && empty($filtros['nome']) && !empty($termo)) {
            $sql .= " AND (u.matricula LIKE :termo OR u.nome LIKE :termo)";
            $p[':termo'] = "%{$termo}%";
        }

        $sql .= " ORDER BY u.nome" . $limite;
        $q = $bd->prepare($sql);
        $q->execute($p);
        return $q->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function buscarDepartamentos($bd, $filtros, $termo, $limite) {
        $sql = "SELECT id, codigo AS identificador, nome AS descricao
                FROM departamentos
                WHERE situacao = :status";
        $p = [':status' => 'ativo'];

        if (!empty($filtros['codigo'] ?? '')) {
            $sql .= " AND codigo LIKE :codigo";
            $p[':codigo'] = "%{$filtros['codigo']}%";
        }

        if (!empty($filtros['nome'] ?? '')) {
            $sql .= " AND nome LIKE :nome";
            $p[':nome'] = "%{$filtros['nome']}%";
        }

        if (!empty($filtros['gerente_id'] ?? '')) {
            $sql .= " AND gerente_id IN (SELECT id FROM usuarios WHERE matricula LIKE :gerente_matricula)";
            $p[':gerente_matricula'] = "%{$filtros['gerente_id']}%";
        }

        if (empty($filtros['codigo']) && empty($filtros['nome']) && !empty($termo)) {
            $sql .= " AND (nome LIKE :termo OR codigo LIKE :termo)";
            $p[':termo'] = "%{$termo}%";
        }

        $sql .= " ORDER BY nome" . $limite;
        $q = $bd->prepare($sql);
        $q->execute($p);
        return $q->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function buscarCategorias($bd, $filtros, $termo, $limite) {
        $sql = "SELECT id, codigo AS identificador, nome AS descricao
                FROM categorias
                WHERE situacao = :status";
        $p = [':status' => 'ativo'];

        if (!empty($filtros['codigo'] ?? '')) {
            $sql .= " AND codigo LIKE :codigo";
            $p[':codigo'] = "%{$filtros['codigo']}%";
        }

        if (!empty($filtros['nome'] ?? '')) {
            $sql .= " AND nome LIKE :nome";
            $p[':nome'] = "%{$filtros['nome']}%";
        }

        if (empty($filtros['codigo']) && empty($filtros['nome']) && !empty($termo)) {
            $sql .= " AND (nome LIKE :termo OR codigo LIKE :termo)";
            $p[':termo'] = "%{$termo}%";
        }

        $sql .= " ORDER BY nome" . $limite;
        $q = $bd->prepare($sql);
        $q->execute($p);
        return $q->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function buscarCondicoesPagamento($bd, $filtros, $termo, $limite) {
        $sql = "SELECT id, codigo AS identificador, descricao
                FROM condicoes_pagamento
                WHERE situacao = :status";
        $p = [':status' => 'ativo'];

        if (!empty($filtros['codigo'] ?? '')) {
            $sql .= " AND codigo LIKE :codigo";
            $p[':codigo'] = "%{$filtros['codigo']}%";
        }

        if (!empty($filtros['descricao'] ?? '')) {
            $sql .= " AND descricao LIKE :descricao";
            $p[':descricao'] = "%{$filtros['descricao']}%";
        }

        if (empty($filtros['codigo']) && empty($filtros['descricao']) && !empty($termo)) {
            $sql .= " AND (descricao LIKE :termo OR codigo LIKE :termo)";
            $p[':termo'] = "%{$termo}%";
        }

        $sql .= " ORDER BY descricao" . $limite;
        $q = $bd->prepare($sql);
        $q->execute($p);
        return $q->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function buscarProdutos($bd, $filtros, $termo, $limite) {
        $sql = "SELECT p.id, p.codigo AS identificador, p.nome AS descricao, c.nome AS extra1
                FROM produtos p
                LEFT JOIN categorias c ON c.id = p.categoria_id
                WHERE p.situacao = :status";
        $p = [':status' => 'ativo'];

        if (!empty($filtros['codigo'] ?? '')) {
            $sql .= " AND p.codigo LIKE :codigo";
            $p[':codigo'] = "%{$filtros['codigo']}%";
        }

        if (!empty($filtros['nome'] ?? '')) {
            $sql .= " AND p.nome LIKE :nome";
            $p[':nome'] = "%{$filtros['nome']}%";
        }

        if (!empty($filtros['categoria_id'] ?? '')) {
            $sql .= " AND p.categoria_id IN (SELECT id FROM categorias WHERE codigo LIKE :cat_codigo)";
            $p[':cat_codigo'] = "%{$filtros['categoria_id']}%";
        }

        if (empty($filtros['codigo']) && empty($filtros['nome']) && !empty($termo)) {
            $sql .= " AND (p.nome LIKE :termo OR p.codigo LIKE :termo)";
            $p[':termo'] = "%{$termo}%";
        }

        $sql .= " ORDER BY p.nome" . $limite;
        $q = $bd->prepare($sql);
        $q->execute($p);
        return $q->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function buscarFornecedores($bd, $filtros, $termo, $limite) {
        $sql = "SELECT f.id, f.codigo AS identificador, f.razao_social AS descricao,
                       f.nome_fantasia AS extra1, f.cnpj AS extra2
                FROM fornecedores f
                WHERE f.situacao = :status";
        $p = [':status' => 'ativo'];

        if (!empty($filtros['codigo'] ?? '')) {
            $sql .= " AND f.codigo LIKE :codigo";
            $p[':codigo'] = "%{$filtros['codigo']}%";
        }

        if (!empty($filtros['razao_social'] ?? '')) {
            $sql .= " AND f.razao_social LIKE :razao";
            $p[':razao'] = "%{$filtros['razao_social']}%";
        }

        if (!empty($filtros['nome_fantasia'] ?? '')) {
            $sql .= " AND f.nome_fantasia LIKE :fantasia";
            $p[':fantasia'] = "%{$filtros['nome_fantasia']}%";
        }

        if (!empty($filtros['cnpj'] ?? '')) {
            $sql .= " AND f.cnpj LIKE :cnpj";
            $p[':cnpj'] = "%{$filtros['cnpj']}%";
        }

        if (!empty($filtros['categoria_id'] ?? '')) {
            $sql .= " AND f.categoria_id IN (SELECT id FROM categorias WHERE codigo LIKE :cat_codigo)";
            $p[':cat_codigo'] = "%{$filtros['categoria_id']}%";
        }

        if (empty($filtros['codigo']) && empty($filtros['razao_social']) && empty($filtros['nome_fantasia']) && empty($filtros['cnpj']) && !empty($termo)) {
            $sql .= " AND (f.razao_social LIKE :termo OR f.codigo LIKE :termo OR f.cnpj LIKE :termo)";
            $p[':termo'] = "%{$termo}%";
        }

        $sql .= " ORDER BY f.razao_social" . $limite;
        $q = $bd->prepare($sql);
        $q->execute($p);
        return $q->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function buscarOrdens($bd, $termo, $limite) {
        $sql = "SELECT o.id, o.numero AS identificador, o.status AS descricao, f.razao_social AS extra1
                FROM ordens_compra o
                LEFT JOIN fornecedores f ON f.id = o.fornecedor_id
                WHERE o.numero LIKE :termo OR f.razao_social LIKE :termo
                ORDER BY o.numero DESC" . $limite;
        $p = [':termo' => "%{$termo}%"];
        $q = $bd->prepare($sql);
        $q->execute($p);
        return $q->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function buscarCotacoes($bd, $filtros, $termo, $limite) {
        $sql = "SELECT c.id, c.numero AS identificador, c.status AS descricao,
                       u.nome AS extra1, s.numero AS extra2
                FROM cotacoes c
                LEFT JOIN usuarios u ON u.id = c.usuario_id
                LEFT JOIN solicitacoes s ON s.id = c.solicitacao_id
                WHERE 1=1";
        $p = [];

        if (!empty($filtros['numero'] ?? '')) {
            $sql .= " AND c.numero LIKE :numero";
            $p[':numero'] = "%{$filtros['numero']}%";
        }

        if (!empty($filtros['status'] ?? '')) {
            $sql .= " AND c.status = :status";
            $p[':status'] = $filtros['status'];
        }

        if (!empty($filtros['solicitacao_id'] ?? '')) {
            $sql .= " AND c.solicitacao_id IN (SELECT id FROM solicitacoes WHERE numero LIKE :sol_numero)";
            $p[':sol_numero'] = "%{$filtros['solicitacao_id']}%";
        }

        if (!empty($filtros['usuario_id'] ?? '')) {
            $sql .= " AND c.usuario_id IN (SELECT id FROM usuarios WHERE matricula LIKE :user_matricula)";
            $p[':user_matricula'] = "%{$filtros['usuario_id']}%";
        }

        if (empty($filtros['numero']) && !empty($termo)) {
            $sql .= " AND c.numero LIKE :termo";
            $p[':termo'] = "%{$termo}%";
        }

        $sql .= " ORDER BY c.numero DESC" . $limite;
        $q = $bd->prepare($sql);
        $q->execute($p);
        return $q->fetchAll(\PDO::FETCH_ASSOC);
    }
}
