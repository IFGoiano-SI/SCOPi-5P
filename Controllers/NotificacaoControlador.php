<?php
namespace Controllers;

use Config\Auxiliares;
use Models\NotificacaoModelo;

class NotificacaoControlador extends BaseController {

    private NotificacaoModelo $modelo;

    public function __construct() {
        $this->modelo = new NotificacaoModelo();
    }

    /**
     * RF15: Listar notificações do usuário logado (retorna JSON)
     */
    public function listar(): void {
        Auxiliares::exigirAutenticacao();
        $usuario = Auxiliares::usuarioLogado();
        $categoria = $_GET['categoria'] ?? 'todas';
        $notificacoes = $this->modelo->listarPorUsuario((int)$usuario['id'], $categoria);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['sucesso' => true, 'dados' => $notificacoes]);
    }

    /**
     * RF15: Buscar dados de uma notificação específica (retorna JSON)
     */
    public function dados(): void {
        Auxiliares::exigirAutenticacao();
        $usuario = Auxiliares::usuarioLogado();
        $id = (int)($_GET['id'] ?? 0);
        $notif = $this->modelo->buscarPorId($id, (int)$usuario['id']);

        if ($notif) {
            // Marca como lida ao visualizar
            $this->modelo->marcarLida($id, (int)$usuario['id']);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['sucesso' => (bool)$notif, 'dados' => $notif]);
    }

    /**
     * RF15: Marcar notificação como lida (retorna JSON)
     */
    public function marcarLida(): void {
        Auxiliares::exigirAutenticacao();
        $usuario = Auxiliares::usuarioLogado();
        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->modelo->marcarLida($id, (int)$usuario['id']);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['sucesso' => $ok]);
    }

    /**
     * RF15: Marcar todas as notificações como lidas (retorna JSON)
     */
    public function marcarTodasLidas(): void {
        Auxiliares::exigirAutenticacao();
        $usuario = Auxiliares::usuarioLogado();
        $qtd = $this->modelo->marcarTodasLidas((int)$usuario['id']);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['sucesso' => true, 'marcadas' => $qtd]);
    }

    /**
     * RF15: Excluir uma notificação (retorna JSON)
     */
    public function excluir(): void {
        Auxiliares::exigirAutenticacao();
        $usuario = Auxiliares::usuarioLogado();
        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->modelo->excluir($id, (int)$usuario['id']);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['sucesso' => $ok]);
    }

    /**
     * RF15: Contar notificações não lidas (retorna JSON — usado pelo badge)
     */
    public function contarNaoLidas(): void {
        Auxiliares::exigirAutenticacao();
        $usuario = Auxiliares::usuarioLogado();
        $total = $this->modelo->contarNaoLidas((int)$usuario['id']);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['sucesso' => true, 'total' => $total]);
    }
}
