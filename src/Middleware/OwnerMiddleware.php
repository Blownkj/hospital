<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Database;
use App\Core\Session;

class OwnerMiddleware
{
    /**
     * Возвращает middleware-замыкание, которое:
     * 1. Пытается извлечь ID ресурса из текущего URI по $uriPattern (первая группа захвата).
     * 2. Если URI не совпадает — пропускает (шаблон не для этого пути).
     * 3. Если совпадает — вызывает $ownerCheck(resourceId, userId); при false → 403.
     *
     * @param string   $uriPattern  Regex с одной группой захвата для ID, например '#/visit/(\d+)/#'
     * @param callable $ownerCheck  fn(int $resourceId, int $userId): bool
     */
    public static function make(string $uriPattern, callable $ownerCheck): callable
    {
        return static function () use ($uriPattern, $ownerCheck): void {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if (!preg_match($uriPattern, $uri, $m)) {
                return;
            }

            $resourceId = (int) $m[1];
            $userId     = (int) Session::get('user_id');

            if (!$ownerCheck($resourceId, $userId)) {
                http_response_code(403);
                die('Доступ запрещён.');
            }
        };
    }

    /**
     * Проверяет, что визит с $visitId принадлежит текущему пользователю-пациенту.
     * visits → appointments.patient_id → patients.user_id
     */
    public static function visit(): callable
    {
        return static::make(
            '#/visit/(\d+)/#',
            static function (int $visitId, int $userId): bool {
                $stmt = Database::getInstance()->prepare(
                    'SELECT 1
                     FROM visits v
                     JOIN appointments a ON a.id = v.appointment_id
                     JOIN patients p     ON p.id = a.patient_id
                     WHERE v.id = ? AND p.user_id = ?'
                );
                $stmt->execute([$visitId, $userId]);
                return (bool) $stmt->fetch();
            }
        );
    }
}
