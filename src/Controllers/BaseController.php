<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Middleware\AuthMiddleware;

abstract class BaseController
{
    protected function validateCsrf(): void
    {
        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            http_response_code(419);
            die('CSRF-токен недействителен.');
        }
    }

    protected function redirectWith(string $path, string $type, string $msg): never
    {
        Session::setFlash($type, $msg);
        AuthMiddleware::redirect($path);
    }

    protected function redirectBack(string $fallback = '/'): never
    {
        $back = $_SERVER['HTTP_REFERER'] ?? $fallback;
        AuthMiddleware::redirect($back);
    }

    protected function requireOwner(bool $condition, string $msg = 'Доступ запрещён.'): void
    {
        if (!$condition) {
            http_response_code(403);
            die($msg);
        }
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
}
