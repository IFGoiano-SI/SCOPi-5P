<?php
namespace Controllers;

use Config\Auxiliares;
use Models\OrdemCompraModelo;
use Config\Notificador;

class OrdemFornecedorControlador extends BaseController {

    private OrdemCompraModelo $m;

    public function __construct() {
        $this->m = new OrdemCompraModelo();
    }

    public function revisar(): void {
        Auxiliares::iniciarSessao();
        $token = $_GET['token'] ?? $_SESSION['fornecedor_oc_token'] ?? '';

        if (empty($_SESSION['fornecedor_oc_logado']) || empty($_SESSION['ordem_compra_id']) || $_SESSION['fornecedor_oc_token'] !== $token) {
            Auxiliares::redirecionar('login/fornecedor/ordem?token=' . urlencode($token));
            return;
        }

        $ocId = $_SESSION['ordem_compra_id'];
        $ordem = $this->m->buscarPorId($ocId);

        if (!$ordem || $ordem['token'] !== $token) {
            Auxiliares::encerrarSessao();
            Auxiliares::flash('erro', 'Sessão inválida ou Ordem de Compra não encontrada.');
            Auxiliares::redirecionar('login/fornecedor/ordem?token=' . urlencode($token));
            return;
        }

        // Buscar itens da ordem de compra
        $ordem['itens'] = $this->m->buscarItens($ocId);

        $this->renderizarSemLayout('ordens/revisar', compact('ordem', 'token'));
    }

    public function confirmar(): void {
        Auxiliares::iniciarSessao();
        $token = $_POST['token'] ?? $_SESSION['fornecedor_oc_token'] ?? '';

        if (empty($_SESSION['fornecedor_oc_logado']) || empty($_SESSION['ordem_compra_id']) || $_SESSION['fornecedor_oc_token'] !== $token) {
            $this->json(false, 'Sessão expirada. Por favor, recarregue a página.');
            return;
        }

        $ocId = $_SESSION['ordem_compra_id'];
        $ordem = $this->m->buscarPorId($ocId);

        if (!$ordem || $ordem['token'] !== $token) {
            $this->json(false, 'Ordem de Compra não encontrada.');
            return;
        }

        if ((int)$ordem['aceito_fornecedor'] === 1) {
            $this->json(false, 'Esta Ordem de Compra já foi aceita anteriormente.');
            return;
        }

        // Atualizar aceitação no BD
        $bd = \Config\BancoDados::obterInstancia()->obterConexao();
        $q = $bd->prepare("
            UPDATE ordens_compra 
            SET aceito_fornecedor = 1, aceito_em = NOW(), status = 'aprovado', atualizado_em = NOW() 
            WHERE id = :id
        ");
        $q->execute([':id' => $ocId]);

        // Atualizar status dos itens da OC para 'aprovado'
        $bd->prepare("
            UPDATE ordem_compra_itens 
            SET status = 'aprovado' 
            WHERE ordem_id = :id
        ")->execute([':id' => $ocId]);

        // Registrar no histórico de cadastros
        $this->m->registrarHistorico('ordens_compra', $ocId, ['aceito_fornecedor' => 0, 'status' => $ordem['status']], ['aceito_fornecedor' => 1, 'status' => 'aprovado'], (int)$ordem['usuario_id'], 'Fornecedor aceitou a Ordem de Compra');

        // Notificar o comprador responsável
        $mensagemNotif = "O fornecedor <strong>" . htmlspecialchars($ordem['nome_fornecedor']) . "</strong> confirmou o recebimento e aceitou a Ordem de Compra <strong>" . htmlspecialchars($ordem['numero']) . "</strong>.";
        Notificador::notificarUsuario(
            (int)$ordem['usuario_id'],
            'Ordem de Compra ' . $ordem['numero'] . ' Aceita',
            $mensagemNotif,
            'ordem'
        );

        $this->json(true, 'Ordem de Compra aceita e confirmada com sucesso!');
    }
}
