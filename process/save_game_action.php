<?php
session_start();
// Previne vazamento de erros HTML em requisições AJAX
ini_set('display_errors', 0);
ob_start();

require_once '../config/conex.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuarios_id'])) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit;
}

$usuarioId = $_SESSION['usuarios_id'];

// Receive JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['rawg_id']) || !isset($data['name'])) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Dados do jogo inválidos']);
    exit;
}

$rawg_id = $data['rawg_id'];
$name = $data['name'];
$image = $data['image'] ?? '';
$genres = $data['genres'] ?? '';

try {
    $pdo->beginTransaction();

    // Verify if user already has 3 games
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM usuario_top_jogos WHERE usuario_id = ?");
    $stmtCount->execute([$usuarioId]);
    $gameCount = $stmtCount->fetchColumn();

    if ($gameCount >= 3) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Você já selecionou 3 jogos. Remova um antes de adicionar outro.']);
        exit;
    }

    // 1. Check if game exists in 'games' table
    $stmtCheck = $pdo->prepare("SELECT id FROM games WHERE rawg_id = ?");
    $stmtCheck->execute([$rawg_id]);
    $jogoId = $stmtCheck->fetchColumn();

    // If not, insert it
    if (!$jogoId) {
        $stmtInsertGame = $pdo->prepare("INSERT INTO games (rawg_id, name, image, genres) VALUES (?, ?, ?, ?)");
        $stmtInsertGame->execute([$rawg_id, $name, $image, $genres]);
        $jogoId = $pdo->lastInsertId();
    }

    // 2. Link game to user
    $stmtCheckRelation = $pdo->prepare("SELECT 1 FROM usuario_top_jogos WHERE usuario_id = ? AND jogo_id = ?");
    $stmtCheckRelation->execute([$usuarioId, $jogoId]);
    if (!$stmtCheckRelation->fetchColumn()) {
        $stmtRelation = $pdo->prepare("INSERT INTO usuario_top_jogos (usuario_id, jogo_id) VALUES (?, ?)");
        $stmtRelation->execute([$usuarioId, $jogoId]);
    }

    $pdo->commit();

    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Jogo salvo com sucesso!']);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro interno ao salvar jogo: ' . $e->getMessage()]);
}
?>
