<?php
namespace Models;

class NotificacaoModelo extends ModeloBase {
    protected string $tabela = 'notificacoes';

    /**
     * Criar uma nova notificação interna (RF15)
     */
    public function criar(int $usuarioId, string $assunto, string $mensagem, string $categoria = 'sistema'): int {
        $q = $this->bd->prepare("
            INSERT INTO notificacoes (usuario_id, assunto, mensagem, categoria, lida, criado_em)
            VALUES (:uid, :assunto, :msg, :cat, 0, NOW())
        ");
        $q->execute([
            ':uid' => $usuarioId,
            ':assunto' => $assunto,
            ':msg' => $mensagem,
            ':cat' => $categoria
        ]);
        return (int)$this->bd->lastInsertId();
    }

    /**
     * Listar notificações de um usuário com filtro opcional por categoria
     */
    public function listarPorUsuario(int $usuarioId, ?string $categoria = null): array {
        $sql = "SELECT * FROM notificacoes WHERE usuario_id = :uid";
        $p = [':uid' => $usuarioId];
        if ($categoria && $categoria !== 'todas') {
            $sql .= " AND categoria = :cat";
            $p[':cat'] = $categoria;
        }
        $sql .= " ORDER BY criado_em DESC LIMIT 100";
        $q = $this->bd->prepare($sql);
        $q->execute($p);
        return $q->fetchAll();
    }

    /**
     * Buscar notificação por ID (com verificação de pertencimento ao usuário)
     */
    public function buscarPorId(int $id, int $usuarioId): ?array {
        $q = $this->bd->prepare("SELECT * FROM notificacoes WHERE id = :id AND usuario_id = :uid LIMIT 1");
        $q->execute([':id' => $id, ':uid' => $usuarioId]);
        return $q->fetch() ?: null;
    }

    /**
     * Contar notificações não lidas
     */
    public function contarNaoLidas(int $usuarioId): int {
        $q = $this->bd->prepare("SELECT COUNT(*) FROM notificacoes WHERE usuario_id = :uid AND lida = 0");
        $q->execute([':uid' => $usuarioId]);
        return (int)$q->fetchColumn();
    }

    /**
     * Marcar uma notificação como lida
     */
    public function marcarLida(int $id, int $usuarioId): bool {
        $q = $this->bd->prepare("UPDATE notificacoes SET lida = 1 WHERE id = :id AND usuario_id = :uid");
        $q->execute([':id' => $id, ':uid' => $usuarioId]);
        return $q->rowCount() > 0;
    }

    /**
     * Marcar todas as notificações do usuário como lidas
     */
    public function marcarTodasLidas(int $usuarioId): int {
        $q = $this->bd->prepare("UPDATE notificacoes SET lida = 1 WHERE usuario_id = :uid AND lida = 0");
        $q->execute([':uid' => $usuarioId]);
        return $q->rowCount();
    }

    /**
     * Excluir uma notificação
     */
    public function excluir(int $id, int $usuarioId): bool {
        $q = $this->bd->prepare("DELETE FROM notificacoes WHERE id = :id AND usuario_id = :uid");
        $q->execute([':id' => $id, ':uid' => $usuarioId]);
        return $q->rowCount() > 0;
    }
}
