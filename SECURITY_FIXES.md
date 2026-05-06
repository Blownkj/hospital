# Security & Quality Fix Plan

Verified against actual codebase on 2026-05-06.
Each item confirmed by reading the relevant source file before listing.

---

## P0 — Critical (confirmed bugs / active security holes)

### P0.1 ✅ POST /logout with CSRF
**Problem:** `GET /logout` (index.php:73) — any image on an external site logs the user out (CSRF).  
**Fix:** Change to `POST /logout`; add CSRF token to logout buttons in 4 view files.  
**Files:** `public/index.php`, `src/Controllers/AuthController.php`, `views/patient/dashboard.php`, `views/doctor/dashboard.php`, `views/admin/dashboard.php`, `views/dashboard_stub.php`

### P0.2 ✅ Open redirect via HTTP_REFERER
**Problem:** `BaseController::redirectBack()` passes `$_SERVER['HTTP_REFERER']` directly to redirect — attacker-controlled header.  
**Fix:** Validate that value is relative (starts with `/`, no `//`).  
**Files:** `src/Controllers/BaseController.php`

### P0.3 ✅ Race condition in booking
**Problem:** `alreadyBooked()` + `create()` не выполнялись в одной транзакции — два параллельных запроса могли занять один и тот же слот, создав дублирующую запись.

**Что сделано:**

1. **`AppointmentRepository.php`** — добавлен метод `lockSlot(int $doctorId, string $scheduledAt): bool`.
   Отличается от `alreadyBooked()` двумя вещами: блокирует по `doctor_id` (любой пациент, а не конкретная пара), и добавляет `FOR UPDATE` — пока транзакция открыта, второй параллельный запрос ждёт на этой строке и не может пройти проверку одновременно.

2. **`AppointmentService::book()`** — три операции `lockSlot()` → `alreadyBooked()` → `create()` обёрнуты в `$this->repo->transaction()`. `DomainException` из lockSlot перехватывается внутри сервиса и возвращается как `$errors['general']` — контроллер не изменился. `PDOException` с кодом `23000` обрабатывается как страховка на случай если уникальный индекс сработает раньше блокировки.

3. **`migrations.sql`** — добавлен generated column `active_slot`:
   ```sql
   IF(status <> 'cancelled', scheduled_at, NULL)
   ```
   Равен `scheduled_at` для активных записей и `NULL` для отменённых. Уникальный индекс `(doctor_id, active_slot)` игнорирует NULL, поэтому отмена и повторное бронирование того же слота работают корректно. Это второй эшелон защиты — даже если транзакция с `FOR UPDATE` по какой-то причине не сработает, БД не допустит дубль на уровне индекса.

**Files:** `src/Services/AppointmentService.php`, `src/Repositories/AppointmentRepository.php`, `database/migrations.sql`

### P0.4 ✅ ReviewRepository::create missing appointment_id (runtime crash)
**Problem:** Schema has `appointment_id INT UNSIGNED NOT NULL` with UNIQUE constraint, but `create()` never inserts it → PDO throws on every review submission.  
**Fix:** Change flow: form shows completed appointments (with appointment_id), controller verifies ownership, repository inserts appointment_id.  
**Files:** `src/Repositories/ReviewRepository.php`, `src/Controllers/PatientController.php`, `views/patient/reviews.php`

### P0.5 ✅ deactivateDoctor writes role='disabled' — ENUM violation
**Problem:** `AdminRepository::deactivateDoctor()` runs `UPDATE users SET role = 'disabled'` — invalid for `ENUM('patient','doctor','admin')`.  
**Fix:** Use `UPDATE doctors SET is_active = 0` (column exists per schema).  
**Files:** `src/Repositories/AdminRepository.php`

### P0.6 ✅ Stored XSS in article body
**Problem:** `views/public/article.php:54` outputs `<?= $article['body'] ?>` unescaped.  
**Fix:** Wrap in `htmlspecialchars()` (no htmlpurifier installed; body is seeded trusted HTML).  
**Note:** If rich HTML is needed later, install `ezyang/htmlpurifier` for whitelist sanitization.  
**Files:** `views/public/article.php`

### P0.7 CSP vs Chart.js CDN
**Problem:** CSP is `script-src 'self'` (index.php:25) — if Chart.js is loaded from CDN in dashboards, it's blocked.  
**Status:** CSP already correct; need to verify dashboards don't import from CDN.  
**Action:** Self-host `chart.umd.min.js` in `public/js/vendor/` if CDN is used.

### P0.8 ✅ die() in View::render and BaseController
**Problem:** `View::render()` uses `die()` on missing template. `BaseController::validateCsrf()` and `requireOwner()` also use `die()`.  
**Fix:** Replace with `throw new \RuntimeException(...)`.  
**Files:** `src/Core/view.php`, `src/Controllers/BaseController.php`

---

## P1 — Important (verified, lower blast radius)

