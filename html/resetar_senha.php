<?php
session_start();
require_once '../config/conex.php';

$token = htmlspecialchars($_GET['token'] ?? '');
$errorMessage = filter_input(INPUT_GET, 'erro', FILTER_SANITIZE_SPECIAL_CHARS);
$successMessage = filter_input(INPUT_GET, 'sucesso', FILTER_SANITIZE_SPECIAL_CHARS);
$validToken = false;

if ($token) {
    try {
        $stmt = $pdo->prepare('SELECT pr.user_id FROM password_resets pr WHERE pr.token = ? AND pr.expires_at > NOW()');
        $stmt->execute([$token]);
        $reset = $stmt->fetch();
        $validToken = (bool) $reset;
    } catch (PDOException $e) {
        error_log('Erro ao validar token resetar_senha: ' . $e->getMessage());
        $errorMessage = 'Ocorreu um erro ao validar o link. Tente novamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../fotos/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <title>Redefinir senha - GameConneCt</title>
</head>
<body>
    <div class="Fundo">
        <div class="ContainerL">
            <section class="LogoL">
                <img src="../fotos/LogoGameConnect.png" alt="logo" class="GL">
            </section>
            <section class="Login">
                <h1 class="txtLogin">Redefinir senha</h1>
                <?php if ($validToken): ?>
                    <form action="../process/reset_password_action.php" method="POST" onsubmit="return validarNovaSenha(event)" novalidate>
                        <?php if (!empty($successMessage)): ?>
                            <div class="successMessage"><?php echo htmlspecialchars($successMessage); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($errorMessage)): ?>
                            <div class="errorMessage"><?php echo htmlspecialchars($errorMessage); ?></div>
                        <?php endif; ?>
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        <label for="idSenha">Nova senha:</label>
                        <div class="inputLoginContainer">
                            <input type="password" name="idSenha" id="idSenha" placeholder="Digite sua nova senha" required class="inputLogin">
                            <button type="button" class="togglePassword" onclick="togglePasswordVisibility('idSenha', this)">👁️</button>
                        </div>
                        <br>
                        <label for="idSenhaConfirm">Confirmar senha:</label>
                        <div class="inputLoginContainer">
                            <input type="password" name="idSenhaConfirm" id="idSenhaConfirm" placeholder="Confirme sua nova senha" required class="inputLogin">
                            <button type="button" class="togglePassword" onclick="togglePasswordVisibility('idSenhaConfirm', this)">👁️</button>
                        </div>
                        <br>
                        <input type="submit" value="Redefinir senha" class="btnLogin">
                        <div style="display:flex; flex-direction:column; gap:10px; align-items:center; margin-top:15px; margin-bottom:10px;">
                            <a href="login.php" class="linkAcao" style="color:#00c50a; font-weight:bold;">Voltar ao login</a>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="messagePanel">
                        <p style="color:#fff; text-align:center; margin:0 0 12px;">Link inválido ou expirado. Solicite um novo link.</p>
                        <div style="display:flex; flex-direction:column; gap:10px; align-items:center;">
                            <a href="esqueci_senha.php" class="linkAcao" style="color:#00c50a; font-weight:bold;">Solicitar novo link</a>
                            <a href="login.php" class="linkAcao" style="color:#00c50a; font-weight:bold;">Voltar ao login</a>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
    <script>
        function validarNovaSenha(event) {
            const senha = document.getElementById('idSenha');
            const senhaConfirm = document.getElementById('idSenhaConfirm');
            let valid = true;

            [senha, senhaConfirm].forEach(campo => campo.classList.remove('inputError'));

            if (!senha.value.trim() || senha.value.length < 6) {
                senha.classList.add('inputError');
                valid = false;
            }

            if (senha.value !== senhaConfirm.value) {
                senhaConfirm.classList.add('inputError');
                valid = false;
            }

            if (!valid) {
                event.preventDefault();
            }

            return valid;
        }

        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                button.textContent = '👁️‍🗨️';
            } else {
                input.type = 'password';
                button.textContent = '👁️';
            }
        }
    </script>
</body>
</html>
