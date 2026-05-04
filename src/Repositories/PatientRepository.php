<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class PatientRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Профиль пациента по user_id (из сессии)
     */
    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, u.email
             FROM patients p
             JOIN users u ON u.id = p.user_id
             WHERE p.user_id = ?
             LIMIT 1'
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Обновить хронические заболевания
     */
    public function updateChronicDiseases(int $patientId, string $text): void
    {
        $stmt = $this->db->prepare(
            'UPDATE patients SET chronic_diseases = ? WHERE id = ?'
        );
        $stmt->execute([$text, $patientId]);
    }
}