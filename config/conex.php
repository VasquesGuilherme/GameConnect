<?php
$host = 'localhost';
$port = '3308';
$dbname = 'gameconnect';
$user = 'root';
$pass = '';

function createDatabaseConnection(): PDO
{
    global $host, $port, $dbname, $user, $pass;

    if (!extension_loaded('pdo')) {
        throw new RuntimeException('A extensão PDO não está carregada. Verifique o php.ini.');
    }

    if (!extension_loaded('pdo_mysql')) {
        $available = implode(', ', PDO::getAvailableDrivers());
        throw new RuntimeException('O driver PDO MySQL não está habilitado. Drivers disponíveis: ' . $available . '. Habilite extension=pdo_mysql no php.ini e reinicie o servidor.');
    }

    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";
    $initCommandAttribute = (class_exists('Pdo\\Mysql') && defined('Pdo\\Mysql::ATTR_INIT_COMMAND'))
        ? Pdo\Mysql::ATTR_INIT_COMMAND
        : PDO::MYSQL_ATTR_INIT_COMMAND;

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        $initCommandAttribute => 'SET NAMES utf8'
    ];

    return new PDO($dsn, $user, $pass, $options);
}

try {
    $pdo = createDatabaseConnection();
} catch (Throwable $e) {
    error_log('[DB] ' . $e->getMessage());
    die('Erro na conexão com o banco de dados. Verifique se o driver pdo_mysql está habilitado no php.ini e se o PHP usado pelo servidor é o mesmo do CLI. Mensagem técnica: ' . htmlspecialchars($e->getMessage()));
}
?>
