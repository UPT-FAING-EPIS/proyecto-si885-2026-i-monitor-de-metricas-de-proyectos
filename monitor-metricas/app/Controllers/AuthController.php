<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

final class AuthController extends Controller
{
    public function showLogin(Request $request, Response $response): void
    {
        if (!empty($_SESSION['user'])) {
            $response->redirect('/dashboard');
        }

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $this->render('pages/login', [
            'csrf' => $_SESSION['csrf'] ?? '',
            'flash' => $flash,
        ]);
    }

    public function login(Request $request, Response $response): void
    {
        if (($request->csrf() ?: '') !== ($_SESSION['csrf'] ?? null)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Sesión inválida. Intenta nuevamente.'];
            $response->redirect('/login');
        }

        $email = trim((string)$request->input('email', ''));
        $password = (string)$request->input('password', '');

        if ($email === '' || $password === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Ingresa un correo válido y tu contraseña.'];
            $response->redirect('/login');
        }

        $result = $this->supabaseLogin($email, $password);
        if ($result['ok'] !== true) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => (string)($result['message'] ?? 'No se pudo iniciar sesión.')];
            $response->redirect('/login');
        }

        $_SESSION['user'] = (array)($result['user'] ?? ['email' => $email]);
        $_SESSION['supabase'] = (array)($result['session'] ?? []);

        $response->redirect('/dashboard');
    }

    public function showRegister(Request $request, Response $response): void
    {
        if (!empty($_SESSION['user'])) {
            $response->redirect('/dashboard');
        }

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $this->render('pages/register', [
            'csrf' => $_SESSION['csrf'] ?? '',
            'flash' => $flash,
        ]);
    }

    public function register(Request $request, Response $response): void
    {
        if (($request->csrf() ?: '') !== ($_SESSION['csrf'] ?? null)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Sesión inválida. Intenta nuevamente.'];
            $response->redirect('/register');
        }

        $fullName = trim((string)$request->input('full_name', ''));
        $email = trim((string)$request->input('email', ''));
        $password = (string)$request->input('password', '');
        $confirm = (string)$request->input('password_confirm', '');

        if ($fullName === '') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Ingresa tu nombre completo.'];
            $response->redirect('/register');
        }
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Ingresa un correo válido.'];
            $response->redirect('/register');
        }
        if ($password === '' || strlen($password) < 6) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'La contraseña debe tener al menos 6 caracteres.'];
            $response->redirect('/register');
        }
        if ($password !== $confirm) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Las contraseñas no coinciden.'];
            $response->redirect('/register');
        }

        $result = $this->supabaseRegister($fullName, $email, $password);
        if ($result['ok'] !== true) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => (string)($result['message'] ?? 'No se pudo crear la cuenta.')];
            $response->redirect('/register');
        }

        if (!empty($result['session'])) {
            $_SESSION['user'] = (array)($result['user'] ?? ['email' => $email]);
            $_SESSION['supabase'] = (array)$result['session'];
            $response->redirect('/dashboard');
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Cuenta creada. Revisa tu correo para confirmar el acceso.'];
        $response->redirect('/login');
    }

    public function logout(Request $request, Response $response): void
    {
        if (($request->csrf() ?: '') !== ($_SESSION['csrf'] ?? null)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Sesión inválida. Intenta nuevamente.'];
            $response->redirect('/login');
        }

        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $response->redirect('/login');
    }

    /** @return array{ok:bool,message?:string,user?:array<string,mixed>,session?:array<string,mixed>} */
    private function supabaseLogin(string $email, string $password): array
    {
        $url = $this->env('SUPABASE_URL');
        $anon = $this->env('SUPABASE_ANON_KEY');
        if ($url === '' || $anon === '') {
            return ['ok' => false, 'message' => 'Supabase no está configurado (SUPABASE_URL / SUPABASE_ANON_KEY).'];
        }

        $endpoint = rtrim($url, '/') . '/auth/v1/token?grant_type=password';
        $resp = $this->httpJson('POST', $endpoint, [
            'email' => $email,
            'password' => $password,
        ], [
            'apikey: ' . $anon,
            'Authorization: Bearer ' . $anon,
        ]);

        if ($resp['ok'] !== true) {
            return ['ok' => false, 'message' => (string)($resp['message'] ?? 'Credenciales inválidas.')];
        }

        $data = (array)($resp['data'] ?? []);
        $user = (array)($data['user'] ?? []);
        $session = [
            'access_token' => (string)($data['access_token'] ?? ''),
            'refresh_token' => (string)($data['refresh_token'] ?? ''),
            'token_type' => (string)($data['token_type'] ?? ''),
            'expires_in' => (int)($data['expires_in'] ?? 0),
        ];

        return [
            'ok' => true,
            'user' => [
                'id' => (string)($user['id'] ?? ''),
                'email' => (string)($user['email'] ?? $email),
                'name' => $this->extractDisplayName($user),
            ],
            'session' => $session,
        ];
    }

    /** @return array{ok:bool,message?:string,user?:array<string,mixed>,session?:array<string,mixed>} */
    private function supabaseRegister(string $fullName, string $email, string $password): array
    {
        $url = $this->env('SUPABASE_URL');
        $anon = $this->env('SUPABASE_ANON_KEY');
        if ($url === '' || $anon === '') {
            return ['ok' => false, 'message' => 'Supabase no está configurado (SUPABASE_URL / SUPABASE_ANON_KEY).'];
        }

        $endpoint = rtrim($url, '/') . '/auth/v1/signup';
        $resp = $this->httpJson('POST', $endpoint, [
            'data' => [
                'full_name' => $fullName,
            ],
            'email' => $email,
            'password' => $password,
        ], [
            'apikey: ' . $anon,
            'Authorization: Bearer ' . $anon,
        ]);

        if ($resp['ok'] !== true) {
            return ['ok' => false, 'message' => (string)($resp['message'] ?? 'No se pudo crear la cuenta.')];
        }

        $data = (array)($resp['data'] ?? []);
        $user = (array)($data['user'] ?? $data);
        $sessionRaw = (array)($data['session'] ?? []);
        $session = [];
        if (!empty($sessionRaw)) {
            $session = [
                'access_token' => (string)($sessionRaw['access_token'] ?? ''),
                'refresh_token' => (string)($sessionRaw['refresh_token'] ?? ''),
                'token_type' => (string)($sessionRaw['token_type'] ?? ''),
                'expires_in' => (int)($sessionRaw['expires_in'] ?? 0),
            ];
        }

        return [
            'ok' => true,
            'user' => [
                'id' => (string)($user['id'] ?? ''),
                'email' => (string)($user['email'] ?? $email),
                'name' => $this->extractDisplayName($user, $fullName),
            ],
            'session' => $session,
        ];
    }

    /** @param array<string,mixed> $user */
    private function extractDisplayName(array $user, string $fallback = ''): string
    {
        $metadata = isset($user['user_metadata']) && is_array($user['user_metadata']) ? $user['user_metadata'] : [];
        $candidates = [
            (string)($metadata['full_name'] ?? ''),
            (string)($metadata['name'] ?? ''),
            (string)($user['full_name'] ?? ''),
            (string)($user['name'] ?? ''),
            $fallback,
        ];
        foreach ($candidates as $candidate) {
            $candidate = trim($candidate);
            if ($candidate !== '') {
                return $candidate;
            }
        }
        return '';
    }

    /** @param array<string,mixed> $body @param list<string> $extraHeaders @return array{ok:bool,status:int,data?:mixed,message?:string} */
    private function httpJson(string $method, string $url, array $body, array $extraHeaders = []): array
    {
        if (!function_exists('curl_init')) {
            return ['ok' => false, 'status' => 0, 'message' => 'Extensión cURL no disponible en PHP.'];
        }

        $ch = curl_init();
        if ($ch === false) {
            return ['ok' => false, 'status' => 0, 'message' => 'No se pudo inicializar HTTP client.'];
        }

        $headers = array_merge([
            'Content-Type: application/json',
        ], $extraHeaders);

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);

        $payload = json_encode($body, JSON_UNESCAPED_UNICODE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload === false ? '{}' : $payload);

        $raw = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            return ['ok' => false, 'status' => $status ?: 0, 'message' => $err ?: 'Error de red.'];
        }

        $data = json_decode((string)$raw, true);
        if ($status < 200 || $status >= 300) {
            $msg = '';
            if (is_array($data)) {
                $msg = (string)($data['msg'] ?? $data['message'] ?? $data['error_description'] ?? $data['error'] ?? '');
            }
            if ($msg === '') {
                $msg = 'Error de autenticación.';
            }
            return ['ok' => false, 'status' => $status, 'data' => $data, 'message' => $msg];
        }

        return ['ok' => true, 'status' => $status, 'data' => $data];
    }

    private function env(string $key): string
    {
        $v = (string)($_ENV[$key] ?? $_SERVER[$key] ?? '');
        if ($v !== '') {
            return $v;
        }

        $g = getenv($key);
        if ($g !== false && $g !== '') {
            return (string)$g;
        }

        static $cache = null;
        if ($cache === null) {
            $cache = [];
            $candidates = [];
            $candidates[] = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . '.env';
            $cwd = getcwd();
            if (is_string($cwd) && $cwd !== '') {
                $candidates[] = rtrim($cwd, "\\/") . DIRECTORY_SEPARATOR . '.env';
                $candidates[] = dirname($cwd) . DIRECTORY_SEPARATOR . '.env';
                $candidates[] = dirname($cwd, 2) . DIRECTORY_SEPARATOR . '.env';
            }

            $envFile = null;
            foreach ($candidates as $candidate) {
                if (is_string($candidate) && is_file($candidate) && is_readable($candidate)) {
                    $envFile = $candidate;
                    break;
                }
            }

            if ($envFile !== null) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES);
                if (is_array($lines)) {
                    foreach ($lines as $line) {
                        $line = trim((string)$line);
                        if ($line === '' || str_starts_with($line, '#')) {
                            continue;
                        }
                        $pos = strpos($line, '=');
                        if ($pos === false) {
                            continue;
                        }
                        $k = trim(substr($line, 0, $pos));
                        if ($k === '') {
                            continue;
                        }
                        $val = trim(substr($line, $pos + 1));
                        if ($val !== '' && (($val[0] === '"' && str_ends_with($val, '"')) || ($val[0] === "'" && str_ends_with($val, "'")))) {
                            $val = substr($val, 1, -1);
                        }
                        $cache[$k] = $val;
                        if (getenv($k) === false) {
                            putenv($k . '=' . $val);
                        }
                        if (!isset($_ENV[$k]) || (string)$_ENV[$k] === '') {
                            $_ENV[$k] = $val;
                        }
                        if (!isset($_SERVER[$k]) || (string)$_SERVER[$k] === '') {
                            $_SERVER[$k] = $val;
                        }
                    }
                }
            }
        }

        return (string)($cache[$key] ?? '');
    }
}
