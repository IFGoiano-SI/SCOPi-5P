<?php
namespace Controllers;

use Config\Auxiliares;
use Config\Notificador;
use Models\OrdemCompraModelo;

class OrdemCompraControlador extends BaseController {

    private OrdemCompraModelo $m;

    public function __construct() {
        $this->m = new OrdemCompraModelo();
    }

    /**
     * RF13: Listar ordens de compra com filtros
     */
    public function listar(): void {
        Auxiliares::exigirPerfil('comprador', 'administrador', 'gerente');
        $filtros = $_GET;
        $ordens = $this->m->listarComFiltros($filtros);

        $bd = \Config\BancoDados::obterInstancia()->obterConexao();
        $qForn = $bd->query("SELECT id, razao_social, cnpj FROM fornecedores WHERE situacao = 'ativo' ORDER BY razao_social");
        $fornecedores = $qForn->fetchAll();

        $this->renderizar('ordens/ordens', compact('ordens', 'filtros', 'fornecedores'));
    }

    /**
     * RF13: Buscar dados de uma ordem por ID (retorna JSON)
     */
    public function dados(): void {
        Auxiliares::exigirAutenticacao();
        $id = (int)($_GET['id'] ?? 0);
        $r = $this->m->buscarPorId($id);
        if ($r) {
            $r['itens'] = $this->m->buscarItens($id);
            $this->json(true, '', $r);
        } else {
            $this->json(false, 'Ordem de compra não encontrada.');
        }
    }

    /**
     * RF13: Salvar ordem de compra (edição apenas quando status = aberto)
     */
    public function salvar(): void {
        Auxiliares::exigirPerfil('comprador', 'administrador');
        $dados = $_POST;

        if (empty($dados['fornecedor_id'])) {
            $this->json(false, 'Fornecedor é obrigatório.');
            return;
        }

        try {
            $usuario = Auxiliares::usuarioLogado();
            $usuarioId = (int)($usuario['id'] ?? 0);

            $id = $this->m->salvar($dados, $usuarioId);

            // Salvar itens se enviados via JSON
            if (!empty($_POST['itens_json'])) {
                $itens = json_decode($_POST['itens_json'], true);
                if (is_array($itens)) {
                    $this->m->salvarItens($id, $itens);
                }
            } else if (!empty($dados['itens']) && is_array($dados['itens'])) {
                $this->m->salvarItens($id, $dados['itens']);
            }

            $this->json(true, 'Ordem de compra salva com sucesso!', ['id' => $id]);
        } catch (\Exception $e) {
            $this->json(false, 'Erro ao salvar ordem de compra: ' . $e->getMessage());
        }
    }

    public function vincularSolicitacao(): void {
        Auxiliares::exigirPerfil('comprador','administrador');
        $usuario = Auxiliares::usuarioLogado();
        $ordemId = (int)($_POST['ordem_id'] ?? 0);
        $solicitacaoId = (int)($_POST['solicitacao_id'] ?? 0);
        if ($ordemId <= 0 || $solicitacaoId <= 0) {
            $this->json(false, 'Dados inválidos.');
            return;
        }
        try {
            $ok = $this->m->vincularSolicitacao($ordemId, $solicitacaoId, $usuario['id']);
            $this->json($ok, $ok ? 'Solicitação vinculada com sucesso.' : 'Erro ao vincular.');
        } catch (\Exception $e) {
            $this->json(false, 'Erro: ' . $e->getMessage());
        }
    }

