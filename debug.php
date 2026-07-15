<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug do Sistema</h1>";

// 1. Teste de conexão
echo "<h2>1. Conexão com Banco de Dados:</h2>";
try {
    $pdo = new PDO("mysql:host=localhost;port=3308;dbname=gameconnect;charset=utf8", "root", "etec123");
    echo "✅ CONEXÃO OK<br>";
    
    $result = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $row = $result->fetch();
    echo "✅ Usuários no BD: " . $row['total'] . "<br>";
    
} catch (Exception $e) {
    echo "❌ ERRO NA CONEXÃO: " . $e->getMessage() . "<br>";
    die();
}

// 2. Teste de arquivo perfil_action.php
echo "<h2>2. Arquivo perfil_action.php:</h2>";
if (file_exists('../process/perfil_action.php')) {
    echo "✅ Arquivo existe<br>";
} else {
    echo "❌ Arquivo NÃO existe<br>";
}

// 3. Session test
echo "<h2>3. Session:</h2>";
session_start();
echo "✅ Session iniciada<br>";
echo "Session ID: " . session_id() . "<br>";

// 4. Teste de redirect
echo "<h2>4. Teste de Redirect:</h2>";
echo "Se você clica em um link abaixo, deve redirecionar:<br>";
echo "<a href='html/login.php'>Login</a><br>";
echo "<a href='html/menu.php'>Menu</a><br>";
echo "<a href='html/perfil.php'>Perfil</a><br>";

?>
