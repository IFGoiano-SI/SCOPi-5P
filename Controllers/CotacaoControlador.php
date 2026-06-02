<?php
namespace Controllers;

use Config\Auxiliares;
use Models\CotacaoModelo;

class CotacaoControlador extends BaseController {
    private CotacaoModelo $m;
    public function __construct() { $this->m = new CotacaoModelo(); }
    public function listar(): void {
        Auxiliares::exigirPerfil('comprador','administrador','gerente');
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

    /**
     * RF10: Regenerar/reenviar token de acesso para um fornecedor vinculado à cotação
     */
    public function reenviarToken(): void {
        Auxiliares::exigirPerfil('comprador','administrador');
        $cotacaoFornecedorId = (int)($_POST['cotacao_fornecedor_id'] ?? 0);

        if ($cotacaoFornecedorId <= 0) {
            $this->json(false, 'Parâmetros inválidos.');
            return;
        }

        $bd = \Config\BancoDados::obterInstancia()->obterConexao();

        // Buscar dados do convite
        $q = $bd->prepare("
            SELECT cf.*, c.numero AS numero_cotacao, c.status AS status_cotacao,
                   f.email, f.razao_social
            FROM cotacao_fornecedores cf
            JOIN cotacoes c ON c.id = cf.cotacao_id
            JOIN fornecedores f ON f.id = cf.fornecedor_id
            WHERE cf.id = :cfid
        ");
        $q->execute([':cfid' => $cotacaoFornecedorId]);
        $cf = $q->fetch();

        if (!$cf) {
            $this->json(false, 'Convite não encontrado.');
            return;
        }

        if ($cf['status_cotacao'] !== 'aberta') {
            $this->json(false, 'Só é possível reenviar tokens enquanto a cotação estiver aberta.');
            return;
        }

        // Gerar novo token (invalida o anterior)
        $novoToken = bin2hex(random_bytes(32));
        $bd->prepare("UPDATE cotacao_fornecedores SET token = :tok, status = 'pendente', enviado_em = NOW() WHERE id = :cfid")
           ->execute([':tok' => $novoToken, ':cfid' => $cotacaoFornecedorId]);

        // Enviar e-mail com novo link
        if (!empty($cf['email'])) {
            $responderUrl = base_url('cotacao/responder?token=' . $novoToken);
            $assunto = "Reenvio de Convite - Cotação " . $cf['numero_cotacao'];
            $mensagem = "
                <h2>Olá, " . htmlspecialchars($cf['razao_social']) . "!</h2>
                <p>Este é um reenvio do convite para a cotação <strong>" . $cf['numero_cotacao'] . "</strong>.</p>
                <p>O link anterior foi invalidado. Use o novo link abaixo para enviar sua proposta:</p>
                <p><a href=\"" . $responderUrl . "\" style=\"display:inline-block; padding:10px 20px; background-color:#510B76; color:#fff; text-decoration:none; border-radius:5px;\">Responder Cotação</a></p>
                <p>Caso o botão não funcione, copie e cole o seguinte link no seu navegador:</p>
                <p>" . $responderUrl . "</p>
                <br>
                <p>Atenciosamente,<br>Departamento de Compras</p>
            ";
            \Config\Notificador::enviarEmail($cf['email'], $assunto, $mensagem);
        }

        $this->json(true, 'Token regenerado e convite reenviado com sucesso.');
    }

    public function exportar(): void {
        Auxiliares::exigirPerfil('comprador', 'administrador', 'gerente');
        $filtros = $_GET;
        $cotacoes = $this->m->listarComFiltros($filtros);

        $cabecalhos = ['ID', 'Número', 'Solicitação', 'Vencedor', 'Status', 'Criado Em'];
        $dadosCsv = [];
        foreach ($cotacoes as $c) {
            $dadosCsv[] = [
                $c['id'],
                $c['numero'],
                $c['numero_solicitacao'] ?? '-',
                $c['fornecedor_vencedor'] ?? '-',
                ucfirst($c['status']),
                date('d/m/Y H:i', strtotime($c['criado_em']))
            ];
        }

        Auxiliares::gerarCSV('cotacoes', $cabecalhos, $dadosCsv);
    }

    public function imprimir(): void {
        Auxiliares::exigirAutenticacao();
        $id = (int)($_GET['id'] ?? 0);
        $cotacao = $this->m->buscarComDetalhes($id);
        
        if (!$cotacao) {
            die('Cotação não encontrada.');
        }

        // Renderizar a view sem o layout padrão para impressão
        $this->renderizarSemLayout('ordens/imprimir-cotacao', compact('cotacao'));
    }
}
