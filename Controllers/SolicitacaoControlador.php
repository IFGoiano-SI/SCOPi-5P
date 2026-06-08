<?php
namespace Controllers;

use Config\Auxiliares;
use Models\SolicitacaoModelo;

class SolicitacaoControlador extends BaseController {
    private SolicitacaoModelo $m;
    public function __construct() { $this->m = new SolicitacaoModelo(); }
    public function listar(): void {
        Auxiliares::exigirAutenticacao(); $usuario=Auxiliares::usuarioLogado(); $filtros=$_GET;
        $depId=in_array($usuario['perfil'],['administrador','comprador'])?null:$usuario['departamento_id'];
        $solicitacoes=$this->m->listarComFiltros($filtros,$depId);

        if (isset($_GET['busca_modal'])) {
            $this->json(true, '', $solicitacoes);
            return;
        }

        $produtosAtivos = (new \Models\ProdutoModelo())->listarAtivos();

        // Adicionar nome do departamento do usuário
        $departamentosMap = [
            1 => 'Administração',
            2 => 'Compras',
            3 => 'Financeiro',
            4 => 'Contabilidade',
            5 => 'Operações'
        ];

        $usuario['departamento_nome'] = '';
        if (!empty($usuario['departamento_id'])) {
            $deptId = (int)$usuario['departamento_id'];
            // Primeiro tenta buscar do banco
            $bd = \Config\BancoDados::obterInstancia()->obterConexao();
            $q = $bd->prepare("SELECT nome FROM departamentos WHERE id = ?");
            $q->execute([$deptId]);
            $nome = $q->fetchColumn();

            // Se não encontrou, usa o mapa como fallback
            $usuario['departamento_nome'] = $nome ?: ($departamentosMap[$deptId] ?? '');
        }

        $this->renderizar('solicitacoes/solicitacoes',compact('solicitacoes','filtros','produtosAtivos','usuario'));
    }
    public function dados(): void { Auxiliares::exigirAutenticacao(); $r=$this->m->buscarComItens((int)($_GET['id']??0)); $r?$this->json(true,'',$r):$this->json(false,'Não encontrado.'); }
    public function salvar(): void {
        Auxiliares::exigirAutenticacao();
        $usuario = Auxiliares::usuarioLogado();
        $id = (int)($_POST['id']??0);
        $dados = [
            'justificativa' => $_POST['justificativa']??'',
            'itens' => json_decode($_POST['itens_json']??'[]', true)
        ];
        if ($id === 0) {
            $novoId = $this->m->cadastrar($dados, $usuario['id'], $usuario['departamento_id']);

            // RF09: Notificar gerente do departamento sobre nova solicitação
            $bd = \Config\BancoDados::obterInstancia()->obterConexao();
            $qDep = $bd->prepare("SELECT gerente_id FROM departamentos WHERE id = :did");
            $qDep->execute([':did' => $usuario['departamento_id']]);
            $gerenteId = $qDep->fetchColumn();
            if ($gerenteId) {
                \Config\Notificador::notificarUsuario(
                    (int)$gerenteId,
                    "Nova Solicitação de Compra",
                    "O usuário {$usuario['nome']} registrou uma nova solicitação de compra que aguarda sua autorização.",
                    'solicitacao'
                );
            }

            $this->json(true, 'Solicitação registrada.', ['id' => $novoId]);
        } else {
            $ok = $this->m->atualizar($id, $dados, $usuario['id']);
            $this->json($ok, $ok ? 'Solicitação atualizada.' : 'Erro ao atualizar.');
        }
    }
    public function autorizar(): void {
        Auxiliares::exigirPerfil('gerente','administrador');
        $usuario = Auxiliares::usuarioLogado();
        $id = (int)($_POST['id']??0);
        $ok = $this->m->autorizar($id, $usuario['id']);
        if ($ok) {
            // RF09: Notificar solicitante que a solicitação foi autorizado
            $sol = $this->m->buscarComItens($id);
            if ($sol) {
                \Config\Notificador::notificarUsuario(
                    (int)$sol['usuario_id'],
                    "Solicitação Autorizada",
                    "Sua solicitação {$sol['numero']} foi autorizado por {$usuario['nome']}.",
                    'solicitacao'
                );
            }
        }
        $this->json($ok, $ok ? 'Autorizada.' : 'Erro ao autorizar.');
    }
    public function autorizarLote(): void {
        Auxiliares::exigirPerfil('gerente','administrador');
        $usuario = Auxiliares::usuarioLogado();
        $ids = json_decode($_POST['ids'] ?? '[]', true);
        if (!is_array($ids) || empty($ids)) {
            $this->json(false, 'Nenhum item selecionado.'); return;
        }
        $sucesso = 0;
        foreach ($ids as $id) {
            $idInt = (int)$id;
            if ($this->m->autorizar($idInt, $usuario['id'])) {
                $sucesso++;
                $sol = $this->m->buscarComItens($idInt);
                if ($sol) {
                    \Config\Notificador::notificarUsuario((int)$sol['usuario_id'], "Solicitação Autorizada", "Sua solicitação {$sol['numero']} foi autorizado por {$usuario['nome']}.", 'solicitacao');
                }
            }
        }
        $this->json(true, "$sucesso itens autorizados com sucesso.");
    }
    public function desautorizar(): void {
        Auxiliares::exigirPerfil('gerente','administrador');
        $usuario = Auxiliares::usuarioLogado();
        $ok = $this->m->desautorizar((int)($_POST['id']??0), $usuario['id']);
        $this->json($ok, $ok ? 'Autorização retirada.' : 'Erro ao retirar autorização (pode haver cotação ativa).');
    }

