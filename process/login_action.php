<?php
session_start();
require_once '../config/conex.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['idEmail']);
    $senha = $_POST['idSenha'];

    if (empty($email) || empty($senha)) {
        header("Location: ../html/login.php?erro=Preencha os campos obrigatorios.");
        exit();
    }

    try {
        // Busca usuário pelo e-mail
        $stmt = $pdo->prepare("SELECT id, senha, foto, descricao, genero FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['senha'])) {
            // Login com sucesso
            $_SESSION['usuarios_id'] = $user['id'];

            // Redirect first-time users to their profile to finish setup
            if (empty($user['foto']) || empty($user['descricao']) || empty($user['genero'])) {
                header("Location: ../html/perfil.php");
            } else {
                header("Location: ../html/menu.php");
            }
            exit();
        } else {
            header("Location: ../html/login.php?erro=" . urlencode("E-mail ou senha inválido"));
            exit();
        }
    } catch (PDOException $e) {
        // Log error internally (don't expose to user)
        error_log("Login error: " . $e->getMessage());
        header("Location: ../html/login.php?erro=" . urlencode("Verifique seus dados e tente novamente."));
        exit();
    }
} else {
    header("Location: ../html/login.php");
    exit();
}
?>