<?php
session_start();

// Se já está logado, não pode voltar para esta página
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

// Usuário logado → redireciona para o feed
if (isset($_SESSION['usuarios_id'])) {
    header("Location: menu.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../fotos/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <title>GameConneCt</title>
</head>

<body>
    <div class="Inicio">
        <section class="telaInicio">
            <img src="../fotos/LogoGameConnect.png" alt="logo" class="G">

        </section>

        <section class="btnInicio">
            <a href="login.php" style="text-decoration:none;"><input type="button" value="Login" class="btnIlogin"></a>
            <a href="cadastro.php" style="text-decoration:none;"><input type="button" value="Cadastro"
                    class="btnIcadastro"></a>
        </section>
    </div>
</body>

</html>
