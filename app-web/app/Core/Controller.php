<?php
declare(strict_types=1);

namespace App\Core;

use App\Services\SupabaseClient;

abstract class Controller
{
    /** @var array<string, mixed> */
    protected array $container;

    public function __construct(array $container = [])
    {
        $this->container = $container;
    }

    protected function config(): array
    {
        return $this->container['config'] ?? [];
    }

    protected function supabase(): SupabaseClient
    {
        return $this->container['supabase'];
    }

    protected function requireAuth(): void
    {
        if (!Cookie::get('auth_access_token')) {
            header('Location: /login', true, 302);
            exit;
        }
    }

    protected function render(string $view, array $data = []): void
    {
        View::render($view, $data + [
            '_app' => $this->config()['app'] ?? [],
            '_flash_error' => Flash::pull('error'),
            '_flash_success' => Flash::pull('success'),
            '_csrf' => Csrf::token(),
            '_auth' => [
                'user' => $this->authUser(),
            ],
        ]);
    }

    protected function redirect(string $to): void
    {
        header('Location: ' . $to, true, 302);
        exit;
    }

    protected function authAccessToken(): string
    {
        $t = Cookie::get('auth_access_token');
        return is_string($t) ? $t : '';
    }

    protected function authUser(): ?array
    {
        $raw = Cookie::get('auth_user');
        if (!is_string($raw) || $raw === '') {
            return null;
        }
        $decoded = base64_decode($raw, true);
        if (!is_string($decoded) || $decoded === '') {
            return null;
        }
        $json = json_decode($decoded, true);
        return is_array($json) ? $json : null;
    }

    protected function extractError(array $res): string
    {
        $json = $res['json'] ?? null;
        if (is_array($json)) {
            $msg = $json['message'] ?? $json['msg'] ?? null;
            if (is_string($msg) && $msg !== '') {
                $details = $json['details'] ?? null;
                if (is_string($details) && $details !== '') {
                    return $msg . ' (' . $details . ')';
                }
                return $msg;
            }
        }

        $body = $res['body'] ?? '';
        if (is_string($body) && $body !== '') {
            return $body;
        }

        return 'Error desconocido.';
    }

    protected function syncProfileIfPossible(): ?string
    {
        $accessToken = $this->authAccessToken();
        if ($accessToken === '') {
            return null;
        }

        $res = $this->supabase()->rpc('ensure_my_profile', [], $accessToken);

        if ($res['ok'] || (int)($res['status'] ?? 0) === 409) {
            return null;
        }

        return $this->extractError($res);
    }
}
