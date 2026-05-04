<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Middleware\AuthMiddleware;
use App\Repositories\AppointmentRepository;
use App\Repositories\DoctorRepository;
use App\Repositories\LabTestRepository;
use App\Repositories\PatientRepository;
use App\Repositories\UserRepository;
use App\Services\AppointmentService;
use App\Repositories\VisitRepository;
use App\Repositories\ReviewRepository;

class PatientController extends BaseController
{
    private PatientRepository     $patients;
    private DoctorRepository      $doctors;
    private AppointmentRepository $appointments;
    private AppointmentService    $appointmentService;
    private LabTestRepository     $labTests;
    private VisitRepository       $visits;
    private ReviewRepository      $reviews;
    private UserRepository        $users;

    public function __construct()
    {
        AuthMiddleware::requireRole('patient');
        $this->patients           = new PatientRepository();
        $this->doctors            = new DoctorRepository();
        $this->appointments       = new AppointmentRepository();
        $this->appointmentService = new AppointmentService();
        $this->labTests           = new LabTestRepository();
        $this->visits             = new VisitRepository();
        $this->reviews            = new ReviewRepository();
        $this->users              = new UserRepository();
    }

    // ── Дашборд ───────────────────────────────────────────────────────────

    public function dashboard(): void
    {
        $patient  = $this->currentPatient();
        $all      = $this->appointments->getByPatientId($patient['id']);
        $upcoming = array_values(array_filter($all, fn($a) =>
            in_array($a['status'], ['pending','confirmed'], true) &&
            strtotime($a['scheduled_at']) >= time()
        ));

        View::render('patient/dashboard', [
            'pageTitle' => 'Личный кабинет',
            'patient'   => $patient,
            'upcoming'  => $upcoming,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    // ── Выбор типа записи (врач или анализ) ──────────────────────────────

    public function bookChoose(): void
    {
        View::render('patient/book_choose', [
            'pageTitle' => 'Запись',
        ]);
    }

    // GET /patient/visit/{visitId}/print
    public function printVisit(string $visitId): void
    {
        AuthMiddleware::requireRole('patient');
        $patient = $this->currentPatient();

        $visit = $this->visits->findByIdForPatient((int)$visitId, (int)$patient['id']);

        if (!$visit) {
            http_response_code(403);
            die('Визит не найден.');
        }

        $prescriptions = $this->visits->getPrescriptions((int) $visitId);

        View::render('patient/print_visit', [
            'visit'         => $visit,
            'prescriptions' => $prescriptions,
        ]);
    }



    // GET /patient/profile
    public function profile(): void
    {
        AuthMiddleware::requireRole('patient');
        $patient = $this->currentPatient();

        View::render('patient/profile', [
            'pageTitle' => 'Мой профиль',
            'patient'   => $patient,
            'flash'     => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    // POST /patient/profile
    public function updateProfile(): void
    {
        AuthMiddleware::requireRole('patient');
        $this->validateCsrf();

        $patient  = $this->currentPatient();
        $fullName = trim($_POST['full_name']        ?? '');
        $phone    = trim($_POST['phone']            ?? '');
        $address  = trim($_POST['address']          ?? '');
        $chronic  = trim($_POST['chronic_diseases'] ?? '');
        $birthDate = trim($_POST['birth_date']      ?? '');

        if (mb_strlen($fullName) < 2) {
            Session::setFlash('error', 'Укажите полное имя.');
            AuthMiddleware::redirect('/patient/profile');
        }

        $this->patients->update(
            (int)$patient['id'], $fullName, $phone, $address, $chronic, $birthDate
        );

        Session::setFlash('success', 'Профиль обновлён.');
        AuthMiddleware::redirect('/patient/profile');
    }

    // ── Запись к врачу: выбор специализации → врача → даты → слота ───────
    public function book(): void
    {
        $specId   = isset($_GET['spec_id'])   ? (int)$_GET['spec_id']   : null;
        $doctorId = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : null;
        $date     = $_GET['date'] ?? null;

        // Все специализации с кол-вом врачей
        $allDoctors = $this->doctors->getAllWithRating();
        $specs = [];
        foreach ($allDoctors as $d) {
            $specs[$d['specialization_id']] = [
                'id'    => $d['specialization_id'],
                'name'  => $d['specialization'],
                'count' => ($specs[$d['specialization_id']]['count'] ?? 0) + 1,
            ];
        }

        // Иконки специализаций
        $specIcons = [
            'Терапевт'   => '🩺', 'Кардиолог'  => '❤️',
            'Невролог'   => '🧠', 'Дерматолог' => '🔬',
            'Хирург'     => '🔪',
        ];

        $selectedSpec   = null;
        $filteredDoctors = [];
        $selectedDoctor = null;
        $workingDays    = [];
        $slots          = [];

        if ($specId) {
            $selectedSpec    = $specs[$specId] ?? null;
            $filteredDoctors = array_values(array_filter(
                $allDoctors, fn($d) => (int)$d['specialization_id'] === $specId
            ));
        }

        if ($doctorId) {
            $selectedDoctor = $this->doctors->findById($doctorId);
            if (!$selectedDoctor) {
                Session::setFlash('error', 'Врач не найден.');
                AuthMiddleware::redirect('/patient/book');
            }
            $workingDays = $this->appointmentService->getWorkingDays($doctorId);
            if (!$date && !empty($workingDays)) {
                $date = $workingDays[0];
            }
            if ($date) {
                $slots = $this->appointmentService->getSlots($doctorId, $date);
            }
        }

        View::render('patient/book', [
            'pageTitle'       => 'Запись к врачу',
            'specs'           => $specs,
            'specIcons'       => $specIcons,
            'selectedSpec'    => $selectedSpec,
            'filteredDoctors' => $filteredDoctors,
            'selectedDoctor'  => $selectedDoctor,
            'workingDays'     => $workingDays,
            'selectedDate'    => $date,
            'slots'           => $slots,
            'error'           => Session::getFlash('error'),
        ]);
    }

    public function doBook(): void
    {
        $patient = $this->currentPatient();

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Недействительный токен.');
            AuthMiddleware::redirect('/patient/book');
        }

        $doctorId = (int)($_POST['doctor_id'] ?? 0);
        $date     = $_POST['date'] ?? '';
        $time     = $_POST['time'] ?? '';

        $errors = $this->appointmentService->book($patient['id'], $doctorId, $date, $time);

        if (!empty($errors)) {
            Session::setFlash('error', reset($errors));
            AuthMiddleware::redirect('/patient/book?doctor_id=' . $doctorId . '&date=' . $date);
        }

        Session::setFlash('success', 'Вы записаны на ' . date('d.m.Y', strtotime($date)) . ' в ' . $time);
        AuthMiddleware::redirect('/patient/appointments');
    }
    
    // POST /patient/profile/password
    public function changePassword(): void
    {
        AuthMiddleware::requireRole('patient');
        $this->validateCsrf();

        $patient = $this->currentPatient();

        $current = $_POST['current_password']  ?? '';
        $new     = $_POST['new_password']      ?? '';
        $confirm = $_POST['confirm_password']  ?? '';

        if (strlen($new) < 8) {
            Session::setFlash('error', 'Новый пароль должен быть не менее 8 символов.');
            AuthMiddleware::redirect('/patient/profile');
        }

        if ($new !== $confirm) {
            Session::setFlash('error', 'Пароли не совпадают.');
            AuthMiddleware::redirect('/patient/profile');
        }

        $ok = $this->users->changePassword((int)Session::get('user_id'), $current, $new);

        if (!$ok) {
            Session::setFlash('error', 'Текущий пароль введён неверно.');
            AuthMiddleware::redirect('/patient/profile');
        }

        Session::setFlash('success', 'Пароль успешно изменён.');
        AuthMiddleware::redirect('/patient/profile');
    }

    // ── Запись на анализ ──────────────────────────────────────────────────
    public function bookAnalysis(): void
    {
        $grouped = $this->labTests->getAllGrouped();
        $selectedId = isset($_GET['test_id']) ? (int)$_GET['test_id'] : null;
        $date       = $_GET['date'] ?? null;
        $selectedTest = null;
        $slots = [];
        $availableDates = [];

        if ($selectedId) {
            $selectedTest = $this->labTests->findById($selectedId);
        }

        if ($selectedTest) {
            // Лаборатория работает Пн–Пт 08:00–18:00, слот 15 мин
            for ($i = 0; $i < 14; $i++) {
                $ts = strtotime("+$i days");
                $dow = (int)date('N', $ts);
                if ($dow <= 5) { // Пн–Пт
                    $availableDates[] = date('Y-m-d', $ts);
                }
            }
            if (!$date && !empty($availableDates)) {
                $date = $availableDates[0];
            }
            if ($date) {
                $booked = $this->labTests->getBookedTimes($date);
                $current = strtotime($date . ' 08:00');
                $end     = strtotime($date . ' 18:00');
                while ($current + 900 <= $end) {
                    $t = date('H:i', $current);
                    $isPast = ($date === date('Y-m-d')) && ($current <= time());
                    $slots[] = [
                        'time'      => $t,
                        'datetime'  => $date . ' ' . $t . ':00',
                        'available' => !$isPast && !in_array($t, $booked, true),
                    ];
                    $current += 900;
                }
            }
        }

        View::render('patient/book_analysis', [
            'pageTitle'      => 'Запись на анализ',
            'grouped'        => $grouped,
            'selectedTest'   => $selectedTest,
            'availableDates' => $availableDates,
            'selectedDate'   => $date,
            'slots'          => $slots,
            'error'          => Session::getFlash('error'),
        ]);
    }

    public function doBookAnalysis(): void
    {
        $patient = $this->currentPatient();

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Недействительный токен.');
            AuthMiddleware::redirect('/patient/book/analysis');
        }

        $labTestId   = (int)($_POST['lab_test_id'] ?? 0);
        $date        = $_POST['date'] ?? '';
        $time        = $_POST['time'] ?? '';

        if (!$labTestId || !$date || !$time) {
            Session::setFlash('error', 'Заполните все поля.');
            AuthMiddleware::redirect('/patient/book/analysis?test_id=' . $labTestId . '&date=' . $date);
        }

        $scheduledAt = $date . ' ' . $time . ':00';
        $this->labTests->bookTest($patient['id'], $labTestId, $scheduledAt);

        $test = $this->labTests->findById($labTestId);
        Session::setFlash('success',
            'Вы записаны на «' . ($test['name'] ?? 'анализ') . '» — ' .
            date('d.m.Y', strtotime($date)) . ' в ' . $time
        );
        AuthMiddleware::redirect('/patient/appointments');
    }

    // ── Список записей ────────────────────────────────────────────────────

    public function appointments(): void
    {
        $patient = $this->currentPatient();

        View::render('patient/appointments', [
            'pageTitle'    => 'Мои записи',
            'appointments' => $this->appointments->getByPatientId($patient['id']),
            'flash'        => Session::getFlash('success'),
            'error'        => Session::getFlash('error'),
        ]);
    }

    // GET /patient/medical-record
    public function medicalRecord(): void
    {
        AuthMiddleware::requireRole('patient');
        $patient = $this->currentPatient();

        $visits = $this->visits->getFullHistoryForPatient((int) $patient['id']);

        View::render('patient/medical_record', [
            'pageTitle' => 'Моя медицинская карта',
            'patient'   => $patient,
            'visits'    => $visits,
            'flash'     => Session::getFlash('success'),
        ]);
    }

    public function cancelAppointment(): void
    {
        $patient = $this->currentPatient();

        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Недействительный токен.');
            AuthMiddleware::redirect('/patient/appointments');
        }

        $ok = $this->appointments->cancelByPatient(
            (int)($_POST['appointment_id'] ?? 0), $patient['id']
        );

        Session::setFlash($ok ? 'success' : 'error', $ok ? 'Запись отменена.' : 'Не удалось отменить.');
        AuthMiddleware::redirect('/patient/appointments');
    }

        // GET /patient/reviews
    public function reviews(): void
    {
        AuthMiddleware::requireRole('patient');
        $patient = $this->currentPatient();

        $myReviews      = $this->reviews->getByPatient((int) $patient['id']);
        $visitedDoctors = $this->reviews->getDoctorsVisited((int) $patient['id']);

        // Убираем врачей, на которых отзыв уже есть
        $reviewed = array_column($myReviews, 'doctor_name');
        $canReview = array_filter(
            $visitedDoctors,
            fn($d) => !$this->reviews->exists((int) $patient['id'], (int) $d['id'])
        );

        View::render('patient/reviews', [
            'pageTitle'   => 'Мои отзывы',
            'myReviews'   => $myReviews,
            'canReview'   => array_values($canReview),
            'csrf'        => Session::generateCsrfToken(),
            'flash'       => Session::getFlash('success'),
            'error'       => Session::getFlash('error'),
        ]);
    }

    // POST /patient/reviews/submit
    public function submitReview(): void
    {
        AuthMiddleware::requireRole('patient');
        $this->validateCsrf();

        $patient  = $this->currentPatient();
        $doctorId = (int) ($_POST['doctor_id'] ?? 0);
        $rating   = (int) ($_POST['rating']    ?? 0);
        $text     = trim($_POST['text']        ?? '');

        if ($rating < 1 || $rating > 5) {
            Session::setFlash('error', 'Выберите оценку от 1 до 5.');
            AuthMiddleware::redirect('/patient/reviews');
        }

        if (mb_strlen($text) < 10) {
            Session::setFlash('error', 'Напишите отзыв (минимум 10 символов).');
            AuthMiddleware::redirect('/patient/reviews');
        }

        if ($this->reviews->exists((int) $patient['id'], $doctorId)) {
            Session::setFlash('error', 'Вы уже оставляли отзыв этому врачу.');
            AuthMiddleware::redirect('/patient/reviews');
        }

        $this->reviews->create((int) $patient['id'], $doctorId, $rating, $text);
        Session::setFlash('success', 'Отзыв отправлен на модерацию. Спасибо!');
        AuthMiddleware::redirect('/patient/reviews');
    }

    /**
     * Профиль пациента по сессии. Если записи в БД нет — сбрасываем сессию и редирект на вход
     * (иначе requireGuest() уводит в цикл: логин → дашборд → ошибка).
     */
    private function currentPatient(): array
    {
        $userId = Session::get('user_id');
        if (!is_int($userId) && !is_string($userId)) {
            Session::destroy();
            Session::start();
            Session::setFlash('error', 'Сессия недействительна. Войдите снова.');
            AuthMiddleware::redirect('/login');
        }

        $patient = $this->patients->findByUserId((int)$userId);
        if ($patient === null) {
            Session::destroy();
            Session::start();
            Session::setFlash(
                'error',
                'Профиль пациента не найден в базе. Войдите снова или зарегистрируйтесь через форму регистрации.'
            );
            AuthMiddleware::redirect('/login');
        }

        return $patient;
    }
}