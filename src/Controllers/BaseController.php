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
            throw new \RuntimeException('CSRF-токен недействителен.');
        }
    }

    protected function redirectWith(string $path, string $type, string $msg): never
    {
        Session::setFlash($type, $msg);
        AuthMiddleware::redirect($path);
    }

    protected function redirectBack(string $fallback = '/'): never
    {
        $ref  = $_SERVER['HTTP_REFERER'] ?? '';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $parsed = $ref !== '' ? parse_url($ref) : false;
        // Only follow referer if it points to the same host (prevents open redirect)
        $back = ($parsed && ($parsed['host'] ?? '') === $host)
            ? ($parsed['path'] ?? $fallback)
            : $fallback;
        AuthMiddleware::redirect($back);
    }

    protected function requireOwner(bool $condition, string $msg = 'Доступ запрещён.'): void
    {
        if (!$condition) {
            http_response_code(403);
            throw new \RuntimeException($msg);
        }
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
}
