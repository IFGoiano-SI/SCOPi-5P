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
        $this->renderizar('cadastros/condicoes_pagamento', compact('condicoes', 'filtros'));
    }

    public function dados(): void {
        Auxiliares::exigirAutenticacao();
        $id = (int)($_GET['id'] ?? 0);
        $cp = $this->modelo->buscarPorId($id);
        $cp ? $this->json(true, '', $cp) : $this->json(false, 'Condição de pagamento não encontrada.');
    }

    public function salvar(): void {
        Auxiliares::exigirPerfil('administrador', 'cadastrador');
        $responsavel = Auxiliares::usuarioLogado();
        $id = (int)($_POST['id'] ?? 0);
        $dados = [
            'descricao' => trim($_POST['descricao'] ?? '')
        ];

        if (empty($dados['descricao'])) {
            $this->json(false, 'A descrição da condição de pagamento é obrigatória.');
            return;
        }

        try {
            if ($id === 0) {
                $novoId = $this->modelo->cadastrar($dados, $responsavel['id']);
                $this->json(true, 'Condição de pagamento cadastrada com sucesso.', ['id' => $novoId]);
            } else {
                $ok = $this->modelo->atualizar($id, $dados, $responsavel['id']);
                $this->json($ok, $ok ? 'Condição de pagamento atualizada com sucesso.' : 'Erro ao atualizar condição de pagamento.');
            }
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), '1062')) {
                $this->json(false, 'Já existe uma condição de pagamento com esta descrição.');
            } else {
                $this->json(false, 'Erro no banco de dados: ' . $e->getMessage());
            }
        }
    }

    public function inativar(): void {
        Auxiliares::exigirPerfil('administrador', 'cadastrador');
        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->modelo->inativar($id);
        $this->json($ok, $ok ? 'Condição de pagamento inativada com sucesso.' : 'Erro ao inativar condição de pagamento.');
    }

    public function reativar(): void {
        Auxiliares::exigirPerfil('administrador', 'cadastrador');
        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->modelo->reativar($id);
        $this->json($ok, $ok ? 'Condição de pagamento reativada com sucesso.' : 'Erro ao reativar condição de pagamento.');
    }
}
