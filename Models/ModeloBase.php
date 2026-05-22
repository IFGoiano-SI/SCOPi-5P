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
        $this->bd->prepare("\n            INSERT INTO historico_alteracoes (tabela, registro_id, dados_anteriores, dados_novos, usuario_id, criado_em)\n            VALUES (:tabela, :registro_id, :anterior, :novo, :usuario_id, NOW())\n        ")->execute([
            ':tabela'      => $tabela,
            ':registro_id' => $registroId,
            ':anterior'    => json_encode($anterior),
            ':novo'        => json_encode($novo),
            ':usuario_id'  => $usuarioId,
        ]);
    }
}
