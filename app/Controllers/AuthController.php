<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Cookie;
use App\Core\Flash;

final class AuthController extends Controller
{
    public function showRegister(): void
    {
        $this->render('auth.register');
    }

    public function register(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'CSRF';
            return;
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            Flash::set('error', 'Email y contraseña son obligatorios.');
            $this->redirect('/register');
        }

        $res = $this->supabase()->signUp($email, $password);
        if (!$res['ok']) {
            Flash::set('error', $this->extractError($res));
            $this->redirect('/register');
        }

        Flash::set('success', 'Registro creado. Si tu proyecto tiene confirmación por email, revisa tu bandeja.');
        $this->redirect('/login');
    }

    public function showLogin(): void
    {
        $this->render('auth.login');
    }

    public function login(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'CSRF';
            return;
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            Flash::set('error', 'Email y contraseña son obligatorios.');
            $this->redirect('/login');
        }

        $res = $this->supabase()->signInWithPassword($email, $password);
        $json = $res['json'] ?? null;
        if (!$res['ok'] || !is_array($json) || !isset($json['access_token'])) {
            Flash::set('error', $this->extractError($res));
            $this->redirect('/login');
        }

        $accessToken = (string)$json['access_token'];
        Cookie::set('auth_access_token', $accessToken, 60 * 60 * 8);
        Cookie::set('auth_refresh_token', (string)($json['refresh_token'] ?? ''), 60 * 60 * 24 * 7);
        Cookie::set('auth_user', base64_encode(json_encode($json['user'] ?? null, JSON_UNESCAPED_SLASHES)), 60 * 60 * 24 * 7);

        $user = is_array($json['user'] ?? null) ? $json['user'] : null;
        $userId = is_array($user) ? (string)($user['id'] ?? '') : '';
        $userEmail = is_array($user) ? (string)($user['email'] ?? '') : '';
        if ($userId !== '' && $userEmail !== '') {
            $profileRes = $this->supabase()->postgrestInsert('profiles', [
                [
                    'id' => $userId,
                    'email' => $userEmail,
                ],
            ], $accessToken, returnRepresentation: false);

            if (!$profileRes['ok'] && (int)($profileRes['status'] ?? 0) !== 409) {
                Flash::set('error', 'Login OK, pero no se pudo sincronizar el perfil: ' . $this->extractError($profileRes));
            }
        }

        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'CSRF';
            return;
        }

        Cookie::forget('auth_access_token');
        Cookie::forget('auth_refresh_token');
        Cookie::forget('auth_user');
        header('Location: /login', true, 302);
        exit;
    }

}
