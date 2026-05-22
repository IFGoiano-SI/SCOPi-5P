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
        $produtosAtivos = (new \Models\ProdutoModelo())->listarAtivos();
        $this->renderizar('solicitacoes/solicitacoes',compact('solicitacoes','filtros','produtosAtivos'));
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
            $this->json(true, 'Solicitação registrada.', ['id' => $novoId]);
        } else {
            $ok = $this->m->atualizar($id, $dados, $usuario['id']);
            $this->json($ok, $ok ? 'Solicitação atualizada.' : 'Erro ao atualizar.');
        }
    }
    public function autorizar(): void {
        Auxiliares::exigirPerfil('gerente','administrador');
        $usuario = Auxiliares::usuarioLogado();
        $ok = $this->m->autorizar((int)($_POST['id']??0), $usuario['id']);
        $this->json($ok, $ok ? 'Autorizada.' : 'Erro ao autorizar.');
    }
    public function recusar(): void {
        Auxiliares::exigirPerfil('gerente','administrador');
        $usuario = Auxiliares::usuarioLogado();
        $ok = $this->m->recusar((int)($_POST['id']??0), $usuario['id']);
        $this->json($ok, $ok ? 'Recusada.' : 'Erro ao recusar.');
    }
    public function desautorizar(): void {
        Auxiliares::exigirPerfil('gerente','administrador');
        $usuario = Auxiliares::usuarioLogado();
        $ok = $this->m->desautorizar((int)($_POST['id']??0), $usuario['id']);
        $this->json($ok, $ok ? 'Autorização retirada.' : 'Erro ao retirar autorização (pode haver cotação ativa).');
    }
    public function cancelar(): void {
        Auxiliares::exigirAutenticacao();
        $usuario = Auxiliares::usuarioLogado();
        $ok = $this->m->cancelar((int)($_POST['id']??0), $usuario['id']);
        $this->json($ok, $ok ? 'Cancelada.' : 'Erro ao cancelar.');
    }
    public function listarAutorizadas(): void {
        Auxiliares::exigirAutenticacao();
        $this->json(true, '', $this->m->listarAutorizadas());
    }
}
