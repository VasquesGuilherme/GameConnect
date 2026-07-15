<?php
session_start();
require_once '../config/conex.php';
require_once __DIR__ . '/send_email.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../html/login.php');
    exit();
}

$email = filter_input(INPUT_POST, 'idEmail', FILTER_SANITIZE_EMAIL);
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../html/esqueci_senha.php?erro=' . urlencode('Por favor, informe um e-mail válido.'));
    exit();
}

try {
    $stmt = $pdo->prepare('SELECT id, nome FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(128) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX(token),
        INDEX(user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    if ($user) {
        $token = bin2hex(random_bytes(24));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);

        $deleteOld = $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?');
        $deleteOld->execute([$user['id']]);

        $insert = $pdo->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)');
        $insert->execute([$user['id'], $token, $expiresAt]);

        $resetUrl = getBaseUrl() . 'resetar_senha.php?token=' . urlencode($token);
        $subject = 'Redefinição de senha GameConneCt';
        $htmlMessage = "<p>Olá " . htmlspecialchars($user['nome']) . ",</p>\n"
            . "<p>Recebemos uma solicitação para redefinir sua senha do GameConneCt.</p>\n"
            . "<p><a href=\"$resetUrl\" target=\"_blank\">Clique aqui para redefinir sua senha</a></p>\n"
            . "<p>O link é válido por 1 hora.</p>\n"
            . "<p>Se você não solicitou a redefinição, ignore esta mensagem.</p>";

        $result = sendBrevoEmail($email, $user['nome'], $subject, $htmlMessage);
        
        if (!$result['success']) {
            // Log detalhado para diagnóstico
            $debugInfo = [
                'destinatario' => $email,
                'usuario' => $user['nome'],
                'timestamp' => date('Y-m-d H:i:s'),
                'erro' => $result['error'] ?? 'Erro desconhecido',
                'statusCode' => $result['statusCode'] ?? null,
                'resposta' => $result['response'] ?? null,
            ];
            error_log('[FORGOT_PASSWORD_FAIL] ' . json_encode($debugInfo, JSON_UNESCAPED_UNICODE));
            
            header('Location: ../html/esqueci_senha.php?erro=' . urlencode('Não foi possível enviar o link de redefinição. Tente novamente mais tarde.'));
            exit();
        }
        
        error_log('[FORGOT_PASSWORD_SUCCESS] E-mail de redefinição enviado para: ' . $email);
    }

    header('Location: ../html/login.php?sucesso=' . urlencode('Se o e-mail existir, você receberá um link para redefinir a senha.'));
    exit();
} catch (PDOException $e) {
    error_log('Erro forgot_password_action: ' . $e->getMessage());
    header('Location: ../html/esqueci_senha.php?erro=' . urlencode('Ocorreu um erro. Tente novamente mais tarde.'));
    exit();
}
