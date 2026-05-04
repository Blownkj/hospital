<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Middleware\AuthMiddleware;
use App\Services\AuthService;

class AuthController
{
    private AuthService $auth;

    public function __construct()
    {
        $this->auth = new AuthService();
    }

    // GET / — главная страница
    public function index(): void
    {
        if ($this->auth->isLoggedIn()) {
            AuthMiddleware::redirectToDashboard();
        }
        AuthMiddleware::redirect('/login');
    }

    // GET /login
    public function showLogin(): void
    {
        AuthMiddleware::requireGuest();
        View::render('auth/login', [
            'csrf'  => Session::generateCsrfToken(),
            'error' => Session::getFlash('error'),
        ]);
    }

    // POST /login
    public function doLogin(): void
    {
        AuthMiddleware::requireGuest();

        // CSRF-проверка
        $token = $_POST['csrf_token'] ?? '';
        if (!Session::validateCsrfToken($token)) {
            Session::setFlash('error', 'Недействительный токен. Попробуйте снова.');
            AuthMiddleware::redirect('/login');
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = $this->auth->login($email, $password);

        if ($user === null) {
            Session::setFlash('error', 'Неверный email или пароль.');
            AuthMiddleware::redirect('/login');
        }

        AuthMiddleware::redirectToDashboard();
    }

    // GET /register
    public function showRegister(): void
    {
        AuthMiddleware::requireGuest();
        View::render('auth/register', [
            'csrf'   => Session::generateCsrfToken(),
            'errors' => [],
            'old'    => [],
        ]);
    }

    // POST /register
    public function doRegister(): void
    {
        AuthMiddleware::requireGuest();

        $token = $_POST['csrf_token'] ?? '';
        if (!Session::validateCsrfToken($token)) {
            Session::setFlash('error', 'Недействительный токен. Попробуйте снова.');
            AuthMiddleware::redirect('/register');
        }

        $data = [
            'email'      => trim($_POST['email'] ?? ''),
            'password'   => $_POST['password'] ?? '',
            'password2'  => $_POST['password2'] ?? '',
            'full_name'  => trim($_POST['full_name'] ?? ''),
            'birth_date' => $_POST['birth_date'] ?? '',
            'phone'      => trim($_POST['phone'] ?? ''),
            'gender'     => $_POST['gender'] ?? '',
        ];

        $errors = $this->auth->register($data);

        if (!empty($errors)) {
            // Возвращаем на форму с ошибками, старые значения подставляем обратно
            View::render('auth/register', [
                'csrf'   => Session::generateCsrfToken(),
                'errors' => $errors,
                'old'    => $data,
            ]);
            return;
        }

        // Успешная регистрация — входим сразу
        $this->auth->login($data['email'], $data['password']);
        Session::setFlash('success', 'Добро пожаловать! Регистрация прошла успешно.');
        AuthMiddleware::redirect('/patient/dashboard');
    }

    // GET /logout
    public function logout(): void
    {
        $this->auth->logout();
        AuthMiddleware::redirect('/login');
    }

    // Временный дашборд — заглушка для всех ролей
    public function dashboard(): void
    {
        AuthMiddleware::requireAuth();

        $role  = Session::get('user_role');
        $email = Session::get('user_email');

        View::render('dashboard_stub', [
            'role'  => $role,
            'email' => $email,
        ]);
    }
}