    /**
     * RF13: Autorizar ordem de compra (gerente de compras)
     * A OC só pode ser enviado ao fornecedor após autorização
     */
    public function autorizar(): void {
        Auxiliares::exigirPerfil('administrador', 'gerente');
        $id = (int)($_POST['id'] ?? 0);
        $usuario = Auxiliares::usuarioLogado();

        if ($id <= 0) {
            $this->json(false, 'ID da ordem é obrigatório.');
            return;
        }

        $ok = $this->m->autorizar($id, (int)$usuario['id']);
        if ($ok) {
            // Notificar comprador que a OC foi autorizado
            $ordem = $this->m->buscarPorId($id);
            if ($ordem) {
                Notificador::notificarUsuario(
                    (int)$ordem['usuario_id'],
                    'OC autorizado: ' . $ordem['numero'],
                    "A ordem de compra {$ordem['numero']} foi autorizado por {$usuario['nome']}.",
                    'ordem'
                );
            }
            $this->m->registrarHistorico('ordens_compra', $id, ['status' => 'aberto'], ['status' => 'autorizado'], (int)$usuario['id']);
            $this->json(true, 'Ordem de compra autorizado com sucesso.');
        } else {
            $this->json(false, 'Não foi possível autorizar. A ordem precisa estar com status "aberto".');
        }
    }

    public function autorizarLote(): void {
        Auxiliares::exigirPerfil('gerente', 'administrador');
        $usuario = Auxiliares::usuarioLogado();
        $ids = json_decode($_POST['ids'] ?? '[]', true);
        if (!is_array($ids) || empty($ids)) {
            $this->json(false, 'Nenhum item selecionado.'); return;
        }
        $sucesso = 0;
        foreach ($ids as $id) {
            $idInt = (int)$id;
            if ($this->m->autorizar($idInt, (int)$usuario['id'])) {
                $sucesso++;
                $ordem = $this->m->buscarPorId($idInt);
                if ($ordem) {
                    \Config\Notificador::notificarUsuario((int)$ordem['usuario_id'], 'OC autorizado: ' . $ordem['numero'], "A ordem de compra {$ordem['numero']} foi autorizado por {$usuario['nome']}.", 'ordem');
                }
                $this->m->registrarHistorico('ordens_compra', $idInt, ['status' => 'aberto'], ['status' => 'autorizado'], (int)$usuario['id']);
            }
        }
        $this->json(true, "$sucesso itens autorizados com sucesso.");
    }

    /**
     * RF13: Remover autorização (se ainda não foi enviado)
     */
    public function desautorizar(): void {
        Auxiliares::exigirPerfil('administrador', 'gerente');
        $id = (int)($_POST['id'] ?? 0);
        $usuario = Auxiliares::usuarioLogado();

        $ok = $this->m->desautorizar($id);
        if ($ok) {
            $ordem = $this->m->buscarPorId($id);
            if ($ordem) {
                Notificador::notificarUsuario(
                    (int)$ordem['usuario_id'],
                    'Autorização removida: ' . $ordem['numero'],
                    "A autorização da ordem de compra {$ordem['numero']} foi removida. Ela pode ser editada novamente.",
                    'ordem'
                );
            }
            $this->m->registrarHistorico('ordens_compra', $id, ['status' => 'autorizado'], ['status' => 'aberto'], (int)$usuario['id']);
            $this->json(true, 'Autorização removida. A ordem pode ser editada novamente.');
        } else {
            $this->json(false, 'Não foi possível remover a autorização. A ordem precisa estar "autorizado" e não pode ter sido enviado.');
        }
    }

