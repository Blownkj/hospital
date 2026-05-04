<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Middleware\AuthMiddleware;
use App\Repositories\AppointmentRepository;
use App\Repositories\VisitRepository;
use App\Services\DoctorService;

class DoctorController
{
    private DoctorService $service;
    private AppointmentRepository $appointments;
    private VisitRepository $visits;

    public function __construct()
    {
        $this->service      = new DoctorService();
        $this->appointments = new AppointmentRepository();
        $this->visits       = new VisitRepository();
    }

    // ── Дашборд ─────────────────────────────────────────────────────────────

    public function dashboard(): void
    {
        AuthMiddleware::requireRole('doctor');

        $userId   = (int) Session::get('user_id');
        $doctorId = $this->service->getDoctorIdByUserId($userId);

        if (!$doctorId) {
            die('Профиль врача не найден. Обратитесь к администратору.');
        }

        $profile = $this->service->getDoctorProfile($userId);
        $today   = $this->appointments->getTodayForDoctor($doctorId);
        $recent  = $this->appointments->getRecentForDoctor($doctorId, 10);
        $stats   = $this->appointments->getStatsForDoctor($doctorId);

        View::render('doctor/dashboard', [
            'pageTitle' => 'Кабинет врача',
            'profile'   => $profile,
            'today'     => $today,
            'recent'    => $recent,
            'stats'     => $stats,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    // ── Страница приёма ──────────────────────────────────────────────────────

    // GET /doctor/appointment/{id}
    public function appointment(string $id): void
    {
        AuthMiddleware::requireRole('doctor');

        $appointmentId = (int) $id;
        $userId        = (int) Session::get('user_id');
        $doctorId      = $this->service->getDoctorIdByUserId($userId);

        $appt = $this->appointments->findByIdWithPatient($appointmentId);

        if (!$appt || (int) $appt['doctor_id'] !== $doctorId) {
            http_response_code(403);
            die('Доступ запрещён.');
        }

        $visit         = $this->visits->findByAppointmentId($appointmentId);
        $prescriptions = $visit ? $this->visits->getPrescriptions((int) $visit['id']) : [];
        $history       = $this->appointments->getCompletedForPatient((int) $appt['patient_id']);

        View::render('doctor/appointment', [
            'pageTitle'     => 'Приём пациента',
            'appt'          => $appt,
            'visit'         => $visit,
            'prescriptions' => $prescriptions,
            'history'       => $history,
            'csrf'          => Session::generateCsrfToken(),
            'flash'         => Session::getFlash('success'),
            'error'         => Session::getFlash('error'),
        ]);
    }

    // ── Начать приём ─────────────────────────────────────────────────────────

    // POST /doctor/appointment/{id}/start
    public function startAppointment(string $id): void
    {
        AuthMiddleware::requireRole('doctor');
        $this->validateCsrf();

        $appointmentId = (int) $id;
        $userId        = (int) Session::get('user_id');
        $doctorId      = $this->service->getDoctorIdByUserId($userId);

        $result = $this->service->startAppointment($appointmentId, $doctorId);

        if (isset($result['error'])) {
            Session::setFlash('error', $result['error']);
        } else {
            Session::setFlash('success', 'Приём начат.');
        }

        AuthMiddleware::redirect('/doctor/appointment/' . $appointmentId);
    }

    // ── Сохранить протокол ───────────────────────────────────────────────────

    // POST /doctor/appointment/{id}/protocol
    public function saveProtocol(string $id): void
    {
        AuthMiddleware::requireRole('doctor');
        $this->validateCsrf();

        $appointmentId = (int) $id;
        $userId        = (int) Session::get('user_id');
        $doctorId      = $this->service->getDoctorIdByUserId($userId);

        $finish = isset($_POST['finish']);

        $result = $this->service->saveProtocol(
            $appointmentId,
            $doctorId,
            trim($_POST['complaints']   ?? ''),
            trim($_POST['examination']  ?? ''),
            trim($_POST['diagnosis']    ?? ''),
            $finish
        );

        if (isset($result['error'])) {
            Session::setFlash('error', $result['error']);
            AuthMiddleware::redirect('/doctor/appointment/' . $appointmentId);
        }

        if ($finish) {
            Session::setFlash('success', 'Приём завершён и сохранён.');
            AuthMiddleware::redirect('/doctor/dashboard');
        }

        Session::setFlash('success', 'Протокол сохранён.');
        AuthMiddleware::redirect('/doctor/appointment/' . $appointmentId);
    }

    // ── Добавить назначение ──────────────────────────────────────────────────

    // POST /doctor/appointment/{id}/prescription/add
    public function addPrescription(string $id): void
    {
        AuthMiddleware::requireRole('doctor');
        $this->validateCsrf();

        $appointmentId = (int) $id;
        $userId        = (int) Session::get('user_id');
        $doctorId      = $this->service->getDoctorIdByUserId($userId);

        $result = $this->service->addPrescription(
            $appointmentId,
            $doctorId,
            $_POST['type']   ?? '',
            $_POST['name']   ?? '',
            $_POST['dosage'] ?? '',
            $_POST['notes']  ?? ''
        );

        if (isset($result['error'])) {
            Session::setFlash('error', $result['error']);
        }

        AuthMiddleware::redirect('/doctor/appointment/' . $appointmentId);
    }

    // ── Удалить назначение ───────────────────────────────────────────────────

    // POST /doctor/appointment/{id}/prescription/delete
    public function deletePrescription(string $id): void
    {
        AuthMiddleware::requireRole('doctor');
        $this->validateCsrf();

        $appointmentId   = (int) $id;
        $prescriptionId  = (int) ($_POST['prescription_id'] ?? 0);
        $userId          = (int) Session::get('user_id');
        $doctorId        = $this->service->getDoctorIdByUserId($userId);

        $this->service->deletePrescription($prescriptionId, $appointmentId, $doctorId);
        AuthMiddleware::redirect('/doctor/appointment/' . $appointmentId);
    }

    // GET /doctor/profile
public function profile(): void
{
    AuthMiddleware::requireRole('doctor');

    $userId  = (int) Session::get('user_id');
    $profile = $this->service->getDoctorProfile($userId);

    if (!$profile) {
        die('Профиль не найден.');
    }

    View::render('doctor/profile', [
        'pageTitle' => 'Мой профиль',
        'profile'   => $profile,
        'flash'     => Session::getFlash('success'),
        'error'     => Session::getFlash('error'),
        'csrf'      => Session::generateCsrfToken(),
    ]);
}

    // POST /doctor/profile
    public function updateProfile(): void
    {
        AuthMiddleware::requireRole('doctor');
        $this->validateCsrf();

        $userId   = (int) Session::get('user_id');
        $doctorId = $this->service->getDoctorIdByUserId($userId);

        $bio      = trim($_POST['bio']       ?? '');
        $photoUrl = trim($_POST['photo_url'] ?? '');

        $stmt = \App\Core\Database::getInstance()->prepare(
            "UPDATE doctors SET bio = ?, photo_url = ? WHERE id = ?"
        );
        $stmt->execute([$bio, $photoUrl, $doctorId]);

        Session::setFlash('success', 'Профиль обновлён.');
        AuthMiddleware::redirect('/doctor/profile');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function validateCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!Session::validateCsrfToken($token)) {
            http_response_code(419);
            die('CSRF-токен недействителен.');
        }
    }
}