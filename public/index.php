<?php
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

// Управление ошибками в зависимости от окружения — должно быть первым после загрузки env
if (($_ENV['APP_ENV'] ?? 'development') === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// HTTP security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; style-src 'self' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; script-src 'self'");

use App\Core\Router;
use App\Core\Session;
use App\Controllers\AuthController;
use App\Controllers\PublicController;
use App\Controllers\PatientController;
use App\Controllers\DoctorController;
use App\Controllers\AdminController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\OwnerMiddleware;

define('BASE_URL', rtrim($_ENV['APP_URL'], '/'));

Session::start();

$router = new Router();

// ── Middleware closures ───────────────────────────────────────────────────
$mwGuest   = static fn() => AuthMiddleware::requireGuest();
$mwPatient = static fn() => AuthMiddleware::requireRole('patient');
$mwDoctor  = static fn() => AuthMiddleware::requireRole('doctor');
$mwAdmin   = static fn() => AuthMiddleware::requireRole('admin');

// Глобальный CSRF — автоматически валидирует все POST-запросы
$router->use(static fn() => CsrfMiddleware::handle());

// ── Публичный сайт ────────────────────────────────────────────────────────
$router->get('/',                [PublicController::class, 'home']);
$router->get('/doctors',         [PublicController::class, 'doctors']);
$router->get('/doctors/{id}',    [PublicController::class, 'doctor']);
$router->get('/services',        [PublicController::class, 'services']);
$router->get('/contact',  [PublicController::class, 'contact']);
$router->post('/contact', [PublicController::class, 'contactSend'], [RateLimitMiddleware::make('contact', 5, 3600)]);
$router->get('/about',           [PublicController::class, 'about']);
$router->get('/faq',             [PublicController::class, 'faq']);
$router->get('/articles',        [PublicController::class, 'articles']);
$router->get('/articles/{slug}', [PublicController::class, 'article']);

// ── Авторизация (только для гостей) ──────────────────────────────────────
$router->group('', [$mwGuest], function (Router $r): void {
    $r->get('/login',    [AuthController::class, 'showLogin']);
    $r->post('/login',   [AuthController::class, 'doLogin'],    [RateLimitMiddleware::make('login', 5, 900)]);
    $r->get('/register', [AuthController::class, 'showRegister']);
    $r->post('/register',[AuthController::class, 'doRegister'], [RateLimitMiddleware::make('register', 5, 3600)]);
});
$router->post('/logout', [AuthController::class, 'logout']);

// ── Пациент ───────────────────────────────────────────────────────────────
$router->group('/patient', [$mwPatient], function (Router $r): void {
    $r->get('/dashboard',            [PatientController::class, 'dashboard']);
    $r->get('/book',                 [PatientController::class, 'book']);
    $r->post('/book',                [PatientController::class, 'doBook']);
    $r->get('/book/analysis',        [PatientController::class, 'bookAnalysis']);
    $r->post('/book/analysis',       [PatientController::class, 'doBookAnalysis']);
    $r->get('/appointments',         [PatientController::class, 'appointments']);
    $r->post('/appointments/cancel', [PatientController::class, 'cancelAppointment']);
    $r->get('/medical-record',       [PatientController::class, 'medicalRecord']);
    $r->get('/profile',              [PatientController::class, 'profile']);
    $r->post('/profile',             [PatientController::class, 'updateProfile']);
    $r->post('/profile/password',    [PatientController::class, 'changePassword']);
    $r->get('/reviews',              [PatientController::class, 'reviews']);
    $r->post('/reviews/submit',      [PatientController::class, 'submitReview']);
    $r->get('/visit/{visitId}/print', [PatientController::class, 'printVisit'], [OwnerMiddleware::visit()]);
});

// ── Врач ──────────────────────────────────────────────────────────────────
$router->group('/doctor', [$mwDoctor], function (Router $r): void {
    $r->get('/dashboard',                          [DoctorController::class, 'dashboard']);
    $r->get('/appointment/{id}',                   [DoctorController::class, 'appointment']);
    $r->post('/appointment/{id}/start',            [DoctorController::class, 'startAppointment']);
    $r->post('/appointment/{id}/protocol',         [DoctorController::class, 'saveProtocol']);
    $r->post('/appointment/{id}/prescription/add', [DoctorController::class, 'addPrescription']);
    $r->post('/appointment/{id}/prescription/delete', [DoctorController::class, 'deletePrescription']);
    $r->get('/profile',                            [DoctorController::class, 'profile']);
    $r->post('/profile',                           [DoctorController::class, 'updateProfile']);
});

// ── Администратор ─────────────────────────────────────────────────────────
$router->group('/admin', [$mwAdmin], function (Router $r): void {
    $r->get('/dashboard',                    [AdminController::class, 'dashboard']);
    $r->get('/appointments',                 [AdminController::class, 'appointments']);
    $r->get('/appointments/export',          [AdminController::class, 'exportCsv']);
    $r->post('/appointment/{id}/confirm',    [AdminController::class, 'confirmAppointment']);
    $r->post('/appointment/{id}/cancel',     [AdminController::class, 'cancelAppointment']);
    $r->post('/appointment/{id}/reschedule', [AdminController::class, 'rescheduleAppointment']);
    $r->get('/schedule',                     [AdminController::class, 'schedule']);
    $r->post('/schedule/{doctorId}/save',    [AdminController::class, 'saveSchedule']);
    $r->get('/reviews',                      [AdminController::class, 'reviews']);
    $r->post('/review/{id}/approve',         [AdminController::class, 'approveReview']);
    $r->post('/review/{id}/delete',          [AdminController::class, 'deleteReview']);
    $r->post('/review/{id}/reply',           [AdminController::class, 'replyToReview']);
    $r->get('/doctors',                      [AdminController::class, 'doctors']);
    $r->get('/doctors/create',               [AdminController::class, 'createDoctorForm']);
    $r->post('/doctors/create',              [AdminController::class, 'createDoctor']);
    $r->get('/doctors/{id}/edit',            [AdminController::class, 'editDoctorForm']);
    $r->post('/doctors/{id}/edit',           [AdminController::class, 'updateDoctor']);
    $r->post('/doctors/{id}/deactivate',     [AdminController::class, 'deactivateDoctor']);
    $r->post('/doctors/{id}/activate',       [AdminController::class, 'activateDoctor']);
    $r->get('/services',                     [AdminController::class, 'services']);
    $r->post('/services/create',             [AdminController::class, 'createService']);
    $r->post('/services/{id}/update',        [AdminController::class, 'updateService']);
    $r->post('/services/{id}/delete',        [AdminController::class, 'deleteService']);
    $r->get('/lab-tests',                    [AdminController::class, 'labTests']);
    $r->post('/lab-tests/create',            [AdminController::class, 'createLabTest']);
    $r->post('/lab-tests/{id}/update',       [AdminController::class, 'updateLabTest']);
    $r->post('/lab-tests/{id}/delete',       [AdminController::class, 'deleteLabTest']);
});

// ── Запуск ────────────────────────────────────────────────────────────────
try {
    $router->dispatch();
} catch (\Throwable $e) {
    error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

    if (($_ENV['APP_ENV'] ?? 'development') !== 'production') {
        throw $e; // В разработке — показываем полную ошибку
    }

    http_response_code(500);
    View::render('errors/500');
}