<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Models\User;
use App\Repositories\UserRepository;

class AuthService
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    /**
     * Попытка входа.
     * Возвращает null если неверные данные, иначе User.
     */
    public function login(string $email, string $password): ?User
    {
        $user = $this->users->findByEmail($email);

        if ($user === null) {
            return null;
        }

        if (!password_verify($password, $user->passwordHash)) {
            return null;
        }

        // Обновляем ID сессии после входа — защита от Session Fixation
        session_regenerate_id(true);

        Session::set('user_id',   $user->id);
        Session::set('user_role', $user->role);
        Session::set('user_email',$user->email);

        return $user;
    }

    /**
     * Регистрация нового пациента.
     * Возвращает массив ошибок (пустой = успех).
     */
    public function register(array $data): array
    {
        $errors = [];

        // Валидация
        $email     = trim($data['email'] ?? '');
        $password  = $data['password'] ?? '';
        $password2 = $data['password2'] ?? '';
        $fullName  = trim($data['full_name'] ?? '');
        $birthDate = $data['birth_date'] ?? '';
        $phone     = trim($data['phone'] ?? '');
        $gender    = $data['gender'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Введите корректный email.';
        } elseif ($this->users->emailExists($email)) {
            $errors['email'] = 'Этот email уже зарегистрирован.';
        }

        if (strlen($password) < 6) {
            $errors['password'] = 'Пароль — минимум 6 символов.';
        } elseif ($password !== $password2) {
            $errors['password2'] = 'Пароли не совпадают.';
        }

        if (strlen($fullName) < 2) {
            $errors['full_name'] = 'Введите полное имя.';
        }

        if (empty($birthDate) || !strtotime($birthDate)) {
            $errors['birth_date'] = 'Введите корректную дату рождения.';
        }

        if (!in_array($gender, ['m', 'f', 'other'], true)) {
            $errors['gender'] = 'Выберите пол.';
        }

        if (!empty($errors)) {
            return $errors;
        }

        // Создаём пользователя + пациента в транзакции
        try {
            $this->users->createPatient(
                $email, $password, $fullName, $birthDate, $phone, $gender
            );
        } catch (\Throwable) {
            $errors['general'] = 'Ошибка при регистрации. Попробуйте позже.';
        }

        return $errors;
    }

    public function logout(): void
    {
        Session::destroy();
    }

    public function isLoggedIn(): bool
    {
        return Session::has('user_id');
    }

    public function getRole(): ?string
    {
        return Session::get('user_role');
    }
}