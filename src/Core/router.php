<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $globalMiddlewares = [];
    private string $groupPrefix = '';
    private array $groupMiddlewares = [];

    // ── Регистрация маршрутов ─────────────────────────────────────────────

    /** Регистрирует глобальный middleware, выполняемый перед группой маршрута. */
    public function use(callable $mw): void
    {
        $this->globalMiddlewares[] = $mw;
    }

    public function get(string $path, array $handler, array $extraMiddlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $extraMiddlewares);
    }

    public function post(string $path, array $handler, array $extraMiddlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $extraMiddlewares);
    }

    /**
     * Группирует маршруты под общим префиксом и набором middleware (callable[]).
     * Группы могут быть вложенными: middlewares накапливаются.
     */
    public function group(string $prefix, array $middlewares, callable $callback): void
    {
        $prevPrefix = $this->groupPrefix;
        $prevMw     = $this->groupMiddlewares;

        $this->groupPrefix      = $prevPrefix . $prefix;
        $this->groupMiddlewares = array_merge($prevMw, $middlewares);

        $callback($this);

        $this->groupPrefix      = $prevPrefix;
        $this->groupMiddlewares = $prevMw;
    }

    private function addRoute(string $method, string $path, array $handler, array $extraMiddlewares = []): void
    {
        $this->routes[] = [
            $method,
            $this->groupPrefix . $path,
            $handler,
            array_merge($this->groupMiddlewares, $extraMiddlewares),
        ];
    }

    // ── Запуск маршрутизатора ─────────────────────────────────────────────

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Убираем базовый путь (например /hospital/public)
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = '/' . trim($uri, '/');
        if ($uri === '') {
            $uri = '/';
        }

        foreach ($this->routes as [$routeMethod, $routePath, $handler, $middlewares]) {
            if ($routeMethod !== $method) {
                continue;
            }

            // /user/{id} → /user/([^/]+)
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                // Middleware chain: сначала глобальные, затем группы/маршрута
                foreach ($this->globalMiddlewares as $gmw) {
                    $gmw();
                }
                foreach ($middlewares as $mw) {
                    $mw();
                }

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
