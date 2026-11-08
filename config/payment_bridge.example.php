<?php
/**
 * LIVETVMARKET - Payment Bridge Configuration Template
 * Copy to config/payment_bridge.php and fill in your shared secret.
 */

define('BRIDGE_SECRET_KEY', 'YOUR_SHARED_HMAC_SECRET_KEY');
define('BRIDGE_PROCESS_URL', 'https://payments.mabbas.dev/process.php');

function getStorefrontBaseUrl(): string {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    $protocol = $isHttps ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return rtrim($protocol . $host, '/');
}

function signBridgeData(array $params, string $secret): string {
    unset($params['signature'], $params['sign']);
    ksort($params);
    $query = http_build_query($params);
    return hash_hmac('sha256', $query, $secret);
}

function verifyBridgeWebhook(array $payload, string $signature, string $secret): bool {
    if (empty($signature)) {
        return false;
    }
    $expected = hash_hmac('sha256', json_encode($payload), $secret);
    return hash_equals($expected, trim($signature));
}

