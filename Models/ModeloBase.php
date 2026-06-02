<?php
namespace Models;

use Config\BancoDados;

abstract class ModeloBase {

    protected \PDO $bd;
    protected string $tabela = '';

    public function __construct() {
        $this->bd = BancoDados::obterInstancia()->obterConexao();
    }

    public function buscarPorId(int $id): ?array {
        $q = $this->bd->prepare("SELECT * FROM {$this->tabela} WHERE id = :id LIMIT 1");
        $q->execute([':id' => $id]);
        return $q->fetch() ?: null;
    }

    public function inativar(int $id): bool {
        return $this->bd->prepare(
            "UPDATE {$this->tabela} SET situacao = 'inativo', atualizado_em = NOW() WHERE id = :id"
        )->execute([':id' => $id]);
    }

    public function reativar(int $id): bool {
        return $this->bd->prepare(
            "UPDATE {$this->tabela} SET situacao = 'ativo', atualizado_em = NOW() WHERE id = :id"
        )->execute([':id' => $id]);
    }

    protected function registrarHistorico(string $tabela, int $registroId, array $anterior, array $novo, int $usuarioId): void {
        $evento = empty($anterior) ? 'criação' : 'edição';
        $detalhes = [];
        if (!empty($anterior)) {
            foreach ($novo as $campo => $valor) {
                if (isset($anterior[$campo]) && $anterior[$campo] != $valor) {
                    $detalhes[] = "Campo '$campo' alterado de '{$anterior[$campo]}' para '$valor'";
                }
            }
        }
        $detalhesStr = empty($detalhes) ? 'Sem detalhes' : implode('; ', $detalhes);
        
        $this->bd->prepare("
            INSERT INTO historico_cadastros (entidade, entidade_id, usuario_id, evento, detalhes, data_hora)
            VALUES (:entidade, :entidade_id, :usuario_id, :evento, :detalhes, NOW())
        ")->execute([
            ':entidade'      => $tabela,
            ':entidade_id' => $registroId,
            ':usuario_id'  => $usuarioId,
            ':evento'      => $evento,
            ':detalhes'    => $detalhesStr
        ]);
    }
}
