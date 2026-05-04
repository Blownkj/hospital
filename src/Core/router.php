<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    // ── Регистрация маршрутов ─────────────────────────────────────────────

    public function get(string $path, array $handler): void
    {
        $this->routes[] = ['GET', $path, $handler];
    }

    public function post(string $path, array $handler): void
    {
        $this->routes[] = ['POST', $path, $handler];
    }

    // ── Запуск маршрутизатора ─────────────────────────────────────────────

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Убираем базовый путь (например /hospital/public)
        // dirname(SCRIPT_NAME) = /hospital/public
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = '/' . trim($uri, '/');
        if ($uri === '') {
            $uri = '/';
        }
        

        foreach ($this->routes as [$routeMethod, $routePath, $handler]) {
            if ($routeMethod !== $method) {
                continue;
            }

            // Конвертируем путь в regex: /user/{id} → /user/([^/]+)
            // Имена параметров могут быть в camelCase: {visitId}, {doctorId}
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // убираем полное совпадение

                [$controllerClass, $method_name] = $handler;

                if (!class_exists($controllerClass)) {
                    die("Контроллер не найден: {$controllerClass}");
                }

                $controller = new $controllerClass();

                if (!method_exists($controller, $method_name)) {
                    die("Метод не найден: {$controllerClass}::{$method_name}");
                }

                $controller->$method_name(...$matches);
                return;
            }
        }

        // Маршрут не найден — 404
        http_response_code(404);
        View::render('errors/404');
    }
}