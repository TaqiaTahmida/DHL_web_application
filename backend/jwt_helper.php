<?php
// backend/jwt_helper.php

class JWT {
    private static $secret = 'dhl_super_secret_key_123!';

    public static function encode($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function decode($jwt) {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) return false;

        list($header64, $payload64, $signature64) = $parts;

        $signature = hash_hmac('sha256', $header64 . "." . $payload64, self::$secret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        if (hash_equals($base64UrlSignature, $signature64)) {
            $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $payload64));
            return json_decode($payload, true);
        }

        return false;
    }
}
?>
