<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class Crypto
{
    private string $key;

    public function __construct(string $appKey)
    {
        $appKey = trim($appKey);
        if ($appKey === '') {
            throw new RuntimeException('APP_KEY no configurada.');
        }
        $this->key = hash('sha256', $appKey, true);
    }

    public function encrypt(string $plaintext): string
    {
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($plaintext, 'aes-256-gcm', $this->key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($cipher === false) {
            throw new RuntimeException('No se pudo encriptar.');
        }
        return rtrim(strtr(base64_encode($iv . $tag . $cipher), '+/', '-_'), '=');
    }

    public function decrypt(string $payload): string
    {
        $raw = base64_decode(strtr($payload, '-_', '+/'), true);
        if ($raw === false || strlen($raw) < 12 + 16 + 1) {
            throw new RuntimeException('Token inválido.');
        }
        $iv = substr($raw, 0, 12);
        $tag = substr($raw, 12, 16);
        $cipher = substr($raw, 28);
        $plain = openssl_decrypt($cipher, 'aes-256-gcm', $this->key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($plain === false) {
            throw new RuntimeException('No se pudo desencriptar.');
        }
        return $plain;
    }
}

