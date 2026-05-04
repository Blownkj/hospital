<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/vendor/autoload.php';

use App\Core\Router;
use App\Core\Session;
use App\Controllers\AuthController;
use App\Controllers\PublicController;
use App\Controllers\PatientController;
use App\Controllers\DoctorController;
use App\Controllers\AdminController;

$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();
define('BASE_URL', rtrim($_ENV['APP_URL'], '/'));

Session::start();

$router = new Router();

// ── Публичный сайт ────────────────────────────────────────────────────────
$router->get('/',         [PublicController::class, 'home']);
$router->get('/doctors',  [PublicController::class, 'doctors']);
$router->get('/doctors/{id}', [PublicController::class, 'doctor']);
$router->get('/services', [PublicController::class, 'services']);
$router->get('/contact',  [PublicController::class, 'contact']);
$router->post('/contact', [PublicController::class, 'contactSend']);
$router->get('/about',            [PublicController::class, 'about']);
$router->get('/faq',              [PublicController::class, 'faq']);
$router->get('/articles',         [PublicController::class, 'articles']);
$router->get('/articles/{slug}',  [PublicController::class, 'article']);

// ── Авторизация ───────────────────────────────────────────────────────────
$router->get('/login',     [AuthController::class, 'showLogin']);
$router->post('/login',    [AuthController::class, 'doLogin']);
$router->get('/register',  [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'doRegister']);
$router->get('/logout',    [AuthController::class, 'logout']);

// ── Пациент ───────────────────────────────────────────────────────────────
$router->get('/patient/dashboard',               [PatientController::class, 'dashboard']);
$router->get('/patient/book',                    [PatientController::class, 'book']);
$router->post('/patient/book',                   [PatientController::class, 'doBook']);
$router->get('/patient/book/analysis',           [PatientController::class, 'bookAnalysis']);
$router->post('/patient/book/analysis',          [PatientController::class, 'doBookAnalysis']);
$router->get('/patient/appointments',            [PatientController::class, 'appointments']);
$router->post('/patient/appointments/cancel',    [PatientController::class, 'cancelAppointment']);
$router->get('/patient/medical-record',          [PatientController::class, 'medicalRecord']);
$router->get('/patient/profile',                 [PatientController::class, 'profile']);
$router->post('/patient/profile',                [PatientController::class, 'updateProfile']);
$router->get('/patient/reviews',                 [PatientController::class, 'reviews']);
$router->post('/patient/reviews/submit',         [PatientController::class, 'submitReview']);
$router->get('/patient/visit/{visitId}/print',   [PatientController::class, 'printVisit']);
$router->post('/patient/profile/password',       [PatientController::class, 'changePassword']);

// ── Врач ──────────────────────────────────────────────────────────────────
$router->get('/doctor/dashboard',                             [DoctorController::class, 'dashboard']);
$router->get('/doctor/appointment/{id}',                      [DoctorController::class, 'appointment']);
$router->post('/doctor/appointment/{id}/start',               [DoctorController::class, 'startAppointment']);
$router->post('/doctor/appointment/{id}/protocol',            [DoctorController::class, 'saveProtocol']);
$router->post('/doctor/appointment/{id}/prescription/add',    [DoctorController::class, 'addPrescription']);
$router->post('/doctor/appointment/{id}/prescription/delete', [DoctorController::class, 'deletePrescription']);
$router->get('/doctor/profile',  [DoctorController::class, 'profile']);
$router->post('/doctor/profile', [DoctorController::class, 'updateProfile']);


// ── Администратор ─────────────────────────────────────────────────────────
$router->get('/admin/dashboard',                    [AdminController::class, 'dashboard']);
$router->get('/admin/appointments',                 [AdminController::class, 'appointments']);
$router->post('/admin/appointment/{id}/confirm',    [AdminController::class, 'confirmAppointment']);
$router->post('/admin/appointment/{id}/cancel',     [AdminController::class, 'cancelAppointment']);
$router->post('/admin/appointment/{id}/reschedule', [AdminController::class, 'rescheduleAppointment']);
$router->get('/admin/schedule',                     [AdminController::class, 'schedule']);
$router->post('/admin/schedule/{doctorId}/save',    [AdminController::class, 'saveSchedule']);
$router->get('/admin/reviews',                      [AdminController::class, 'reviews']);
$router->post('/admin/review/{id}/approve',         [AdminController::class, 'approveReview']);
$router->post('/admin/review/{id}/delete',          [AdminController::class, 'deleteReview']);
$router->post('/admin/review/{id}/reply',           [AdminController::class, 'replyToReview']);
$router->get('/admin/doctors',                    [AdminController::class, 'doctors']);
$router->get('/admin/doctors/create',             [AdminController::class, 'createDoctorForm']);
$router->post('/admin/doctors/create',            [AdminController::class, 'createDoctor']);
$router->get('/admin/doctors/{id}/edit',          [AdminController::class, 'editDoctorForm']);
$router->post('/admin/doctors/{id}/edit',         [AdminController::class, 'updateDoctor']);
$router->post('/admin/doctors/{id}/deactivate',   [AdminController::class, 'deactivateDoctor']);
$router->post('/admin/doctors/{id}/activate',     [AdminController::class, 'activateDoctor']);
$router->get('/admin/services',                  [AdminController::class, 'services']);
$router->post('/admin/services/create',          [AdminController::class, 'createService']);
$router->post('/admin/services/{id}/update',     [AdminController::class, 'updateService']);
$router->post('/admin/services/{id}/delete',     [AdminController::class, 'deleteService']);
$router->get('/admin/lab-tests',                 [AdminController::class, 'labTests']);
$router->post('/admin/lab-tests/create',         [AdminController::class, 'createLabTest']);
$router->post('/admin/lab-tests/{id}/update',    [AdminController::class, 'updateLabTest']);
$router->post('/admin/lab-tests/{id}/delete',    [AdminController::class, 'deleteLabTest']);
$router->get('/admin/appointments/export', [AdminController::class, 'exportCsv']);

// Управление ошибками в зависимости от окружения
if ($_ENV['APP_ENV'] === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// ── Запуск ────────────────────────────────────────────────────────────────
try {
    $router->dispatch();
} catch (\Throwable $e) {
    error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

    if ($_ENV['APP_ENV'] !== 'production') {
        throw $e; // В разработке — показываем полную ошибку
    }

    http_response_code(500);
    View::render('errors/500');
}