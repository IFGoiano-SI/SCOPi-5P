<?php
namespace Controllers;

use Config\Auxiliares;
use Config\Notificador;
use Models\NotaFiscalModelo;
use Models\OrdemCompraModelo;

class NotaFiscalControlador extends BaseController {

    private NotaFiscalModelo $m;

    public function __construct() {
        $this->m = new NotaFiscalModelo();
    }

    /**
     * RF14: Listar notas fiscais com filtros
     */
    public function listar(): void {
        Auxiliares::exigirPerfil('contabilidade', 'comprador', 'administrador');
        $filtros = $_GET;
        $notas = $this->m->listarComFiltros($filtros);
        $this->renderizar('notas/notas', compact('notas', 'filtros'));
    }

    /**
     * RF14: Buscar dados de uma nota fiscal por ID (retorna JSON)
     */
    public function dados(): void {
        Auxiliares::exigirAutenticacao();
        $r = $this->m->buscarPorId((int)($_GET['id'] ?? 0));
        $r ? $this->json(true, '', $r) : $this->json(false, 'Nota fiscal não encontrada.');
    }

    /**
     * RF14: Salvar nota fiscal com todos os campos obrigatórios
     */
    public function salvar(): void {
        Auxiliares::exigirPerfil('contabilidade', 'administrador');
        $usuario = Auxiliares::usuarioLogado();

        $dados = $_POST;
        if (!empty($_POST['itens_json'])) {
            $dados['itens'] = json_decode($_POST['itens_json'], true);
        }

        // Validações básicas
        if (empty($dados['numero']) || empty($dados['fornecedor_id']) || empty($dados['data_emissao'])) {
            $this->json(false, 'Os campos número, fornecedor e data de emissão são obrigatórios.');
            return;
        }

        try {
            $novoId = $this->m->cadastrar($dados, (int)$usuario['id']);

            // Salvar itens se enviados
            if (!empty($dados['itens']) && is_array($dados['itens'])) {
                $this->m->salvarItens($novoId, $dados['itens']);
            }

            // Registrar histórico
            $this->m->registrarHistorico('notas_fiscais', $novoId, [], $dados, (int)$usuario['id']);

            $this->json(true, 'Nota fiscal cadastrada com sucesso.', ['id' => $novoId]);
        } catch (\Exception $e) {
            $this->json(false, $e->getMessage());
        }
    }

    /**
     * RF14: Importar nota fiscal a partir de XML NF-e
     */
    public function importar(): void {
        Auxiliares::exigirPerfil('contabilidade', 'administrador');
        $usuario = Auxiliares::usuarioLogado();

        // Verificar se o arquivo foi enviado
        if (empty($_FILES['arquivo_xml']['tmp_name'])) {
            $this->json(false, 'Nenhum arquivo XML enviado.');
            return;
        }

        $xmlContent = file_get_contents($_FILES['arquivo_xml']['tmp_name']);
        if (empty($xmlContent)) {
            $this->json(false, 'Arquivo XML vazio ou inválido.');
            return;
        }

        $dados = $this->m->importarXml($xmlContent);

        if (isset($dados['erro'])) {
            $this->json(false, 'Erro ao processar XML: ' . $dados['erro']);
            return;
        }

        // Tentar identificar fornecedor pelo CNPJ
        $bd = \Config\BancoDados::obterInstancia()->obterConexao();
        if (!empty($dados['fornecedor_cnpj'])) {
            $cnpjLimpo = preg_replace('/\D/', '', $dados['fornecedor_cnpj']);
            $cnpjFormatado = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpjLimpo);
            $qF = $bd->prepare("SELECT id, razao_social FROM fornecedores WHERE cnpj = :cnpj OR cnpj = :cnpjLimpo LIMIT 1");
            $qF->execute([':cnpj' => $cnpjFormatado, ':cnpjLimpo' => $cnpjLimpo]);
            $forn = $qF->fetch();
            if ($forn) {
                $dados['fornecedor_id'] = $forn['id'];
                $dados['fornecedor_nome'] = $forn['razao_social'];
            }
        }

        // Tentar identificar produtos pelo código
        if (!empty($dados['itens']) && is_array($dados['itens'])) {
            $qP = $bd->prepare("SELECT id, nome FROM produtos WHERE codigo = :cod LIMIT 1");
            foreach ($dados['itens'] as &$item) {
                if (!empty($item['produto_codigo'])) {
                    $qP->execute([':cod' => $item['produto_codigo']]);
                    $prod = $qP->fetch();
                    if ($prod) {
                        $item['produto_id'] = $prod['id'];
                    }
                }
            }
        }