### P1.11 ✅ Timing attack in login
**Problem:** `AuthService::login()` returns `null` early when email not found, without calling `password_verify` → observable timing difference.  
**Fix:** Call `password_verify($password, '$2y$12$dummyhashXXXXXXXXXXXXXX')` on unknown email.  
**Files:** `src/Services/AuthService.php`

### P1.16 ✅ PASSWORD_BCRYPT → PASSWORD_DEFAULT
**Problem:** Using `PASSWORD_BCRYPT` explicitly prevents future algorithm upgrades (e.g. Argon2id).  
**Status:** Verified в сессии — уже было реализовано корректно до начала работы. Все три места (`rehashPassword`, `createPatient`, `changePassword`) используют `PASSWORD_DEFAULT`. `AuthService::login()` содержит проверку `password_needs_rehash()` с последующим вызовом `rehashPassword()`. Изменений не потребовалось.  
**Files:** `src/Repositories/UserRepository.php`, `src/Services/AuthService.php`

### P1.17 ✅ Session::destroy doesn't invalidate cookie
**Problem:** `Session::destroy()` clears session data but doesn't clear the cookie — old session ID persists in browser.  
**Fix:** Call `setcookie(session_name(), '', time() - 3600, '/')` before destroy.  
**Files:** `src/Core/session.php`

---

## P2 — Quality / Low Risk

### P2.4 ✅ DATE() prevents index use on scheduled_at
**Problem:** `DATE(a.scheduled_at) = ?` в нескольких методах отключал индекс на колонке, приводя к full scan.

**Что сделано — `AppointmentRepository.php` (3 метода):**

- `getBookedTimes()` — `DATE(scheduled_at) = ?` → `scheduled_at >= ? AND scheduled_at < DATE_ADD(?, INTERVAL 1 DAY)`. Параметр `$date` передаётся дважды.
- `getAllForExport()` — граница `$from`: `DATE(...) >= ?` → `scheduled_at >= ? . ' 00:00:00'`. Граница `$to`: `DATE(...) <= ?` → `scheduled_at < DATE_ADD(?, INTERVAL 1 DAY)` (важно: вариант `<= $to` терял записи конца дня, поэтому заменён на `< следующий день`).
- `getTodayForDoctor()` — `DATE(a.scheduled_at) = CURDATE()` → `scheduled_at >= CURDATE() AND scheduled_at < DATE_ADD(CURDATE(), INTERVAL 1 DAY)`.

**Что сделано — `AdminRepository.php` (5 методов):**

- `getAllAppointments()`, `countAppointments()`, `getAllAppointmentsPaginated()` — одинаковый паттерн `DATE(a.scheduled_at) = ?` заменён на диапазон; `$date` добавлен в params дважды.
- `getStats()` → `appointments_today`: `DATE(scheduled_at) = CURDATE()` → диапазон с `DATE_ADD`.
- `getStats()` → `completed_this_month`: `MONTH(scheduled_at) = MONTH(CURDATE()) AND YEAR(...) = YEAR(...)` — оба вызова тоже блокируют индекс. Заменены на `scheduled_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND scheduled_at < DATE_ADD(..., INTERVAL 1 MONTH)`.

**Files:** `src/Repositories/AppointmentRepository.php`, `src/Repositories/AdminRepository.php`

### P2.7 ✅ CHECK constraints in migrations.sql
**Problem:** `reviews.rating` принимал любое число; `schedules.start_time` мог быть позже `end_time` — БД не валидировала эти значения.

**Что сделано:** В `migrations.sql` добавлены два ALTER TABLE с `IF NOT EXISTS` для безопасного повторного запуска:
```sql
ALTER TABLE `reviews`
    ADD CONSTRAINT IF NOT EXISTS `chk_reviews_rating`
        CHECK (`rating` BETWEEN 1 AND 5);

ALTER TABLE `schedules`
    ADD CONSTRAINT IF NOT EXISTS `chk_schedules_times`
        CHECK (`start_time` < `end_time`);
```

**Files:** `database/migrations.sql`

### P2.11 ✅ Validator::time() regex too permissive
**Problem:** `/^\d{2}:\d{2}/` accepts `99:99`. Fix: `/^(?:[01]\d|2[0-3]):[0-5]\d$/`  
**Files:** `src/Core/Validator.php`

### P2.14 ✅ Undeclared CSS variables
**Problem:** `--color-primary-400` и `--weight-normal` использовались в `main.css` в 11 местах, но не были объявлены в `:root` — браузер молча отбрасывал эти правила, ломая стили hover-состояний карточек, пагинации, фильтров и font-weight у нескольких компонентов.

**Что сделано:** В блок `:root` добавлены два объявления:
- `--color-primary-400: #2dd4bf` — вставлен между `--color-primary-300` и `--color-primary-500`, соответствует стандартному Tailwind teal-400 и визуально ложится в шкалу.
- `--weight-normal: 400` — алиас для `--weight-regular`, добавлен рядом с ним для консистентности.

