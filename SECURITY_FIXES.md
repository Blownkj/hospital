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

### P0.3 Race condition in booking
**Problem:** `alreadyBooked()` + `create()` not in one transaction — two concurrent requests can book the same slot.  
**Fix:** Wrap in `transaction()` with `SELECT ... FOR UPDATE`. Add unique index on `(doctor_id, scheduled_at)` for active statuses.  
**Status:** Pending — requires AppointmentService + AppointmentRepository + migrations.sql  
**Note:** Partially mitigated by unique index if added.

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

### P1.16 PASSWORD_BCRYPT → PASSWORD_DEFAULT
**Problem:** Using `PASSWORD_BCRYPT` explicitly prevents future algorithm upgrades (e.g. Argon2id).  
**Fix:** Change constant to `PASSWORD_DEFAULT`. Add `password_needs_rehash()` check on login.  
**Files:** `src/Repositories/UserRepository.php`, `src/Services/AuthService.php`

### P1.17 ✅ Session::destroy doesn't invalidate cookie
**Problem:** `Session::destroy()` clears session data but doesn't clear the cookie — old session ID persists in browser.  
**Fix:** Call `setcookie(session_name(), '', time() - 3600, '/')` before destroy.  
**Files:** `src/Core/session.php`

---

## P2 — Quality / Low Risk (verified where noted)

### P2.4 DATE() prevents index use on scheduled_at
**Problem:** `DATE(a.scheduled_at) = ?` in AdminRepository and AppointmentRepository disables index on the column.  
**Fix:** Replace with `scheduled_at >= :start AND scheduled_at < :end`.

### P2.7 CHECK constraints in migrations.sql
**Add:**
```sql
CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5),
CONSTRAINT chk_schedules_times CHECK (start_time < end_time)
```

### P2.11 ✅ Validator::time() regex too permissive
**Problem:** `/^\d{2}:\d{2}/` accepts `99:99`. Fix: `/^(?:[01]\d|2[0-3]):[0-5]\d$/`  
**Files:** `src/Core/Validator.php`

### P2.14 Undeclared CSS variables
**Problem:** `--color-primary-400` and `--weight-normal` referenced but not declared in `:root`.  
**Fix:** Declare them in `public/css/main.css`.

### P2.17 seeds.php runs in production
**Fix:** Add guard: `if (($_ENV['APP_ENV'] ?? '') === 'production') { exit('Not in production.'); }`

### P2.22 Session::destroy — see P1.17 (same fix)

---

## P3 — Refactoring (optional, lower priority)

- **AdminController** (~600 lines) → split into Admin/AppointmentsController, Admin/DoctorsController, Admin/ReviewsController, Admin/CatalogController, Admin/DashboardController
- **N+1 queries** in PatientController → `ReviewRepository::existingDoctorIdsForPatient()`
- **Lab slot generation** → extract to LabBookingService
- **CSV export** → extract to ExportService
- **Soft-delete** → add `updated_at`, `deleted_at` to services, lab_tests, articles
- **CSP** → add `frame-ancestors 'none'`, `base-uri 'none'`, `object-src 'none'`, `form-action 'self'`
