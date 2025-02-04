<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('X-Powered-By: Validonix/1.0');
header('X-RateLimit-Limit: 10');
header('X-RateLimit-Remaining: 9');

function sendResponse($statusCode, $data, $error = null)
{
    http_response_code($statusCode);

    $response = [
        'status' => $statusCode === 200 ? 'success' : 'error',
        'code' => $statusCode,
        'timestamp' => date('c'),
        'data' => $data,
        'metadata' => [
            'api_version' => '1.2.1',
            'author' => 'Validonix API',
            'docs' => 'http://localhost/'
        ]
    ];

    if ($error) {
        $response['error'] = $error;
        unset($response['data']);
    }

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(405, null, [
        'message' => 'Method not allowed',
        'allowed_methods' => ['GET']
    ]);
}

if (!isset($_GET['token'])) {
    sendResponse(400, null, [
        'message' => 'Missing required parameter: token',
        'documentation' => 'http://localhost/docs.php#token-check'
    ]);
}

$token = $_GET['token'];
$tokenPreview = substr($token, 0, 5) . '*****' . substr($token, -5);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://discord.com/api/v9/users/@me',
    CURLOPT_HTTPHEADER => [
        'Authorization: ' . $token,
        'User-Agent: Validonix/1.0 (+http://localhost)'
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_ENCODING => 'gzip'
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    sendResponse(500, null, [
        'message' => 'Gateway error',
        'internal_code' => 'CURL_' . curl_errno($ch),
        'details' => curl_error($ch)
    ]);
}

$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $userData = json_decode($body, true);

    // Calculate account creation date from user ID
    $discordEpoch = 1420070400000;
    $userId = (int) $userData['id'];
    $creationTimestamp = (($userId >> 22) + $discordEpoch) / 1000;

    $formattedUser = [
        'id' => $userData['id'],
        'username' => $userData['username'],
        'global_name' => $userData['global_name'] ?? null,
        'discriminator' => $userData['discriminator'],
        'avatar' => $userData['avatar'] ?
            'https://cdn.discordapp.com/avatars/' . $userData['id'] . '/' . $userData['avatar'] .
            (strpos($userData['avatar'], 'a_') === 0 ? '.gif' : '.png') : null,
        'banner' => $userData['banner'] ?
            'https://cdn.discordapp.com/banners/' . $userData['id'] . '/' . $userData['banner'] . '.png' : null,
        'accent_color' => $userData['accent_color'] ?? null,
        'email' => $userData['email'] ?? null,
        'verified' => $userData['verified'] ?? null,
        'mfa_enabled' => $userData['mfa_enabled'] ?? null,
        'flags' => $userData['flags'] ?? null,
        'public_flags' => $userData['public_flags'] ?? null,
        'premium_type' => $userData['premium_type'] ?? null,
        'creation_date' => date('Y-m-d\TH:i:s\Z', $creationTimestamp)
    ];

    $tokenInfo = [
        'token_preview' => $tokenPreview,
        'token_type' => strlen($token) > 59 ? 'User' : 'Bot',
        'authentication_method' => $userData['mfa_enabled'] ? 'MFA' : 'Password'
    ];

    sendResponse(200, [
        'authentication' => [
            'valid' => true,
            'token' => $tokenInfo
        ],
        'user' => $formattedUser
    ]);

} else {
    $errorData = json_decode($body, true);

    sendResponse($httpCode, null, [
        'message' => 'Token validation failed',
        'details' => $errorData['message'] ?? 'Unknown authentication error',
        'token_preview' => $tokenPreview,
        'resolution' => 'Check token validity or request new credentials',
        'documentation' => 'http://localhost/docs.php#token-errors'
    ]);
}