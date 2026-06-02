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
    '/usuarios/exportar' => ['UsuarioControlador', 'exportar'],
    '/usuarios/consultar-matricula' => ['UsuarioControlador', 'consultarMatricula'],

    '/departamentos' => ['DepartamentoControlador', 'listar'],
    '/departamentos/dados' => ['DepartamentoControlador', 'dados'],
    '/departamentos/salvar' => ['DepartamentoControlador', 'salvar'],
    '/departamentos/inativar' => ['DepartamentoControlador', 'inativar'],
    '/departamentos/reativar' => ['DepartamentoControlador', 'reativar'],
    '/departamentos/exportar' => ['DepartamentoControlador', 'exportar'],
    '/departamentos/consultar-codigo' => ['DepartamentoControlador', 'consultarCodigo'],

    '/fornecedores' => ['FornecedorControlador', 'listar'],
    '/fornecedores/dados' => ['FornecedorControlador', 'dados'],
    '/fornecedores/salvar' => ['FornecedorControlador', 'salvar'],
    '/fornecedores/inativar' => ['FornecedorControlador', 'inativar'],
    '/fornecedores/reativar' => ['FornecedorControlador', 'reativar'],
    '/fornecedores/consultar-cnpj' => ['FornecedorControlador', 'consultarCnpj'],
    '/fornecedores/consultar-codigo' => ['FornecedorControlador', 'consultarCodigo'],
    '/fornecedores/exportar' => ['FornecedorControlador', 'exportar'],

    '/produtos' => ['ProdutoControlador', 'listar'],
    '/produtos/dados' => ['ProdutoControlador', 'dados'],
    '/produtos/salvar' => ['ProdutoControlador', 'salvar'],
    '/produtos/inativar' => ['ProdutoControlador', 'inativar'],
    '/produtos/reativar' => ['ProdutoControlador', 'reativar'],
    '/produtos/exportar' => ['ProdutoControlador', 'exportar'],
    '/produtos/consultar-codigo' => ['ProdutoControlador', 'consultarCodigo'],

    '/categorias' => ['CategoriaControlador', 'listar'],
    '/categorias/dados' => ['CategoriaControlador', 'dados'],
    '/categorias/salvar' => ['CategoriaControlador', 'salvar'],
    '/categorias/inativar' => ['CategoriaControlador', 'inativar'],
    '/categorias/reativar' => ['CategoriaControlador', 'reativar'],
    '/categorias/exportar' => ['CategoriaControlador', 'exportar'],
    '/categorias/consultar-codigo' => ['CategoriaControlador', 'consultarCodigo'],


    '/solicitacoes' => ['SolicitacaoControlador', 'listar'],
    '/solicitacoes/dados' => ['SolicitacaoControlador', 'dados'],
    '/solicitacoes/salvar' => ['SolicitacaoControlador', 'salvar'],
    '/solicitacoes/cancelar' => ['SolicitacaoControlador', 'cancelar'],
    '/solicitacoes/autorizar' => ['SolicitacaoControlador', 'autorizar'],
    '/solicitacoes/recusar' => ['SolicitacaoControlador', 'recusar'],
    '/solicitacoes/desautorizar' => ['SolicitacaoControlador', 'desautorizar'],
    '/solicitacoes/autorizacoes' => ['SolicitacaoControlador', 'telaAutorizacoes'],
    '/solicitacoes/autorizar-lote' => ['SolicitacaoControlador', 'autorizarLote'],
    '/solicitacoes/autorizadas' => ['SolicitacaoControlador', 'listarAutorizadas'],
    '/solicitacoes/exportar' => ['SolicitacaoControlador', 'exportar'],

    '/produtos/ativos' => ['ProdutoControlador', 'ativos'],
    '/produtos/consultar-codigo' => ['ProdutoControlador', 'consultarCodigo'],

    '/cotacoes' => ['CotacaoControlador', 'listar'],
    '/cotacoes/dados' => ['CotacaoControlador', 'dados'],
    '/cotacoes/criar' => ['CotacaoControlador', 'criar'],
    '/cotacoes/selecionar-vencedor' => ['CotacaoControlador', 'selecionarVencedor'],
    '/cotacoes/exportar' => ['CotacaoControlador', 'exportar'],
    '/cotacoes/imprimir' => ['CotacaoControlador', 'imprimir'],

    '/ordens' => ['OrdemCompraControlador', 'listar'],
    '/ordens/dados' => ['OrdemCompraControlador', 'dados'],
    '/ordens/salvar' => ['OrdemCompraControlador', 'salvar'],
    '/ordens/autorizar' => ['OrdemCompraControlador', 'autorizar'],
    '/ordens/desautorizar' => ['OrdemCompraControlador', 'desautorizar'],
    '/ordens/autorizacoes' => ['OrdemCompraControlador', 'telaAutorizacoes'],
    '/ordens/autorizar-lote' => ['OrdemCompraControlador', 'autorizarLote'],
    '/ordens/enviar' => ['OrdemCompraControlador', 'enviar'],
    '/ordens/cancelar-item' => ['OrdemCompraControlador', 'cancelarItem'],
    '/ordens/criar-de-solicitacao' => ['OrdemCompraControlador', 'criarDeSolicitacao'],
    '/ordens/exportar' => ['OrdemCompraControlador', 'exportar'],
    '/ordens/imprimir' => ['OrdemCompraControlador', 'imprimir'],

    '/notas' => ['NotaFiscalControlador', 'listar'],
    '/notas/dados' => ['NotaFiscalControlador', 'dados'],
    '/notas/salvar' => ['NotaFiscalControlador', 'salvar'],
    '/notas/importar' => ['NotaFiscalControlador', 'importar'],
    '/notas/vincular' => ['NotaFiscalControlador', 'vincular'],
    '/notas/exportar' => ['NotaFiscalControlador', 'exportar'],
    '/notas/imprimir' => ['NotaFiscalControlador', 'imprimir'],

    '/notificacoes' => ['NotificacaoControlador', 'listar'],
    '/notificacoes/dados' => ['NotificacaoControlador', 'dados'],
    '/notificacoes/marcar-lida' => ['NotificacaoControlador', 'marcarLida'],
    '/notificacoes/marcar-todas' => ['NotificacaoControlador', 'marcarTodasLidas'],
    '/notificacoes/excluir' => ['NotificacaoControlador', 'excluir'],
    '/notificacoes/contar' => ['NotificacaoControlador', 'contarNaoLidas'],

    '/senha/recuperar' => ['AutenticacaoControlador', 'recuperarSenha'],
    '/senha/redefinir' => ['AutenticacaoControlador', 'redefinirSenha'],
    '/cotacao/responder' => ['CotacaoFornecedorControlador', 'exibir'],
    '/cotacao/responder/salvar' => ['CotacaoFornecedorControlador', 'salvar'],
    '/cotacoes/reenviar-token' => ['CotacaoControlador', 'reenviarToken'],
];

return $routes;


