<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class AppointmentRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── Запись пациента / слоты (использует AppointmentService) ───────────

    public function getException(int $doctorId, string $date): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, is_day_off, note
             FROM schedule_exceptions
             WHERE doctor_id = ? AND exception_date = ?
             LIMIT 1'
        );
        $stmt->execute([$doctorId, $date]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getScheduleForDay(int $doctorId, int $dayOfWeek): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT start_time, end_time, slot_duration_min
             FROM schedules
             WHERE doctor_id = ? AND day_of_week = ?
             LIMIT 1'
        );
        $stmt->execute([$doctorId, $dayOfWeek]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Занятые слоты врача на дату (только приёмы у врача, не лаборатория) */
    public function getBookedTimes(int $doctorId, string $date): array
    {
        $stmt = $this->db->prepare(
            "SELECT TIME_FORMAT(scheduled_at, '%H:%i') AS t
             FROM appointments
             WHERE doctor_id = ?
               AND DATE(scheduled_at) = ?
               AND status NOT IN ('cancelled')"
        );
        $stmt->execute([$doctorId, $date]);
        return array_column($stmt->fetchAll(), 't');
    }

    public function alreadyBooked(int $patientId, int $doctorId, string $scheduledAt): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM appointments
             WHERE patient_id = ? AND doctor_id = ?
               AND scheduled_at = ?
               AND status NOT IN ('cancelled')
             LIMIT 1"
        );
        $stmt->execute([$patientId, $doctorId, $scheduledAt]);
        return (bool)$stmt->fetchColumn();
    }

    public function create(int $patientId, int $doctorId, string $scheduledAt): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO appointments
                (patient_id, appointment_type, doctor_id, lab_test_id, scheduled_at, status)
             VALUES (?, 'doctor', ?, NULL, ?, 'pending')"
        );
        $stmt->execute([$patientId, $doctorId, $scheduledAt]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Все записи пациента: приёмы у врачей и анализы (для кабинета / «Мои записи»)
     *
     * @return list<array<string, mixed>>
     */
    public function getByPatientId(int $patientId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                a.id,
                a.scheduled_at,
                a.status,
                COALESCE(a.appointment_type, 'doctor') AS appointment_type,
                d.full_name AS doctor_name,
                COALESCE(s.name, lt.category, 'Лаборатория') AS specialization,
                lt.name AS lab_test_name
             FROM appointments a
             LEFT JOIN doctors d ON d.id = a.doctor_id
             LEFT JOIN specializations s ON s.id = d.specialization_id
             LEFT JOIN lab_tests lt ON lt.id = a.lab_test_id
             WHERE a.patient_id = ?
             ORDER BY a.scheduled_at DESC"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }

    /**
     * Все записи для экспорта в CSV
     */
    public function getAllForExport(string $from = '', string $to = ''): array
    {
        $sql = "SELECT
                    a.id,
                    a.scheduled_at,
                    a.status,
                    a.appointment_type,
                    a.created_at,
                    COALESCE(p.full_name, '—')  AS patient_name,
                    p.phone                      AS patient_phone,
                    COALESCE(d.full_name, '—')  AS doctor_name,
                    COALESCE(s.name, 'Анализ')  AS specialization,
                    COALESCE(lt.name, '—')      AS lab_test_name
                FROM appointments a
                LEFT JOIN patients p       ON p.id = a.patient_id
                LEFT JOIN doctors d        ON d.id = a.doctor_id
                LEFT JOIN specializations s ON s.id = d.specialization_id
                LEFT JOIN lab_tests lt     ON lt.id = a.lab_test_id
                WHERE 1=1";

        $params = [];

        if ($from) {
            $sql .= ' AND DATE(a.scheduled_at) >= ?';
            $params[] = $from;
        }
        if ($to) {
            $sql .= ' AND DATE(a.scheduled_at) <= ?';
            $params[] = $to;
        }

        $sql .= ' ORDER BY a.scheduled_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Статистика врача: приёмов за месяц, всего, средний рейтинг
     */
    public function getStatsForDoctor(int $doctorId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*)                                                        AS total,
                SUM(status = 'completed')                                       AS completed,
                SUM(status = 'completed'
                    AND scheduled_at >= DATE_FORMAT(NOW(),'%Y-%m-01'))          AS this_month,
                SUM(status IN ('pending','confirmed')
                    AND scheduled_at >= NOW())                                  AS upcoming
            FROM appointments
            WHERE doctor_id = ?"
        );
        $stmt->execute([$doctorId]);
        $row = $stmt->fetch();

        // Средний рейтинг из отзывов
        $stmt2 = $this->db->prepare(
            "SELECT ROUND(AVG(rating),1), COUNT(*)
            FROM reviews WHERE doctor_id = ? AND is_approved = 1"
        );
        $stmt2->execute([$doctorId]);
        [$avgRating, $reviewCount] = array_values($stmt2->fetch(\PDO::FETCH_NUM));

        return [
            'total'        => (int)$row['total'],
            'completed'    => (int)$row['completed'],
            'this_month'   => (int)$row['this_month'],
            'upcoming'     => (int)$row['upcoming'],
            'avg_rating'   => (float)($avgRating ?? 0),
            'review_count' => (int)($reviewCount ?? 0),
        ];
    }

    public function cancelByPatient(int $appointmentId, int $patientId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE appointments SET status = 'cancelled'
             WHERE id = ? AND patient_id = ?
               AND status IN ('pending', 'confirmed')"
        );
        $stmt->execute([$appointmentId, $patientId]);
        return $stmt->rowCount() > 0;
    }

    /** Все приёмы врача на сегодня, отсортированные по времени */
    public function getTodayForDoctor(int $doctorId): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.id, a.scheduled_at, a.status,
                    p.full_name AS patient_name,
                    p.phone AS patient_phone,
                    p.birth_date AS patient_birth_date,
                    p.chronic_diseases
             FROM appointments a
             JOIN patients p ON p.id = a.patient_id
             WHERE a.doctor_id = ?
               AND DATE(a.scheduled_at) = CURDATE()
               AND a.status IN ('confirmed', 'pending', 'in_progress')
             ORDER BY a.scheduled_at"
        );
        $stmt->execute([$doctorId]);
        return $stmt->fetchAll();
    }

    /** Один приём по id — с данными пациента */
    public function findByIdWithPatient(int $appointmentId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT a.id, a.scheduled_at, a.status, a.doctor_id,
                    p.id AS patient_id, p.full_name AS patient_name,
                    p.birth_date AS patient_birth_date,
                    p.phone AS patient_phone, p.gender,
                    p.address, p.chronic_diseases,
                    u.email AS patient_email
             FROM appointments a
             JOIN patients p ON p.id = a.patient_id
             JOIN users u ON u.id = p.user_id
             WHERE a.id = ? LIMIT 1"
        );
        $stmt->execute([$appointmentId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Все прошлые завершённые приёмы пациента (история для врача) */
    public function getCompletedForPatient(int $patientId): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.id, a.scheduled_at,
                    d.full_name AS doctor_name,
                    s.name AS specialization,
                    v.diagnosis, v.started_at, v.ended_at
             FROM appointments a
             JOIN doctors d ON d.id = a.doctor_id
             JOIN specializations s ON s.id = d.specialization_id
             LEFT JOIN visits v ON v.appointment_id = a.id
             WHERE a.patient_id = ? AND a.status = 'completed'
             ORDER BY a.scheduled_at DESC
             LIMIT 20"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }

    /** Обновить статус приёма */
    public function updateStatus(int $appointmentId, string $status): void
    {
        $stmt = $this->db->prepare(
            "UPDATE appointments SET status = ? WHERE id = ?"
        );
        $stmt->execute([$status, $appointmentId]);
    }

    /** Приёмы врача за последние 30 дней (история) */
    public function getRecentForDoctor(int $doctorId, int $limit = 30): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.id, a.scheduled_at, a.status,
                    p.full_name AS patient_name,
                    v.diagnosis
             FROM appointments a
             JOIN patients p ON p.id = a.patient_id
             LEFT JOIN visits v ON v.appointment_id = a.id
             WHERE a.doctor_id = ?
               AND a.status = 'completed'
             ORDER BY a.scheduled_at DESC
             LIMIT ?"
        );
        $stmt->execute([$doctorId, $limit]);
        return $stmt->fetchAll();
    }
}