    /**
     * RF13: Enviar ordem de compra ao fornecedor por e-mail
     */
    public function enviar(): void {
        Auxiliares::exigirPerfil('comprador', 'administrador');
        $id = (int)($_POST['id'] ?? 0);
        $usuario = Auxiliares::usuarioLogado();

        $ok = $this->m->enviar($id);
        if ($ok) {
            $ordem = $this->m->buscarPorId($id);
            if ($ordem) {
                // Buscar e-mail do fornecedor
                $bd = \Config\BancoDados::obterInstancia()->obterConexao();
                $qF = $bd->prepare("SELECT email, razao_social FROM fornecedores WHERE id = :fid");
                $qF->execute([':fid' => $ordem['fornecedor_id']]);
                $fornecedor = $qF->fetch();

                if ($fornecedor && !empty($fornecedor['email'])) {
                    $tokenOC = $ordem['token'];
                    if (empty($tokenOC)) {
                        $tokenOC = bin2hex(random_bytes(32));
                        $bd->prepare("UPDATE ordens_compra SET token = :token WHERE id = :id")->execute([':token' => $tokenOC, ':id' => $ordem['id']]);
                    }
                    $revisarUrl = base_url('login/fornecedor/ordem?token=' . $tokenOC);

                    $assunto = "Ordem de Compra {$ordem['numero']} - SCOPi";
                    $mensagem = "Prezado(a) {$fornecedor['razao_social']},\n\n";
                    $mensagem .= "Segue a ordem de compra {$ordem['numero']}.\n\n";
                    $mensagem .= "Valor total: R$ " . number_format((float)$ordem['valor_total'], 2, ',', '.') . "\n";
                    $mensagem .= "Prazo de entrega: {$ordem['prazo_entrega']}\n\n";
                    $mensagem .= "Para revisar os detalhes e confirmar a aprovação/envio dos produtos, acesse o link abaixo:\n";
                    $mensagem .= "{$revisarUrl}\n\n";
                    $mensagem .= "Atenciosamente,\nSCOPi";

                    Notificador::enviarEmail($fornecedor['email'], $assunto, $mensagem);
                }

                Notificador::notificarUsuario(
                    (int)$ordem['usuario_id'],
                    'OC enviado: ' . $ordem['numero'],
                    "A ordem de compra {$ordem['numero']} foi enviado ao fornecedor.",
                    'ordem'
                );
            }
            $this->m->registrarHistorico('ordens_compra', $id, ['status' => 'autorizado'], ['status' => 'enviado'], (int)$usuario['id']);
            $this->json(true, 'Ordem de compra enviado ao fornecedor com sucesso.');
        } else {
            $this->json(false, 'Não foi possível enviar. A ordem precisa estar "autorizado".');
        }
    }

    /**
     * RF13: Cancelar item individual da ordem de compra
     */
    public function cancelarItem(): void {
        Auxiliares::exigirPerfil('comprador', 'administrador');
        $itemId = (int)($_POST['item_id'] ?? 0);
        $usuario = Auxiliares::usuarioLogado();

        if ($itemId <= 0) {
            $this->json(false, 'ID do item é obrigatório.');
            return;
        }

        $ok = $this->m->cancelarItem($itemId);
        if ($ok) {
            $this->m->registrarHistorico('ordem_compra_itens', $itemId, ['status_item' => 'pendente'], ['status_item' => 'cancelado'], (int)$usuario['id']);
            $this->json(true, 'Item cancelado com sucesso.');
        } else {
            $this->json(false, 'Não foi possível cancelar o item.');
        }
    }

