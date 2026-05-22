<?php

namespace Controllers;

require_once("Models/Database.php");
require_once("Config/Helpers.php");
use Config\Auxiliares;

class BaseController{

    protected $data;

    public function __construct(){
        $this->data = [];
    }

    protected function render($view){
        return view($view, $this->data);
    }

    /**
     * Renderiza uma view com o layout (header + footer)
     */
    protected function renderizar(string $view, array $variaveis = []): void {
        extract($variaveis);
        $flash   = Auxiliares::obterFlash();
        $usuario = Auxiliares::usuarioLogado();
        require_once __DIR__ . '/../Views/templates/header.php';
        require_once __DIR__ . '/../Views/' . $view . '.php';
        require_once __DIR__ . '/../Views/templates/footer.php';
    }

    /**
     * Renderiza uma view sem layout (páginas de login, etc.)
     */
    protected function renderizarSemLayout(string $view, array $variaveis = []): void {
        extract($variaveis);
        require_once __DIR__ . '/../Views/' . $view . '.php';
    }

    /**
     * Responde com JSON (compatibilidade com controllers importados)
     */
    protected function json(bool $sucesso, string $mensagem = '', $dados = null): void {
        Auxiliares::json($sucesso, $mensagem, $dados);
    }

    // isLogged
    protected function isLogged(){
        return isset($_SESSION['usuario_id']);
    }

    // Redireciona para uma página e define mensagem opcional na sessão
    protected function redirect($path, $message = null)
    {
        if ($message) {
            $_SESSION['msg'] = $message;
        }
        header("Location: {$path}");
        exit;
    }

    // Cria array de mensagem para exibição
    protected function flash(string $texto, string $color = 'success'): array
    {
        return [
            'texto' => $texto,
            'color' => $color
        ];
    }

    // Verifica se usuário está logado, redireciona para login se não
    protected function requireLogin()
    {
        if (!$this->isLogged()) {
            header('Location: ' . base_url('login'));
            exit;
        }
    }
    
}