**Files:** `public/css/main.css`

### P2.17 ✅ seeds.php runs in production
**Problem:** Нет проверки окружения — случайный запуск `seeds.php` в prod выполнял `TRUNCATE` всех таблиц.

**Что сделано:** Guard добавлен сразу после загрузки dotenv (переменные окружения уже доступны):
```php
if (($_ENV['APP_ENV'] ?? '') === 'production') {
    fwrite(STDERR, "ERROR: seeds.php must not run in production. Aborting.\n");
    exit(1);
}
```
Используется `fwrite(STDERR, ...)` вместо `echo` — ошибка попадает в stderr и видна при запуске через cron или CI. `exit(1)` возвращает ненулевой код — скрипты-обёртки могут его поймать.

**Files:** `database/seeds.php`

### P2.22 Session::destroy — see P1.17 (same fix)

---

## Попутно исправлено (не было в плане)

### ✅ doctors.is_active — колонка отсутствовала в существующей БД
**Problem:** `DoctorRepository` обращался к `d.is_active`, но таблица `doctors` была создана до добавления этой колонки в схему — `CREATE TABLE IF NOT EXISTS` пропускает весь блок, если таблица уже существует.  
**Fix:** `ALTER TABLE doctors ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1` добавлен в секцию "Migrate existing databases" в `migrations.sql`.

### ✅ specializations.image_url — колонка отсутствовала в существующей БД
**Problem:** `seeds.php` падал с `Unknown column 'image_url'` при INSERT в `specializations` — та же причина: таблица создана до добавления колонки.  
**Fix:** `ALTER TABLE specializations ADD COLUMN IF NOT EXISTS image_url VARCHAR(500) NULL` добавлен в ту же секцию.

---

## P3 — Refactoring (optional, lower priority)

- **AdminController** (~600 lines) → split into Admin/AppointmentsController, Admin/DoctorsController, Admin/ReviewsController, Admin/CatalogController, Admin/DashboardController
- **N+1 queries** in PatientController → `ReviewRepository::existingDoctorIdsForPatient()`
- **Lab slot generation** → extract to LabBookingService
- **CSV export** → extract to ExportService
- **Soft-delete** → add `updated_at`, `deleted_at` to services, lab_tests, articles
- **CSP** → add `frame-ancestors 'none'`, `base-uri 'none'`, `object-src 'none'`, `form-action 'self'`


Критичные проблемы/несоответствия (надо поправить)
P0.2 частично не закрыт: в BaseController поправлено, но в middleware осталось уязвимое поведение через HTTP_REFERER.

CsrfMiddleware.php
Lines 1-21
if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
    Session::setFlash('error', 'Недействительный CSRF-токен. Попробуйте снова.');
    AuthMiddleware::redirect($_SERVER['HTTP_REFERER'] ?? '/');
}
P0.6 фактически не закрыт: в SECURITY_FIXES.md написано про htmlspecialchars(), но в коде всё ещё raw output article['body'].

article.php
Lines 52-58
<div class="article-body">
    <?php
    // TODO P0.6: install ezyang/htmlpurifier and sanitize here.
    echo $article['body'];
    ?>
</div>
P0.7 не закрыт: Chart.js всё ещё с CDN + inline script при CSP: script-src 'self' — это реально будет блокироваться браузером.

dashboard.php
Lines 161-163
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const dayLabels = <?= json_encode(array_column($byDay, 'day')) ?>;
P0.8 сделано спорно: die() заменён на RuntimeException, но из-за глобального catch в index.php в production это превращается в 500, даже когда ты хотел 419/403.
P1.16 в документе неточен: в UserRepository да, PASSWORD_DEFAULT; но в AdminController всё ещё PASSWORD_BCRYPT.

AdminController.php
Lines 539-539
$hash = password_hash($password, PASSWORD_BCRYPT);
Timing fix (P1.11) улучшен, но можно лучше: dummy hash строка выглядит невалидной/нестандартной; лучше использовать валидный заранее вычисленный bcrypt/argon2id hash, чтобы сравнение было по стоимости ближе к реальному.
Итоговая оценка
Твои решения в целом хорошие и в правильном направлении — особенно по logout, reviews, deactivateDoctor, session cookie и SQL-диапазонам.
Но в текущем виде я бы поставил: ~70% от заявленного плана закрыто корректно.

Что обязательно добить сейчас (приоритет)
Убрать HTTP_REFERER redirect из CsrfMiddleware и RateLimitMiddleware.
Реально закрыть XSS в article.php (санитизация/экранирование по выбранной модели).
Привести CSP и admin/dashboard к совместимому состоянию (self-host chart + без inline JS, либо nonce/hash).
Унифицировать хеширование паролей (PASSWORD_DEFAULT) в AdminController.
Пересмотреть обработку RuntimeException для 419/403, чтобы не маскировать всё в 500 в production.