<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;

class RateLimitMiddleware
{
    /**
     * Возвращает middleware-замыкание, считающее POST-запросы по ключу.
     * При превышении лимита — flash-ошибка + редирект назад.
     */
    public static function make(string $key, int $limit = 5, int $windowSeconds = 900): callable
    {
        return static function () use ($key, $limit, $windowSeconds): void {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return;
            }

            $now     = time();
            $count   = (int) Session::get("rl_{$key}_count", 0);
            $resetAt = (int) Session::get("rl_{$key}_reset", 0);

            if ($resetAt && $now > $resetAt) {
                $count   = 0;
                $resetAt = 0;
                Session::set("rl_{$key}_count", 0);
                Session::set("rl_{$key}_reset", 0);
            }

            if ($count >= $limit) {
                $wait = $resetAt > $now ? (int) ceil(($resetAt - $now) / 60) : 1;
                Session::setFlash('error', "Слишком много попыток. Подождите {$wait} мин.");
                AuthMiddleware::redirect($_SERVER['HTTP_REFERER'] ?? '/');
            }

            $count++;
            Session::set("rl_{$key}_count", $count);
            if ($count === 1) {
                Session::set("rl_{$key}_reset", $now + $windowSeconds);
            }
        };
    }
}
