<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class ReviewRepository extends BaseRepository
{
    protected string $table = 'reviews';

    /** Уже оставлен ли отзыв на данный приём? */
    public function existsByAppointment(int $appointmentId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM reviews WHERE appointment_id = ?"
        );
        $stmt->execute([$appointmentId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** Создать отзыв */
    public function create(int $patientId, int $doctorId, int $appointmentId, int $rating, string $review_text): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO reviews (patient_id, doctor_id, appointment_id, rating, review_text, is_approved, created_at)
             VALUES (?, ?, ?, ?, ?, 0, NOW())"
        );
        $stmt->execute([$patientId, $doctorId, $appointmentId, $rating, $review_text]);
    }

    /** Одобренные отзывы для страницы врача */
    public function getApprovedForDoctor(int $doctorId): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.id, r.rating, r.review_text AS text, r.created_at,
                    r.admin_reply, r.admin_reply_at,
                    CONCAT_WS(' ', p.last_name, p.first_name, p.middle_name) AS patient_name
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

    /** Завершённые приёмы пациента, на которые ещё нет отзыва (для формы отзыва) */
    public function getCompletedWithoutReview(int $patientId): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.id AS appointment_id, a.scheduled_at,
                    d.id AS doctor_id,
                    CONCAT_WS(' ', d.last_name, d.first_name, d.middle_name) AS full_name,
                    s.name AS specialization
             FROM appointments a
             JOIN doctors d ON d.id = a.doctor_id
             JOIN specializations s ON s.id = d.specialization_id
             LEFT JOIN reviews r ON r.appointment_id = a.id
             WHERE a.patient_id = ? AND a.status = 'completed' AND r.id IS NULL
             ORDER BY a.scheduled_at DESC"
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
                    CONCAT_WS(' ', d.last_name, d.first_name, d.middle_name) AS doctor_name,
                    s.name AS specialization
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