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

        if (isset($_GET['busca_modal'])) {
            $this->json(true, '', $cotacoes);
            return;
        }

        $fornecedoresAtivos = (new \Models\FornecedorModelo())->listarAtivoComCategorias();
        $categorias = (new \Models\CategoriaModelo())->listarAtivas();
        $this->renderizar('ordens/cotacoes', compact('cotacoes', 'filtros', 'fornecedoresAtivos', 'categorias'));
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
    public function salvarCapa(): void {
        Auxiliares::exigirPerfil('comprador','administrador');
        $usuario = Auxiliares::usuarioLogado();
        $id = (int)($_POST['id'] ?? 0);
        $dataAbertura    = trim($_POST['data_abertura']    ?? '');
        $dataEncerramento = trim($_POST['data_encerramento'] ?? '');

        if (empty($dataAbertura) || empty($dataEncerramento)) {
            $this->json(false, 'As datas de abertura e encerramento são obrigatórias.');
            return;
        }
        if ($dataEncerramento < $dataAbertura) {
            $this->json(false, 'A data de encerramento não pode ser anterior à data de abertura.');
            return;
        }

        try {
            if ($id === 0) {
                $id = $this->m->criarCapa($usuario['id'], $dataAbertura, $dataEncerramento);
                $this->json(true, 'Cotação iniciada com sucesso.', ['id' => $id]);
            } else {
                $bd = \Config\BancoDados::obterInstancia()->obterConexao();
                $bd->prepare("UPDATE cotacoes SET data_abertura=:dta, data_encerramento=:dte WHERE id=:id AND status='aberta'")
                   ->execute([':dta' => $dataAbertura, ':dte' => $dataEncerramento, ':id' => $id]);
                $this->json(true, 'Capa atualizada.', ['id' => $id]);
            }
        } catch (\Exception $e) {
            $this->json(false, 'Erro ao salvar cotação: ' . $e->getMessage());
        }
    }

    public function criarCompleta(): void {
        Auxiliares::exigirPerfil('comprador','administrador');
        $usuario = Auxiliares::usuarioLogado();
        
        $solicitacaoId = (int)($_POST['solicitacao_id'] ?? 0);
        $dataAbertura    = trim($_POST['data_abertura']    ?? '');
        $dataEncerramento = trim($_POST['data_encerramento'] ?? '');
        $fornecedorIds = $_POST['fornecedores'] ?? [];

        if ($solicitacaoId <= 0) {
            $this->json(false, 'Selecione uma solicitação aprovada.');
            return;
        }
        if (empty($dataAbertura) || empty($dataEncerramento)) {
            $this->json(false, 'As datas de abertura e encerramento são obrigatórias.');
            return;
        }
        if ($dataEncerramento < $dataAbertura) {
            $this->json(false, 'A data de encerramento não pode ser anterior à data de abertura.');
            return;
        }
        if (!is_array($fornecedorIds) || empty($fornecedorIds)) {
            $this->json(false, 'Selecione ao menos um fornecedor.');
            return;
        }

        try {
            $id = $this->m->criarCompleta($usuario['id'], $solicitacaoId, $dataAbertura, $dataEncerramento, $fornecedorIds);
            $this->json(true, 'Cotação criada e convites enviados com sucesso.', ['id' => $id]);
        } catch (\Exception $e) {
            $this->json(false, 'Erro ao criar cotação: ' . $e->getMessage());
        }
    }
    
    public function salvarItens(): void {
        Auxiliares::exigirPerfil('comprador','administrador');
        $cotacaoId = (int)($_POST['id'] ?? 0);
        $itensJson = $_POST['itens_json'] ?? '[]';
        $itens = json_decode($itensJson, true);
        
        if ($cotacaoId <= 0 || !is_array($itens)) {
            $this->json(false, 'Dados inválidos.');
            return;
        }
        
        try {
            $this->m->salvarItensCotacao($cotacaoId, $itens);
            $this->json(true, 'Itens salvos com sucesso.');
        } catch (\Exception $e) {
            $this->json(false, 'Erro ao salvar itens: ' . $e->getMessage());
        }
    }
    
    public function vincularSolicitacao(): void {
        Auxiliares::exigirPerfil('comprador','administrador');
        $usuario = Auxiliares::usuarioLogado();
        $cotacaoId = (int)($_POST['cotacao_id'] ?? 0);
        $solicitacaoId = (int)($_POST['solicitacao_id'] ?? 0);
        if ($cotacaoId <= 0 || $solicitacaoId <= 0) {
            $this->json(false, 'Dados inválidos.');
            return;
        }
        try {
            $ok = $this->m->vincularSolicitacao($cotacaoId, $solicitacaoId, $usuario['id']);
            $this->json($ok, $ok ? 'Solicitação vinculada com sucesso.' : 'Erro ao vincular.');
        } catch (\Exception $e) {
            $this->json(false, 'Erro: ' . $e->getMessage());
        }
    }

    public function convidarFornecedores(): void {
        Auxiliares::exigirPerfil('comprador','administrador');
        $usuario = Auxiliares::usuarioLogado();
        $cotacaoId = (int)($_POST['cotacao_id'] ?? 0);
        $fornecedorIds = $_POST['fornecedores'] ?? [];
        if (!is_array($fornecedorIds)) $fornecedorIds = [];

        if ($cotacaoId <= 0) {
            $this->json(false, 'Cotação inválida.');
            return;
        }
        if (empty($fornecedorIds)) {
            $this->json(false, 'Selecione ao menos um fornecedor para enviar o convite.');
            return;
        }

        try {
            $enviados = $this->m->convidarFornecedores($cotacaoId, $fornecedorIds, $usuario['id']);
            $this->json(true, "Convites enviados com sucesso para $enviados fornecedor(es).");
        } catch (\Exception $e) {
            $this->json(false, 'Erro ao convidar fornecedores: ' . $e->getMessage());
        }
    }
    public function adicionarFornecedores(): void {
        Auxiliares::exigirPerfil('comprador','administrador');
        $usuario = Auxiliares::usuarioLogado();
        $cotacaoId = (int)($_POST['id'] ?? 0);
        $fornecedorIds = $_POST['fornecedores'] ?? [];
        if (!is_array($fornecedorIds)) $fornecedorIds = [];
        
        if ($cotacaoId <= 0 || empty($fornecedorIds)) {
            $this->json(false, 'Selecione ao menos um fornecedor para adicionar.');
            return;
        }
        
        try {
            $this->m->adicionarFornecedoresACotacao($cotacaoId, $fornecedorIds, $usuario['id']);
            $this->json(true, 'Fornecedores convidados com sucesso.');
        } catch (\Exception $e) {
            $this->json(false, 'Erro ao adicionar fornecedores: ' . $e->getMessage());
        }
    }
    public function selecionarVencedor(): void {
        Auxiliares::exigirPerfil('comprador','administrador');
        $usuario = Auxiliares::usuarioLogado();

        $json = json_decode(file_get_contents('php://input'), true);
        if ($json) {
            $cotacaoId = (int)($json['cotacao_id'] ?? 0);
            $propostaIds = array_map('intval', $json['proposta_ids'] ?? []);
            $gerarOC = isset($json['gerar_oc']) ? (int)$json['gerar_oc'] : 1;
        } else {
            $cotacaoId = (int)($_POST['cotacao_id'] ?? 0);
            $propostaIds = isset($_POST['proposta_ids']) ? array_map('intval', $_POST['proposta_ids']) : [];
            $gerarOC = isset($_POST['gerar_oc']) ? (int)$_POST['gerar_oc'] : 1;
        }

        if ($cotacaoId <= 0 || empty($propostaIds)) {
            $this->json(false, 'Parâmetros inválidos ou nenhuma proposta selecionada.');
            return;
        }

        try {
            $ok = $this->m->definirVencedoresPorItens($cotacaoId, $propostaIds, $usuario['id'], $gerarOC === 1);
            $msg = $gerarOC === 1 ? 'Vencedores selecionados e Ordem(ns) de Compra gerada(s).' : 'Vencedores selecionados. Cotação encerrada sem OC.';
            $this->json($ok, $ok ? $msg : 'Erro ao selecionar proposta.');
        } catch (\Exception $e) {
            $this->json(false, 'Erro ao selecionar propostas vencedoras: ' . $e->getMessage());
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

    public function excluir_item(): void {
        Auxiliares::exigirAutenticacao();
        $usuario = Auxiliares::usuarioLogado();
        $id = (int)($_POST['id']??0);
        $ok = $this->m->excluirItem($id, $usuario['id']);
        $this->json($ok, $ok ? 'Item removido e desvinculado com sucesso.' : 'Falha ao remover item. A cotação pode não estar aberta.');
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

    /**
     * Responder cotação (fornecedor) - acesso por token
     */
    public function responder(): void {
        $cotacaoId = (int)($_GET['c'] ?? 0);
        $token = $_GET['token'] ?? '';

        if (!$cotacaoId || !$token) {
            http_response_code(400);
            die('Link inválido.');
        }

        $resultado = $this->m->buscarParaRespostaComToken($cotacaoId, $token);
        if (!$resultado) {
            http_response_code(404);
            die('Cotação não encontrada ou link expirado.');
        }

        $cotacao = $resultado['cotacao'];
        $fornecedor = $resultado['fornecedor'];
        $itens = $resultado['itens'];
        $respostasAnteriores = $resultado['respostasAnteriores'];
        $totalEnvios = $resultado['totalEnvios'];

        $this->renderizar('cotacao/fornecedor-responder', compact('cotacao', 'fornecedor', 'itens', 'respostasAnteriores', 'totalEnvios'));
    }

    /**
     * Salvar resposta do fornecedor (validação + persistência)
     */
    public function salvarResposta(): void {
        $dados = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $cotacaoFornecedorId = (int)($dados['cotacao_fornecedor_id'] ?? 0);
        if (!$cotacaoFornecedorId) {
            $this->json(false, 'Fornecedor não identificado.');
            return;
        }

        // Validar itens obrigatórios
        $itens = $dados['itens'] ?? [];
        if (empty($itens)) {
            $this->json(false, 'Nenhum item foi preenchido.');
            return;
        }

        foreach ($itens as $idx => $item) {
            if (empty($item['preco_unitario']) || (float)$item['preco_unitario'] <= 0) {
                $this->json(false, "Item " . ($idx + 1) . ": Valor Unitário é obrigatório.");
                return;
            }
            if (empty($item['prazo_entrega']) || (int)$item['prazo_entrega'] < 1) {
                $this->json(false, "Item " . ($idx + 1) . ": Prazo é obrigatório (mínimo 1 dia).");
                return;
            }
        }

        $ok = $this->m->salvarResposta($cotacaoFornecedorId, $dados);
        $this->json($ok, $ok ? 'Resposta enviada com sucesso!' : 'Erro ao salvar resposta.');
    }
}
