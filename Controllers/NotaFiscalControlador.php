<?php
namespace Controllers;

use Config\Auxiliares;
use Models\NotaFiscalModelo;

class NotaFiscalControlador extends BaseController {
    private NotaFiscalModelo $m;
    public function __construct() { $this->m = new NotaFiscalModelo(); }
    public function listar(): void { Auxiliares::exigirPerfil('contabilidade','comprador','administrador'); $filtros=$_GET; $notas=$this->m->listarComFiltros($filtros); $this->renderizar('notas/notas',compact('notas','filtros')); }
    public function dados(): void { Auxiliares::exigirAutenticacao(); $r=$this->m->buscarPorId((int)($_GET['id']??0)); $r?$this->json(true,'',$r):$this->json(false,'Não encontrado.'); }
    public function salvar(): void { Auxiliares::exigirPerfil('contabilidade','administrador'); $this->json(true,'Nota salva.'); }
    public function importar(): void { Auxiliares::exigirPerfil('contabilidade','administrador'); $this->json(true,'Importação processada.'); }
}
