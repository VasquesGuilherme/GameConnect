<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../fotos/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <title>Cadastro Realizado - GameConneCt</title>
</head>

<body>
    <div class="ContainerSucesso">
        <div class="SucessoBox">
            <!-- Mensagem -->
            <h1 class="MensagemSucesso">Cadastro Realizado!</h1>
            <p class="SubtitloSucesso">Bem-vindo ao GameConneCt</p>

            <!-- Loading animation -->
            <div class="SquareLoading"></div>
        </div>
    </div>

    <script>
        // Redireciona para perfil após 3.5 segundos
        setTimeout(() => {
            window.location.href = 'perfil.php';
        }, 3500);
    </script>
</body>

</html>
