<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\User;
use PDO;

class UserRepository extends BaseRepository
{
    protected string $table = 'users';

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

    public function rehashPassword(int $userId, string $plainPassword): void
    {
        $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([password_hash($plainPassword, PASSWORD_DEFAULT), $userId]);
    }

    public function isDoctorActive(int $userId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT is_active FROM doctors WHERE user_id = ? LIMIT 1'
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row !== false && (bool)$row['is_active'];
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
        string  $email,
        string  $password,
        string  $lastName,
        string  $firstName,
        ?string $middleName,
        string  $birthDate,
        string  $phone,
        string  $gender,
    ): int {
        return \App\Core\Database::transaction(function () use (
            $email, $password, $lastName, $firstName, $middleName, $birthDate, $phone, $gender
        ): int {
            $stmt = $this->db->prepare(
                "INSERT INTO users (email, password_hash, role)
                 VALUES (?, ?, 'patient')"
            );
            $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT)]);
            $userId = (int) $this->db->lastInsertId();

            $stmt = $this->db->prepare(
                'INSERT INTO patients (user_id, last_name, first_name, middle_name, birth_date, phone, gender)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$userId, $lastName, $firstName, $middleName, $birthDate, $phone, $gender]);

            return $userId;
        });
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $stmt = $this->db->prepare(
            "SELECT password_hash FROM users WHERE id = ? LIMIT 1"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE users SET password_hash = ? WHERE id = ?"
        );
        $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
        return true;
    }
}