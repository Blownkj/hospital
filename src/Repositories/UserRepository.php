<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\User;
use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare(
            'SELECT id, email, password_hash, role, created_at
             FROM users WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new User(
            id:           (int)$row['id'],
            email:        $row['email'],
            passwordHash: $row['password_hash'],
            role:         $row['role'],
            createdAt:    $row['created_at'],
        );
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare(
            'SELECT id, email, password_hash, role, created_at
             FROM users WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new User(
            id:           (int)$row['id'],
            email:        $row['email'],
            passwordHash: $row['password_hash'],
            role:         $row['role'],
            createdAt:    $row['created_at'],
        );
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM users WHERE email = ?'
        );
        $stmt->execute([$email]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Создаёт пользователя и сразу профиль пациента.
     * Всё в одной транзакции — или оба INSERT, или ни одного.
     */
    public function createPatient(
        string $email,
        string $password,
        string $fullName,
        string $birthDate,
        string $phone,
        string $gender,
    ): int {
        $this->db->beginTransaction();

        try {
            // 1. Создаём пользователя
            $stmt = $this->db->prepare(
                "INSERT INTO users (email, password_hash, role)
                 VALUES (?, ?, 'patient')"
            );
            $stmt->execute([
                $email,
                password_hash($password, PASSWORD_BCRYPT),
            ]);
            $userId = (int)$this->db->lastInsertId();

            // 2. Создаём профиль пациента
            $stmt = $this->db->prepare(
                'INSERT INTO patients (user_id, full_name, birth_date, phone, gender)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$userId, $fullName, $birthDate, $phone, $gender]);

            $this->db->commit();
            return $userId;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('createPatient error: ' . $e->getMessage());
            throw $e;
        }
    }
}