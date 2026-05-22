<?php

// Rotas baseadas no SCOPi — adaptadas para o projeto
$routes = [
    '/' => ['AutenticacaoControlador', 'exibirLogin'],
    '/login' => ['AutenticacaoControlador', 'exibirLogin'],
    '/login/entrar' => ['AutenticacaoControlador', 'entrar'],
    '/login/sair' => ['AutenticacaoControlador', 'sair'],
    '/login/fornecedor' => ['AutenticacaoControlador', 'exibirLoginFornecedor'],
    '/login/fornecedor/entrar' => ['AutenticacaoControlador', 'entrarFornecedor'],

    '/home' => ['InicioControlador', 'exibir'],
    '/inicio' => ['InicioControlador', 'exibir'],

    '/usuarios' => ['UsuarioControlador', 'listar'],
    '/usuarios/dados' => ['UsuarioControlador', 'dados'],
    '/usuarios/salvar' => ['UsuarioControlador', 'salvar'],
    '/usuarios/inativar' => ['UsuarioControlador', 'inativar'],
    '/usuarios/reativar' => ['UsuarioControlador', 'reativar'],

    '/departamentos' => ['DepartamentoControlador', 'listar'],
    '/departamentos/dados' => ['DepartamentoControlador', 'dados'],
    '/departamentos/salvar' => ['DepartamentoControlador', 'salvar'],
    '/departamentos/inativar' => ['DepartamentoControlador', 'inativar'],
    '/departamentos/reativar' => ['DepartamentoControlador', 'reativar'],

    '/fornecedores' => ['FornecedorControlador', 'listar'],
    '/fornecedores/dados' => ['FornecedorControlador', 'dados'],
    '/fornecedores/salvar' => ['FornecedorControlador', 'salvar'],
    '/fornecedores/inativar' => ['FornecedorControlador', 'inativar'],
    '/fornecedores/reativar' => ['FornecedorControlador', 'reativar'],
    '/fornecedores/consultar-cnpj' => ['FornecedorControlador', 'consultarCnpj'],

    '/produtos' => ['ProdutoControlador', 'listar'],
    '/produtos/dados' => ['ProdutoControlador', 'dados'],
    '/produtos/salvar' => ['ProdutoControlador', 'salvar'],
    '/produtos/inativar' => ['ProdutoControlador', 'inativar'],
    '/produtos/reativar' => ['ProdutoControlador', 'reativar'],

    '/categorias' => ['CategoriaControlador', 'listar'],
    '/categorias/dados' => ['CategoriaControlador', 'dados'],
    '/categorias/salvar' => ['CategoriaControlador', 'salvar'],
    '/categorias/inativar' => ['CategoriaControlador', 'inativar'],
    '/categorias/reativar' => ['CategoriaControlador', 'reativar'],

    '/condicoes-pagamento' => ['CondicaoPagamentoControlador', 'listar'],
    '/condicoes-pagamento/dados' => ['CondicaoPagamentoControlador', 'dados'],
    '/condicoes-pagamento/salvar' => ['CondicaoPagamentoControlador', 'salvar'],
    '/condicoes-pagamento/inativar' => ['CondicaoPagamentoControlador', 'inativar'],
    '/condicoes-pagamento/reativar' => ['CondicaoPagamentoControlador', 'reativar'],

    '/solicitacoes' => ['SolicitacaoControlador', 'listar'],
    '/solicitacoes/dados' => ['SolicitacaoControlador', 'dados'],
    '/solicitacoes/salvar' => ['SolicitacaoControlador', 'salvar'],
    '/solicitacoes/cancelar' => ['SolicitacaoControlador', 'cancelar'],
    '/solicitacoes/autorizar' => ['SolicitacaoControlador', 'autorizar'],
    '/solicitacoes/recusar' => ['SolicitacaoControlador', 'recusar'],
    '/solicitacoes/desautorizar' => ['SolicitacaoControlador', 'desautorizar'],
    '/solicitacoes/autorizadas' => ['SolicitacaoControlador', 'listarAutorizadas'],

    '/produtos/ativos' => ['ProdutoControlador', 'ativos'],

    '/cotacoes' => ['CotacaoControlador', 'listar'],
    '/cotacoes/dados' => ['CotacaoControlador', 'dados'],
    '/cotacoes/criar' => ['CotacaoControlador', 'criar'],
    '/cotacoes/selecionar-vencedor' => ['CotacaoControlador', 'selecionarVencedor'],

    '/ordens' => ['OrdemCompraControlador', 'listar'],
    '/ordens/dados' => ['OrdemCompraControlador', 'dados'],
    '/ordens/salvar' => ['OrdemCompraControlador', 'salvar'],

    '/notas' => ['NotaFiscalControlador', 'listar'],
    '/notas/dados' => ['NotaFiscalControlador', 'dados'],
    '/notas/salvar' => ['NotaFiscalControlador', 'salvar'],
    '/notas/importar' => ['NotaFiscalControlador', 'importar'],

    '/senha/recuperar' => ['AutenticacaoControlador', 'recuperarSenha'],
    '/senha/redefinir' => ['AutenticacaoControlador', 'redefinirSenha'],
    '/cotacao/responder' => ['CotacaoFornecedorControlador', 'exibir'],
    '/cotacao/responder/salvar' => ['CotacaoFornecedorControlador', 'salvar'],
];

return $routes;


