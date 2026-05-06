## Purpose (read first)
This repository is a small PHP 8.1 + MySQL clinic information system with a custom minimal MVC stack.
Optimize for correctness and low-token work: read only the files needed for the task, keep changes localized, and avoid scanning dependencies.

## Runtime + entrypoints
- **Web entrypoint**: `public/index.php`
  - Loads env via `vlucas/phpdotenv`
  - Defines `ROOT_PATH` (repo root) and `BASE_URL` (from `APP_URL`)
  - Starts session via `App\Core\Session::start()`
  - Registers routes on `App\Core\Router` with middleware chain and calls `$router->dispatch()`
- **Rewrite**: `public/.htaccess` routes all non-files/non-directories to `index.php`

## Strict rules (must follow)
- Never read entire repository unless explicitly required.
- Never open more than 3–5 files per step.
- Always propose a plan before making changes for non-trivial tasks.
- Keep edits minimal and localized.
- Do not refactor unrelated code.

## File access strategy
- Start from entrypoint (`public/index.php`).
- Then follow only relevant files in the call chain.
- Do not explore unrelated directories.
- Prefer targeted reads over searching.
- Never read `.env` or any secret/config files.

## Debugging protocol
1. Identify route.
2. Locate controller.
3. Trace service → repository.
4. Check SQL/schema if needed.
5. Inspect view last.

Do not jump randomly across files.

## Directory map (what lives where)
- `public/`: web root
  - `index.php` routes + bootstrap
  - `css/`, `js/`: assets
  - `images/`: image files (icons, photos, etc.)
- `src/`: application code (autoload `App\\` → `src/`)
  - `Core/`: infrastructure (`Router`, `Database`, `Session`, `View`, `Logger`, `Paginator`, `Validator`)
  - `Controllers/`: HTTP controllers (one per area) — all extend `BaseController`
  - `Services/`: business logic
  - `Repositories/`: PDO queries + persistence — all extend `BaseRepository`
  - `Models/`: DTOs (e.g. `DoctorProfile`, `User`)
  - `Middleware/`: access control helpers (auth, CSRF, rate-limit, owner checks)
  - `Enums/`: `Role`, `AppointmentStatus`, `PrescriptionType`, `ReviewState`
- `views/`: PHP templates
  - `layout/`: headers/footers
  - `auth/`, `public/`, `patient/`, `doctor/`, `admin/`: area-specific pages
  - `errors/404.php`, `errors/500.php`
  - `partials/`: reusable components (flash, doctor-card, status-badge, empty-state, appointment-row, icon)
- `database/`:
  - `migrations.sql`: schema (CREATE + ALTER statements)
  - `seeds.php`: dev seed script (writes sample data)
- `logs/`: rotating daily log files (`app-YYYY-MM-DD.log`), managed by `Logger`
- `vendor/`: Composer dependencies (do not read unless explicitly asked)

## Core mechanics (important conventions)

### Routing + Middleware
Implemented in `src/Core/router.php`.
- Supports `GET` and `POST`.
- Path params: `/doctors/{id}` extracted as named args (e.g. `$controller->method(id: '42')`).
- Middleware chain: `Router::group()` groups routes with prefix and/or middleware closures. `dispatch()` executes middleware before calling controller.
- Global middleware: `Router::use()` registers CSRF, rate-limiting, etc.
- If no route matches: `404` + `View::render('errors/404')`.

### View rendering + escaping
Implemented in `src/Core/view.php`.
- `View::render('auth/login', ['csrf' => ...])` loads `views/auth/login.php` and `extract()` variables.
- Always escape untrusted output using `View::e(...)` in templates.
- Icon helper: `icon($name, $size, $class)` renders Lucide SVG (from `views/partials/icon.php`).

### Database access
Implemented in `src/Core/database.php`.
- PDO singleton configured from `.env`: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.
- `PDO::ATTR_ERRMODE = EXCEPTION`, fetch mode = `ASSOC`, emulate prepares = `false`.
- In production, DB connection errors are not shown to the user (only `error_log`).

### Sessions + CSRF + flash
Implemented in `src/Core/session.php`.
- Session cookies: `httponly=true`, `samesite=Strict`. `secure=false` unless HTTPS.
- CSRF:
  - Generate: `Session::generateCsrfToken()`
  - Validate: `Session::validateCsrfToken($_POST['csrf_token'] ?? '')` (also via `CsrfMiddleware`)
- Flash messages:
  - Set: `Session::setFlash('error', '...')`
  - Read-once: `Session::getFlash('error')`

### Auth + roles
- Login state is stored in session keys: `user_id`, `user_role`, `user_email`
- Roles: `patient`, `doctor`, `admin` (see `database/migrations.sql` → `users.role` enum)
- Middleware in `src/Middleware/`:
  - Role-based groups: `/patient/*`, `/doctor/*`, `/admin/*` have middleware that enforce role
  - Access control helpers: `requireAuth()`, `requireRole()`, `requireGuest()`, `redirectToDashboard()`
  - Owner checks: `OwnerMiddleware` for patient visit printing

### Logging
Implemented in `src/Core/Logger.php`.
- Dependency-free rotating daily file logger (30-day retention).
- PSR-3-like interface: `Logger::get()->info('msg', ['key' => $value])`.
- Integrated: AuthService logs login attempts, successes, registration, logouts.

### Pagination
Implemented in `src/Core/Paginator.php`.
- `new Paginator(total, perPage, currentPage)` → `$paginator->pages()`, `hasNext()`, `hasPrev()`, `offset()`.
- Used in admin appointments view.

