<?php
/**
 * DIAGNÓSTICO DE E-MAIL - GameConneCt
 * Use este arquivo para verificar se tudo está configurado corretamente
 * Acesse via navegador: http://localhost/tcc/email_diagnostics.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/email_config.php';
require_once __DIR__ . '/process/send_email.php';

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de E-mail - GameConneCt</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #1a1a1a; color: #fff; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        h1 { margin-bottom: 30px; color: #00c50a; }
        .section { background: #2a2a2a; border-left: 4px solid #00c50a; padding: 20px; margin-bottom: 20px; border-radius: 4px; }
        .section h2 { color: #00c50a; margin-bottom: 15px; font-size: 18px; }
        .item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #444; }
        .item:last-child { border-bottom: none; }
        .label { font-weight: bold; color: #bbb; }
        .value { color: #fff; }
        .success { color: #00c50a; }
        .error { color: #ff4444; }
        .warning { color: #ffaa00; }
        .code { background: #1a1a1a; padding: 10px; border-radius: 4px; margin-top: 10px; font-family: 'Courier New'; font-size: 12px; overflow-x: auto; }
        button { background: #00c50a; color: #000; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; margin-top: 10px; }
        button:hover { background: #00aa00; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Diagnóstico de E-mail - GameConneCt</h1>

        <!-- CONFIGURAÇÕES BREVO -->
        <div class="section">
            <h2>⚙️ Configurações Brevo</h2>
            <div class="item">
                <span class="label">BREVO_API_KEY:</span>
                <span class="value">
                    <?php
                    if (empty(BREVO_API_KEY)) {
                        echo '<span class="error">❌ NÃO CONFIGURADA</span>';
                    } elseif (BREVO_API_KEY === 'COLOQUE_SUA_CHAVE_AQUI') {
                        echo '<span class="error">❌ AINDA ESTÁ COM PLACEHOLDER</span>';
                    } else {
                        echo '<span class="success">✅ CONFIGURADA</span> (primeiros 20 chars: ' . substr(BREVO_API_KEY, 0, 20) . '...)';
                    }
                    ?>
                </span>
            </div>
            <div class="item">
                <span class="label">BREVO_SENDER_EMAIL:</span>
                <span class="value">
                    <?php
                    if (empty(BREVO_SENDER_EMAIL)) {
                        echo '<span class="error">❌ NÃO CONFIGURADO</span>';
                    } elseif (BREVO_SENDER_EMAIL === 'no-reply@seudominio.com') {
                        echo '<span class="error">❌ AINDA ESTÁ COM PLACEHOLDER</span>';
                    } else {
                        echo '<span class="success">✅ CONFIGURADO: ' . BREVO_SENDER_EMAIL . '</span>';
                    }
                    ?>
                </span>
            </div>
            <div class="item">
                <span class="label">BREVO_SENDER_NAME:</span>
                <span class="value">
                    <?php
                    if (empty(BREVO_SENDER_NAME)) {
                        echo '<span class="error">❌ NÃO CONFIGURADO</span>';
                    } else {
                        echo '<span class="success">✅ CONFIGURADO: ' . BREVO_SENDER_NAME . '</span>';
                    }
                    ?>
                </span>
            </div>
        </div>

        <!-- AMBIENTE PHP -->
        <div class="section">
            <h2>🐘 Ambiente PHP</h2>
            <div class="item">
                <span class="label">Versão PHP:</span>
                <span class="value success"><?php echo phpversion(); ?></span>
            </div>
            <div class="item">
                <span class="label">cURL habilitado:</span>
                <span class="value <?php echo extension_loaded('curl') ? 'success' : 'error'; ?>">
                    <?php echo extension_loaded('curl') ? '✅ SIM' : '❌ NÃO - Habilite extension=curl em php.ini'; ?>
                </span>
            </div>
            <div class="item">
                <span class="label">OpenSSL habilitado:</span>
                <span class="value <?php echo extension_loaded('openssl') ? 'success' : 'error'; ?>">
                    <?php echo extension_loaded('openssl') ? '✅ SIM' : '❌ NÃO - Necessário para HTTPS'; ?>
                </span>
            </div>
            <div class="item">
                <span class="label">allow_url_fopen:</span>
                <span class="value <?php echo ini_get('allow_url_fopen') ? 'success' : 'warning'; ?>">
                    <?php echo ini_get('allow_url_fopen') ? '✅ HABILITADO (fallback)' : '⚠️ DESABILITADO (precisa de cURL)'; ?>
                </span>
            </div>
            <div class="item">
                <span class="label">PDO MySQL:</span>
                <span class="value <?php echo extension_loaded('pdo_mysql') ? 'success' : 'error'; ?>">
                    <?php echo extension_loaded('pdo_mysql') ? '✅ SIM' : '❌ NÃO'; ?>
                </span>
            </div>
            <div class="item">
                <span class="label">PHP.ini usado:</span>
                <span class="value"><?php echo php_ini_loaded_file(); ?></span>
            </div>
        </div>

        <!-- VERIFICAÇÃO DE AMBIENTE -->
        <div class="section">
            <h2>✔️ Verificação Geral</h2>
            <?php
            $envCheck = checkEmailEnvironment();
            if ($envCheck['canSend']) {
                echo '<div class="item"><span class="label">Status Geral:</span><span class="value success">✅ PRONTO PARA ENVIAR E-MAILS</span></div>';
            } else {
                echo '<div class="item"><span class="label">Status Geral:</span><span class="value error">❌ PROBLEMAS DETECTADOS:</span></div>';
                foreach ($envCheck['errors'] as $error) {
                    echo '<div style="padding-left: 20px; margin-top: 5px;"><span class="error">• ' . htmlspecialchars($error) . '</span></div>';
                }
            }
            ?>
        </div>

        <!-- TESTE DE ENVIO -->
        <div class="section">
            <h2>📧 Teste de Envio</h2>
            <p style="margin-bottom: 10px;">Para testar o envio de e-mail, preencha os dados abaixo:</p>
            <form method="POST" style="background: #1a1a1a; padding: 15px; border-radius: 4px;">
                <div style="margin-bottom: 10px;">
                    <label style="display: block; margin-bottom: 5px;">E-mail de destino:</label>
                    <input type="email" name="test_email" placeholder="seu@email.com" style="width: 100%; padding: 8px; border: 1px solid #444; border-radius: 4px; background: #333; color: #fff;">
                </div>
                <div style="margin-bottom: 10px;">
                    <label style="display: block; margin-bottom: 5px;">Nome (opcional):</label>
                    <input type="text" name="test_name" placeholder="Seu Nome" style="width: 100%; padding: 8px; border: 1px solid #444; border-radius: 4px; background: #333; color: #fff;">
                </div>
                <button type="submit">Enviar E-mail de Teste</button>
            </form>

            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['test_email'])) {
                $testEmail = filter_var($_POST['test_email'], FILTER_VALIDATE_EMAIL);
                $testName = $_POST['test_name'] ?? 'Usuário Teste';

                if (!$testEmail) {
                    echo '<div class="item" style="margin-top: 15px;"><span class="error">❌ E-mail inválido!</span></div>';
                } else {
                    echo '<div style="background: #1a1a1a; padding: 15px; border-radius: 4px; margin-top: 15px;">';
                    echo '<h3 style="color: #00c50a; margin-bottom: 10px;">Enviando teste para: ' . htmlspecialchars($testEmail) . '</h3>';

                    $testResult = sendBrevoEmail(
                        $testEmail,
                        $testName,
                        'Teste de E-mail - GameConneCt',
                        '<h2>Olá ' . htmlspecialchars($testName) . '!</h2>' .
                        '<p>Este é um e-mail de teste do sistema GameConneCt.</p>' .
                        '<p>Se você recebeu este e-mail, a integração com Brevo está funcionando corretamente!</p>'
                    );

                    if ($testResult['success']) {
                        echo '<div class="item"><span class="success">✅ E-mail enviado com sucesso!</span></div>';
                        echo '<div class="item"><span class="label">ID da Mensagem:</span><span class="value">' . ($testResult['response']['id'] ?? 'N/A') . '</span></div>';
                    } else {
                        echo '<div class="item"><span class="error">❌ Falha ao enviar:</span></div>';
                        echo '<div style="padding-left: 20px; margin-top: 5px;"><span class="error">' . htmlspecialchars($testResult['error']) . '</span></div>';
                        if (!empty($testResult['response'])) {
                            echo '<div class="code"><strong>Resposta da API:</strong><br>' . htmlspecialchars(json_encode($testResult['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</div>';
                        }
                    }

                    echo '</div>';
                }
            }
            ?>
        </div>

        <!-- DICAS E SOLUÇÕES -->
        <div class="section">
            <h2>💡 Dicas de Solução de Problemas</h2>
            <ol style="padding-left: 20px;">
                <li><strong>cURL desabilitado:</strong> Edite C:\php\php.ini, encontre a linha <code>;extension=curl</code> e remova o ponto-e-vírgula. Reinicie o servidor.</li>
                <li><strong>E-mail não autorizado em Brevo:</strong> Acesse sua conta Brevo e verifique se o e-mail remetente está verificado em "Senders".</li>
                <li><strong>Domínio não verificado:</strong> Se estiver usando um domínio personalizado, adicione registros SPF e DKIM conforme instruções do Brevo.</li>
                <li><strong>API Key inválida:</strong> Gere uma nova chave em Brevo > Configurações > Chaves API.</li>
                <li><strong>Verificar logs:</strong> Procure por mensagens [BREVO_*] nos logs do PHP/servidor.</li>
            </ol>
        </div>

        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #444; color: #888;">
            <p>Diagnóstico gerado em <?php echo date('d/m/Y H:i:s'); ?></p>
            <p>⚠️ <strong>IMPORTANTE:</strong> Este arquivo deve ser removido ou protegido em produção por questões de segurança!</p>
        </div>
    </div>
</body>
</html>
