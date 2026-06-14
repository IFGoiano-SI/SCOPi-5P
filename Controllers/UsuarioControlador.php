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

        if (isset($_GET['busca_modal'])) {
            $this->json(true, '', $usuarios);
            return;
        }

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

    public function consultarMatricula(): void {
        Auxiliares::exigirAutenticacao();
        $matricula = trim($_GET['matricula'] ?? '');
        if (empty($matricula)) {
            $this->json(false, 'Matrícula não informada.');
            return;
        }

        $bd = \Config\BancoDados::obterInstancia()->obterConexao();
        $q = $bd->prepare("SELECT id, nome, matricula FROM usuarios WHERE matricula = :matricula AND situacao = 'ativo' LIMIT 1");
        $q->execute([':matricula' => $matricula]);
        $usu = $q->fetch();

        if ($usu) {
            $this->json(true, 'Sucesso', $usu);
        } else {
            $this->json(false, 'Usuário não encontrado ou inativo.');
        }
    }

    public function salvar(): void {
        Auxiliares::exigirPerfil('administrador');
        $responsavel = Auxiliares::usuarioLogado();
        $id = (int) ($_POST['id'] ?? 0);
        $dados = [
            'nome' => trim($_POST['nome'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'matricula' => trim($_POST['matricula'] ?? ''),
            'contato' => trim($_POST['contato'] ?? ''),
            'departamento_id' => (int) ($_POST['departamento_id'] ?? 0),
            'perfil' => trim($_POST['perfil'] ?? 'usuario'),
            'senha' => trim($_POST['senha'] ?? ''),
            'senha' => trim($_POST['senha'] ?? ''),
        ];

        if ($dados['nome'] === '' || $dados['email'] === '' || $dados['departamento_id'] <= 0) {
            $this->json(false, 'Preencha nome, e-mail e departamento.');
            return;
        }

        if ($id === 0) {
            if (empty($dados['senha'])) $dados['senha'] = 'SCOPi2026*';
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
        $responsavel = Auxiliares::usuarioLogado();
        $id = (int) ($_POST['id'] ?? 0);
        $usuario = $this->modelo->buscarPorId($id);
        $ok = $this->modelo->inativar($id, (int)$responsavel['id']);
        if ($ok && $usuario) {
            \Config\Notificador::notificarUsuario($id, "Cadastro inativado", "Seu cadastro no SCOPi foi inativado.");
            \Config\Notificador::enviarEmail($usuario['email'], "Cadastro Inativado - SCOPi", "Olá {$usuario['nome']},\n\nSeu cadastro no sistema SCOPi foi inativado.");
        }
        $this->json($ok, $ok ? 'Inativado.' : 'Erro.');
    }

    public function reativar(): void {
        Auxiliares::exigirPerfil('administrador');
        $responsavel = Auxiliares::usuarioLogado();
        $id = (int) ($_POST['id'] ?? 0);
        $usuario = $this->modelo->buscarPorId($id);
        $ok = $this->modelo->reativar($id, (int)$responsavel['id']);
        if ($ok && $usuario) {
            \Config\Notificador::notificarUsuario($id, "Cadastro reativado", "Seu cadastro no SCOPi foi reativado.");
            \Config\Notificador::enviarEmail($usuario['email'], "Cadastro Reativado - SCOPi", "Olá {$usuario['nome']},\n\nSeu cadastro no sistema SCOPi foi reativado.");
        }
        $this->json($ok, $ok ? 'Reativado.' : 'Erro.');
    }

    public function redefinirSenha(): void {
        Auxiliares::exigirPerfil('administrador');
        $id = (int) ($_POST['id'] ?? 0);
        $senha = trim($_POST['senhaPadrao'] ?? 'SCOPi2026*');
        if (empty($senha)) $senha = 'SCOPi2026*';
        
        $responsavel = Auxiliares::usuarioLogado();
        $ok = $this->modelo->atualizarSenha($id, $senha);
        
        if ($ok) {
            $this->modelo->registrarAcaoPersonalizada('usuarios', $id, (int)$responsavel['id'], 'Redefinição de Senha', "A senha foi redefinida para a senha padrão ($senha) pelo administrador.");
            $this->json(true, "Senha redefinida com sucesso para $senha.");
        } else {
            $this->json(false, 'Erro ao redefinir a senha.');
        }
    }

    public function exportar(): void {
        Auxiliares::exigirPerfil('administrador', 'gerente');
        $usuario = Auxiliares::usuarioLogado();
        $filtros = $_GET;
        $departamentoId = ($usuario['perfil'] ?? '') === 'gerente' ? (int) $usuario['departamento_id'] : null;
        $usuarios = $this->modelo->listarComFiltros($filtros, $departamentoId);

        $cabecalhos = ['ID', 'Nome', 'Matrícula', 'E-mail', 'Contato', 'Perfil', 'Departamento', 'Situação', 'Criado Em'];
        $dadosCsv = [];
        foreach ($usuarios as $u) {
            $dadosCsv[] = [
                $u['id'],
                $u['nome'],
                $u['matricula'],
                $u['email'],
                $u['contato'],
                ucfirst($u['perfil']),
                $u['nome_departamento'] ?? '—',
                ucfirst($u['situacao']),
                date('d/m/Y H:i', strtotime($u['criado_em']))
            ];
        }

        Auxiliares::gerarCSV('usuarios', $cabecalhos, $dadosCsv);
    }
}
