<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class LabTestRepository extends BaseRepository
{
    protected string $table = 'lab_tests';

    /**
     * Все анализы, сгруппированные по категории
     */
    public function getAllGrouped(): array
    {
        $rows = $this->db->query(
            'SELECT id, name, category, description, preparation, price, duration_min
             FROM lab_tests ORDER BY category, name'
        )->fetchAll();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['category']][] = $row;
        }
        return $grouped;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM lab_tests WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Занятые слоты лаборатории на дату
     * (общая очередь — doctor_id IS NULL, appointment_type = 'lab_test')
     */
    public function getBookedTimes(string $date): array
    {
        $stmt = $this->db->prepare(
            "SELECT TIME_FORMAT(scheduled_at, '%H:%i') AS t
             FROM appointments
             WHERE appointment_type = 'lab_test'
               AND DATE(scheduled_at) = ?
               AND status NOT IN ('cancelled')"
        );
        $stmt->execute([$date]);
        return array_column($stmt->fetchAll(), 't');
    }

    public function getAll(): array
    {
        return $this->db->query(
            'SELECT id, name, category, description, preparation, price, duration_min
             FROM lab_tests ORDER BY category, name'
        )->fetchAll();
    }

    public function create(string $name, string $category, float $price, int $durationMin, string $desc, string $prep): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO lab_tests (name, category, price, duration_min, description, preparation)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $category, $price, $durationMin, $desc, $prep]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $name, string $category, float $price, int $durationMin, string $desc, string $prep): void
    {
        $stmt = $this->db->prepare(
            'UPDATE lab_tests SET name=?, category=?, price=?, duration_min=?, description=?, preparation=?
             WHERE id=?'
        );
        $stmt->execute([$name, $category, $price, $durationMin, $desc, $prep, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM lab_tests WHERE id=?');
        $stmt->execute([$id]);
    }

    /**
     * Создать запись на анализ
     */
    public function bookTest(int $patientId, int $labTestId, string $scheduledAt): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO appointments
                (patient_id, appointment_type, doctor_id, lab_test_id, scheduled_at, status)
             VALUES (?, 'lab_test', NULL, ?, ?, 'confirmed')"
        );
        $stmt->execute([$patientId, $labTestId, $scheduledAt]);
        return (int)$this->db->lastInsertId();
    }
}