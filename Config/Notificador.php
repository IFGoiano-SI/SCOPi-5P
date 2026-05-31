<?php
namespace Config;

class Notificador {
    
    /**
     * Envia um e-mail via SMTP seguro (ou registra em log como fallback)
     */
    public static function enviarEmail(string $destinatario, string $assunto, string $mensagem): bool {
        // 1. Sempre registra o e-mail no log local para fins de auditoria/desenvolvimento
        $baseDir = defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__);
        $logPath = $baseDir . '/enviados_emails.log';
        $data = date('Y-m-d H:i:s');
        $logEntry = "============================================================\n";
        $logEntry .= "DATA: {$data}\n";
        $logEntry .= "PARA: {$destinatario}\n";
        $logEntry .= "ASSUNTO: {$assunto}\n";
        $logEntry .= "MENSAGEM:\n" . strip_tags($mensagem) . "\n";
        $logEntry .= "============================================================\n\n";
        file_put_contents($logPath, $logEntry, FILE_APPEND);

        // 2. Obter credenciais do ambiente
        $smtpHost = getenv('SMTP_HOST') ?: getenv('SMTP') ?: 'smtp.gmail.com';
        $smtpPort = getenv('SMTP_PORT') ?: getenv('SSL') ?: '465';
        $smtpUser = getenv('SMTP_USER');
        $smtpPass = getenv('SMTP_PASS') ?: getenv('CHAVE_APP_GOOGLE');
        $smtpFrom = getenv('SMTP_FROM') ?: $smtpUser;

        // Se o usuário não configurou e-mail no .env, não tenta enviar via SMTP
        if (empty($smtpUser) || $smtpUser === 'seu_email@gmail.com' || empty($smtpPass)) {
            file_put_contents($logPath, "[SMTP INFO] Envio ignorado (credenciais não configuradas ou padrão no .env)\n\n", FILE_APPEND);
            return true;
        }

        try {
            // Conectar via SSL socket
            $socket = @stream_socket_client("ssl://{$smtpHost}:{$smtpPort}", $errno, $errstr, 10);
            if (!$socket) {
                file_put_contents($logPath, "[SMTP ERROR] Falha ao conectar: {$errstr} ({$errno})\n\n", FILE_APPEND);
                return false;
            }

            $ler = function() use ($socket) {
                $resposta = '';
                while ($linha = fgets($socket, 515)) {
                    $resposta .= $linha;
                    if (substr($linha, 3, 1) === ' ') {
                        break;
                    }
                }
                return $resposta;
            };

            $enviar = function($cmd) use ($socket) {
                fwrite($socket, $cmd . "\r\n");
            };

            $ler(); // 220 Greeting
            
            $enviar("EHLO localhost");
            $ler();

            $enviar("AUTH LOGIN");
            $ler(); // 334 Username prompt

            $enviar(base64_encode($smtpUser));
            $ler(); // 334 Password prompt

            $enviar(base64_encode($smtpPass));
            $respAuth = $ler(); // 235 Success

            if (strpos($respAuth, '235') === false) {
                $enviar("QUIT");
                fclose($socket);
                file_put_contents($logPath, "[SMTP ERROR] Falha na autenticação: {$respAuth}\n\n", FILE_APPEND);
                return false;
            }

            $enviar("MAIL FROM:<{$smtpFrom}>");
            $ler();

            $enviar("RCPT TO:<{$destinatario}>");
            $ler();

            $enviar("DATA");
            $ler(); // 354 Start mail input

            // Cabeçalhos padrão MIME
            $headers = [
                "MIME-Version: 1.0",
                "Content-Type: text/html; charset=UTF-8",
                "To: <{$destinatario}>",
                "From: SCOPi <{$smtpFrom}>",
                "Subject: =?UTF-8?B?" . base64_encode($assunto) . "?=",
                "Date: " . date('r'),
                "Message-ID: <" . time() . "-" . md5($destinatario . $assunto) . "@scopi.com>"
            ];

            // Trata corpo do e-mail (caso não possua tags HTML, insere quebras de linha básicas)
            $corpoEmail = $mensagem;
            if (strpos($mensagem, '<') === false) {
                $corpoEmail = nl2br(htmlspecialchars($mensagem));
            }

            $emailConteudo = implode("\r\n", $headers) . "\r\n\r\n" . $corpoEmail . "\r\n.\r\n";
            $enviar($emailConteudo);
            $respData = $ler(); // 250 Ok

            $enviar("QUIT");
            fclose($socket);

            if (strpos($respData, '250') === false) {
                file_put_contents($logPath, "[SMTP ERROR] Envio dos dados falhou: {$respData}\n\n", FILE_APPEND);
                return false;
            }

            file_put_contents($logPath, "[SMTP SUCCESS] E-mail enviado com sucesso via {$smtpHost}!\n\n", FILE_APPEND);
            return true;
        } catch (\Exception $e) {
            file_put_contents($logPath, "[SMTP EXCEPTION] " . $e->getMessage() . "\n\n", FILE_APPEND);
            return false;
        }
    }

    /**
     * Cria uma notificação interna para o usuário (RF15).
     * Grava no banco de dados e também em log como fallback.
     */
    public static function notificarUsuario(int $usuarioId, string $assunto, string $mensagem, string $categoria = 'sistema'): void {
        // 1. Gravar no banco de dados (notificações reais — RF15)
        try {
            $db = \Config\BancoDados::obterInstancia()->obterConexao();
            $q = $db->prepare("
                INSERT INTO notificacoes (usuario_id, assunto, mensagem, categoria, lida, criado_em)
                VALUES (:uid, :assunto, :msg, :cat, 0, NOW())
            ");
            $q->execute([
                ':uid' => $usuarioId,
                ':assunto' => $assunto,
                ':msg' => strip_tags($mensagem),
                ':cat' => $categoria
            ]);
        } catch (\Exception $e) {
            // Fallback silencioso — se o banco não está disponível, registra apenas em log
        }

        // 2. Log em arquivo como backup de auditoria
        $baseDir = defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__);
        $logPath = $baseDir . '/notificacoes_sistema.log';
        $data = date('Y-m-d H:i:s');
        $logEntry = "[{$data}] Usuário ID: {$usuarioId} | Categoria: {$categoria} | Assunto: {$assunto} | Mensagem: " . strip_tags($mensagem) . "\n";
        file_put_contents($logPath, $logEntry, FILE_APPEND);
    }
}

