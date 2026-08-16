<?php
include_once __DIR__ . '/config.php';

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    $remainder = strlen($data) % 4;
    if ($remainder) {
        $padlen = 4 - $remainder;
        $data .= str_repeat('=', $padlen);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

function jwt_encode($payload, $exp_seconds = 604800) { // default 7 days
    $header = ['alg' => JWT_ALGO, 'typ' => 'JWT'];
    $payload['iat'] = time();
    $payload['exp'] = time() + $exp_seconds;

    $header_b64 = base64url_encode(json_encode($header));
    $payload_b64 = base64url_encode(json_encode($payload));

    $signature = hash_hmac('sha256', "$header_b64.$payload_b64", JWT_SECRET, true);
    $signature_b64 = base64url_encode($signature);

    return "$header_b64.$payload_b64.$signature_b64";
}

function jwt_decode($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;

    list($header_b64, $payload_b64, $signature_b64) = $parts;
    $header = json_decode(base64url_decode($header_b64), true);
    $payload = json_decode(base64url_decode($payload_b64), true);
    $signature = base64url_decode($signature_b64);

    if (!$header || !$payload || !$signature) return false;

    // verify alg
    if (!isset($header['alg']) || $header['alg'] !== JWT_ALGO) return false;

    $expected = hash_hmac('sha256', "$header_b64.$payload_b64", JWT_SECRET, true);

    // timing-safe comparison
    if (!hash_equals($expected, $signature)) return false;

    // check exp
    if (isset($payload['exp']) && time() > $payload['exp']) return false;

    return $payload;
}
?>