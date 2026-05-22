<?php
namespace Controllers;

use Config\Auxiliares;
use Models\SolicitacaoModelo;
use Models\CotacaoModelo;
use Models\OrdemCompraModelo;
use Models\NotaFiscalModelo;

class InicioControlador extends BaseController {

    public function exibir(): void {
        Auxiliares::exigirAutenticacao();

        $solicitacaoModelo  = new SolicitacaoModelo();
        $cotacaoModelo      = new CotacaoModelo();
        $ordemModelo        = new OrdemCompraModelo();
        $notaModelo         = new NotaFiscalModelo();

        $dadosSolicitacoes = $solicitacaoModelo->contarPorStatus();
        $dadosCotacoes = $cotacaoModelo->contarPorMes(6);
        $dadosOrdens = $ordemModelo->contarPorStatus();
        $dadosNotas = $notaModelo->totalPorMes(6);

        $this->renderizar('home/inicio', [
            'dadosSolicitacoes' => $dadosSolicitacoes,
            'dadosCotacoes'     => $dadosCotacoes,
            'dadosOrdens'       => $dadosOrdens,
            'dadosNotas'        => $dadosNotas,
        ]);
    }
}
