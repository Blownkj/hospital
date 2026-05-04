<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class ReviewRepository extends BaseRepository
{
    protected string $table = 'reviews';

    /** Уже оставил ли пациент отзыв на этого врача? */
    public function exists(int $patientId, int $doctorId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM reviews WHERE patient_id = ? AND doctor_id = ?"
        );
        $stmt->execute([$patientId, $doctorId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** Создать отзыв */
    public function create(int $patientId, int $doctorId, int $rating, string $review_text): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO reviews (patient_id, doctor_id, rating, review_text, is_approved, created_at)
             VALUES (?, ?, ?, ?, 0, NOW())"
        );
        $stmt->execute([$patientId, $doctorId, $rating, $review_text]);
    }

    /** Одобренные отзывы для страницы врача */
    public function getApprovedForDoctor(int $doctorId): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.id, r.rating, r.review_text AS text, r.created_at,
                    r.admin_reply, r.admin_reply_at,
                    p.full_name AS patient_name
             FROM reviews r
             JOIN patients p ON p.id = r.patient_id
             WHERE r.doctor_id = ? AND r.is_approved = 1
             ORDER BY r.created_at DESC"
        );
        $stmt->execute([$doctorId]);
        return $stmt->fetchAll();
    }

    /** Средний рейтинг врача (только одобренные) */
    public function getAverageRating(int $doctorId): float
    {
        $stmt = $this->db->prepare(
            "SELECT AVG(rating) FROM reviews WHERE doctor_id = ? AND is_approved = 1"
        );
        $stmt->execute([$doctorId]);
        return round((float) $stmt->fetchColumn(), 1);
    }

    /** Список врачей у которых пациент завершил приём (для формы отзыва) */
    public function getDoctorsVisited(int $patientId): array
    {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT d.id, d.full_name, s.name AS specialization
             FROM appointments a
             JOIN doctors d ON d.id = a.doctor_id
             JOIN specializations s ON s.id = d.specialization_id
             WHERE a.patient_id = ? AND a.status = 'completed'
             ORDER BY d.full_name"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }

    /** Отзывы пациента */
    public function getByPatient(int $patientId): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.id, r.rating, r.review_text AS text, r.is_approved, r.created_at,
                    r.admin_reply, r.admin_reply_at,
                    d.full_name AS doctor_name, s.name AS specialization
            FROM reviews r
            JOIN doctors d ON d.id = r.doctor_id
            JOIN specializations s ON s.id = d.specialization_id
            WHERE r.patient_id = ?
            ORDER BY r.created_at DESC"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }

    /**
     * Рейтинги для массива doctorId.
     * Возвращает: [ doctorId => ['avg_rating' => float, 'review_count' => int], ... ]
     */
    public function ratingsByDoctorIds(array $doctorIds): array
    {
        if (empty($doctorIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($doctorIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT doctor_id,
                    ROUND(AVG(rating), 1) AS avg_rating,
                    COUNT(*) AS review_count
             FROM reviews
             WHERE doctor_id IN ({$placeholders}) AND is_approved = 1
             GROUP BY doctor_id"
        );
        $stmt->execute($doctorIds);
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[(int)$row['doctor_id']] = [
                'avg_rating'   => (float)$row['avg_rating'],
                'review_count' => (int)$row['review_count'],
            ];
        }
        return $result;
    }
}