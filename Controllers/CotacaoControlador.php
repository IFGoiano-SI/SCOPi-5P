<?php
namespace Controllers;

use Config\Auxiliares;
use Models\CotacaoModelo;

class CotacaoControlador extends BaseController {
    private CotacaoModelo $m;
    public function __construct() { $this->m = new CotacaoModelo(); }
    public function listar(): void {
        Auxiliares::exigirPerfil('comprador','administrador');
        $filtros = $_GET;
        $cotacoes = $this->m->listarComFiltros($filtros);
        $fornecedoresAtivos = (new \Models\FornecedorModelo())->listarComFiltros(['situacao' => 'ativo']);
        $this->renderizar('ordens/cotacoes', compact('cotacoes', 'filtros', 'fornecedoresAtivos'));
    }
    public function dados(): void {
        Auxiliares::exigirAutenticacao();
        $id = (int)($_GET['id'] ?? 0);
        $r = $this->m->buscarComDetalhes($id);
        if ($r) {
            $this->json(true, '', $r);
        } else {
            $this->json(false, 'Não encontrado.');
        }
    }
    public function criar(): void {
        Auxiliares::exigirPerfil('comprador','administrador');
        $usuario = Auxiliares::usuarioLogado();
        $solicitacaoId = (int)($_POST['solicitacao_id'] ?? 0);
        $fornecedorIds = $_POST['fornecedores'] ?? [];
        if (!is_array($fornecedorIds)) {
            $fornecedorIds = [];
        }

        if ($solicitacaoId <= 0) {
            $this->json(false, 'Selecione uma solicitação autorizada.');
            return;
        }
        if (empty($fornecedorIds)) {
            $this->json(false, 'Selecione ao menos um fornecedor para enviar o convite.');
            return;
        }

        try {
            $id = $this->m->criarCotacao($solicitacaoId, $fornecedorIds, $usuario['id']);
            $this->json(true, 'Cotação criada com sucesso. Convites enviados aos fornecedores.', ['id' => $id]);
        } catch (\Exception $e) {
            $this->json(false, 'Erro ao criar cotação: ' . $e->getMessage());
        }
    }
    public function selecionarVencedor(): void {
        Auxiliares::exigirPerfil('comprador','administrador');
        $usuario = Auxiliares::usuarioLogado();
        $cotacaoId = (int)($_POST['cotacao_id'] ?? 0);
        $cfId = (int)($_POST['cotacao_fornecedor_id'] ?? 0);

        if ($cotacaoId <= 0 || $cfId <= 0) {
            $this->json(false, 'Parâmetros inválidos.');
            return;
        }

        try {
            $ok = $this->m->definirVencedora($cotacaoId, $cfId, $usuario['id']);
            $this->json($ok, $ok ? 'Proposta vencedora selecionada. Ordem de compra gerada em rascunho.' : 'Erro ao selecionar proposta.');
        } catch (\Exception $e) {
            $this->json(false, 'Erro ao selecionar proposta vencedora: ' . $e->getMessage());
        }
    }
    public function fechar(): void { Auxiliares::exigirPerfil('comprador','administrador'); $this->json(true,'Cotação encerrada.'); }
}
