<?php
declare(strict_types=1);

namespace App\Repositories;

class StatisticsRepository extends BaseRepository
{
    protected string $table = '';

    public function getSpecializations(): array
    {
        return $this->db
            ->query("SELECT id, name, description FROM specializations ORDER BY name")
            ->fetchAll();
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
                    p.full_name AS patient_name,
                    d.full_name AS doctor_name,
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
