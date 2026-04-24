<?php
declare(strict_types=1);

namespace App\Services;

final class SupabaseClient
{
    public function __construct(
        private string $supabaseUrl,
        private string $anonKey,
    ) {}

    public function signUp(string $email, string $password): array
    {
        return $this->requestJson(
            method: 'POST',
            url: $this->supabaseUrl . '/auth/v1/signup',
            headers: [
                'apikey: ' . $this->anonKey,
            ],
            body: [
                'email' => $email,
                'password' => $password,
            ],
        );
    }

    public function signInWithPassword(string $email, string $password): array
    {
        return $this->requestJson(
            method: 'POST',
            url: $this->supabaseUrl . '/auth/v1/token?grant_type=password',
            headers: [
                'apikey: ' . $this->anonKey,
            ],
            body: [
                'email' => $email,
                'password' => $password,
            ],
        );
    }

    public function postgrestSelect(string $resource, array $query, string $accessToken): array
    {
        $url = $this->supabaseUrl . '/rest/v1/' . ltrim($resource, '/');
        if ($query) {
            $url .= '?' . http_build_query($query, arg_separator: '&', encoding_type: PHP_QUERY_RFC3986);
        }

        return $this->requestJson(
            method: 'GET',
            url: $url,
            headers: [
                'apikey: ' . $this->anonKey,
                'Authorization: Bearer ' . $accessToken,
            ],
        );
    }

    public function postgrestInsert(string $resource, array $rows, string $accessToken, bool $returnRepresentation = true): array
    {
        $prefer = $returnRepresentation ? 'return=representation' : 'return=minimal';

        return $this->requestJson(
            method: 'POST',
            url: $this->supabaseUrl . '/rest/v1/' . ltrim($resource, '/'),
            headers: [
                'apikey: ' . $this->anonKey,
                'Authorization: Bearer ' . $accessToken,
                'Prefer: ' . $prefer,
            ],
            body: $rows,
        );
    }

    public function postgrestPatch(string $resource, array $query, array $changes, string $accessToken, bool $returnRepresentation = true): array
    {
        $url = $this->supabaseUrl . '/rest/v1/' . ltrim($resource, '/');
        if ($query) {
            $url .= '?' . http_build_query($query, arg_separator: '&', encoding_type: PHP_QUERY_RFC3986);
        }

        $prefer = $returnRepresentation ? 'return=representation' : 'return=minimal';

        return $this->requestJson(
            method: 'PATCH',
            url: $url,
            headers: [
                'apikey: ' . $this->anonKey,
                'Authorization: Bearer ' . $accessToken,
                'Prefer: ' . $prefer,
            ],
            body: $changes,
        );
    }

    public function rpc(string $fn, array $args, string $accessToken): array
    {
        $body = $args ? $args : new \stdClass();

        return $this->requestJson(
            method: 'POST',
            url: $this->supabaseUrl . '/rest/v1/rpc/' . $fn,
            headers: [
                'apikey: ' . $this->anonKey,
                'Authorization: Bearer ' . $accessToken,
            ],
            body: $body,
        );
    }

    public function storageUpload(string $bucket, string $path, string $bytes, string $contentType, string $accessToken): array
    {
        $url = $this->supabaseUrl . '/storage/v1/object/' . rawurlencode($bucket) . '/' . str_replace('%2F', '/', rawurlencode($path));

        return $this->requestRaw(
            method: 'POST',
            url: $url,
            headers: [
                'apikey: ' . $this->anonKey,
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: ' . $contentType,
                'x-upsert: true',
            ],
            body: $bytes,
        );
    }

    public function storageCreateSignedUrl(string $bucket, string $path, int $expiresInSeconds, string $accessToken): array
    {
        return $this->requestJson(
            method: 'POST',
            url: $this->supabaseUrl . '/storage/v1/object/sign/' . rawurlencode($bucket) . '/' . str_replace('%2F', '/', rawurlencode($path)),
            headers: [
                'apikey: ' . $this->anonKey,
                'Authorization: Bearer ' . $accessToken,
            ],
            body: [
                'expiresIn' => $expiresInSeconds,
            ],
        );
    }

    private function requestJson(string $method, string $url, array $headers, mixed $body = null): array
    {
        $headers[] = 'Content-Type: application/json';
        $rawBody = $body === null ? null : json_encode($body, JSON_UNESCAPED_SLASHES);

        $result = $this->requestRaw($method, $url, $headers, $rawBody);
        $decoded = json_decode($result['body'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $result + ['json' => null];
        }
        return $result + ['json' => $decoded];
    }

    private function requestRaw(string $method, string $url, array $headers, ?string $body = null): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADER => true,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return [
                'ok' => false,
                'status' => 0,
                'headers' => [],
                'body' => $error,
            ];
        }

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $rawHeaders = substr($response, 0, $headerSize);
        $bodyStr = substr($response, $headerSize);

        return [
            'ok' => $status >= 200 && $status < 300,
            'status' => $status,
            'headers' => $this->parseHeaders($rawHeaders),
            'body' => $bodyStr,
        ];
    }

    private function parseHeaders(string $rawHeaders): array
    {
        $lines = preg_split("/\r\n|\n|\r/", trim($rawHeaders));
        if (!is_array($lines)) {
            return [];
        }
        $headers = [];
        foreach ($lines as $line) {
            if (str_contains($line, ':') === false) {
                continue;
            }
            [$k, $v] = explode(':', $line, 2);
            $headers[strtolower(trim($k))] = trim($v);
        }
        return $headers;
    }
}
