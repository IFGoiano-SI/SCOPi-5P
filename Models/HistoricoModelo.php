<?php
namespace Models;

class HistoricoModelo extends ModeloBase {

    public function __construct() {
        parent::__construct();
        $this->tabela = 'historico_cadastros';
    }

    /**
     * Busca o histórico de uma entidade específica
     */
    public function buscarHistorico(string $entidade, int $entidadeId): array {
        $q = $this->bd->prepare("
            SELECT h.*, u.nome AS nome_usuario
            FROM {$this->tabela} h
            LEFT JOIN usuarios u ON h.usuario_id = u.id
            WHERE h.entidade = :entidade AND h.entidade_id = :entidade_id
            ORDER BY h.data_hora DESC, h.id DESC
        ");
        $q->execute([
            ':entidade' => $entidade,
            ':entidade_id' => $entidadeId
        ]);
        return $q->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Registra um evento de inativação/reativação/etc
     */
    public function registrarAcao(string $entidade, int $entidadeId, int $usuarioId, string $evento, string $detalhes = ''): void {
        $q = $this->bd->prepare("
            INSERT INTO {$this->tabela} (entidade, entidade_id, usuario_id, evento, detalhes, data_hora)
            VALUES (:entidade, :entidade_id, :usuario_id, :evento, :detalhes, NOW())
        ");
        $q->execute([
            ':entidade' => $entidade,
            ':entidade_id' => $entidadeId,
            ':usuario_id' => $usuarioId,
            ':evento' => $evento,
            ':detalhes' => $detalhes
        ]);
    }
}
