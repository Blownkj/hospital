<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Paginator;
use App\Core\Session;
use App\Core\View;
use App\Middleware\AuthMiddleware;
use App\Repositories\AdminRepository;
use App\Repositories\AppointmentRepository;
use App\Repositories\DoctorRepository;
use App\Repositories\UserRepository;

class AdminController extends BaseController
{
    public function __construct(
        private AdminRepository       $repo         = new AdminRepository(),
        private AppointmentRepository $appointments = new AppointmentRepository(),
        private UserRepository        $users        = new UserRepository(),
    ) {}

    // ── Дашборд ──────────────────────────────────────────────────────────────

    public function dashboard(): void
    {

        $stats      = $this->repo->getStats();
        $byDay      = $this->repo->getAppointmentsByDay();
        $topDoctors = $this->repo->getTopDoctors();
        $pending    = $this->repo->getAllAppointments('pending');

        View::render('admin/dashboard', [
            'pageTitle'  => 'Панель администратора',
            'stats'      => $stats,
            'byDay'      => $byDay,
            'topDoctors' => $topDoctors,
            'pending'    => $pending,
            'flash'      => Session::getFlash('success'),
            'error'      => Session::getFlash('error'),
        ]);
    }

    // ── Записи ───────────────────────────────────────────────────────────────

    public function appointments(): void
    {

        $status  = $_GET['status'] ?? '';
        $date    = $_GET['date']   ?? '';
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 25;

        $total     = $this->repo->countAppointments($status, $date);
        $paginator = new Paginator($total, $perPage, $page);
        $appointments = $this->repo->getAllAppointmentsPaginated(
            $perPage, $paginator->offset, $status, $date
        );

        View::render('admin/appointments', [
            'pageTitle'    => 'Управление записями',
            'appointments' => $appointments,
            'status'       => $status,
            'date'         => $date,
            'paginator'    => $paginator,
            'csrf'         => Session::generateCsrfToken(),
            'flash'        => Session::getFlash('success'),
            'error'        => Session::getFlash('error'),
        ]);
    }

    public function confirmAppointment(string $id): void
    {
        $this->validateCsrf();

        $this->repo->updateAppointmentStatus((int) $id, 'confirmed');
        Logger::get()->info('Admin confirmed appointment', [
            'admin_id'       => Session::get('user_id'),
            'appointment_id' => (int) $id,
        ]);
        Session::setFlash('success', 'Запись подтверждена.');
        AuthMiddleware::redirect('/admin/appointments');
    }

    public function cancelAppointment(string $id): void
    {
        $this->validateCsrf();

        $this->repo->updateAppointmentStatus((int) $id, 'cancelled');
        Logger::get()->info('Admin cancelled appointment', [
            'admin_id'       => Session::get('user_id'),
            'appointment_id' => (int) $id,
        ]);
        Session::setFlash('success', 'Запись отменена.');
        AuthMiddleware::redirect('/admin/appointments');
    }

    public function rescheduleAppointment(string $id): void
    {
        $this->validateCsrf();

        $newDatetime = trim($_POST['new_datetime'] ?? '');

        if (!$newDatetime || !strtotime($newDatetime)) {
            Session::setFlash('error', 'Укажите корректную дату и время.');
            AuthMiddleware::redirect('/admin/appointments');
        }

        $this->repo->rescheduleAppointment((int) $id, $newDatetime);
        Session::setFlash('success', 'Запись перенесена.');
        AuthMiddleware::redirect('/admin/appointments');
    }

    // ── Расписание ───────────────────────────────────────────────────────────

    public function schedule(): void
    {

        $doctors  = $this->repo->getAllDoctors();
        $doctorId = (int) ($_GET['doctor_id'] ?? ($doctors[0]['id'] ?? 0));
        $schedule = $doctorId ? $this->repo->getDoctorSchedule($doctorId) : [];

        // Индексируем по дню недели для удобства в шаблоне
        $scheduleByDay = [];
        foreach ($schedule as $row) {
            $scheduleByDay[(int) $row['day_of_week']] = $row;
        }

        View::render('admin/schedule', [
            'pageTitle'    => 'Расписание врачей',
            'doctors'      => $doctors,
            'selectedId'   => $doctorId,
            'scheduleByDay'=> $scheduleByDay,
            'csrf'         => Session::generateCsrfToken(),
            'flash'        => Session::getFlash('success'),
            'error'        => Session::getFlash('error'),
        ]);
    }

