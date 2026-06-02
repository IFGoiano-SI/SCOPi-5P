<?php
namespace Models;

class UsuarioModelo extends ModeloBase {

    protected string $tabela = 'usuarios';

    public function buscarPorEmail(string $email): ?array {
        $q = $this->bd->prepare("SELECT * FROM {$this->tabela} WHERE email = :email AND situacao = 'ativo' LIMIT 1");
        $q->execute([':email' => $email]);
        return $q->fetch() ?: null;
    }

    public function buscarComDepartamento(int $id): ?array {
        $q = $this->bd->prepare(
            "SELECT u.*, d.nome AS nome_departamento FROM usuarios u
             LEFT JOIN departamentos d ON d.id = u.departamento_id
             WHERE u.id = :id LIMIT 1"
        );
        $q->execute([':id' => $id]);
        return $q->fetch() ?: null;
    }

    public function listarComFiltros(array $filtros = [], ?int $departamentoId = null): array {
        $sql = "SELECT u.*, d.nome AS nome_departamento FROM usuarios u
                LEFT JOIN departamentos d ON d.id = u.departamento_id WHERE 1=1";
        $p = [];
        if ($departamentoId) { $sql .= ' AND u.departamento_id = :dep'; $p[':dep'] = $departamentoId; }
        if (!empty($filtros['nome']))      { $sql .= ' AND u.nome LIKE :nome';      $p[':nome']      = "%{$filtros['nome']}%"; }
        if (!empty($filtros['matricula'])) { $sql .= ' AND u.matricula LIKE :matricula'; $p[':matricula'] = "%{$filtros['matricula']}%"; }
        if (!empty($filtros['departamento_codigo'])) { $sql .= ' AND d.codigo = :depcod'; $p[':depcod'] = $filtros['departamento_codigo']; }
        if (!empty($filtros['situacao']))  { $sql .= ' AND u.situacao = :situacao'; $p[':situacao']  = $filtros['situacao']; }
        $sql .= ' ORDER BY u.nome ASC';
        $q = $this->bd->prepare($sql);
        $q->execute($p);
        return $q->fetchAll();
    }

    public function cadastrar(array $dados, ?int $responsavelId = null): int {
        $tempMatricula = uniqid('temp_');
        $this->bd->prepare("
            INSERT INTO usuarios (nome, email, senha, matricula, contato, departamento_id, perfil, situacao, tentativas_falhas, criado_em)
            VALUES (:nome, :email, :senha, :matricula, :contato, :dep, :perfil, 'ativo', 0, NOW())
        ")->execute([
            ':nome'      => $dados['nome'],
            ':email'     => $dados['email'],
            ':senha'     => password_hash($dados['senha'], PASSWORD_DEFAULT),
            ':matricula' => $tempMatricula,
            ':contato'   => $dados['contato'],
            ':dep'       => $dados['departamento_id'],
            ':perfil'    => $dados['perfil'],
        ]);
        $novoId = (int) $this->bd->lastInsertId();
        
        $matriculaDefinitiva = '26' . str_pad($novoId, 6, '0', STR_PAD_LEFT);
        $this->bd->prepare("UPDATE usuarios SET matricula = :matr WHERE id = :id")->execute([':matr' => $matriculaDefinitiva, ':id' => $novoId]);
        
        $dados['matricula'] = $matriculaDefinitiva;
        
        if ($responsavelId) {
            // Remove password field for history privacy/security
            $dadosLimpos = $dados;
            unset($dadosLimpos['senha']);
            $this->registrarHistorico($this->tabela, $novoId, [], $dadosLimpos, $responsavelId);
        }
        return $novoId;
    }

    public function atualizar(int $id, array $dados, int $responsavelId): bool {
        $anterior = $this->buscarPorId($id);
        $sql = "UPDATE usuarios SET nome=:nome, email=:email, 
                contato=:contato, departamento_id=:dep, perfil=:perfil, atualizado_em=NOW()";
        $p = [':nome'=>$dados['nome'], ':email'=>$dados['email'],
              ':contato'=>$dados['contato'], ':dep'=>$dados['departamento_id'], ':perfil'=>$dados['perfil']];
        if (!empty($dados['senha'])) {
            $sql .= ', senha=:senha';
            $p[':senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
        }
        $sql .= ' WHERE id=:id';
        $p[':id'] = $id;
        $ok = $this->bd->prepare($sql)->execute($p);
        if ($ok && $anterior) $this->registrarHistorico($this->tabela, $id, $anterior, $dados, $responsavelId);
        return $ok;
    }

    public function verificarSenha(string $informada, string $armazenada): bool {
        return password_verify($informada, $armazenada);
    }

    public function incrementarTentativasFalhas(int $id): void {
        $this->bd->prepare("UPDATE usuarios SET tentativas_falhas = tentativas_falhas+1, ultima_tentativa=NOW() WHERE id=:id")->execute([':id'=>$id]);
    }

    public function resetarTentativasFalhas(int $id): void {
        $this->bd->prepare("UPDATE usuarios SET tentativas_falhas=0, ultima_tentativa=NULL WHERE id=:id")->execute([':id'=>$id]);
    }

    public function salvarTokenRecuperacao(int $usuarioId, string $token): void {
        $this->bd->prepare("\n            INSERT INTO recuperacao_senha (usuario_id, token, expira_em)\n            VALUES (:uid, :token, DATE_ADD(NOW(), INTERVAL 1 HOUR))\n            ON DUPLICATE KEY UPDATE token=VALUES(token), expira_em=VALUES(expira_em)\n        ")->execute([':uid' => $usuarioId, ':token' => $token]);
    }

    public function validarTokenRecuperacao(string $token): ?array {
        $q = $this->bd->prepare("\n            SELECT * FROM recuperacao_senha WHERE token = :token AND expira_em > NOW() LIMIT 1\n        ");
        $q->execute([':token' => $token]);
        return $q->fetch() ?: null;
    }

    public function atualizarSenha(int $usuarioId, string $novaSenha): bool {
        return $this->bd->prepare("\n            UPDATE usuarios SET senha=:senha, tentativas_falhas=0, ultima_tentativa=NULL, atualizado_em=NOW() WHERE id=:id\n        ")->execute([':senha' => password_hash($novaSenha, PASSWORD_DEFAULT), ':id' => $usuarioId]);
    }

    public function deletarTokenRecuperacao(string $token): void {
        $this->bd->prepare("DELETE FROM recuperacao_senha WHERE token=:token")->execute([':token' => $token]);
    }
}
