<?php

declare(strict_types=1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type, X-Bakong-Verify-Secret');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    http_response_code(204);
    exit;
}

header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'error' => 'Method not allowed.',
        'responseCode' => 1,
    ]);
    exit;
}

$sharedSecret = trim((string) getenv('BAKONG_VERIFY_SECRET'));
$providedSecret = trim((string) ($_SERVER['HTTP_X_BAKONG_VERIFY_SECRET'] ?? ''));

if ($sharedSecret !== '' && !hash_equals($sharedSecret, $providedSecret)) {
    http_response_code(403);
    echo json_encode([
        'error' => 'Forbidden.',
        'responseCode' => 1,
    ]);
    exit;
}

$rawBody = file_get_contents('php://input') ?: '';
$payload = json_decode($rawBody, true);

if (!is_array($payload)) {
    $payload = $_POST;
}

$md5 = trim((string) ($payload['md5'] ?? ''));

if ($md5 === '') {
    http_response_code(422);
    echo json_encode([
        'error' => 'Bakong MD5 cannot be blank.',
        'responseCode' => 1,
    ]);
    exit;
}

$token = trim((string) getenv('BAKONG_TOKEN'));
$apiUrl = trim((string) getenv('BAKONG_API_URL'));

if ($token === '') {
    http_response_code(500);
    echo json_encode([
        'error' => 'Bakong token is not configured.',
        'responseCode' => 1,
    ]);
    exit;
}

if ($apiUrl === '') {
    $apiUrl = 'https://api-bakong.nbc.gov.kh';
}

$url = rtrim($apiUrl, '/').'/v1/check_transaction_by_md5';

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['md5' => $md5], JSON_UNESCAPED_SLASHES),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer '.$token,
        'User-Agent: KHQR-Verify-Proxy/1.0',
    ],
    CURLOPT_TIMEOUT => 20,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
]);

$responseBody = curl_exec($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($responseBody === false || $curlError !== '') {
    http_response_code(502);
    echo json_encode([
        'error' => 'Unable to reach the Bakong API.',
        'responseCode' => 1,
    ]);
    exit;
}

$data = json_decode((string) $responseBody, true);

if (is_array($data)) {
    http_response_code(200);
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

http_response_code($httpCode > 0 ? $httpCode : 502);
echo json_encode([
    'error' => trim(strip_tags((string) $responseBody)),
    'responseCode' => 1,
], JSON_UNESCAPED_SLASHES);
