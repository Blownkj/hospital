<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\DoctorProfile;
use PDO;

class DoctorRepository extends BaseRepository
{
    protected string $table = 'doctors';

    private function doctorSelectBase(): string
    {
        return 'SELECT
                    d.id,
                    d.user_id,
                    d.full_name,
                    d.bio,
                    d.photo_url,
                    d.is_active,
                    d.specialization_id,
                    s.name AS specialization,
                    u.email,
                    ROUND(AVG(r.rating), 1) AS avg_rating,
                    COUNT(r.id)             AS review_count
                FROM doctors d
                JOIN specializations s ON s.id = d.specialization_id
                JOIN users u           ON u.id = d.user_id
                LEFT JOIN reviews r    ON r.doctor_id = d.id AND r.is_approved = 1';
    }

    /** Все врачи со специализацией и средним рейтингом. @return DoctorProfile[] */
    public function getAllWithRating(): array
    {
        $stmt = $this->db->query(
            $this->doctorSelectBase() . '
            GROUP BY d.id, d.user_id, d.full_name, d.bio, d.photo_url,
                     d.is_active, d.specialization_id, s.name, u.email
            ORDER BY s.name, d.full_name'
        );
        return array_map(
            fn(array $row) => DoctorProfile::fromRow($row),
            $stmt->fetchAll()
        );
    }

    /** Один врач по id. */
    public function findById(int $id): ?DoctorProfile
    {
        $stmt = $this->db->prepare(
            $this->doctorSelectBase() . '
            WHERE d.id = ?
            GROUP BY d.id, d.user_id, d.full_name, d.bio, d.photo_url,
                     d.is_active, d.specialization_id, s.name, u.email
            LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? DoctorProfile::fromRow($row) : null;
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

    /** Поиск врачей по имени и/или специализации. @return DoctorProfile[] */
    public function search(string $query = '', int $specId = 0, int $limit = 0, int $offset = 0): array
    {
        $sql = $this->doctorSelectBase() . ' WHERE 1=1';
        $params = $this->buildSearchParams($sql, $query, $specId);

        $sql .= ' GROUP BY d.id, d.user_id, d.full_name, d.bio, d.photo_url,
                           d.is_active, d.specialization_id, s.name, u.email
                  ORDER BY s.name, d.full_name';

        if ($limit > 0) {
            $sql .= ' LIMIT ? OFFSET ?';
            $params[] = $limit;
            $params[] = $offset;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return array_map(
            fn(array $row) => DoctorProfile::fromRow($row),
            $stmt->fetchAll()
        );
    }

    /** Кол-во врачей для пагинации */
    public function countSearch(string $query = '', int $specId = 0): int
    {
        $sql = 'SELECT COUNT(DISTINCT d.id) FROM doctors d
                JOIN specializations s ON s.id = d.specialization_id
                JOIN users u           ON u.id = d.user_id
                WHERE 1=1';
        $params = $this->buildSearchParams($sql, $query, $specId);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    private function buildSearchParams(string &$sql, string $query, int $specId): array
    {
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
        return $params;
    }

    /** Все специализации — для фильтра */
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
