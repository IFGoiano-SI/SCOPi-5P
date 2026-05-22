<?php
namespace Controllers;

use Config\Auxiliares;
use Models\DepartamentoModelo;
use Models\UsuarioModelo;

class DepartamentoControlador extends BaseController {
    private DepartamentoModelo $modelo;
    public function __construct() { $this->modelo = new DepartamentoModelo(); }

    public function listar(): void {
        Auxiliares::exigirAutenticacao();
        $usuario       = Auxiliares::usuarioLogado();
        $filtros       = $_GET;
        $departamentoId = ($usuario['perfil'] ?? '') === 'gerente' ? (int) $usuario['departamento_id'] : null;
        $departamentos = $this->modelo->listarComFiltros($filtros, $departamentoId);
        $gerentes      = (new UsuarioModelo())->listarComFiltros(['perfil' => 'gerente']);
        $this->renderizar('cadastros/departamentos', compact('departamentos','gerentes','filtros'));
    }

    public function dados(): void {
        Auxiliares::exigirAutenticacao();
        $id = (int)($_GET['id'] ?? 0);
        $dep = $this->modelo->buscarPorId($id);
        $dep ? $this->json(true, '', $dep) : $this->json(false, 'Não encontrado.');
    }

    public function salvar(): void {
        Auxiliares::exigirPerfil('administrador','cadastrador');
        $responsavel = Auxiliares::usuarioLogado();
        $id = (int)($_POST['id'] ?? 0);
        $dados = ['nome' => trim($_POST['nome'] ?? ''), 'gerente_id' => (int)($_POST['gerente_id'] ?? 0)];
        if (empty($dados['nome'])) { $this->json(false, 'Nome obrigatório.'); return; }
        if ($id === 0) {
            $novoId = $this->modelo->cadastrar($dados, $responsavel['id']);
            $this->json(true, 'Departamento cadastrado.', ['id' => $novoId]);
        } else {
            $anterior = $this->modelo->buscarPorId($id);
            $ok = $this->modelo->atualizar($id, $dados, $responsavel['id']);
            if ($ok && $anterior) {
                // Notificar gerente do departamento
                $gerenteId = $dados['gerente_id'] ?: ($anterior['gerente_id'] ?? null);
                if ($gerenteId) {
                    $gerente = (new UsuarioModelo())->buscarPorId($gerenteId);
                    if ($gerente) {
                        \Config\Notificador::notificarUsuario($gerente['id'], "Alteração no Departamento", "O departamento '{$dados['nome']}' (Código: {$anterior['codigo']}) que você gerencia teve seu cadastro alterado.");
                        \Config\Notificador::enviarEmail($gerente['email'], "Alteração no Departamento - SCOPi", "Olá {$gerente['nome']},\n\nOcorreram alterações no cadastro do departamento que você gerencia: {$dados['nome']}.\n");
                    }
                }
            }
            $this->json($ok, $ok ? 'Atualizado com sucesso.' : 'Erro ao atualizar.');
        }
    }

    public function inativar(): void { Auxiliares::exigirPerfil('administrador','cadastrador'); $ok = $this->modelo->inativar((int)($_POST['id'] ?? 0)); $this->json($ok, $ok ? 'Inativado.' : 'Erro.'); }
    public function reativar(): void { Auxiliares::exigirPerfil('administrador','cadastrador'); $ok = $this->modelo->reativar((int)($_POST['id'] ?? 0)); $this->json($ok, $ok ? 'Reativado.' : 'Erro.'); }
}
