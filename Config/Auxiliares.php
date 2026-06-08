<?php
namespace Config;

/**
 * Auxiliares.php
 * Funções auxiliares: sessão, autenticação, sanitização, flash e JSON.
 */
class Auxiliares {

    public static function iniciarSessao(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public static function exigirAutenticacao(): void {
        self::iniciarSessao();
        if (empty($_SESSION['usuario_id'])) self::redirecionar('login');
    }

    public static function exigirPerfil(string ...$perfis): void {
        self::exigirAutenticacao();
        if (!in_array($_SESSION['usuario_perfil'] ?? '', $perfis)) self::redirecionar('inicio');
    }

    public static function usuarioLogado(): array {
        self::iniciarSessao();
        return [
            'id'              => $_SESSION['usuario_id']              ?? null,
            'nome'            => $_SESSION['usuario_nome']            ?? '',
            'email'           => $_SESSION['usuario_email']           ?? '',
            'perfil'          => $_SESSION['usuario_perfil']          ?? '',
            'departamento_id' => $_SESSION['usuario_departamento_id'] ?? null,
        ];
    }

    public static function definirSessaoUsuario(array $dados): void {
        self::iniciarSessao();
        $_SESSION['usuario_id']              = $dados['id'];
        $_SESSION['usuario_nome']            = $dados['nome'];
        $_SESSION['usuario_email']           = $dados['email'];
        $_SESSION['usuario_perfil']          = $dados['perfil'];
        $_SESSION['usuario_departamento_id'] = $dados['departamento_id'];
    }

    public static function encerrarSessao(): void {
        self::iniciarSessao();
        session_destroy();
    }

    public static function redirecionar(string $rota): void {
        if (function_exists('base_url')) {
            header('Location: ' . base_url($rota));
        } else {
            header('Location: ' . $rota);
        }
        exit;
    }

    public static function escapar(?string $valor): string {
        return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
    }

    public static function flash(string $tipo, string $texto): void {
        self::iniciarSessao();
        $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
    }

    public static function obterFlash(): ?array {
        self::iniciarSessao();
        if (!empty($_SESSION['flash'])) {
            $f = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $f;
        }
        return null;
    }

    public static function json(bool $sucesso, string $mensagem = '', mixed $dados = null): void {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['sucesso' => $sucesso, 'mensagem' => $mensagem, 'dados' => $dados]);
        exit;
    }

    public static function ehAjax(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public static function gerarCSV(string $nomeArquivo, array $cabecalhos, array $dados): void {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '_' . date('Ymd_His') . '.csv"');
        
        $saida = fopen('php://output', 'w');
        fputs($saida, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));

        if (!empty($cabecalhos)) {
            fputcsv($saida, $cabecalhos, ';');
        }

        foreach ($dados as $linha) {
            fputcsv($saida, $linha, ';');
        }

        fclose($saida);
        exit;
    }

    public static function formatarStatus(string $status): string {
        $mapa = [
            'em_aberto' => 'Aberto',
            'aberta' => 'Aberto',
            'autorizada' => 'Autorizado',
            'autorizado' => 'Autorizado',
            'em_cotacao' => 'Em Cotação',
            'fechada' => 'Fechado',
            'fechado' => 'Fechado',
            'enviada' => 'Enviado',
            'enviado' => 'Enviado',
            'parcialmente_atendida' => 'Parcialmente Atendido',
            'parcialmente_atendido' => 'Parcialmente Atendido',
            'concluida' => 'Concluído',
            'concluido' => 'Concluído',
            'cancelada' => 'Cancelado',
            'cancelado' => 'Cancelado',
            'recusada' => 'Recusado',
            'recusado' => 'Recusado',
            'registrada' => 'Registrado',
            'vinculada' => 'Vinculado'
        ];
        return $mapa[strtolower($status)] ?? ucfirst(str_replace('_', ' ', $status));
    }
}
