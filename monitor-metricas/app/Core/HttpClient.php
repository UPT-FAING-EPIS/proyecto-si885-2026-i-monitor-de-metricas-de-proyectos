<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class HttpClient
{
    /** @param array<string,string> $query @param array<string,mixed>|null $json */
    public function request(string $method, string $url, array $headers = [], array $query = [], ?array $json = null): array
    {
        $method = strtoupper($method);
        $qs = '';
        if (!empty($query)) {
            $qs = http_build_query($query);
        }
        $fullUrl = $qs === '' ? $url : ($url . (str_contains($url, '?') ? '&' : '?') . $qs);

        $attempt = 0;
        while (true) {
            $attempt++;
            $resp = $this->send($method, $fullUrl, $headers, $json);
            if (($resp['status'] ?? 0) !== 429 || $attempt >= 4) {
                return $resp;
            }
            $retryAfter = (int)($resp['headers']['retry-after'] ?? 0);
            $sleep = $retryAfter > 0 ? $retryAfter : min(2 ** $attempt, 8);
            sleep($sleep);
        }
    }

    /** @param array<string,string> $headers @param array<string,mixed>|null $json */
    private function send(string $method, string $url, array $headers, ?array $json): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('Extensión cURL no disponible en PHP.');
        }

        $ch = curl_init();
        if ($ch === false) {
            throw new RuntimeException('No se pudo inicializar HTTP client.');
        }

        $hdrs = [];
        foreach ($headers as $k => $v) {
            $hdrs[] = $k . ': ' . $v;
        }
        if ($json !== null) {
            $hdrs[] = 'Content-Type: application/json';
        }

        $responseHeaders = [];
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $hdrs,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HEADERFUNCTION => static function ($curl, string $headerLine) use (&$responseHeaders): int {
                $len = strlen($headerLine);
                $parts = explode(':', $headerLine, 2);
                if (count($parts) === 2) {
                    $name = strtolower(trim($parts[0]));
                    $value = trim($parts[1]);
                    if ($name !== '') {
                        $responseHeaders[$name] = $value;
                    }
                }
                return $len;
            },
        ]);

        if ($json !== null) {
            $payload = json_encode($json, JSON_UNESCAPED_UNICODE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload === false ? '{}' : $payload);
        }

        $body = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            return ['ok' => false, 'status' => $status ?: 0, 'headers' => $responseHeaders, 'error' => $err ?: 'Error de red'];
        }

        $decoded = json_decode((string)$body, true);
        $ok = $status >= 200 && $status < 300;
        return [
            'ok' => $ok,
            'status' => $status,
            'headers' => $responseHeaders,
            'body' => (string)$body,
            'data' => $decoded,
        ];
    }
}