    public function saveSchedule(string $doctorId): void
    {
        $this->validateCsrf();

        $did  = (int) $doctorId;
        $days = $_POST['days'] ?? [];   // массив: day_of_week => [start, end, slot, active]

        for ($dow = 1; $dow <= 7; $dow++) {
            $d = $days[$dow] ?? [];
            if (!empty($d['active'])) {
                $this->repo->upsertSchedule(
                    $did,
                    $dow,
                    $d['start'] ?? '09:00',
                    $d['end']   ?? '18:00',
                    (int) ($d['slot'] ?? 30)
                );
            } else {
                $this->repo->deleteScheduleDay($did, $dow);
            }
        }

        Session::setFlash('success', 'Расписание сохранено.');
        AuthMiddleware::redirect('/admin/schedule?doctor_id=' . $did);
    }

    // ── Отзывы ────────────────────────────────────────────────────────────────

    public function reviews(): void
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;

        $pending   = $this->repo->getPendingReviews();
        $total     = $this->repo->countApprovedReviews();
        $paginator = new Paginator($total, $perPage, $page);
        $approved  = $this->repo->getApprovedReviewsPaginated($perPage, $paginator->offset);

        View::render('admin/reviews', [
            'pageTitle' => 'Модерация отзывов',
            'pending'   => $pending,
            'approved'  => $approved,
            'paginator' => $paginator,
            'csrf'      => Session::generateCsrfToken(),
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    public function approveReview(string $id): void
    {
        $this->validateCsrf();

        $this->repo->approveReview((int) $id);
        Session::setFlash('success', 'Отзыв опубликован.');
        AuthMiddleware::redirect('/admin/reviews');
    }

    public function deleteReview(string $id): void
    {
        $this->validateCsrf();

        $this->repo->deleteReview((int) $id);
        Session::setFlash('success', 'Отзыв удалён.');
        AuthMiddleware::redirect('/admin/reviews');
    }

    public function replyToReview(string $id): void
    {
        $this->validateCsrf();

        $reply = trim($_POST['reply'] ?? '');
        if ($reply === '') {
            Session::setFlash('error', 'Ответ не может быть пустым.');
            AuthMiddleware::redirect('/admin/reviews');
            return;
        }

        $this->repo->saveReply((int) $id, $reply);
        Session::setFlash('success', 'Ответ сохранён.');
        AuthMiddleware::redirect('/admin/reviews');
    }

        // ── Врачи ─────────────────────────────────────────────────────────────────

    // GET /admin/doctors
    public function doctors(): void
    {

        $query  = trim((string)($_GET['q'] ?? ''));
        $specId = (int)($_GET['spec'] ?? 0);

        $specs   = $this->repo->getAllSpecializations();
        $doctors = $this->repo->getAllDoctors();

        if ($query !== '' || $specId > 0) {
            $q = mb_strtolower($query, 'UTF-8');
            $doctors = array_values(array_filter($doctors, function (array $d) use ($q, $specId): bool {
                if ($specId > 0 && (int)($d['specialization_id'] ?? 0) !== $specId) {
                    return false;
                }

                if ($q === '') {
                    return true;
                }

                $haystacks = [
                    (string)($d['full_name'] ?? ''),
                    (string)($d['specialization'] ?? ''),
                    (string)($d['email'] ?? ''),
                ];

                foreach ($haystacks as $h) {
                    if ($h !== '' && str_contains(mb_strtolower($h, 'UTF-8'), $q)) {
                        return true;
                    }
                }
                return false;
            }));
        }

        View::render('admin/doctors', [
            'pageTitle' => 'Управление врачами',
            'doctors'   => $doctors,
            'specs'     => $specs,
            'query'     => $query,
            'specId'    => $specId,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
            'csrf'      => Session::generateCsrfToken(),
        ]);
    }

    // GET /admin/services
    public function services(): void
    {

        $serviceRepo = new \App\Repositories\ServiceRepository();
        $doctorRepo  = new \App\Repositories\DoctorRepository();

        View::render('admin/services', [
            'pageTitle' => 'Управление услугами',
            'services'  => $serviceRepo->getAll(),
            'specs'     => $doctorRepo->getAllSpecializations(),
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
            'csrf'      => Session::generateCsrfToken(),
        ]);
    }

    // POST /admin/services/create
    public function createService(): void
    {

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Недействительный токен.');
            AuthMiddleware::redirect('/admin/services');
        }

        $name   = trim($_POST['name'] ?? '');
        $price  = (float)($_POST['price'] ?? 0);
        $specId = (int)($_POST['specialization_id'] ?? 0);
        $desc   = trim($_POST['description'] ?? '');

        if ($name === '' || $price <= 0) {
            Session::setFlash('error', 'Заполните название и цену.');
            AuthMiddleware::redirect('/admin/services');
        }

        (new \App\Repositories\ServiceRepository())->create($name, $price, $specId, $desc);
        Session::setFlash('success', 'Услуга добавлена.');
        AuthMiddleware::redirect('/admin/services');
    }

    // POST /admin/services/{id}/update
    public function updateService(string $id): void
    {

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Недействительный токен.');
            AuthMiddleware::redirect('/admin/services');
        }

        $repo   = new \App\Repositories\ServiceRepository();
        $name   = trim($_POST['name'] ?? '');
        $price  = (float)($_POST['price'] ?? 0);
        $specId = (int)($_POST['specialization_id'] ?? 0);
        $desc   = trim($_POST['description'] ?? '');

        if ($name === '' || $price <= 0) {
            Session::setFlash('error', 'Заполните название и цену.');
            AuthMiddleware::redirect('/admin/services');
        }

        $repo->update((int)$id, $name, $price, $specId, $desc);
        Session::setFlash('success', 'Услуга обновлена.');
        AuthMiddleware::redirect('/admin/services');
    }

