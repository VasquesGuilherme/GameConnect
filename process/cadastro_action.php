<?php
session_start();
require_once '../config/conex.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../html/login.php");
    exit();
}

$nome = trim(filter_input(INPUT_POST, 'idNome', FILTER_DEFAULT) ?? '');
$email = trim(filter_input(INPUT_POST, 'idEmail', FILTER_SANITIZE_EMAIL) ?? '');
$senha = $_POST['idSenha'] ?? '';
$dataNascimento = trim(filter_input(INPUT_POST, 'idNasc', FILTER_DEFAULT) ?? '');

if ($nome === '' || $email === '' || $senha === '' || $dataNascimento === '') {
    header("Location: ../html/cadastro.php?erro=" . urlencode('Preencha todos os campos'));
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../html/cadastro.php?erro=" . urlencode('E-mail inválido'));
    exit();
}

$birthDate = DateTime::createFromFormat('Y-m-d', $dataNascimento);
$today = new DateTime();
$minimumBirthDate = (clone $today)->modify('-18 years');

if (!$birthDate || $birthDate > $minimumBirthDate) {
    header("Location: ../html/cadastro.php?erro=" . urlencode('Você precisa ter 18 anos ou mais para se cadastrar.'));
    exit();
}

try {
    $checkStmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $checkStmt->execute([$email]);
    if ($checkStmt->fetch()) {
        header("Location: ../html/cadastro.php");
        exit();
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (nome, email, senha, data_nascimento) VALUES (:nome, :email, :senha, :data_nascimento)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':senha', $senhaHash, PDO::PARAM_STR);
    $stmt->bindValue(':data_nascimento', $dataNascimento, PDO::PARAM_STR);

    $stmt->execute();
    $userId = $pdo->lastInsertId();

    if (!$userId) {
        throw new PDOException('Não foi possível recuperar o ID do usuário após inserção.');
    }

    $_SESSION['usuarios_id'] = $userId;
    $_SESSION['user_nome'] = $nome;
    $_SESSION['user_email'] = $email;

    header("Location: ../html/cadastro_sucesso.php");
    exit();
} catch (PDOException $e) {
    error_log('Cadastro error: ' . $e->getMessage());

    $message = 'Erro ao cadastrar. Tente novamente.';
    if (isset($e->errorInfo[0]) && $e->errorInfo[0] === '23000') {
        $message = 'E-mail já cadastrado.';
    }

    header("Location: ../html/cadastro.php?erro=" . urlencode($message));
    exit();
}
?>