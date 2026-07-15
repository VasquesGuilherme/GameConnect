<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste de Login</h1>";

// Conecta ao banco
try {
    $pdo = new PDO("mysql:host=localhost;port=3308;dbname=gameconnect;charset=utf8", "root", "etec123");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("❌ Erro de conexão: " . $e->getMessage());
}

// Procura o usuário de teste
$email = 'joao@test.com';
echo "<p>Procurando usuário: <strong>$email</strong></p>";

$stmt = $pdo->prepare("SELECT id, nome, senha, foto, descricao, genero FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    echo "<p>✅ Usuário encontrado:</p>";
    echo "<ul>";
    echo "<li>ID: " . $user['id'] . "</li>";
    echo "<li>Nome: " . $user['nome'] . "</li>";
    echo "<li>Email: $email</li>";
    echo "<li>Foto: " . ($user['foto'] ? $user['foto'] : "NÃO PREENCHIDA") . "</li>";
    echo "<li>Descrição: " . ($user['descricao'] ? $user['descricao'] : "NÃO PREENCHIDA") . "</li>";
    echo "<li>Gênero: " . ($user['genero'] ? $user['genero'] : "NÃO PREENCHIDO") . "</li>";
    echo "<li>Hash: " . substr($user['senha'], 0, 20) . "...</li>";
    echo "</ul>";
    
    // Testa senha
    $senhaTest = 'etec123';
    $hashCorreto = password_verify($senhaTest, $user['senha']);
    
    echo "<p><strong>Teste de Senha:</strong></p>";
    echo "<p>Senha teste: $senhaTest</p>";
    echo "<p>password_verify()? " . ($hashCorreto ? "✅ CORRETO" : "❌ ERRADO") . "</p>";
    
    if ($hashCorreto) {
        echo "<p>✅ O LOGIN FUNCIONARIA!</p>";
        echo "<p>Redirecionaria para: ";
        if (empty($user['foto']) || empty($user['descricao']) || empty($user['genero'])) {
            echo "<strong>perfil.php</strong> (onboarding)";
        } else {
            echo "<strong>menu.php</strong> (feed)";
        }
        echo "</p>";
    }
} else {
    echo "<p>❌ Usuário <strong>$email</strong> NÃO encontrado!</p>";
    
    echo "<p>Usuários cadastrados:</p>";
    $result = $pdo->query("SELECT email, nome FROM usuarios LIMIT 10");
    echo "<ul>";
    while ($row = $result->fetch()) {
        echo "<li>" . $row['email'] . " - " . $row['nome'] . "</li>";
    }
    echo "</ul>";
}

?>
