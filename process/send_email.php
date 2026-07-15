<?php
require_once __DIR__ . '/../config/email_config.php';

/**
 * Função para obter a URL base da aplicação
 * Usada para construir links de redefinição de senha nos e-mails
 */
function getBaseUrl()
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptPath = $_SERVER['PHP_SELF'] ?? '';
    $basePath = rtrim(dirname(dirname($scriptPath)), '/\\');
    $basePath .= '/html';

    return $scheme . '://' . $host . $basePath . '/';
}

/**
 * Função para verificar se o ambiente está configurado para envio de e-mails
 * Retorna array com 'canSend' (bool) e 'error' (string se houver problema)
 */
function checkEmailEnvironment()
{
    $errors = [];

    // Verificar configurações básicas
    if (!defined('BREVO_API_KEY') || empty(BREVO_API_KEY) || BREVO_API_KEY === 'COLOQUE_SUA_CHAVE_AQUI') {
        $errors[] = 'BREVO_API_KEY não configurada ou inválida em email_config.php';
    }

    if (!defined('BREVO_SENDER_EMAIL') || empty(BREVO_SENDER_EMAIL) || BREVO_SENDER_EMAIL === 'no-reply@seudominio.com') {
        $errors[] = 'BREVO_SENDER_EMAIL não configurada ou inválida em email_config.php';
    }

    if (!defined('BREVO_SENDER_NAME') || empty(BREVO_SENDER_NAME)) {
        $errors[] = 'BREVO_SENDER_NAME não configurada em email_config.php';
    }

    // Verificar disponibilidade de transporte HTTP
    if (!extension_loaded('curl') && !ini_get('allow_url_fopen')) {
        $errors[] = 'cURL não está habilitado e allow_url_fopen está desabilitado. Habilite pelo menos um em php.ini';
    }

    if (!extension_loaded('curl') && !function_exists('openssl_verify')) {
        $errors[] = 'OpenSSL não está habilitado. Necessário para HTTPS com file_get_contents';
    }

    return [
        'canSend' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Função principal para envio de e-mail via API Brevo
 * 
 * @param string $toEmail E-mail do destinatário
 * @param string $toName Nome do destinatário
 * @param string $subject Assunto do e-mail
 * @param string $htmlContent Conteúdo HTML do e-mail
 * @param string|null $textContent Conteúdo em texto puro (opcional)
 * 
 * @return array Array com 'success' (bool), 'error' (string), 'response' (array)
 */
function sendBrevoEmail($toEmail, $toName, $subject, $htmlContent, $textContent = null)
{
    // Verificar ambiente primeiro
    $envCheck = checkEmailEnvironment();
    if (!$envCheck['canSend']) {
        return [
            'success' => false,
            'error' => 'Configuração de e-mail inválida: ' . implode('; ', $envCheck['errors']),
            'response' => null
        ];
    }

    // Validar parâmetros
    if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return [
            'success' => false,
            'error' => 'E-mail do destinatário inválido: ' . $toEmail,
            'response' => null
        ];
    }

    // Preparar payload para API Brevo
    $payload = [
        'sender' => [
            'name' => BREVO_SENDER_NAME,
            'email' => BREVO_SENDER_EMAIL,
        ],
        'to' => [
            [
                'email' => $toEmail,
                'name' => $toName ?: 'Usuário',
            ]
        ],
        'subject' => $subject,
        'htmlContent' => $htmlContent,
        'textContent' => $textContent ?? strip_tags($htmlContent),
    ];

    $payloadJson = json_encode($payload);
    if ($payloadJson === false) {
        return [
            'success' => false,
            'error' => 'Erro ao codificar payload JSON: ' . json_last_error_msg(),
            'response' => null
        ];
    }

    $headers = [
        'accept: application/json',
        'content-type: application/json',
        'api-key: ' . BREVO_API_KEY,
    ];

    $responseBody = null;
    $responseCode = 0;

    // Tentar com cURL primeiro (mais confiável)
    if (extension_loaded('curl')) {
        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        if ($ch === false) {
            return [
                'success' => false,
                'error' => 'Erro ao inicializar cURL',
                'response' => null
            ];
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $payloadJson,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => false, // Alterado para false devido ao ambiente local do Windows
            CURLOPT_SSL_VERIFYHOST => 0,     // Alterado para 0 acompanhando o VERIFYPEER
        ]);

        $responseBody = curl_exec($ch);
        $responseCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log('[BREVO_CURL_ERROR] ' . $curlError);
            return [
                'success' => false,
                'error' => 'Erro cURL: ' . $curlError,
                'response' => ['curl_error' => $curlError]
            ];
        }
    } else {
        // Fallback para file_get_contents com stream
        if (!ini_get('allow_url_fopen')) {
            return [
                'success' => false,
                'error' => 'cURL desabilitado e allow_url_fopen desabilitado em php.ini',
                'response' => null
            ];
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers) . "\r\n",
                'content' => $payloadJson,
                'timeout' => 20,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => false,      // Alterado para false devido ao ambiente local do Windows
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ]);

        $responseBody = @file_get_contents('https://api.brevo.com/v3/smtp/email', false, $context);
        
        if ($responseBody === false) {
            $lastError = error_get_last();
            error_log('[BREVO_STREAM_ERROR] ' . ($lastError['message'] ?? 'Erro desconhecido'));
            return [
                'success' => false,
                'error' => 'Falha ao conectar à API Brevo: ' . ($lastError['message'] ?? 'Erro desconhecido'),
                'response' => ['stream_error' => $lastError]
            ];
        }

        // Extrair código HTTP da resposta
        if (!empty($http_response_header)) {
            if (preg_match('#HTTP/[\d.]+\s+(\d+)#', $http_response_header[0], $matches)) {
                $responseCode = (int) $matches[1];
            }
        }
    }

    // Analisar resposta
    $responseData = null;
    if (!empty($responseBody)) {
        $responseData = json_decode($responseBody, true);
    }

    // Sucesso (status 200-299)
    if ($responseCode >= 200 && $responseCode < 300) {
        error_log('[BREVO_SUCCESS] E-mail enviado para: ' . $toEmail . ' | ID: ' . ($responseData['id'] ?? 'N/A'));
        return [
            'success' => true,
            'response' => $responseData
        ];
    }

    // Erro da API
    $errorMsg = "Falha ao enviar e-mail via Brevo (HTTP $responseCode)";
    if (is_array($responseData)) {
        if (!empty($responseData['message'])) {
            $errorMsg .= ": " . $responseData['message'];
        }
        if (!empty($responseData['code'])) {
            $errorMsg .= " [Código: " . $responseData['code'] . "]";
        }
    }

    error_log('[BREVO_API_ERROR] ' . $errorMsg . ' | Destinatário: ' . $toEmail . ' | Resposta: ' . $responseBody);

    return [
        'success' => false,
        'error' => $errorMsg,
        'response' => $responseData,
        'statusCode' => $responseCode,
        'rawResponse' => $responseBody
    ];
}
?>
