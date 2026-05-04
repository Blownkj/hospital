<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class VisitRepository extends BaseRepository
{
    protected string $table = 'visits';

    /** Найти визит по appointment_id */
    public function findByAppointmentId(int $appointmentId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM visits WHERE appointment_id = ? LIMIT 1"
        );
        $stmt->execute([$appointmentId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Создать новый визит (при нажатии «Начать приём») */
    public function create(int $appointmentId): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO visits (appointment_id, started_at)
             VALUES (?, NOW())"
        );
        $stmt->execute([$appointmentId]);
        return (int) $this->db->lastInsertId();
    }

    /** Обновить протокол приёма */
    public function updateProtocol(
        int $visitId,
        string $complaints,
        string $examination,
        string $diagnosis
    ): void {
        $stmt = $this->db->prepare(
            "UPDATE visits
             SET complaints = ?, examination = ?, diagnosis = ?
             WHERE id = ?"
        );
        $stmt->execute([$complaints, $examination, $diagnosis, $visitId]);
    }

    /** Завершить визит — проставить ended_at */
    public function finish(int $visitId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE visits SET ended_at = NOW() WHERE id = ?"
        );
        $stmt->execute([$visitId]);
    }

    /** Все назначения для визита */
    public function getPrescriptions(int $visitId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM prescriptions
             WHERE visit_id = ?
             ORDER BY id"
        );
        $stmt->execute([$visitId]);
        return $stmt->fetchAll();
    }

    /** Добавить назначение */
    public function addPrescription(
        int $visitId,
        string $type,
        string $name,
        string $dosage,
        string $notes
    ): void {
        $stmt = $this->db->prepare(
            "INSERT INTO prescriptions (visit_id, type, name, dosage, notes)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$visitId, $type, $name, $dosage, $notes]);
    }

    /** Удалить назначение */
    public function deletePrescription(int $prescriptionId, int $visitId): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM prescriptions WHERE id = ? AND visit_id = ?"
        );
        $stmt->execute([$prescriptionId, $visitId]);
    }

    /** Полная история визитов пациента с назначениями */
    public function getFullHistoryForPatient(int $patientId): array
    {
        $stmt = $this->db->prepare(
            "SELECT v.id AS visit_id, v.started_at, v.ended_at,
                    v.complaints, v.examination, v.diagnosis,
                    a.scheduled_at,
                    d.full_name AS doctor_name,
                    s.name AS specialization
             FROM visits v
             JOIN appointments a ON a.id = v.appointment_id
             JOIN doctors d ON d.id = a.doctor_id
             JOIN specializations s ON s.id = d.specialization_id
             WHERE a.patient_id = ?
             ORDER BY v.started_at DESC"
        );
        $stmt->execute([$patientId]);
        $visits = $stmt->fetchAll();

        if (empty($visits)) {
            return [];
        }

        $visitIds     = array_column($visits, 'visit_id');
        $placeholders = implode(',', array_fill(0, count($visitIds), '?'));
        $pStmt        = $this->db->prepare(
            "SELECT * FROM prescriptions
             WHERE visit_id IN ($placeholders)
             ORDER BY visit_id, id"
        );
        $pStmt->execute($visitIds);

        $byVisit = [];
        foreach ($pStmt->fetchAll() as $p) {
            $byVisit[$p['visit_id']][] = $p;
        }

        foreach ($visits as &$visit) {
            $visit['prescriptions'] = $byVisit[$visit['visit_id']] ?? [];
        }

        return $visits;
    }

    public function findByIdForPatient(int $visitId, int $patientId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT v.id, v.complaints, v.examination, v.diagnosis,
                    v.started_at, v.ended_at,
                    a.scheduled_at,
                    d.full_name AS doctor_name,
                    d.photo_url AS doctor_photo,
                    s.name AS specialization,
                    p.full_name AS patient_name,
                    p.birth_date AS patient_birth_date,
                    p.phone AS patient_phone
             FROM visits v
             JOIN appointments a ON a.id = v.appointment_id
             JOIN doctors d      ON d.id = a.doctor_id
             JOIN specializations s ON s.id = d.specialization_id
             JOIN patients p     ON p.id = a.patient_id
             WHERE v.id = ? AND a.patient_id = ?
             LIMIT 1"
        );
        $stmt->execute([$visitId, $patientId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}