    /**
     * RF13/RF10: Criar OC diretamente de solicitação autorizado (sem cotação prévia)
     */
    public function criarDeSolicitacao(): void {
        Auxiliares::exigirPerfil('comprador', 'administrador');
        $usuario = Auxiliares::usuarioLogado();
        $solicitacaoId = (int)($_POST['solicitacao_id'] ?? 0);
        $fornecedorId = (int)($_POST['fornecedor_id'] ?? 0);

        if ($solicitacaoId <= 0 || $fornecedorId <= 0) {
            $this->json(false, 'Informe a solicitação e o fornecedor.');
            return;
        }

        $bd = \Config\BancoDados::obterInstancia()->obterConexao();

        // Verificar se a solicitação está autorizado
        $qSol = $bd->prepare("SELECT * FROM solicitacoes WHERE id = :id AND status = 'autorizado'");
        $qSol->execute([':id' => $solicitacaoId]);
        $sol = $qSol->fetch();

        if (!$sol) {
            $this->json(false, 'A solicitação precisa estar autorizado.');
            return;
        }

        try {
            $bd->beginTransaction();

            // Gerar OC
            $numero = 'OC-' . date('Ymd') . '-' . rand(1000, 9999);
            $qOC = $bd->prepare("
                INSERT INTO ordens_compra (numero, solicitacao_id, fornecedor_id, usuario_id, status, emitido_em, criado_em)
                VALUES (:num, :sid, :fid, :uid, 'aberto', CURDATE(), NOW())
            ");
            $qOC->execute([
                ':num' => $numero,
                ':sid' => $solicitacaoId,
                ':fid' => $fornecedorId,
                ':uid' => (int)$usuario['id']
            ]);
            $ordemId = (int)$bd->lastInsertId();

            // Copiar itens da solicitação para a OC (preço unitário zerado — comprador preenche depois)
            $qItens = $bd->prepare("SELECT produto_id, quantidade FROM solicitacao_itens WHERE solicitacao_id = :sid");
            $qItens->execute([':sid' => $solicitacaoId]);
            $itens = $qItens->fetchAll();

            foreach ($itens as $item) {
                $bd->prepare("
                    INSERT INTO ordem_compra_itens (ordem_id, produto_id, quantidade, preco_unitario)
                    VALUES (:oid, :pid, :qtd, 0.00)
                ")->execute([
                    ':oid' => $ordemId,
                    ':pid' => $item['produto_id'],
                    ':qtd' => $item['quantidade']
                ]);
            }

            // Atualizar status da solicitação
            $bd->prepare("UPDATE solicitacoes SET status = 'em_cotacao', atualizado_em = NOW() WHERE id = :sid")
               ->execute([':sid' => $solicitacaoId]);

            $this->m->registrarHistorico('ordens_compra', $ordemId, [], ['numero' => $numero, 'status' => 'aberto'], (int)$usuario['id']);

            $bd->commit();
            $this->json(true, "Ordem de compra {$numero} criada com sucesso a partir da solicitação. Os preços unitários devem ser preenchidos manualmente.", ['id' => $ordemId]);
        } catch (\Exception $e) {
            if ($bd->inTransaction()) {
                $bd->rollBack();
            }
            $this->json(false, 'Erro ao criar ordem de compra: ' . $e->getMessage());
        }
    }

    public function telaAutorizacoes(): void {
        Auxiliares::exigirPerfil('gerente', 'administrador');
        $usuario = Auxiliares::usuarioLogado();
        $filtros = $_GET;
        $filtros['status'] = 'aberto';
        $ordens = $this->m->listarComFiltros($filtros);
        
        $departamentos = (new \Models\DepartamentoModelo())->listarAtivos();
        
        $this->renderizar('ordens/autorizacoes', compact('ordens', 'filtros', 'departamentos'));
    }

    public function exportar(): void {
        Auxiliares::exigirPerfil('comprador', 'administrador', 'gerente');
        $filtros = $_GET;
        $ordens = $this->m->listarComFiltros($filtros);

        $cabecalhos = ['ID', 'Número', 'Fornecedor', 'Valor Total', 'Status', 'Emitido Em'];
        $dadosCsv = [];
        foreach ($ordens as $o) {
            $dadosCsv[] = [
                $o['id'],
                $o['numero'],
                $o['nome_fornecedor'] ?? '-',
                number_format($o['valor_total'] ?? 0, 2, ',', '.'),
                ucfirst(str_replace('_', ' ', $o['status'])),
                $o['emitido_em'] ? date('d/m/Y', strtotime($o['emitido_em'])) : '-'
            ];
        }

        Auxiliares::gerarCSV('ordens_compra', $cabecalhos, $dadosCsv);
    }

    public function excluir_item(): void {
        Auxiliares::exigirAutenticacao();
        $usuario = Auxiliares::usuarioLogado();
        $id = (int)($_POST['id']??0);
        $ok = $this->m->excluirItem($id, $usuario['id']);
        $this->json($ok, $ok ? 'Item removido com sucesso.' : 'Falha ao remover item. A ordem pode não estar aberto.');
    }

    public function imprimir(): void {
        Auxiliares::exigirAutenticacao();
        $id = (int)($_GET['id'] ?? 0);
        $ordem = $this->m->buscarPorId($id);
        
        if (!$ordem) {
            die('Ordem de Compra não encontrada.');
        }

        $ordem['itens'] = $this->m->buscarItens($id);
        
        // Renderizar a view sem o layout padrão (header/footer) para impressão
        $this->renderizarSemLayout('ordens/imprimir', compact('ordem'));
    }
}