        // Retornar dados parseados para o frontend preencher o formulário
        $this->json(true, 'XML processado com sucesso.', $dados);
    }

    /**
     * RF14: Vincular nota fiscal a ordens de compra (N:N)
     */
    public function vincular(): void {
        Auxiliares::exigirPerfil('contabilidade', 'administrador');
        $usuario = Auxiliares::usuarioLogado();
        $notaId = (int)($_POST['nota_id'] ?? 0);
        $ordemId = (int)($_POST['ordem_id'] ?? 0);

        if ($notaId <= 0 || $ordemId <= 0) {
            $this->json(false, 'IDs de nota fiscal e ordem de compra são obrigatórios.');
            return;
        }

        // Verificar divergências antes de vincular (RF14)
        $divergencias = $this->m->verificarDivergencias($notaId, $ordemId);

        // Vincular mesmo com divergências (mas alertar o usuário)
        $vinculado = $this->m->vincularOrdem($notaId, $ordemId, (int)$usuario['id']);

        if (!$vinculado) {
            $this->json(false, 'Este vínculo já existe.');
            return;
        }

        // Atualizar status dos itens da OC conforme recebimento
        $ordemModelo = new OrdemCompraModelo();
        $itensNF = $this->m->buscarItens($notaId);
        $itensOC = $ordemModelo->buscarItens($ordemId);
        $bd = \Config\BancoDados::obterInstancia()->obterConexao();

        foreach ($itensOC as $itemOC) {
            foreach ($itensNF as $itemNF) {
                if ($itemNF['produto_id'] == $itemOC['produto_id']) {
                    $novaQtdAtendida = (float)$itemOC['quantidade_atendida'] + (float)$itemNF['quantidade'];
                    $statusItem = $novaQtdAtendida >= (float)$itemOC['quantidade'] ? 'atendido' : 'parcial';

                    $qUp = $bd->prepare("UPDATE ordem_compra_itens SET quantidade_atendida = :qtd, status_item = :st WHERE id = :id");
                    $qUp->execute([
                        ':qtd' => $novaQtdAtendida,
                        ':st' => $statusItem,
                        ':id' => $itemOC['id']
                    ]);
                    break;
                }
            }
        }

        // Atualizar status da OC com base nos itens (RF13)
        $ordemModelo->atualizarStatusPorItens($ordemId);

        // Notificar comprador responsável
        $oc = $ordemModelo->buscarPorId($ordemId);
        if ($oc) {
            Notificador::notificarUsuario(
                (int)$oc['usuario_id'],
                'NF vinculada à OC ' . $oc['numero'],
                "A nota fiscal foi vinculada à ordem de compra {$oc['numero']}.",
                'nota'
            );
        }

        $resultado = ['vinculado' => true];
        if (!empty($divergencias)) {
            $resultado['divergencias'] = $divergencias;
            $this->json(true, 'Vínculo realizado com sucesso, porém foram identificadas divergências.', $resultado);
        } else {
            $this->json(true, 'Vínculo realizado com sucesso.', $resultado);
        }
    }

    public function exportar(): void {
        Auxiliares::exigirPerfil('contabilidade', 'comprador', 'administrador');
        $filtros = $_GET;
        $notas = $this->m->listarComFiltros($filtros);

        $cabecalhos = ['Número da NF', 'Ordem de Compra', 'Fornecedor', 'Data de Emissão', 'Valor', 'Status'];
        $dadosCsv = [];
        foreach ($notas as $n) {
            $dadosCsv[] = [
                $n['numero'],
                $n['ordem_numero'] ?? '-',
                $n['nome_fornecedor'] ?? '-',
                $n['data_emissao'] ? date('d/m/Y', strtotime($n['data_emissao'])) : '-',
                number_format($n['valor_total'] ?? 0, 2, ',', '.'),
                ucfirst($n['status'] ?? 'Pendente')
            ];
        }

        Auxiliares::gerarCSV('notas_fiscais', $cabecalhos, $dadosCsv);
    }

    public function lancar_item(): void {
        Auxiliares::exigirPerfil('contabilidade', 'administrador');
        $usuario = Auxiliares::usuarioLogado();
        $nfItemId = (int)($_POST['nf_item_id'] ?? 0);
        $numeroOc = $_POST['numero_oc'] ?? '';
        $numeroItemOc = (int)($_POST['numero_item_oc'] ?? 0);

        if ($nfItemId <= 0 || empty($numeroOc) || $numeroItemOc <= 0) {
            $this->json(false, 'Preencha a Ordem de Compra e o Item da Ordem.');
            return;
        }

        $res = $this->m->lancarItem($nfItemId, $numeroOc, $numeroItemOc, $usuario['id']);
        $this->json($res['sucesso'], $res['mensagem']);
    }

    public function retirar_lancamento_item(): void {
        Auxiliares::exigirPerfil('contabilidade', 'administrador');
        $usuario = Auxiliares::usuarioLogado();
        $nfItemId = (int)($_POST['nf_item_id'] ?? 0);

        if ($nfItemId <= 0) {
            $this->json(false, 'Item inválido.');
            return;
        }

        $ok = $this->m->retirarLancamentoItem($nfItemId, $usuario['id']);
        $this->json($ok, $ok ? 'Lançamento desfeito com sucesso.' : 'Erro ao desfazer lançamento. Talvez o item não estivesse lançado.');
    }

    public function imprimir(): void {
        Auxiliares::exigirAutenticacao();
        $id = (int)($_GET['id'] ?? 0);
        $nota = $this->m->buscarPorId($id);
        
        if (!$nota) {
            die('Nota Fiscal não encontrada.');
        }

        $nota['itens'] = $this->m->buscarItens($id);
        
        // Renderizar a view sem o layout padrão (header/footer) para impressão
        $this->renderizarSemLayout('notas/imprimir', compact('nota'));
    }

    public function excluir(): void {
        Auxiliares::exigirPerfil('contabilidade', 'administrador');
        $usuario = Auxiliares::usuarioLogado();
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->json(false, 'ID de nota fiscal inválido.');
            return;
        }

        try {
            $sucesso = $this->m->excluir($id, (int)$usuario['id']);
            if ($sucesso) {
                $this->json(true, 'Nota fiscal excluída com sucesso.');
            } else {
                $this->json(false, 'Erro ao excluir a nota fiscal.');
            }
        } catch (\Exception $e) {
            $this->json(false, $e->getMessage());
        }
    }
}
