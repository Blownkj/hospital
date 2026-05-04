<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class DoctorRepository extends BaseRepository
{
    protected string $table = 'doctors';

    /**
     * Все врачи со специализацией и средним рейтингом
     */
    public function getAllWithRating(): array
    {
        $stmt = $this->db->query(
            'SELECT
                d.id,
                d.full_name,
                d.bio,
                d.photo_url,
                d.specialization_id,
                s.name AS specialization,
                ROUND(AVG(r.rating), 1) AS avg_rating,
                COUNT(r.id)             AS review_count
            FROM doctors d
            JOIN specializations s ON s.id = d.specialization_id
            LEFT JOIN reviews r    ON r.doctor_id = d.id AND r.is_approved = 1
            GROUP BY d.id, d.full_name, d.bio, d.photo_url, d.specialization_id, s.name
            ORDER BY s.name, d.full_name'
        );
        return $stmt->fetchAll();
    }

    /**
     * Один врач по id — для страницы врача (понадобится позже)
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT
                d.id,
                d.full_name,
                d.bio,
                d.photo_url,
                d.specialization_id,
                s.name AS specialization,
                ROUND(AVG(r.rating), 1) AS avg_rating,
                COUNT(r.id)             AS review_count
            FROM doctors d
            JOIN specializations s ON s.id = d.specialization_id
            LEFT JOIN reviews r ON r.doctor_id = d.id AND r.is_approved = 1
            WHERE d.id = ?
            GROUP BY d.id, d.full_name, d.bio, d.photo_url, d.specialization_id, s.name
            LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
    
    public function getApprovedReviews(int $doctorId): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.rating, r.review_text AS text, r.created_at,
                    p.full_name AS patient_name
             FROM reviews r
             JOIN patients p ON p.id = r.patient_id
             WHERE r.doctor_id = ? AND r.is_approved = 1
             ORDER BY r.created_at DESC
             LIMIT 20"
        );
        $stmt->execute([$doctorId]);
        return $stmt->fetchAll();
    }

    

    /**
     * Поиск врачей по имени и/или специализации
     */
    public function search(string $query = '', int $specId = 0): array
    {
        $sql = 'SELECT
                    d.id,
                    d.full_name,
                    d.bio,
                    d.photo_url,
                    d.specialization_id,
                    s.name AS specialization,
                    ROUND(AVG(r.rating), 1) AS avg_rating,
                    COUNT(r.id)             AS review_count
                FROM doctors d
                JOIN specializations s ON s.id = d.specialization_id
                LEFT JOIN reviews r    ON r.doctor_id = d.id AND r.is_approved = 1
                WHERE 1=1';

        $params = [];

        if ($query !== '') {
            $sql .= ' AND (d.full_name LIKE ? OR s.name LIKE ?)';
            $params[] = '%' . $query . '%';
            $params[] = '%' . $query . '%';
        }

        if ($specId > 0) {
            $sql .= ' AND d.specialization_id = ?';
            $params[] = $specId;
        }

        $sql .= ' GROUP BY d.id, d.full_name, d.bio, d.photo_url, d.specialization_id, s.name
                ORDER BY s.name, d.full_name';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Все специализации — для фильтра
     */
    public function getAllSpecializations(): array
    {
        return $this->db->query(
            'SELECT id, name FROM specializations ORDER BY name'
        )->fetchAll();
    }

    public function update(int $doctorId, string $bio, string $photoUrl): void
    {
        $stmt = $this->db->prepare(
            "UPDATE doctors SET bio = ?, photo_url = ? WHERE id = ?"
        );
        $stmt->execute([$bio, $photoUrl, $doctorId]);
    }

    /** Расписание врача */
    public function getSchedule(int $doctorId): array
    {
        $stmt = $this->db->prepare(
            "SELECT day_of_week, start_time, end_time, slot_duration_min
            FROM schedules WHERE doctor_id = ? ORDER BY day_of_week"
        );
        $stmt->execute([$doctorId]);
        return $stmt->fetchAll();
    }
}