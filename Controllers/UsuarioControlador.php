<?php
namespace Controllers;

use Config\Auxiliares;
use Models\DepartamentoModelo;
use Models\UsuarioModelo;

class UsuarioControlador extends BaseController {
    private UsuarioModelo $modelo;

    public function __construct() {
        $this->modelo = new UsuarioModelo();
    }

    public function listar(): void {
        Auxiliares::exigirPerfil('administrador', 'gerente');
        $usuario = Auxiliares::usuarioLogado();
        $filtros = $_GET;
        $departamentoId = ($usuario['perfil'] ?? '') === 'gerente' ? (int) $usuario['departamento_id'] : null;
        $usuarios = $this->modelo->listarComFiltros($filtros, $departamentoId);
        $departamentos = (new DepartamentoModelo())->listarAtivos();

        $this->renderizar('cadastros/usuarios', compact('usuarios', 'departamentos', 'filtros'));
    }

    public function dados(): void {
        Auxiliares::exigirPerfil('administrador', 'gerente');
        $id = (int) ($_GET['id'] ?? 0);
        
        // Gerentes só podem ver dados de usuários de seu próprio departamento
        $usuarioLogado = Auxiliares::usuarioLogado();
        $usuario = $this->modelo->buscarComDepartamento($id);

        if ($usuario && $usuarioLogado['perfil'] === 'gerente' && (int)$usuario['departamento_id'] !== (int)$usuarioLogado['departamento_id']) {
            $this->json(false, 'Acesso negado.');
            return;
        }

        $usuario ? $this->json(true, '', $usuario) : $this->json(false, 'Nao encontrado.');
    }

    public function salvar(): void {
        Auxiliares::exigirPerfil('administrador');
        $responsavel = Auxiliares::usuarioLogado();
        $id = (int) ($_POST['id'] ?? 0);
        $dados = [
            'nome' => trim($_POST['nome'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'senha' => $_POST['senha'] ?? '',
            'matricula' => trim($_POST['matricula'] ?? ''),
            'contato' => trim($_POST['contato'] ?? ''),
            'departamento_id' => (int) ($_POST['departamento_id'] ?? 0),
            'perfil' => trim($_POST['perfil'] ?? 'usuario'),
        ];

        if ($dados['nome'] === '' || $dados['email'] === '' || $dados['departamento_id'] <= 0) {
            $this->json(false, 'Preencha nome, e-mail e departamento.');
            return;
        }

        // Validar complexidade da senha
        if ($id === 0 || !empty($dados['senha'])) {
            $senhaTestada = $dados['senha'];
            if ($id === 0 || $senhaTestada !== '') {
                if (strlen($senhaTestada) < 8 ||
                     !preg_match('/[A-Z]/', $senhaTestada) ||
                     !preg_match('/[a-z]/', $senhaTestada) ||
                     !preg_match('/[0-9]/', $senhaTestada) ||
                     !preg_match('/[^A-Za-z0-9]/', $senhaTestada)) {
                    $this->json(false, 'A senha deve conter no mínimo 8 caracteres, incluindo letras maiúsculas, minúsculas, números e caracteres especiais.');
                    return;
                }
            }
        }

        if ($id === 0) {
            $novoId = $this->modelo->cadastrar($dados, (int) $responsavel['id']);
            $this->json(true, 'Usuario cadastrado.', ['id' => $novoId]);
            return;
        }

        $anterior = $this->modelo->buscarPorId($id);
        if ($anterior && $dados['perfil'] !== $anterior['perfil']) {
            \Config\Notificador::notificarUsuario($id, "Perfil de acesso alterado", "Seu perfil de acesso no SCOPi foi alterado de '{$anterior['perfil']}' para '{$dados['perfil']}'.");
            \Config\Notificador::enviarEmail($anterior['email'], "Perfil de acesso alterado - SCOPi", "Olá {$anterior['nome']},\n\nSeu perfil de acesso foi alterado de '{$anterior['perfil']}' para '{$dados['perfil']}'.");
        }

        $ok = $this->modelo->atualizar($id, $dados, (int) $responsavel['id']);
        $this->json($ok, $ok ? 'Atualizado com sucesso.' : 'Erro ao atualizar.');
    }

    public function inativar(): void {
        Auxiliares::exigirPerfil('administrador');
        $id = (int) ($_POST['id'] ?? 0);
        $usuario = $this->modelo->buscarPorId($id);
        $ok = $this->modelo->inativar($id);
        if ($ok && $usuario) {
            \Config\Notificador::notificarUsuario($id, "Cadastro inativado", "Seu cadastro no SCOPi foi inativado.");
            \Config\Notificador::enviarEmail($usuario['email'], "Cadastro Inativado - SCOPi", "Olá {$usuario['nome']},\n\nSeu cadastro no sistema SCOPi foi inativado.");
        }
        $this->json($ok, $ok ? 'Inativado.' : 'Erro.');
    }

    public function reativar(): void {
        Auxiliares::exigirPerfil('administrador');
        $id = (int) ($_POST['id'] ?? 0);
        $usuario = $this->modelo->buscarPorId($id);
        $ok = $this->modelo->reativar($id);
        if ($ok && $usuario) {
            \Config\Notificador::notificarUsuario($id, "Cadastro reativado", "Seu cadastro no SCOPi foi reativado.");
            \Config\Notificador::enviarEmail($usuario['email'], "Cadastro Reativado - SCOPi", "Olá {$usuario['nome']},\n\nSeu cadastro no sistema SCOPi foi reativado.");
        }
        $this->json($ok, $ok ? 'Reativado.' : 'Erro.');
    }
}
