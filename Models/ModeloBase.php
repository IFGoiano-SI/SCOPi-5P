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

    public function inativar(int $id, ?int $usuarioId = null): bool {
        $ok = $this->bd->prepare(
            "UPDATE {$this->tabela} SET situacao = 'inativo', atualizado_em = NOW() WHERE id = :id"
        )->execute([':id' => $id]);

        if ($ok && $usuarioId) {
            $this->registrarHistorico($this->tabela, $id, ['situacao' => 'ativo'], ['situacao' => 'inativo'], $usuarioId);
        }

        return $ok;
    }

    public function reativar(int $id, ?int $usuarioId = null): bool {
        $ok = $this->bd->prepare(
            "UPDATE {$this->tabela} SET situacao = 'ativo', atualizado_em = NOW() WHERE id = :id"
        )->execute([':id' => $id]);

        if ($ok && $usuarioId) {
            $this->registrarHistorico($this->tabela, $id, ['situacao' => 'inativo'], ['situacao' => 'ativo'], $usuarioId);
        }

        return $ok;
    }

    /**
     * Registra uma ação personalizada no histórico de auditoria.
     */
    public function registrarAcaoPersonalizada(string $tabela, int $registroId, int $usuarioId, string $evento, string $detalhes = ''): void {
        $q = $this->bd->prepare("
            INSERT INTO historico_cadastros (entidade, entidade_id, usuario_id, evento, detalhes, data_hora)
            VALUES (:entidade, :entidade_id, :usuario_id, :evento, :detalhes, NOW())
        ");
        $q->execute([
            ':entidade' => $tabela,
            ':entidade_id' => $registroId,
            ':usuario_id' => $usuarioId,
            ':evento' => $evento,
            ':detalhes' => $detalhes
        ]);
    }

    public function registrarHistorico(string $tabela, int $registroId, array $anterior, array $novo, int $usuarioId, ?string $eventoPersonalizado = null): void {
        if ($eventoPersonalizado) {
            $evento = $eventoPersonalizado;
        } else {
            $evento = empty($anterior) ? 'criação' : 'edição';
        }
        
        $detalhes = [];
        if (!empty($anterior)) {
            foreach ($novo as $campo => $valor) {
                if (isset($anterior[$campo]) && $anterior[$campo] != $valor) {
                    $detalhes[] = "Campo '$campo' alterado de '{$anterior[$campo]}' para '$valor'";
                }
            }
        } else if ($eventoPersonalizado) {
            foreach ($novo as $campo => $valor) {
                // Ignore complex arrays like 'itens' for simple log string
                if (!is_array($valor)) {
                    $detalhes[] = ucfirst($campo) . " definido como '$valor'";
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

    protected static function formatarStatus(string $status): string {
        $mapa = [
            'aberto' => 'Aberto',
            'aberto' => 'Aberto',
            'autorizado' => 'Autorizado',
            'autorizado' => 'Autorizado',
            'em_cotacao' => 'Em Cotação',
            'fechada' => 'Fechado',
            'fechado' => 'Fechado',
            'enviado' => 'Enviado',
            'enviado' => 'Enviado',
            'parcialmente_atendido' => 'Parcialmente Atendido',
            'parcialmente_atendido' => 'Parcialmente Atendido',
            'concluido' => 'Concluído',
            'concluido' => 'Concluído',
            'cancelado' => 'Cancelado',
            'cancelado' => 'Cancelado',
            'recusada' => 'Recusado',
            'recusado' => 'Recusado',
            'registrada' => 'Registrado',
            'vinculada' => 'Vinculado'
        ];
        return $mapa[strtolower($status)] ?? ucfirst(str_replace('_', ' ', $status));
    }
}
