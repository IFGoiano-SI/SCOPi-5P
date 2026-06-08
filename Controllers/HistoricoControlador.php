<?php
namespace Controllers;

use Models\HistoricoModelo;
use Config\Auxiliares;

class HistoricoControlador extends BaseController {

    private HistoricoModelo $historicoModelo;

    public function __construct() {
        parent::__construct();
        $this->historicoModelo = new HistoricoModelo();
    }

    /**
     * Retorna os dados de histórico via AJAX (JSON)
     */
    public function dados() {
        Auxiliares::exigirAutenticacao();
        
        $entidade = $_GET['entidade'] ?? '';
        $entidade_id = (int)($_GET['entidade_id'] ?? 0);

        if (empty($entidade) || empty($entidade_id)) {
            $this->json(false, 'Parâmetros inválidos.');
        }

        $historico = $this->historicoModelo->buscarHistorico($entidade, $entidade_id);

        // Formatar as datas para exibição
        foreach ($historico as &$h) {
            $h['data_hora_formatada'] = date('d/m/Y H:i:s', strtotime($h['data_hora']));
            
            // Tratamento de ação amigável
            $acao = ucfirst($h['evento'] ?? 'Atualização');
            if ($acao === 'Criação') $acao = 'Cadastro';
            
            $h['acao'] = $acao;
            $h['detalhes_html'] = nl2br(htmlspecialchars($h['detalhes'] ?? 'Sem detalhes adicionais.'));
        }

        $this->json(true, 'Histórico carregado com sucesso', $historico);
    }
}
