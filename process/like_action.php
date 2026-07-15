<?php
session_start();
require_once '../config/conex.php';

if (!isset($_SESSION['usuarios_id'])) {
    header("Location: ../html/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['usuarios_to']) && isset($_POST['action'])) {
    $userFrom = $_SESSION['usuarios_id'];
    $userTo = $_POST['usuarios_to'];
    $action = $_POST['action']; // 'like' ou 'pass'

    $liked = ($action === 'like') ? 1 : 0;

    try {
        // Verifica se já não curtiu antes (prevenção)
        $stmtVerifica = $pdo->prepare("SELECT id FROM curtidas WHERE usuario_origem = ? AND usuario_destino = ?");
        $stmtVerifica->execute([$userFrom, $userTo]);
        if ($stmtVerifica->rowCount() == 0) {
            // Insere o like/pass
            $stmt = $pdo->prepare("INSERT INTO curtidas (usuario_origem, usuario_destino, liked) VALUES (?, ?, ?)");
            $stmt->execute([$userFrom, $userTo, $liked]);

            // Se foi Like (Sim), verifica se tem Match
            if ($liked == 1) {
                // Checa se o outro usuário já curtiu o userFrom
                $stmtMatch = $pdo->prepare("SELECT id FROM curtidas WHERE usuario_origem = ? AND usuario_destino = ? AND liked = 1");
                $stmtMatch->execute([$userTo, $userFrom]);

                if ($stmtMatch->rowCount() > 0) {
                    // Nós temos um Match! INSERE SOMENTE SE AINDA NÃO EXISTIR
                    $stmtCheckMatch = $pdo->prepare("SELECT id FROM matches WHERE (usuario1 = ? AND usuario2 = ?) OR (usuario1 = ? AND usuario2 = ?)");
                    $stmtCheckMatch->execute([$userFrom, $userTo, $userTo, $userFrom]);

                    if ($stmtCheckMatch->rowCount() == 0) {
                        $stmtInsertMatch = $pdo->prepare("INSERT INTO matches (usuario1, usuario2) VALUES (?, ?)");
                        // Por convenção, pode inserir o menor id em usuario1 ou apenas userFrom
                        $stmtInsertMatch->execute([$userFrom, $userTo]);
                    }
                }
            }
        }
    } catch (PDOException $e) {
        // Error handling if needed
    }
}

// Redireciona de volta pro menu para ver a próxima pessoa
header("Location: ../html/menu.php");
exit();
?>