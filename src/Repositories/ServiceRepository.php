<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class ServiceRepository extends BaseRepository
{
    protected string $table = 'services';

    /**
     * Все услуги, сгруппированные по специализации
     * Возвращает: [ 'Терапевт' => [ [...], [...] ], 'Кардиолог' => [...] ]
     */
    public function getAllGroupedBySpecialization(): array
    {
        $stmt = $this->db->query(
            'SELECT
                sv.id,
                sv.name,
                sv.price,
                sv.description,
                COALESCE(sp.name, "Общие") AS specialization
             FROM services sv
             LEFT JOIN specializations sp ON sp.id = sv.specialization_id
             ORDER BY specialization, sv.name'
        );
        $rows = $stmt->fetchAll();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['specialization']][] = $row;
        }
        return $grouped;
    }

    public function getAll(): array
    {
        return $this->db->query(
            'SELECT sv.*, sp.name AS specialization_name
            FROM services sv
            LEFT JOIN specializations sp ON sp.id = sv.specialization_id
            ORDER BY sp.name, sv.name'
        )->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM services WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(string $name, float $price, ?int $specId, string $desc): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO services (name, price, specialization_id, description)
            VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$name, $price, $specId ?: null, $desc]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $name, float $price, ?int $specId, string $desc): void
    {
        $stmt = $this->db->prepare(
            'UPDATE services
            SET name = ?, price = ?, specialization_id = ?, description = ?
            WHERE id = ?'
        );
        $stmt->execute([$name, $price, $specId ?: null, $desc, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM services WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
    
}