### Validation
Implemented in `src/Core/Validator.php`.
- Static methods: `email($val)`, `password($val)` (min 8), `phone($val)`, `dateInFuture($val)`, `time($val)`, `nonEmpty($val)`.
- Returns `true` on success, throws `Exception` on failure.

## Routes (source of truth = `public/index.php`)
### Public
- `GET /` → home
- `GET /doctors` → list
- `GET /doctors/{id}` → details
- `GET /services`
- `GET /contact`, `POST /contact`
- `GET /about`
- `GET /faq`

### Auth
- `GET /login`, `POST /login` (rate-limited: 5/15min)
- `GET /register`, `POST /register` (rate-limited: 5/1h)
- `GET /logout`

### Patient
- `GET /patient/dashboard`
- `GET /patient/book`, `POST /patient/book`
- `GET /patient/book/analysis`, `POST /patient/book/analysis`
- `GET /patient/appointments`
- `POST /patient/appointments/cancel`
- `GET /patient/medical-record`
- `GET /patient/profile`, `POST /patient/profile`
- `POST /patient/profile/password`
- `GET /patient/reviews`
- `POST /patient/reviews/submit`
- `GET /patient/visit/{visitId}/print` (owner-checked)

### Doctor
- `GET /doctor/dashboard`
- `GET /doctor/appointment/{id}`
- `POST /doctor/appointment/{id}/start`
- `POST /doctor/appointment/{id}/protocol`
- `POST /doctor/appointment/{id}/prescription/add`
- `POST /doctor/appointment/{id}/prescription/delete`
- `GET /doctor/profile`, `POST /doctor/profile`

### Admin
- `GET /admin/dashboard`
- `GET /admin/appointments` (paginated: 25/page)
- `POST /admin/appointment/{id}/confirm`
- `POST /admin/appointment/{id}/cancel`
- `POST /admin/appointment/{id}/reschedule`
- `GET /admin/schedule`
- `POST /admin/schedule/{doctorId}/save`
- `GET /admin/reviews`
- `POST /admin/review/{id}/approve`
- `POST /admin/review/{id}/delete`
- `GET /admin/doctors`
- `GET /admin/doctors/create`, `POST /admin/doctors/create`
- `GET /admin/doctors/{id}/edit`, `POST /admin/doctors/{id}/edit`
- `POST /admin/doctors/{id}/deactivate`
- `POST /admin/doctors/{id}/activate`
- `GET /admin/services`
- `POST /admin/services/create`
- `POST /admin/services/{id}/update`
- `POST /admin/services/{id}/delete`
- `GET /admin/appointments/export` (CSV)

## Database schema (high level)
Source of truth: `database/migrations.sql`.
- `users` (auth) → `patients` / `doctors` (profiles)
- `specializations` → used by `doctors` and optional for `services`
- `schedules` + `schedule_exceptions` define availability per doctor
- `appointments` connect `patients` + `doctors`
- `visits` is 1:1 to `appointments` when started by doctor
- `prescriptions` belong to a `visit`
- `reviews` belong to a completed appointment (unique per appointment)
- `articles` (is_published, author_id, image_url columns added)
- Composite indices on appointments/reviews for query optimization

## Dev database seeding
`database/seeds.php`:
- Connects to DB using `.env`
- TRUNCATE tables (FK checks disabled temporarily)
- Inserts specializations/users/doctors/schedules/patients/services/appointments/visits/prescriptions/reviews
- Prints known credentials (e.g. `admin@hospital.local / password123`)

## How to run (Docker on Linux CachyOS with Fish shell)
**Environment**: Linux CachyOS with Fish shell. Project runs via Docker Compose (PHP 8.1 + Apache, MySQL 8.0, phpMyAdmin).

**Startup**:
- Start all containers: `docker-compose up -d`
- App: `http://localhost:8000/` (Apache document root = `/var/www/html/public`)
- phpMyAdmin: `http://localhost:8081/` (user: `user` / password: `password`)
- Database auto-initialized from `database/migrations.sql`

**Running PHP commands through Docker**:
- Always prefix with `docker-compose exec php`: `docker-compose exec php php database/seeds.php`
- Syntax check: `docker-compose exec php php -l src/Core/Router.php`
- Composer: `docker-compose exec php composer install`
- Do NOT run bare `php` commands; route all through Docker

**Credentials** (from docker-compose.yml):
- DB: `hospital_is` / user `user` / password `password` (or `root` / `root`)
- See `database/seeds.php` for app user accounts

## Coding standards (keep diffs consistent)
- PHP files use `declare(strict_types=1);`
- Prefer typed properties and return types.
- Controllers extend `BaseController` (validateCsrf, redirectWith, redirectBack, input, etc.).
- Repositories extend `BaseRepository` (findById, findAll, count, delete, transaction, etc.).
- Prefer repository methods for DB operations; do not inline SQL in controllers.
- Validate CSRF for all state-changing POST actions.
- Never output raw DB/user data in views without `View::e`.
- Use enums (`Role`, `AppointmentStatus`, etc.) instead of magic strings.
- CSS: BEM naming (`.block`, `.block__element`, `.block--modifier`), utilities `.u-*`.
- Icons: Use `icon()` function, never emoji in views.

## Token-efficient workflow (strict)
- Never scan `vendor/` or large/binary assets.
- When debugging, start from the route → controller → service → repository → view chain.
- If unsure about data shape, read the SQL migration for the relevant table(s) first.

## Secrets policy
- `.env` is secret: never request it, never print it, never commit it.
