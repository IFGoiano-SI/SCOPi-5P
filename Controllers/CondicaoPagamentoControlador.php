<?php
namespace Controllers;

use Config\Auxiliares;
use Models\CondicaoPagamentoModelo;

class CondicaoPagamentoControlador extends BaseController {
    private CondicaoPagamentoModelo $modelo;

    public function __construct() {
        $this->modelo = new CondicaoPagamentoModelo();
    }

    public function listar(): void {
        Auxiliares::exigirAutenticacao();
        $filtros = $_GET;
        $condicoes = $this->modelo->listarComFiltros($filtros);

        if (isset($_GET['busca_modal'])) {
            $this->json(true, '', $condicoes);
            return;
        }

        $this->renderizar('cadastros/condicoes-pagamento', compact('condicoes', 'filtros'));
    }

    public function dados(): void {
        Auxiliares::exigirAutenticacao();
        $id = (int)($_GET['id'] ?? 0);
        $res = $this->modelo->buscarPorId($id);
        $res ? $this->json(true, '', $res) : $this->json(false, 'Condição de pagamento não encontrada.');
    }

    public function salvar(): void {
        Auxiliares::exigirPerfil('administrador', 'cadastrador');
        $responsavel = Auxiliares::usuarioLogado();
        $id = (int)($_POST['id'] ?? 0);
        $dados = [
            'codigo' => trim($_POST['codigo'] ?? ''),
            'descricao' => trim($_POST['descricao'] ?? '')
        ];

        
        if (empty($dados['descricao'])) {
            $this->json(false, 'A descrição é obrigatória.');
            return;
        }

        try {
            if ($id === 0) {
                $novoId = $this->modelo->cadastrar($dados, $responsavel['id']);
                $this->json(true, 'Condição de pagamento cadastrada com sucesso.', ['id' => $novoId]);
            } else {
                $ok = $this->modelo->atualizar($id, $dados, $responsavel['id']);
                $this->json($ok, $ok ? 'Condição de pagamento atualizada com sucesso.' : 'Erro ao atualizar condição.');
            }
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), '1062')) {
                $this->json(false, 'Já existe uma condição com este código.');
            } else {
                $this->json(false, 'Erro no banco de dados: ' . $e->getMessage());
            }
        }
    }

    public function inativar(): void {
        Auxiliares::exigirPerfil('administrador', 'cadastrador');
        $usuario = Auxiliares::usuarioLogado();
        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->modelo->inativar($id, (int)$usuario['id']);
        $this->json($ok, $ok ? 'Condição inativada com sucesso.' : 'Erro ao inativar condição.');
    }

    public function reativar(): void {
        Auxiliares::exigirPerfil('administrador', 'cadastrador');
        $usuario = Auxiliares::usuarioLogado();
        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->modelo->reativar($id, (int)$usuario['id']);
        $this->json($ok, $ok ? 'Condição reativada com sucesso.' : 'Erro ao reativar condição.');
    }

    public function consultarCodigo(): void {
        Auxiliares::exigirAutenticacao();
        $codigo = trim($_GET['codigo'] ?? '');
        if (empty($codigo)) {
            $this->json(false, 'Código não informado.');
            return;
        }

        $bd = \Config\BancoDados::obterInstancia()->obterConexao();
        $q = $bd->prepare("SELECT id, descricao, codigo FROM condicoes_pagamento WHERE codigo = :codigo AND situacao = 'ativo' LIMIT 1");
        $q->execute([':codigo' => $codigo]);
        $res = $q->fetch();

        if ($res) {
            $this->json(true, 'Sucesso', $res);
        } else {
            $this->json(false, 'Condição não encontrada ou inativa.');
        }
    }

    public function exportar(): void {
        Auxiliares::exigirAutenticacao();
        $filtros = $_GET;
        $condicoes = $this->modelo->listarComFiltros($filtros);

        $cabecalhos = ['ID', 'Código', 'Descrição', 'Situação', 'Criado Em'];
        $dadosCsv = [];
        foreach ($condicoes as $c) {
            $dadosCsv[] = [
                $c['id'],
                $c['codigo'],
                $c['descricao'],
                ucfirst($c['situacao']),
                date('d/m/Y H:i', strtotime($c['criado_em']))
            ];
        }

        Auxiliares::gerarCSV('condicoes_pagamento', $cabecalhos, $dadosCsv);
    }
}