    public function excluir_item(): void {
        Auxiliares::exigirAutenticacao();
        $usuario = Auxiliares::usuarioLogado();
        $id = (int)($_POST['id']??0);
        $ok = $this->m->excluirItem($id, $usuario['id']);
        $this->json($ok, $ok ? 'Item removido com sucesso.' : 'Falha ao remover item. A solicitação pode não estar em aberto.');
    }

    public function cancelar(): void {
        Auxiliares::exigirAutenticacao();
        $usuario = Auxiliares::usuarioLogado();
        $id = (int)($_POST['id'] ?? 0);

        // RF09: Buscar a solicitação para validar permissões
        $sol = $this->m->buscarComItens($id);
        if (!$sol) {
            $this->json(false, 'Solicitação não encontrada.');
            return;
        }

        // RF09: Solicitações em aberto podem ser cancelados por usuários do mesmo departamento
        if ($sol['status'] === 'aberto') {
            if ((int)$sol['departamento_id'] !== (int)$usuario['departamento_id']
                && !in_array($usuario['perfil'], ['administrador'])) {
                $this->json(false, 'Você só pode cancelar solicitações do seu departamento.');
                return;
            }
        }
        // RF09: Solicitações autorizados só podem ser cancelados pelo gerente responsável
        elseif ($sol['status'] === 'autorizado') {
            if (!in_array($usuario['perfil'], ['gerente', 'administrador'])) {
                $this->json(false, 'Apenas o gerente ou administrador pode cancelar uma solicitação autorizado.');
                return;
            }
        } else {
            $this->json(false, 'Não é possível cancelar uma solicitação com status "' . $sol['status'] . '".');
            return;
        }

        $ok = $this->m->cancelar($id, $usuario['id']);
        $this->json($ok, $ok ? 'Cancelada.' : 'Erro ao cancelar (pode haver cotação ativa vinculada).');
    }
    public function listarAutorizadas(): void {
        Auxiliares::exigirAutenticacao();
        $this->json(true, '', $this->m->listarAutorizadas());
    }

    public function telaAutorizacoes(): void {
        Auxiliares::exigirPerfil('gerente', 'administrador');
        $usuario = Auxiliares::usuarioLogado();
        $filtros = $_GET;
        $filtros['status'] = 'aberto'; // Força listar apenas em aberto
        $depId = in_array($usuario['perfil'], ['administrador']) ? null : $usuario['departamento_id'];
        if ($usuario['perfil'] === 'gerente') {
            $filtros['departamento_id'] = $usuario['departamento_id'];
        }
        $solicitacoes = $this->m->listarComFiltros($filtros, $depId);
        $departamentos = (new \Models\DepartamentoModelo())->listarAtivos();
        $this->renderizar('solicitacoes/autorizacoes', compact('solicitacoes', 'filtros', 'departamentos'));
    }

    public function exportar(): void {
        Auxiliares::exigirAutenticacao();
        $usuario = Auxiliares::usuarioLogado();
        $filtros = $_GET;
        $depId = in_array($usuario['perfil'], ['administrador', 'comprador']) ? null : $usuario['departamento_id'];
        $solicitacoes = $this->m->listarComFiltros($filtros, $depId);

        $cabecalhos = ['ID', 'Número', 'Solicitante', 'Departamento', 'Qtd Itens', 'Status', 'Criado Em'];
        $dadosCsv = [];
        foreach ($solicitacoes as $s) {
            $dadosCsv[] = [
                $s['id'],
                $s['numero'],
                $s['nome_solicitante'],
                $s['nome_departamento'],
                $s['total_itens'] ?? 0,
                ucfirst(str_replace('_', ' ', $s['status'])),
                date('d/m/Y H:i', strtotime($s['criado_em']))
            ];
        }

        Auxiliares::gerarCSV('solicitacoes', $cabecalhos, $dadosCsv);
    }
}
