<?php
session_start();
require_once '../config/conex.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuarios_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['jogo_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID não informado']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM usuario_top_jogos WHERE usuario_id = ? AND jogo_id = ?");
    $stmt->execute([$_SESSION['usuarios_id'], $data['jogo_id']]);
    echo json_encode(['success' => true, 'message' => 'Jogo removido com sucesso.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
