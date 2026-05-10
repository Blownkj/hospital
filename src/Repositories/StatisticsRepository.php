<?php
declare(strict_types=1);

namespace App\Repositories;

class StatisticsRepository extends BaseRepository
{
    protected string $table = '';

    public function getSpecializations(): array
    {
        return $this->db
            ->query("SELECT s.id, s.name, s.description,
                            COUNT(d.id) AS doctors_count
                     FROM specializations s
                     LEFT JOIN doctors d ON d.specialization_id = s.id AND d.is_active = 1
                     GROUP BY s.id, s.name, s.description
                     ORDER BY s.name")
            ->fetchAll();
    }

    public function getAverageRating(): float
    {
        $val = $this->db
            ->query("SELECT ROUND(AVG(rating), 1) FROM reviews WHERE is_approved = 1")
            ->fetchColumn();
        return $val !== false ? (float)$val : 0.0;
    }

    public function getPatientCount(): int
    {
        return (int)$this->db
            ->query("SELECT COUNT(*) FROM patients")
            ->fetchColumn();
    }

    public function getReviewCount(): int
    {
        return (int)$this->db
            ->query("SELECT COUNT(*) FROM reviews WHERE is_approved = 1")
            ->fetchColumn();
    }

    public function getLatestReviews(int $limit = 3): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.rating, r.review_text AS text, r.created_at,
                    CONCAT_WS(' ', p.last_name, p.first_name, p.middle_name) AS patient_name,
                    CONCAT_WS(' ', d.last_name, d.first_name, d.middle_name) AS doctor_name,
                    s.name AS specialization
             FROM reviews r
             JOIN patients p ON p.id = r.patient_id
             JOIN doctors d  ON d.id = r.doctor_id
             JOIN specializations s ON s.id = d.specialization_id
             WHERE r.is_approved = 1
             ORDER BY r.created_at DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
