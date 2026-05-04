<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Session;
use App\Repositories\AppointmentRepository;
use App\Repositories\VisitRepository;
use PDO;

class DoctorService
{
    private AppointmentRepository $appointments;
    private VisitRepository $visits;
    private PDO $db;

    public function __construct()
    {
        $this->appointments = new AppointmentRepository();
        $this->visits       = new VisitRepository();
        $this->db           = Database::getInstance();
    }

    /** Получить doctor.id по user.id */
    public function getDoctorIdByUserId(int $userId): ?int
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM doctors WHERE user_id = ? LIMIT 1"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row ? (int) $row['id'] : null;
    }

    /** Получить профиль врача */
    public function getDoctorProfile(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT d.id, d.full_name, d.bio, d.photo_url,
                    s.name AS specialization
             FROM doctors d
             JOIN specializations s ON s.id = d.specialization_id
             WHERE d.user_id = ? LIMIT 1"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Начать приём: создать визит, поменять статус */
    public function startAppointment(int $appointmentId, int $doctorId): array
    {
        $appt = $this->appointments->findByIdWithPatient($appointmentId);

        if (!$appt || (int) $appt['doctor_id'] !== $doctorId) {
            return ['error' => 'Приём не найден.'];
        }

        if (!in_array($appt['status'], ['pending', 'confirmed'], true)) {
            return ['error' => 'Этот приём нельзя начать (статус: ' . $appt['status'] . ').'];
        }

        // Есть ли уже визит?
        $visit = $this->visits->findByAppointmentId($appointmentId);
        if (!$visit) {
            $this->visits->create($appointmentId);
        }

        $this->appointments->updateStatus($appointmentId, 'in_progress');
        return ['ok' => true];
    }

    /** Сохранить протокол (автосохранение и финальное) */
    public function saveProtocol(
        int $appointmentId,
        int $doctorId,
        string $complaints,
        string $examination,
        string $diagnosis,
        bool $finish = false
    ): array {
        $appt = $this->appointments->findByIdWithPatient($appointmentId);

        if (!$appt || (int) $appt['doctor_id'] !== $doctorId) {
            return ['error' => 'Приём не найден.'];
        }

        $visit = $this->visits->findByAppointmentId($appointmentId);
        if (!$visit) {
            return ['error' => 'Визит не начат.'];
        }

        $this->visits->updateProtocol(
            (int) $visit['id'],
            $complaints,
            $examination,
            $diagnosis
        );

        if ($finish) {
            $this->visits->finish((int) $visit['id']);
            $this->appointments->updateStatus($appointmentId, 'completed');
        }

        return ['ok' => true];
    }

    /** Добавить назначение */
    public function addPrescription(
        int $appointmentId,
        int $doctorId,
        string $type,
        string $name,
        string $dosage,
        string $notes
    ): array {
        $appt = $this->appointments->findByIdWithPatient($appointmentId);

        if (!$appt || (int) $appt['doctor_id'] !== $doctorId) {
            return ['error' => 'Приём не найден.'];
        }

        if ($appt['status'] !== 'in_progress') {
            return ['error' => 'Назначения можно добавлять только во время активного приёма.'];
        }

        $visit = $this->visits->findByAppointmentId($appointmentId);
        if (!$visit) {
            return ['error' => 'Визит не начат.'];
        }

        $allowed = ['drug', 'procedure', 'referral'];
        if (!in_array($type, $allowed, true)) {
            return ['error' => 'Неверный тип назначения.'];
        }

        if (trim($name) === '') {
            return ['error' => 'Укажите название.'];
        }

        $this->visits->addPrescription(
            (int) $visit['id'],
            $type,
            trim($name),
            trim($dosage),
            trim($notes)
        );

        return ['ok' => true];
    }

    /** Удалить назначение */
    public function deletePrescription(
        int $prescriptionId,
        int $appointmentId,
        int $doctorId
    ): array {
        $appt = $this->appointments->findByIdWithPatient($appointmentId);

        if (!$appt || (int) $appt['doctor_id'] !== $doctorId) {
            return ['error' => 'Доступ запрещён.'];
        }

        $visit = $this->visits->findByAppointmentId($appointmentId);
        if (!$visit) {
            return ['error' => 'Визит не найден.'];
        }

        $this->visits->deletePrescription($prescriptionId, (int) $visit['id']);
        return ['ok' => true];
    }
}