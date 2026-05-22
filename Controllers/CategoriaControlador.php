<?php
namespace Controllers;

use Config\Auxiliares;
use Models\CategoriaModelo;

class CategoriaControlador extends BaseController {
    private CategoriaModelo $modelo;

    public function __construct() {
        $this->modelo = new CategoriaModelo();
    }

    public function listar(): void {
        Auxiliares::exigirAutenticacao();
        $filtros = $_GET;
        $categorias = $this->modelo->listarComFiltros($filtros);
        $this->renderizar('cadastros/categorias', compact('categorias', 'filtros'));
    }

    public function dados(): void {
        Auxiliares::exigirAutenticacao();
        $id = (int)($_GET['id'] ?? 0);
        $cat = $this->modelo->buscarPorId($id);
        $cat ? $this->json(true, '', $cat) : $this->json(false, 'Categoria não encontrada.');
    }

    public function salvar(): void {
        Auxiliares::exigirPerfil('administrador', 'cadastrador');
        $responsavel = Auxiliares::usuarioLogado();
        $id = (int)($_POST['id'] ?? 0);
        $dados = [
            'nome' => trim($_POST['nome'] ?? '')
        ];

        if (empty($dados['nome'])) {
            $this->json(false, 'O nome da categoria é obrigatório.');
            return;
        }

        try {
            if ($id === 0) {
                $novoId = $this->modelo->cadastrar($dados, $responsavel['id']);
                $this->json(true, 'Categoria cadastrada com sucesso.', ['id' => $novoId]);
            } else {
                $ok = $this->modelo->atualizar($id, $dados, $responsavel['id']);
                $this->json($ok, $ok ? 'Categoria atualizada com sucesso.' : 'Erro ao atualizar categoria.');
            }
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), '1062')) {
                $this->json(false, 'Já existe uma categoria com este nome.');
            } else {
                $this->json(false, 'Erro no banco de dados: ' . $e->getMessage());
            }
        }
    }

    public function inativar(): void {
        Auxiliares::exigirPerfil('administrador', 'cadastrador');
        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->modelo->inativar($id);
        $this->json($ok, $ok ? 'Categoria inativada com sucesso.' : 'Erro ao inativar categoria.');
    }

    public function reativar(): void {
        Auxiliares::exigirPerfil('administrador', 'cadastrador');
        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->modelo->reativar($id);
        $this->json($ok, $ok ? 'Categoria reativada com sucesso.' : 'Erro ao reativar categoria.');
    }
}
