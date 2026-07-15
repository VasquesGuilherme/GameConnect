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

$maxBirthDate = (new DateTime())->modify('-18 years')->format('Y-m-d');
$errorMessage = filter_input(INPUT_GET, 'erro', FILTER_SANITIZE_SPECIAL_CHARS);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../fotos/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .inputCadastro.inputError {
            border: 3px solid #ff0000;
            box-shadow: 0 0 12px rgba(255, 0, 0, 0.7);
        }
        .fieldErrorMessage,
        .ageErrorMessage {
            color: #ffffff;
            background: rgba(204, 0, 0, 0.95);
            padding: 8px 10px;
            border-radius: 8px;
            margin-top: 8px;
            font-size: 13px;
            text-align: left;
            display: none;
        }
        .fieldErrorMessage {
            margin-bottom: 5px;
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
    <div class="Container">
    <section class="Logo">
        <img src="../fotos/LogoGameConnect.png" alt="logo" class="GCadastro">

    </section>
     
    <section class="Cadastro">
        <h1 class="txtCadastro">Cadastro</h1>
            <form action="../process/cadastro_action.php" method="POST" onsubmit="return validarCadastro(event)" novalidate>
            <br>
            <?php if (!empty($errorMessage)): ?>
                <div id="topErrorMessage" class="errorMessage" style="color:#ffcccc; background:#660000; padding:10px; border-radius:8px; margin-bottom:15px; text-align:center;">
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>
            <label for="idNome">Usuário:</label>
            <input type="text" name="idNome" placeholder="Digite seu Usuário" required class="inputCadastro">
            <div id="nomeErrorMessage" class="fieldErrorMessage" aria-live="polite"></div>
            <br>
            <label for="idSenha">Senha:</label>
            <div class="inputCadastroContainer">
                <input type="password" name="idSenha" id="senhaCadastro" placeholder="Digite sua Senha" required class="inputCadastro">
                <button type="button" class="togglePassword" onclick="togglePasswordVisibility('senhaCadastro', this)">👁️‍🗨️</button>
            </div>
            <div id="senhaErrorMessage" class="fieldErrorMessage" aria-live="polite"></div>
            <br>
            <label for="idEmail">E-mail:</label>
            <input type="text" name="idEmail" id="idEmail" placeholder="Digite seu E-mail" required class="inputCadastro">
            <div id="emailErrorMessage" class="fieldErrorMessage" aria-live="polite"></div>
            <br>
            <label for="idNasc">Data de Nascimento:</label>
            <div class="inputCadastroContainer dateInputContainer">
                <input type="date" id="idNasc" name="idNasc" required class="inputCadastro dateInput">
                <span class="dateIcon" aria-hidden="true" onclick="abrirCalendario()">📅</span>
            </div>
            <div id="ageErrorMessage" class="ageErrorMessage" aria-live="polite"></div>
            <br>
            <input type="submit" value="Cadastra-se" class="btnCadastro">
            <div style="display:flex; flex-direction:column; gap:10px; align-items:center; margin-top:10px;">
                <a href="login.php" class="linkAcao" style="color:#a70000ff; font-weight:bold;">Já tenho login</a>
            </div>
        </form>
    </section>
    </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ageMessage = document.getElementById('ageErrorMessage');
            const emailMessage = document.getElementById('emailErrorMessage');
            const topErrorMessage = document.getElementById('topErrorMessage');

            if (ageMessage) {
                ageMessage.style.display = 'none';
                ageMessage.textContent = '';
            }

            if (topErrorMessage) {
                const msg = topErrorMessage.textContent.trim().toLowerCase();
                let handled = false;
                
                if (msg.includes('18 anos') || msg.includes('idade')) {
                    if (ageMessage) {
                        ageMessage.textContent = topErrorMessage.textContent.trim();
                        ageMessage.style.display = 'block';
                        document.getElementById('idNasc').classList.add('inputError');
                    }
                    handled = true;
                } else if (msg.includes('e-mail') || msg.includes('email')) {
                    if (emailMessage) {
                        emailMessage.textContent = topErrorMessage.textContent.trim();
                        emailMessage.style.display = 'block';
                        document.getElementById('idEmail').classList.add('inputError');
                    }
                    handled = true;
                }

                if (handled) {
                    topErrorMessage.style.display = 'none';
                    if (window.history && window.history.replaceState) {
                        window.history.replaceState(null, '', window.location.pathname);
                    }
                }
            }
        });

        function showFieldError(elementId, message) {
            const errorDiv = document.getElementById(elementId);
            if (errorDiv) {
                errorDiv.textContent = message;
                errorDiv.style.display = 'block';
            }
        }

        function clearFieldErrors() {
            const topErrorMessage = document.getElementById('topErrorMessage');
            if (topErrorMessage) {
                topErrorMessage.style.display = 'none';
            }
            document.querySelectorAll('.fieldErrorMessage, .ageErrorMessage').forEach(div => {
                div.textContent = '';
                div.style.display = 'none';
            });
            document.querySelectorAll('.inputCadastro').forEach(input => {
                input.classList.remove('inputError');
            });
        }

        function validarCadastro(event) {
            clearFieldErrors();

            const nome = document.querySelector('input[name="idNome"]');
            const senha = document.querySelector('input[name="idSenha"]');
            const email = document.querySelector('input[name="idEmail"]');
            const dateInput = document.querySelector('input[name="idNasc"]');
            let valid = true;

            if (!nome || !nome.value.trim()) {
                showFieldError('nomeErrorMessage', 'Preencha seu usuário');
                if (nome) nome.classList.add('inputError');
                valid = false;
            }

            if (!senha || !senha.value.trim()) {
                showFieldError('senhaErrorMessage', 'Preencha sua senha');
                if (senha) senha.classList.add('inputError');
                valid = false;
            }

            if (!email || !email.value.trim()) {
                showFieldError('emailErrorMessage', 'Preencha seu e-mail');
                if (email) email.classList.add('inputError');
                valid = false;
            } else {
                const emailValue = email.value.trim();
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(emailValue)) {
                    showFieldError('emailErrorMessage', 'E-mail inválido');
                    email.classList.add('inputError');
                    valid = false;
                }
            }

            if (!dateInput || !dateInput.value.trim()) {
                showFieldError('ageErrorMessage', 'Preencha sua data de nascimento');
                if (dateInput) dateInput.classList.add('inputError');
                valid = false;
            } else {
                const birthDate = new Date(dateInput.value);
                const today = new Date();
                const ageLimit = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());

                if (isNaN(birthDate.getTime()) || birthDate > ageLimit) {
                    if (dateInput) dateInput.classList.add('inputError');
                    showFieldError('ageErrorMessage', 'Você precisa ter 18 anos ou mais para se cadastrar.');
                    valid = false;
                }
            }

            if (!valid) {
                event.preventDefault();
                return false;
            }

            return true;
        }

        function abrirCalendario() {
            const dateInput = document.getElementById('idNasc');
            if (!dateInput) return;

            dateInput.focus();
            if (typeof dateInput.showPicker === 'function') {
                dateInput.showPicker();
            } else {
                dateInput.click();
            }
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