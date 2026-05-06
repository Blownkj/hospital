<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Session;
use App\Core\View;
use App\Exceptions\DomainException;
use App\Exceptions\ForbiddenException;
use App\Middleware\AuthMiddleware;
use App\Repositories\AppointmentRepository;
use App\Repositories\DoctorRepository;
use App\Repositories\VisitRepository;
use App\Services\DoctorService;

class DoctorController extends BaseController
{
    public function __construct(
        private DoctorService $service              = new DoctorService(),
        private AppointmentRepository $appointments = new AppointmentRepository(),
        private VisitRepository $visits             = new VisitRepository(),
        private DoctorRepository $doctorRepo        = new DoctorRepository(),
    ) {}

    private function currentDoctorId(): int
    {
        $userId = (int) Session::get('user_id');
        $id     = $this->service->getDoctorIdByUserId($userId);
        if ($id === null) {
            http_response_code(403);
            die('Профиль врача не найден. Обратитесь к администратору.');
        }
        return $id;
    }

    // ── Дашборд ─────────────────────────────────────────────────────────────

    public function dashboard(): void
    {

        $doctorId = $this->currentDoctorId();
        $userId   = (int) Session::get('user_id');
        $profile  = $this->service->getDoctorProfile($userId);
        $today    = $this->appointments->getTodayForDoctor($doctorId);
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

        $appointmentId = (int) $id;
        $doctorId      = $this->currentDoctorId();

        $appt = $this->appointments->findByIdWithPatient($appointmentId, $doctorId);

        if (!$appt) {
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
        $this->validateCsrf();

        $appointmentId = (int) $id;
        $doctorId      = $this->currentDoctorId();

        try {
            $this->service->startAppointment($appointmentId, $doctorId);
            Logger::get()->info('Appointment started', [
                'doctor_id'      => $doctorId,
                'appointment_id' => $appointmentId,
            ]);
            Session::setFlash('success', 'Приём начат.');
        } catch (DomainException | ForbiddenException $e) {
            Logger::get()->warning('Start appointment failed', [
                'doctor_id'      => $doctorId,
                'appointment_id' => $appointmentId,
                'error'          => $e->getMessage(),
            ]);
            Session::setFlash('error', $e->getMessage());
        }

        AuthMiddleware::redirect('/doctor/appointment/' . $appointmentId);
    }

    // ── Сохранить протокол ───────────────────────────────────────────────────

    // POST /doctor/appointment/{id}/protocol
    public function saveProtocol(string $id): void
    {
        $this->validateCsrf();

        $appointmentId = (int) $id;
        $doctorId      = $this->currentDoctorId();

        $finish = isset($_POST['finish']);

        try {
            $this->service->saveProtocol(
                $appointmentId,
                $doctorId,
                trim($_POST['complaints']   ?? ''),
                trim($_POST['examination']  ?? ''),
                trim($_POST['diagnosis']    ?? ''),
                $finish
            );
        } catch (DomainException | ForbiddenException $e) {
            Logger::get()->warning('Save protocol failed', [
                'doctor_id'      => $doctorId,
                'appointment_id' => $appointmentId,
                'error'          => $e->getMessage(),
            ]);
            Session::setFlash('error', $e->getMessage());
            AuthMiddleware::redirect('/doctor/appointment/' . $appointmentId);
        }

        if ($finish) {
            Logger::get()->info('Appointment finished', [
                'doctor_id'      => $doctorId,
                'appointment_id' => $appointmentId,
            ]);
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
        $this->validateCsrf();

        $appointmentId = (int) $id;
        $doctorId      = $this->currentDoctorId();

        try {
            $this->service->addPrescription(
                $appointmentId,
                $doctorId,
                $_POST['type']   ?? '',
                $_POST['name']   ?? '',
                $_POST['dosage'] ?? '',
                $_POST['notes']  ?? ''
            );
        } catch (DomainException | ForbiddenException $e) {
            Logger::get()->warning('Add prescription failed', [
                'doctor_id'      => $doctorId,
                'appointment_id' => $appointmentId,
                'error'          => $e->getMessage(),
            ]);
            Session::setFlash('error', $e->getMessage());
        }

        AuthMiddleware::redirect('/doctor/appointment/' . $appointmentId);
    }

    // ── Удалить назначение ───────────────────────────────────────────────────

    // POST /doctor/appointment/{id}/prescription/delete
    public function deletePrescription(string $id): void
    {
        $this->validateCsrf();

        $appointmentId  = (int) $id;
        $prescriptionId = (int) ($_POST['prescription_id'] ?? 0);
        $doctorId       = $this->currentDoctorId();

        try {
            $this->service->deletePrescription($prescriptionId, $appointmentId, $doctorId);
        } catch (DomainException | ForbiddenException $e) {
            Session::setFlash('error', $e->getMessage());
        }

        AuthMiddleware::redirect('/doctor/appointment/' . $appointmentId);
    }

    // GET /doctor/profile
public function profile(): void
{

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
        $this->validateCsrf();

        $doctorId = $this->currentDoctorId();

        $bio      = trim($_POST['bio']       ?? '');
        $photoUrl = trim($_POST['photo_url'] ?? '');

        $this->doctorRepo->update($doctorId, $bio, $photoUrl);

        Session::setFlash('success', 'Профиль обновлён.');
        AuthMiddleware::redirect('/doctor/profile');
    }

}