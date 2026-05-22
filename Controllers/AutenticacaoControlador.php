<?php
namespace Controllers;

use Config\Auxiliares;
use Models\UsuarioModelo;

class AutenticacaoControlador extends BaseController {

    private UsuarioModelo $modelo;

    public function __construct() {
        $this->modelo = new UsuarioModelo();
    }

    public function exibirLogin(): void {
        Auxiliares::iniciarSessao();
        if (!empty($_SESSION['usuario_id'])) Auxiliares::redirecionar('inicio');
        $this->renderizarSemLayout('login/login');
    }

    public function entrar(): void {
        Auxiliares::iniciarSessao();
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        $usuario = $this->modelo->buscarPorEmail($email);

        if (!$usuario) {
            Auxiliares::flash('erro', 'E-mail ou senha inválidos.');
            Auxiliares::redirecionar('login');
        }

        // RF01 - Bloqueio temporário após 5 tentativas falhas
        if ($usuario['tentativas_falhas'] >= 5 && !empty($usuario['ultima_tentativa'])) {
            $ultimaTentativa = strtotime($usuario['ultima_tentativa']);
            if ((time() - $ultimaTentativa) < 15 * 60) {
                $minutosRestantes = ceil((15 * 60 - (time() - $ultimaTentativa)) / 60);
                Auxiliares::flash('erro', "Acesso bloqueado por excesso de tentativas. Tente novamente em {$minutosRestantes} minuto(s).");
                Auxiliares::redirecionar('login');
            }
        }

        if (!$this->modelo->verificarSenha($senha, $usuario['senha'])) {
            $this->modelo->incrementarTentativasFalhas($usuario['id']);
            Auxiliares::flash('erro', 'E-mail ou senha inválidos.');
            Auxiliares::redirecionar('login');
        }

        $this->modelo->resetarTentativasFalhas($usuario['id']);
        Auxiliares::definirSessaoUsuario($usuario);
        Auxiliares::redirecionar('inicio');
    }

    public function sair(): void {
        Auxiliares::encerrarSessao();
        Auxiliares::redirecionar('login');
    }

    public function exibirLoginFornecedor(): void {
        $token = $_GET['token'] ?? '';
        $this->renderizarSemLayout('login/login-fornecedor', ['token' => $token]);
    }

    public function recuperarSenha(): void {
        Auxiliares::iniciarSessao();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $usuario = $this->modelo->buscarPorEmail($email);
            if ($usuario) {
                $token = bin2hex(random_bytes(32));
                $this->modelo->salvarTokenRecuperacao($usuario['id'], $token);

                $link = base_url('senha/redefinir?token=' . urlencode($token));
                $mensagem = "Olá {$usuario['nome']},\n\nRecebemos uma solicitação de redefinição de senha para sua conta no SCOPi.\n\nPara redefinir sua senha, acesse o link abaixo (válido por 1 hora):\n{$link}\n\nSe você não solicitou esta redefinição, apenas desconsidere este e-mail.\n";
                \Config\Notificador::enviarEmail($usuario['email'], "Recuperação de Senha - SCOPi", $mensagem);
            }
            Auxiliares::flash('sucesso', 'Se o e-mail estiver cadastrado, você receberá as instruções em breve.');
            Auxiliares::redirecionar('senha/recuperar');
            return;
        }
        $this->renderizarSemLayout('senha/recuperar');
    }

    public function redefinirSenha(): void {
        Auxiliares::iniciarSessao();
        $token = trim($_GET['token'] ?? $_POST['token'] ?? '');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $senha    = $_POST['senha'] ?? '';
            $confirmar = $_POST['senha_confirmar'] ?? '';

            if ($senha !== $confirmar) {
                Auxiliares::flash('erro', 'As senhas não coincidem.');
                Auxiliares::redirecionar('senha/redefinir?token=' . urlencode($token));
                return;
            }

            // Validar complexidade de senha
            if (strlen($senha) < 8 ||
                !preg_match('/[A-Z]/', $senha) ||
                !preg_match('/[a-z]/', $senha) ||
                !preg_match('/[0-9]/', $senha) ||
                !preg_match('/[^A-Za-z0-9]/', $senha)) {
                Auxiliares::flash('erro', 'A senha deve conter no mínimo 8 caracteres, incluindo letras maiúsculas, minúsculas, números e caracteres especiais.');
                Auxiliares::redirecionar('senha/redefinir?token=' . urlencode($token));
                return;
            }

            $registro = $this->modelo->validarTokenRecuperacao($token);
            if (!$registro) {
                Auxiliares::flash('erro', 'Link inválido ou expirado. Solicite um novo.');
                Auxiliares::redirecionar('senha/recuperar');
                return;
            }
            $this->modelo->atualizarSenha($registro['usuario_id'], $senha);
            $this->modelo->deletarTokenRecuperacao($token);
            Auxiliares::flash('sucesso', 'Senha redefinida com sucesso. Faça login com a nova senha.');
            Auxiliares::redirecionar('login');
            return;
        }

        $registro = $this->modelo->validarTokenRecuperacao($token);
        if (!$registro) {
            Auxiliares::flash('erro', 'Link inválido ou expirado. Solicite um novo.');
            Auxiliares::redirecionar('senha/recuperar');
            return;
        }
        $this->renderizarSemLayout('senha/redefinir', ['token' => $token]);
    }

    public function entrarFornecedor(): void {
        Auxiliares::iniciarSessao();
        $token = trim($_POST['token'] ?? '');
        $cnpjInput = preg_replace('/\D/', '', $_POST['cnpj'] ?? '');

        if (empty($token) || empty($cnpjInput)) {
            Auxiliares::flash('erro', 'Por favor, preencha todos os campos.');
            Auxiliares::redirecionar('login/fornecedor?token=' . urlencode($token));
            return;
        }

        $bd = \Config\BancoDados::obterInstancia()->obterConexao();
        $q = $bd->prepare("
            SELECT cf.*, f.cnpj 
            FROM cotacao_fornecedores cf
            JOIN fornecedores f ON f.id = cf.fornecedor_id
            WHERE cf.token = :token
        ");
        $q->execute([':token' => $token]);
        $cf = $q->fetch();

        if (!$cf) {
            Auxiliares::flash('erro', 'Convite de cotação não encontrado ou link inválido.');
            Auxiliares::redirecionar('login/fornecedor?token=' . urlencode($token));
            return;
        }

        $cnpjDb = preg_replace('/\D/', '', $cf['cnpj']);

        if ($cnpjInput !== $cnpjDb) {
            Auxiliares::flash('erro', 'CNPJ informado não corresponde ao convite.');
            Auxiliares::redirecionar('login/fornecedor?token=' . urlencode($token));
            return;
        }

        // Sessão do Fornecedor
        $_SESSION['fornecedor_logado'] = true;
        $_SESSION['fornecedor_id'] = (int)$cf['fornecedor_id'];
        $_SESSION['cotacao_fornecedor_id'] = (int)$cf['id'];
        $_SESSION['fornecedor_token'] = $token;

        // Atualizar status do convite para visualizado
        if ($cf['status'] === 'pendente') {
            $bd->prepare("UPDATE cotacao_fornecedores SET status = 'visualizado' WHERE id = :id")
               ->execute([':id' => $cf['id']]);
        }

        Auxiliares::redirecionar('cotacao/responder?token=' . urlencode($token));
    }
}
