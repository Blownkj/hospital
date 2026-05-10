<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;

class AuthMiddleware
{
    /**
     * Требует, чтобы пользователь был залогинен.
     * Если нет — редирект на /login.
     */
    public static function requireAuth(): void
    {
        if (!Session::has('user_id')) {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if ($uri !== '' && !str_contains($uri, '/login') && !str_contains($uri, '/logout')) {
                $_SESSION['auth_return_url'] = $uri;
            }
            Session::setFlash('error', 'Войдите в систему для доступа.');
            self::redirect('/login');
        }
    }

    /**
     * Требует конкретную роль.
     * Если роль не совпадает — редирект на dashboard пользователя.
     */
    public static function requireRole(string $role): void
    {
        self::requireAuth();

        if (Session::get('user_role') !== $role) {
            self::redirect('/');
        }
    }

    /**
     * Редирект гостя — если уже залогинен, отправляем на дашборд.
     */
    public static function requireGuest(): void
    {
        if (Session::has('user_id')) {
            self::redirectToDashboard();
        }
    }

    public static function redirectToDashboard(): never
    {
        $role = Session::get('user_role', '');
        match ($role) {
            'patient' => self::redirect('/patient/dashboard'),
            'doctor'  => self::redirect('/doctor/dashboard'),
            'admin'   => self::redirect('/admin/dashboard'),
            default   => self::redirect('/login'),
        };
    }

    public static function redirect(string $path): never
    {
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        header('Location: ' . $base . $path);
        exit;
    }
}