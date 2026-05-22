<?php
namespace Config;

/**
 * BancoDados.php
 * Conexão singleton com o banco MySQL via PDO.
 */
class BancoDados {

    private static ?self $instancia = null;
    private \PDO $conexao;

    private function __construct() {
        $hospedeiro = getenv('DB_HOST') ?: 'mysql';
        $nomeBanco  = getenv('NAME') ?: 'scopi';
        $usuario    = getenv('USER') ?: 'root';
        $senha      = getenv('PASS') !== false ? getenv('PASS') : 'root';
        $port       = getenv('DB_PORT') ?: '3306';

        $dsn = "mysql:host={$hospedeiro};dbname={$nomeBanco};port={$port};charset=utf8mb4";
        $this->conexao = new \PDO($dsn, $usuario, $senha, [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    public static function obterInstancia(): self {
        if (self::$instancia === null) self::$instancia = new self();
        return self::$instancia;
    }

    public function obterConexao(): \PDO {
        return $this->conexao;
    }
}
