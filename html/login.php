<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
if (isset($_SESSION['usuarios_id'])) {
    header("Location: menu.php");
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
    <style>
        .inputLogin.inputError {
            border: 3px solid #ff0000 !important;
            box-shadow: 0 0 12px rgba(255, 0, 0, 0.7);
        }
        /* Esconde o olho nativo do Edge/Chrome nos campos de senha */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }
    </style>
    <title>GameConneCt</title>
</head>

<body>
    <div class="Fundo">
        <div class="ContainerL">
            <section class="LogoL">
                <img src="../fotos/LogoGameConnect.png" alt="logo" class="GL">

            </section>

            <section class="Login">
                <h1 class="txtLogin">Login</h1>
                <form action="../process/login_action.php" method="POST" onsubmit="return validarLogin(event)" novalidate>
                    <br>
                    <?php if (!empty($errorMessage)): ?>
                        <div id="topErrorMessage" class="errorMessage" style="display:block;">
                            <?php echo htmlspecialchars($errorMessage); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($successMessage)): ?>
                        <div id="topSuccessMessage" class="successMessage" style="display:block;">
                            <?php echo htmlspecialchars($successMessage); ?>
                        </div>
                    <?php endif; ?>
                    <label for="idEmail">E-mail:</label>
                    <input type="text" name="idEmail" id="idEmail" placeholder="Digite seu E-mail" required class="inputLogin">
                    <br>
                    <label for="idSenha">Senha:</label>
                    <div class="inputLoginContainer">
                        <input type="password" name="idSenha" id="senhaLogin" placeholder="Digite sua Senha" required class="inputLogin">
                        <button type="button" class="togglePassword" onclick="togglePasswordVisibility('senhaLogin', this)">👁️‍🗨️</button>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:5px; align-items:flex-start; margin-top:6px; margin-bottom:10px; padding-left:2px;">
                        <a href="esqueci_senha.php" class="linkAcao" style="color:#00c50a; font-weight:bold;">Esqueci minha senha</a>
                    </div>
                    <br>
                    <input type="submit" value="Login" class="btnLogin">
                    <div style="display:flex; flex-direction:column; gap:10px; align-items:center; margin-top:15px; margin-bottom:10px;">
                        <a href="cadastro.php" class="linkAcao" style="color:#00c50a; font-weight:bold;">Não tenho conta</a>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const topErrorMessage = document.getElementById('topErrorMessage');
            if (topErrorMessage) {
                if (window.history && window.history.replaceState) {
                    window.history.replaceState(null, '', window.location.pathname);
                }
            }
        });

        function validarLogin(event) {
            const email = document.querySelector('input[name="idEmail"]');
            const senha = document.querySelector('input[name="idSenha"]');
            let valid = true;

            // Limpar erros anteriores
            [email, senha].forEach(campo => campo.classList.remove('inputError'));

            if (!email || !email.value.trim()) {
                email.classList.add('inputError');
                valid = false;
            }

            if (!senha || !senha.value.trim()) {
                senha.classList.add('inputError');
                valid = false;
            }

            if (!valid) {
                event.preventDefault();
                return false;
            }

            return true;
        }

        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                button.textContent = '👁️';
            } else {
                input.type = 'password';
                button.textContent = '👁️‍🗨️';
            }
        }
    </script>
</body>

</html>