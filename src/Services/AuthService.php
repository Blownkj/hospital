<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Logger;
use App\Core\Session;
use App\Core\Validator;
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
            // Run dummy verify to equalise response time (timing attack mitigation)
            password_verify($password, '$2y$12$invaliddummyhashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
            Logger::get()->warning('Login failed: unknown email', ['email' => $email]);
            return null;
        }

        if (!password_verify($password, $user->passwordHash)) {
            Logger::get()->warning('Login failed: wrong password', ['email' => $email]);
            return null;
        }

        if (password_needs_rehash($user->passwordHash, PASSWORD_DEFAULT)) {
            $this->users->rehashPassword($user->id, $password);
        }

        if ($user->role === 'doctor' && !$this->users->isDoctorActive($user->id)) {
            Logger::get()->warning('Login failed: doctor is deactivated', ['user_id' => $user->id]);
            return null;
        }

        // Обновляем ID сессии после входа — защита от Session Fixation
        session_regenerate_id(true);

        Session::set('user_id',   $user->id);
        Session::set('user_role', $user->role);
        Session::set('user_email',$user->email);

        Logger::get()->info('User logged in', ['user_id' => $user->id, 'role' => $user->role]);
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
        $email      = trim($data['email'] ?? '');
        $password   = $data['password'] ?? '';
        $password2  = $data['password2'] ?? '';
        $lastName   = trim($data['last_name']   ?? '');
        $firstName  = trim($data['first_name']  ?? '');
        $middleName = trim($data['middle_name'] ?? '');
        $birthDate  = $data['birth_date'] ?? '';
        $phone      = trim($data['phone'] ?? '');
        $gender     = $data['gender'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Введите корректный email.';
        } elseif ($this->users->emailExists($email)) {
            $errors['email'] = 'Этот email уже зарегистрирован.';
        }

        if (!Validator::password($password)) {
            $errors['password'] = 'Пароль — минимум 8 символов.';
        } elseif ($password !== $password2) {
            $errors['password2'] = 'Пароли не совпадают.';
        }

        if (mb_strlen($lastName) < 2) {
            $errors['last_name'] = 'Введите фамилию.';
        }
        if (mb_strlen($firstName) < 2) {
            $errors['first_name'] = 'Введите имя.';
        }

        if (!Validator::dateInPast($birthDate)) {
            $errors['birth_date'] = 'Введите корректную дату рождения (в прошлом).';
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
                $email, $password, $lastName, $firstName, $middleName ?: null, $birthDate, $phone, $gender
            );
            Logger::get()->info('New patient registered', ['email' => $email]);
        } catch (\Throwable $e) {
            Logger::get()->error('Registration failed', ['email' => $email, 'error' => $e->getMessage()]);
            $errors['general'] = 'Ошибка при регистрации. Попробуйте позже.';
        }

        return $errors;
    }

    public function logout(): void
    {
        $userId = Session::get('user_id');
        Logger::get()->info('User logged out', ['user_id' => $userId]);
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