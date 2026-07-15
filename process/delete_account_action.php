<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
ini_set('display_errors', 0);
ob_start();

// Verifica se está logado
if (!isset($_SESSION['usuarios_id'])) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

// Aceita apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
    exit;
}

include_once '../config/conex.php';

$usuarioId = $_SESSION['usuarios_id'];

try {
    // 1. Remove fotos do usuário da pasta uploads
    $stmtFoto = $pdo->prepare("SELECT foto FROM usuarios WHERE id = ?");
    $stmtFoto->execute([$usuarioId]);
    $fotoRow = $stmtFoto->fetch(PDO::FETCH_ASSOC);
    if ($fotoRow && !empty($fotoRow['foto'])) {
        $caminhoFoto = __DIR__ . '/../uploads/' . $fotoRow['foto'];
        if (file_exists($caminhoFoto)) {
            unlink($caminhoFoto);
        }
    }

    // 2. Remove registros relacionados (curtidas, matches, jogos)
    $pdo->prepare("DELETE FROM curtidas WHERE usuario_origem = ? OR usuario_destino = ?")->execute([$usuarioId, $usuarioId]);
    $pdo->prepare("DELETE FROM matches WHERE usuario1 = ? OR usuario2 = ?")->execute([$usuarioId, $usuarioId]);
    $pdo->prepare("DELETE FROM usuario_top_jogos WHERE usuario_id = ?")->execute([$usuarioId]);

    // 3. Remove o usuário
    $stmtDel = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmtDel->execute([$usuarioId]);

    // 4. Destrói a sessão completamente
    session_unset();
    session_destroy();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => true]);
    exit;

} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir conta: ' . $e->getMessage()]);
    exit;
}
?>
