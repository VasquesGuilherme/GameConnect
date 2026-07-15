<?php
session_start();
require_once '../config/conex.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../html/login.php');
    exit();
}

 $token = trim($_POST['token'] ?? '');
 $senha = $_POST['idSenha'] ?? '';
 $senhaConfirm = $_POST['idSenhaConfirm'] ?? '';

if (!$token) {
    header('Location: ../html/login.php?erro=' . urlencode('Token de redefinição ausente.'));
    exit();
}

if (empty($senha) || empty($senhaConfirm) || $senha !== $senhaConfirm || strlen($senha) < 6) {
    header('Location: ../html/resetar_senha.php?token=' . urlencode($token) . '&erro=' . urlencode('As senhas devem ser iguais e ter pelo menos 6 caracteres.'));
    exit();
}

try {
    $stmt = $pdo->prepare('SELECT pr.user_id FROM password_resets pr WHERE pr.token = ? AND pr.expires_at > NOW()');
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        header('Location: ../html/resetar_senha.php?erro=' . urlencode('Link inválido ou expirado.'));
        exit();
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $update = $pdo->prepare('UPDATE usuarios SET senha = ? WHERE id = ?');
    $update->execute([$senhaHash, $reset['user_id']]);

    if ($update->rowCount() !== 1) {
        error_log('Reset password update falhou para user_id: ' . $reset['user_id']);
        header('Location: ../html/resetar_senha.php?token=' . urlencode($token) . '&erro=' . urlencode('Não foi possível redefinir a senha. Tente novamente.'));
        exit();
    }

    $delete = $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?');
    $delete->execute([$reset['user_id']]);

    header('Location: ../html/login.php?sucesso=' . urlencode('Senha redefinida com sucesso. Faça login com a nova senha.'));
    exit();
} catch (PDOException $e) {
    error_log('Erro reset_password_action: ' . $e->getMessage());
    header('Location: ../html/resetar_senha.php?token=' . urlencode($token) . '&erro=' . urlencode('Ocorreu um erro. Tente novamente.'));
    exit();
}
