<?php
session_start();
if (isset($_SESSION['usuarios_id'])) {
    header('Location: menu.php');
    exit();
}

$errorMessage = filter_input(INPUT_GET, 'erro', FILTER_SANITIZE_SPECIAL_CHARS);
$successMessage = filter_input(INPUT_GET, 'sucesso', FILTER_SANITIZE_SPECIAL_CHARS);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../fotos/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <title>Esqueci minha senha - GameConneCt</title>
</head>
<body>
    <div class="Fundo">
        <div class="ContainerL">
            <section class="LogoL">
                <img src="../fotos/LogoGameConnect.png" alt="logo" class="GL">
            </section>
            <section class="Login">
                <h1 class="txtLogin">Esqueci minha senha</h1>
                <form action="../process/forgot_password_action.php" method="POST" onsubmit="return validarEsqueciSenha(event)" novalidate>
                    <?php if (!empty($successMessage)): ?>
                        <div class="successMessage"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($errorMessage)): ?>
                        <div class="errorMessage"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <label for="idEmail">E-mail:</label>
                    <input type="email" name="idEmail" id="idEmail" placeholder="Digite seu E-mail" required class="inputLogin">
                    <br>
                    <input type="submit" value="Enviar link" class="btnLogin">
                    <div style="display:flex; flex-direction:column; gap:10px; align-items:center; margin-top:15px; margin-bottom:10px;">
                        <a href="login.php" class="linkAcao" style="color:#00c50a; font-weight:bold;">Voltar ao login</a>
                    </div>
                </form>
            </section>
        </div>
    </div>
    <script>
        function validarEsqueciSenha(event) {
            const email = document.querySelector('input[name="idEmail"]');
            if (!email || !email.value.trim()) {
                event.preventDefault();
                email.classList.add('inputError');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
