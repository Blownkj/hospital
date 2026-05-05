<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;

class CsrfMiddleware
{
    public static function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Недействительный CSRF-токен. Попробуйте снова.');
            AuthMiddleware::redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }
    }
}
