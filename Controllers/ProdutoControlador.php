<?php
namespace Controllers;

use Config\Auxiliares;
use Models\ProdutoModelo;

class ProdutoControlador extends BaseController {
    private ProdutoModelo $m;
    public function __construct() { $this->m = new ProdutoModelo(); }
    public function listar(): void {
        Auxiliares::exigirAutenticacao();
        $filtros = $_GET;
        $produtos = $this->m->listarComFiltros($filtros);
        $categorias = (new \Models\CategoriaModelo())->listarAtivas();
        $this->renderizar('cadastros/produtos', compact('produtos', 'filtros', 'categorias'));
    }
    public function dados(): void { Auxiliares::exigirAutenticacao(); $r=$this->m->buscarPorId((int)($_GET['id']??0)); $r?$this->json(true,'',$r):$this->json(false,'Não encontrado.'); }
    public function salvar(): void {
        Auxiliares::exigirPerfil('administrador','cadastrador');
        $resp=Auxiliares::usuarioLogado(); $id=(int)($_POST['id']??0);
        $dados=[
            'nome'=>trim($_POST['nome']??''),
            'descricao'=>trim($_POST['descricao']??''),
            'categoria_id'=>!empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null
        ];
        if(empty($dados['nome'])){$this->json(false,'Nome obrigatório.');return;}
        if($id===0){$this->json(true,'Produto cadastrado.',['id'=>$this->m->cadastrar($dados, $resp['id'])]);} else{$ok=$this->m->atualizar($id,$dados,$resp['id']);$this->json($ok,$ok?'Atualizado.':'Erro.');}
    }
    public function inativar(): void { Auxiliares::exigirPerfil('administrador','cadastrador'); $ok=$this->m->inativar((int)($_POST['id']??0)); $this->json($ok,$ok?'Inativado.':'Erro.'); }
    public function reativar(): void { Auxiliares::exigirPerfil('administrador','cadastrador'); $ok=$this->m->reativar((int)($_POST['id']??0)); $this->json($ok,$ok?'Reativado.':'Erro.'); }
    public function ativos(): void {
        Auxiliares::exigirAutenticacao();
        $this->json(true, '', $this->m->listarAtivos());
    }

    public function consultarCodigo(): void {
        Auxiliares::exigirAutenticacao();
        $codigo = trim($_GET['codigo'] ?? '');
        if ($codigo === '') {
            $this->json(false, 'Código não informado.'); return;
        }
        $bd = \Config\BancoDados::obterInstancia()->obterConexao();
        $q = $bd->prepare("SELECT p.id, p.nome, c.nome as categoria_nome FROM produtos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.codigo = :codigo AND p.situacao = 'ativo'");
        $q->execute([':codigo' => $codigo]);
        $res = $q->fetch(\PDO::FETCH_ASSOC);
        if ($res) {
            $this->json(true, '', $res);
        } else {
            $this->json(false, 'Produto não encontrado ou inativo.');
        }
    }

    public function exportar(): void {
        Auxiliares::exigirAutenticacao();
        $filtros = $_GET;
        $produtos = $this->m->listarComFiltros($filtros);

        $cabecalhos = ['ID', 'Código', 'Nome', 'Descrição', 'Categoria', 'Situação', 'Criado Em'];
        $dadosCsv = [];
        foreach ($produtos as $p) {
            $dadosCsv[] = [
                $p['id'],
                $p['codigo'],
                $p['nome'],
                $p['descricao'],
                $p['categoria_nome'] ?? '-',
                ucfirst($p['situacao']),
                date('d/m/Y H:i', strtotime($p['criado_em']))
            ];
        }

        Auxiliares::gerarCSV('produtos', $cabecalhos, $dadosCsv);
    }
}
