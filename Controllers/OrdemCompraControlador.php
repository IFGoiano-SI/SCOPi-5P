<?php
namespace Controllers;

use Config\Auxiliares;
use Models\OrdemCompraModelo;

class OrdemCompraControlador extends BaseController {
    private OrdemCompraModelo $m;
    
    public function __construct() {
        $this->m = new OrdemCompraModelo();
    }
    
    public function listar(): void {
        Auxiliares::exigirPerfil('comprador','administrador','gerente');
        $filtros = $_GET;
        $ordens = $this->m->listarComFiltros($filtros);
        
        $bd = \Config\BancoDados::obterInstancia()->obterConexao();
        
        $qForn = $bd->query("SELECT id, razao_social, cnpj FROM fornecedores WHERE situacao = 'ativo' ORDER BY razao_social");
        $fornecedores = $qForn->fetchAll();
        
        $qCP = $bd->query("SELECT id, descricao FROM condicoes_pagamento ORDER BY descricao");
        $condicoesPagamento = $qCP->fetchAll();
        
        $this->renderizar('ordens/ordens', compact('ordens', 'filtros', 'fornecedores', 'condicoesPagamento'));
    }
    
    public function dados(): void {
        Auxiliares::exigirAutenticacao();
        $r = $this->m->buscarPorId((int)($_GET['id']??0));
        $r ? $this->json(true, '', $r) : $this->json(false, 'Não encontrado.');
    }
    
    public function salvar(): void {
        Auxiliares::exigirPerfil('comprador','administrador');
        $dados = $_POST;
        
        if (empty($dados['fornecedor_id'])) {
            $this->json(false, 'Fornecedor é obrigatório.');
            return;
        }
        
        try {
            $usuario = Auxiliares::usuarioLogado();
            $usuarioId = (int)($usuario['id'] ?? 0);
            
            $id = $this->m->salvar($dados, $usuarioId);
            $this->json(true, 'Ordem de compra salva com sucesso!', ['id' => $id]);
        } catch (\Exception $e) {
            $this->json(false, 'Erro ao salvar ordem de compra: ' . $e->getMessage());
        }
    }
    
    public function autorizar(): void {
        Auxiliares::exigirPerfil('administrador','gerente');
        $this->json(true,'Ordem autorizada.');
    }
}