    // POST /admin/services/{id}/delete
    public function deleteService(string $id): void
    {

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Недействительный токен.');
            AuthMiddleware::redirect('/admin/services');
        }

        (new \App\Repositories\ServiceRepository())->delete((int)$id);
        Session::setFlash('success', 'Услуга удалена.');
        AuthMiddleware::redirect('/admin/services');
    }

    // GET /admin/lab-tests
    public function labTests(): void
    {

        View::render('admin/lab_tests', [
            'pageTitle' => 'Управление анализами',
            'tests'     => (new \App\Repositories\LabTestRepository())->getAll(),
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
            'csrf'      => Session::generateCsrfToken(),
        ]);
    }

    // POST /admin/lab-tests/create
    public function createLabTest(): void
    {

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Недействительный токен.');
            AuthMiddleware::redirect('/admin/lab-tests');
        }

        $name     = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $price    = (float)($_POST['price'] ?? 0);
        $duration = max(1, (int)($_POST['duration_min'] ?? 15));
        $desc     = trim($_POST['description'] ?? '');
        $prep     = trim($_POST['preparation'] ?? '');

        if ($name === '' || $category === '' || $price <= 0) {
            Session::setFlash('error', 'Заполните название, категорию и цену.');
            AuthMiddleware::redirect('/admin/lab-tests');
        }

        (new \App\Repositories\LabTestRepository())->create($name, $category, $price, $duration, $desc, $prep);
        Session::setFlash('success', 'Анализ добавлен.');
        AuthMiddleware::redirect('/admin/lab-tests');
    }

    // POST /admin/lab-tests/{id}/update
    public function updateLabTest(string $id): void
    {

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Недействительный токен.');
            AuthMiddleware::redirect('/admin/lab-tests');
        }

        $name     = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $price    = (float)($_POST['price'] ?? 0);
        $duration = max(1, (int)($_POST['duration_min'] ?? 15));
        $desc     = trim($_POST['description'] ?? '');
        $prep     = trim($_POST['preparation'] ?? '');

        if ($name === '' || $category === '' || $price <= 0) {
            Session::setFlash('error', 'Заполните название, категорию и цену.');
            AuthMiddleware::redirect('/admin/lab-tests');
        }

        (new \App\Repositories\LabTestRepository())->update((int)$id, $name, $category, $price, $duration, $desc, $prep);
        Session::setFlash('success', 'Анализ обновлён.');
        AuthMiddleware::redirect('/admin/lab-tests');
    }

    // POST /admin/lab-tests/{id}/delete
    public function deleteLabTest(string $id): void
    {

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Недействительный токен.');
            AuthMiddleware::redirect('/admin/lab-tests');
        }

        (new \App\Repositories\LabTestRepository())->delete((int)$id);
        Session::setFlash('success', 'Анализ удалён.');
        AuthMiddleware::redirect('/admin/lab-tests');
    }

    // GET /admin/appointments/export
    public function exportCsv(): void
    {

        $from = $_GET['from'] ?? '';
        $to   = $_GET['to']   ?? '';

        $rows = $this->appointments->getAllForExport($from, $to);

        // Заголовки HTTP для скачивания файла
        $filename = 'appointments_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        // BOM для корректного открытия в Excel
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');

        // Заголовки столбцов
        fputcsv($out, [
            'ID', 'Дата и время', 'Статус', 'Тип', 'Пациент', 'Телефон',
            'Врач / Анализ', 'Специализация', 'Создана'
        ], ';');

        $statusLabels = [
            'pending'     => 'Ожидает',
            'confirmed'   => 'Подтверждена',
            'in_progress' => 'Идёт приём',
            'completed'   => 'Завершена',
            'cancelled'   => 'Отменена',
        ];
        $typeLabels = [
            'doctor'   => 'Врач',
            'lab_test' => 'Анализ',
        ];

        foreach ($rows as $row) {
            fputcsv($out, [
                $row['id'],
                date('d.m.Y H:i', strtotime($row['scheduled_at'])),
                $statusLabels[$row['status']] ?? $row['status'],
                $typeLabels[$row['appointment_type']] ?? $row['appointment_type'],
                $row['patient_name'],
                $row['patient_phone'] ?? '—',
                $row['appointment_type'] === 'lab_test'
                    ? $row['lab_test_name']
                    : $row['doctor_name'],
                $row['specialization'],
                date('d.m.Y', strtotime($row['created_at'])),
            ], ';');
        }

        fclose($out);
        exit;
    }

    // GET /admin/doctors/create
    public function createDoctorForm(): void
    {

        $specializations = $this->repo->getAllSpecializations();

        View::render('admin/doctor_form', [
            'pageTitle'       => 'Добавить врача',
            'specializations' => $specializations,
            'doctor'          => null,
            'csrf'            => Session::generateCsrfToken(),
            'error'           => Session::getFlash('error'),
        ]);
    }

    // POST /admin/doctors/create
    public function createDoctor(): void
    {
        $this->validateCsrf();

        $email    = trim($_POST['email']      ?? '');
        $password = trim($_POST['password']   ?? '');
        $name     = trim($_POST['full_name']  ?? '');
        $specId   = (int) ($_POST['specialization_id'] ?? 0);
        $bio      = trim($_POST['bio']        ?? '');

        // Валидация
        if (!$email || !$password || !$name || !$specId) {
            Session::setFlash('error', 'Заполните все обязательные поля.');
            AuthMiddleware::redirect('/admin/doctors/create');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('error', 'Некорректный email.');
            AuthMiddleware::redirect('/admin/doctors/create');
        }

        if (strlen($password) < 8) {
            Session::setFlash('error', 'Пароль должен быть не менее 8 символов.');
            AuthMiddleware::redirect('/admin/doctors/create');
        }

        // Проверка дубликата email
        if ($this->users->emailExists($email)) {
            Session::setFlash('error', 'Пользователь с таким email уже существует.');
            AuthMiddleware::redirect('/admin/doctors/create');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $this->repo->createDoctor($email, $hash, $name, $specId, $bio);

        Session::setFlash('success', 'Врач добавлен. Логин: ' . $email);
        AuthMiddleware::redirect('/admin/doctors');
    }

    // GET /admin/doctors/{id}/edit
    public function editDoctorForm(string $id): void
    {

        $doctor          = $this->repo->findDoctorById((int) $id);
        $specializations = $this->repo->getAllSpecializations();

        if (!$doctor) {
            Session::setFlash('error', 'Врач не найден.');
            AuthMiddleware::redirect('/admin/doctors');
        }

        View::render('admin/doctor_form', [
            'pageTitle'       => 'Редактировать врача',
            'specializations' => $specializations,
            'doctor'          => $doctor,
            'csrf'            => Session::generateCsrfToken(),
            'error'           => Session::getFlash('error'),
        ]);
    }

    // POST /admin/doctors/{id}/edit
    public function updateDoctor(string $id): void
    {
        $this->validateCsrf();

        $doctorId = (int) $id;
        $name     = trim($_POST['full_name']           ?? '');
        $specId   = (int) ($_POST['specialization_id'] ?? 0);
        $bio      = trim($_POST['bio']                 ?? '');

        if (!$name || !$specId) {
            Session::setFlash('error', 'Заполните обязательные поля.');
            AuthMiddleware::redirect('/admin/doctors/' . $doctorId . '/edit');
        }

        $this->repo->updateDoctor($doctorId, $name, $specId, $bio);

        Session::setFlash('success', 'Данные врача обновлены.');
        AuthMiddleware::redirect('/admin/doctors');
    }

    // POST /admin/doctors/{id}/deactivate
    public function deactivateDoctor(string $id): void
    {
        $this->validateCsrf();

        $this->repo->deactivateDoctor((int) $id);
        Session::setFlash('success', 'Врач деактивирован.');
        AuthMiddleware::redirect('/admin/doctors');
    }

    // POST /admin/doctors/{id}/activate
    public function activateDoctor(string $id): void
    {
        $this->validateCsrf();

        $this->repo->activateDoctor((int) $id);
        Session::setFlash('success', 'Врач активирован.');
        AuthMiddleware::redirect('/admin/doctors');
    }

}