<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class AdminRepository extends BaseRepository
{
    protected string $table = 'appointments';

    // ── Записи ───────────────────────────────────────────────────────────────

    public function getAllAppointments(string $status = '', string $date = ''): array
    {
        $where = ['1=1'];
        $params = [];

        if ($status) {
            $where[] = 'a.status = ?';
            $params[] = $status;
        }
        if ($date) {
            $where[] = 'a.scheduled_at >= ? AND a.scheduled_at < DATE_ADD(?, INTERVAL 1 DAY)';
            $params[] = $date;
            $params[] = $date;
        }

        $sql = "SELECT a.id, a.scheduled_at, a.status, a.appointment_type,
                       p.full_name AS patient_name, p.phone AS patient_phone,
                       d.full_name AS doctor_name,
                       s.name AS specialization
                FROM appointments a
                JOIN patients p ON p.id = a.patient_id
                LEFT JOIN doctors d ON d.id = a.doctor_id
                LEFT JOIN specializations s ON s.id = d.specialization_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY a.scheduled_at DESC
                LIMIT 200";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAppointments(string $status = '', string $date = ''): int
    {
        $where  = ['1=1'];
        $params = [];

        if ($status) {
            $where[]  = 'a.status = ?';
            $params[] = $status;
        }
        if ($date) {
            $where[]  = 'a.scheduled_at >= ? AND a.scheduled_at < DATE_ADD(?, INTERVAL 1 DAY)';
            $params[] = $date;
            $params[] = $date;
        }

        $sql  = "SELECT COUNT(*) FROM appointments a WHERE " . implode(' AND ', $where);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getAllAppointmentsPaginated(
        int    $limit,
        int    $offset,
        string $status = '',
        string $date   = ''
    ): array {
        $where  = ['1=1'];
        $params = [];

        if ($status) {
            $where[]  = 'a.status = ?';
            $params[] = $status;
        }
        if ($date) {
            $where[]  = 'a.scheduled_at >= ? AND a.scheduled_at < DATE_ADD(?, INTERVAL 1 DAY)';
            $params[] = $date;
            $params[] = $date;
        }

        $sql = "SELECT a.id, a.scheduled_at, a.status, a.appointment_type,
                       p.full_name AS patient_name, p.phone AS patient_phone,
                       d.full_name AS doctor_name,
                       s.name AS specialization
                FROM appointments a
                JOIN patients p ON p.id = a.patient_id
                LEFT JOIN doctors d ON d.id = a.doctor_id
                LEFT JOIN specializations s ON s.id = d.specialization_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY a.scheduled_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findAppointmentById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, p.full_name AS patient_name, d.full_name AS doctor_name
             FROM appointments a
             JOIN patients p ON p.id = a.patient_id
             LEFT JOIN doctors d ON d.id = a.doctor_id
             WHERE a.id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ── CRUD врачей ───────────────────────────────────────────────────────────
    public function getAllSpecializations(): array
    {
        return $this->db->query(
            "SELECT id, name FROM specializations ORDER BY name"
        )->fetchAll();
    }

    public function createDoctor(
        string $email,
        string $passwordHash,
        string $fullName,
        int    $specializationId,
        string $bio
    ): int {
        // 1. Создаём пользователя
        $this->db->prepare(
            "INSERT INTO users (email, password_hash, role)
            VALUES (?, ?, 'doctor')"
        )->execute([$email, $passwordHash]);

        $userId = (int) $this->db->lastInsertId();

        // 2. Создаём профиль врача
        $this->db->prepare(
            "INSERT INTO doctors (user_id, full_name, specialization_id, bio)
            VALUES (?, ?, ?, ?)"
        )->execute([$userId, $fullName, $specializationId, $bio]);

        return (int) $this->db->lastInsertId();
    }

    public function updateDoctor(
        int    $doctorId,
        string $fullName,
        int    $specializationId,
        string $bio
    ): void {
        $this->db->prepare(
            "UPDATE doctors
            SET full_name = ?, specialization_id = ?, bio = ?
            WHERE id = ?"
        )->execute([$fullName, $specializationId, $bio, $doctorId]);
    }

    public function findDoctorById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT d.*, s.name AS specialization, u.email
            FROM doctors d
            JOIN specializations s ON s.id = d.specialization_id
            JOIN users u ON u.id = d.user_id
            WHERE d.id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deactivateDoctor(int $doctorId): void
    {
        $this->db->prepare(
            "UPDATE doctors SET is_active = 0 WHERE id = ?"
        )->execute([$doctorId]);
    }

    public function activateDoctor(int $doctorId): void
    {
        $this->db->prepare(
            "UPDATE doctors SET is_active = 1 WHERE id = ?"
        )->execute([$doctorId]);
    }

    public function updateAppointmentStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare(
            "UPDATE appointments SET status = ? WHERE id = ?"
        );
        $stmt->execute([$status, $id]);
    }

    public function rescheduleAppointment(int $id, string $newDatetime): void
    {
        $stmt = $this->db->prepare(
            "UPDATE appointments SET scheduled_at = ?, status = 'confirmed' WHERE id = ?"
        );
        $stmt->execute([$newDatetime, $id]);
    }

    // ── Врачи ─────────────────────────────────────────────────────────────────

    public function getAllDoctors(): array
    {
        $stmt = $this->db->query(
            "SELECT d.id, d.full_name, d.bio, d.photo_url, d.user_id,
                    s.name AS specialization, s.id AS specialization_id,
                    u.email, u.role
            FROM doctors d
            JOIN specializations s ON s.id = d.specialization_id
            JOIN users u ON u.id = d.user_id
            ORDER BY d.full_name"
        );
        return $stmt->fetchAll();
    }

    public function getDoctorSchedule(int $doctorId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM schedules WHERE doctor_id = ? ORDER BY day_of_week"
        );
        $stmt->execute([$doctorId]);
        return $stmt->fetchAll();
    }

    public function upsertSchedule(
        int $doctorId,
        int $dayOfWeek,
        string $startTime,
        string $endTime,
        int $slotDuration
    ): void {
        $stmt = $this->db->prepare(
            "INSERT INTO schedules (doctor_id, day_of_week, start_time, end_time, slot_duration_min)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
               start_time = VALUES(start_time),
               end_time = VALUES(end_time),
               slot_duration_min = VALUES(slot_duration_min)"
        );
        $stmt->execute([$doctorId, $dayOfWeek, $startTime, $endTime, $slotDuration]);
    }

    public function deleteScheduleDay(int $doctorId, int $dayOfWeek): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM schedules WHERE doctor_id = ? AND day_of_week = ?"
        );
        $stmt->execute([$doctorId, $dayOfWeek]);
    }

    // ── Статистика ────────────────────────────────────────────────────────────

    public function getStats(): array
    {
        $stats = [];

        $stats['total_patients'] = (int) $this->db->query(
            "SELECT COUNT(*) FROM patients"
        )->fetchColumn();

        $stats['total_appointments'] = (int) $this->db->query(
            "SELECT COUNT(*) FROM appointments"
        )->fetchColumn();

        $stats['appointments_today'] = (int) $this->db->query(
            "SELECT COUNT(*) FROM appointments
             WHERE scheduled_at >= CURDATE() AND scheduled_at < DATE_ADD(CURDATE(), INTERVAL 1 DAY)"
        )->fetchColumn();

        $stats['pending_count'] = (int) $this->db->query(
            "SELECT COUNT(*) FROM appointments WHERE status = 'pending'"
        )->fetchColumn();

        $stats['completed_this_month'] = (int) $this->db->query(
            "SELECT COUNT(*) FROM appointments
             WHERE status = 'completed'
               AND scheduled_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
               AND scheduled_at < DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 1 MONTH)"
        )->fetchColumn();

        return $stats;
    }

    /** Записи по дням за последние 14 дней */
    public function getAppointmentsByDay(): array
    {
        $stmt = $this->db->query(
            "SELECT DATE(scheduled_at) AS day, COUNT(*) AS cnt
             FROM appointments
             WHERE scheduled_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
             GROUP BY DATE(scheduled_at)
             ORDER BY day"
        );
        return $stmt->fetchAll();
    }

    /** Топ врачей по количеству завершённых приёмов */
    public function getTopDoctors(): array
    {
        $stmt = $this->db->query(
            "SELECT d.full_name, COUNT(a.id) AS cnt
             FROM appointments a
             JOIN doctors d ON d.id = a.doctor_id
             WHERE a.status = 'completed'
             GROUP BY a.doctor_id, d.full_name
             ORDER BY cnt DESC
             LIMIT 5"
        );
        return $stmt->fetchAll();
    }

    // ── Отзывы ────────────────────────────────────────────────────────────────

    public function getPendingReviews(): array
    {
        $stmt = $this->db->query(
            "SELECT r.id, r.rating, r.review_text AS text, r.created_at,
                    p.full_name AS patient_name,
                    d.full_name AS doctor_name
             FROM reviews r
             JOIN patients p ON p.id = r.patient_id
             JOIN doctors d ON d.id = r.doctor_id
             WHERE r.is_approved = 0
             ORDER BY r.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    public function getApprovedReviews(): array
    {
        $stmt = $this->db->query(
            "SELECT r.id, r.rating, r.review_text AS text, r.created_at,
                    r.admin_reply, r.admin_reply_at,
                    p.full_name AS patient_name,
                    d.full_name AS doctor_name
             FROM reviews r
             JOIN patients p ON p.id = r.patient_id
             JOIN doctors d ON d.id = r.doctor_id
             WHERE r.is_approved = 1
             ORDER BY r.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    public function countApprovedReviews(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM reviews WHERE is_approved = 1"
        )->fetchColumn();
    }

    public function getApprovedReviewsPaginated(int $limit, int $offset): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.id, r.rating, r.review_text AS text, r.created_at,
                    r.admin_reply, r.admin_reply_at,
                    p.full_name AS patient_name,
                    d.full_name AS doctor_name
             FROM reviews r
             JOIN patients p ON p.id = r.patient_id
             JOIN doctors d ON d.id = r.doctor_id
             WHERE r.is_approved = 1
             ORDER BY r.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public function saveReply(int $id, string $reply): void
    {
        $this->db->prepare(
            "UPDATE reviews SET admin_reply = ?, admin_reply_at = NOW() WHERE id = ? AND is_approved = 1"
        )->execute([$reply, $id]);
    }

    public function approveReview(int $id): void
    {
        $this->db->prepare("UPDATE reviews SET is_approved = 1 WHERE id = ?")
                 ->execute([$id]);
    }

    public function deleteReview(int $id): void
    {
        $this->db->prepare("DELETE FROM reviews WHERE id = ?")
                 ->execute([$id]);
    }
}