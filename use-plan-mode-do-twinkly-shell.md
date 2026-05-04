# План архитектурного рефакторинга и развития — hospital IS

## Контекст

Проект — клиника на PHP 8.1 + MySQL с custom-MVC. Кодовая база ~7 тысяч строк, ~50 маршрутов,
10 репозиториев, 5 контроллеров, 36 шаблонов. Функционально система покрывает три роли
(пациент / врач / админ) и базовые сценарии: запись, протокол приёма, назначения, отзывы,
расписание, статьи.

Аудит выявил **здоровое ядро** (View::e везде, CSRF на 27 формах, типизация, FK консистентны),
но **накопленные слои дублирования** в трёх местах:

1. **Контроллерах** — 3 разные реализации CSRF, 6 мест inline-SQL в нарушение CLAUDE.md, разбросанная валидация (email/password — даже разная минимальная длина), магические строки для ролей и статусов.
2. **Репозиториях** — отсутствует `BaseRepository` (200+ строк boilerplate), SQL рейтинга врача дублируется 5 раз, JOIN doctors/specializations/reviews — 4 раза, нет пагинации.
3. **Шаблонах** — 295 inline-стилей, дублирующийся markup карточки врача и строки записи (4-5 мест), мёртвый CSS (`.price-table`, `.spec-card`), повторное определение `.reviews-*`.

Плюс несколько **точечных рисков безопасности**: нет `session_regenerate_id()` после логина (session fixation), POST `/contact` без CSRF, нет rate-limiting на `/login`, проверка владельца ресурса делается непоследовательно.

Цель плана — устранить технический долг **снизу вверх** (фундаментальные абстракции → дедупликация → новые фичи), не ломая работающую функциональность и сохраняя минималистичность стека.

---

## Карта архитектуры (текущая)

```
public/index.php   ──►  Router  ──►  Controllers  ──►  Services  ──►  Repositories  ──►  Database
                                          │                                                    │
                                          └──► AuthMiddleware                                  │
                                          └──► View::render ──► views/{area}/*.php  ◄──────────┘
```

**Слабые звенья на схеме:**
- Router → Controllers: нет middleware-цепочки (роли проверяются вручную в каждом методе).
- Controllers → Services: используется неравномерно (DoctorController хорош, AdminController почти не пользуется сервисами).
- Controllers → Database: 6 мест прямого SQL минуя репозитории.
- Repositories → Database: нет общего предка, нет транзакций кроме `UserRepository`.

---

## План рефакторинга по группам файлов

### Группа 1 — Безопасность (impact: ВЫСОКИЙ, effort: НИЗКИЙ) ⚠

**Цель:** закрыть конкретные дыры до любого рефакторинга.

| Файл | Проблема | Действие |
|---|---|---|
| `src/Services/AuthService.php` | Нет `session_regenerate_id()` после успешного логина — session fixation | Добавить `session_regenerate_id(true)` после установки `user_id` |
| `src/Controllers/PublicController.php:181` | `POST /contact` без CSRF | Валидировать `$_POST['csrf_token']` |
| `views/public/contact.php` | Форма без `csrf_token` | Добавить hidden-инпут |
| `src/Controllers/AuthController.php` | Нет rate-limiting на `/login` | Простой счётчик попыток в сессии (5/15мин) или таблица `login_attempts(ip, ts)` |
| `src/Controllers/PatientController.php` (cancelAppointment) | Не проверяет владельца до вызова репозитория | В репозитории + в контроллере: `WHERE patient_id = ?` явно |
| `src/Repositories/AppointmentRepository.php` | Использовать в `cancelByPatient` явный `WHERE patient_id = ?` если ещё нет | Аудит метода |

**Окно работ:** 1 заход, ~150 LOC изменений.

---

### Группа 2 — Базовые абстракции (impact: ВЫСОКИЙ, effort: СРЕДНИЙ)

**Цель:** убрать корень дублирования. Без этого любые «новые фичи» будут множить старый долг.

#### 2.1 BaseController
- Новый файл `src/Controllers/BaseController.php`
- Методы: `validateCsrf()`, `redirectBack()`, `redirectWith($path, $type, $msg)`, `requireOwner($condition, $msg)`, `input(string, ?default)`
- Унаследовать всеми пятью контроллерами; удалить локальные `validateCsrf()` из `PatientController:449`, `DoctorController:238`, `AdminController:621`

#### 2.2 BaseRepository
- Новый файл `src/Repositories/BaseRepository.php` (abstract)
- Дженерик-методы: `findById(int)`, `findAll(int $limit, int $offset)`, `count()`, `delete(int)`, `transaction(callable)`
- Поля `protected string $table`, `protected \PDO $db`
- Унаследовать всеми 10 репозиториями, удалить boilerplate `private \PDO $db; __construct() {...}`

#### 2.3 Enums + константы
- Новый каталог `src/Enums/`
- Файлы: `Role.php` (PATIENT/DOCTOR/ADMIN), `AppointmentStatus.php` (PENDING/CONFIRMED/IN_PROGRESS/COMPLETED/CANCELLED), `PrescriptionType.php` (DRUG/PROCEDURE/REFERRAL), `ReviewState.php`
- Заменить ~30 мест с магическими строками. PHP 8.1 backed enums (`: string`) совместимы со схемой.

#### 2.4 Validator
- Новый файл `src/Core/Validator.php`
- Статические методы: `email`, `password` (единая мин.длина 8), `phone`, `dateInFuture`, `time`, `nonEmpty`
- Заменяет 4-5 мест с разной логикой в `AuthService:62`, `PatientController:262`, `AdminController:531`

**Окно работ:** 2-3 захода. Затрагивает все контроллеры и репозитории, но рефакторинг механический.

---

### Группа 3 — Дедупликация SQL и шаблонов (impact: СРЕДНИЙ, effort: СРЕДНИЙ)

#### 3.1 Inline SQL → репозитории (6 мест)
| Источник | Куда |
|---|---|
| `PublicController.php:54-70` (4 SQL-метода статистики) | Новый `StatisticsRepository.php` |
| `PatientController.php:75-94` (visit с проверкой владельца) | `VisitRepository::findByIdForPatient(int, int)` |
| `PatientController.php:143-153` | Использовать существующий `PatientRepository::update` |
| `PatientController.php:273-287` (смена пароля) | Новый `UserRepository::changePassword(int, string)` + транзакция |
| `DoctorController.php:227-230` | `DoctorRepository::update` |
| `AdminController.php:537-544` | `UserRepository::findByEmail` (уже есть, использовать) |

#### 3.2 Дедупликация SQL рейтинга
- Извлечь общий `SELECT ROUND(AVG(rating),1), COUNT(*) FROM reviews WHERE doctor_id IN (...)` в `ReviewRepository::ratingsByDoctorIds(array): array`
- Заменить 5 мест: `DoctorRepository:31,55,98`, `ReviewRepository:57`, `AppointmentRepository:173`

#### 3.3 N+1 в истории визитов
- `VisitRepository::getFullHistoryForPatient` (строка 121) — заменить foreach + getPrescriptions на 1 запрос с JOIN + группировка в PHP по `visit_id`

#### 3.4 Partials для дублирующейся разметки
- Новый каталог `views/partials/`
- Файлы: `doctor-card.php`, `appointment-row.php`, `flash.php`, `status-badge.php`, `empty-state.php`
- Включать через `include` с локальными переменными
- Затрагивает: `home.php`, `doctors.php`, `doctor.php`, `book.php`, `admin/doctors.php`, `patient/appointments.php`, `admin/appointments.php`, `doctor/dashboard.php`

#### 3.5 CSS-чистка
- `public/css/main.css`: удалить дубль `.reviews-*` (строки 464-572), мёртвые `.price-table`, `.spec-card`
- Добавить утилитарные классы: `.muted-sm`, `.stat-value`, `.flex-row`, `.stack-sm`
- Заменить ~50 наиболее частых inline-стилей

**Окно работ:** 3-4 захода. Самая объёмная группа по диффу.

---

### Группа 4 — Индексы и пагинация (impact: СРЕДНИЙ, effort: НИЗКИЙ)

#### 4.1 Индексы (`database/migrations.sql`)
```
ALTER TABLE appointments ADD INDEX idx_appt_status (status);
ALTER TABLE appointments ADD INDEX idx_appt_date_status (appointment_date, status);
ALTER TABLE reviews      ADD INDEX idx_reviews_approved (is_approved, created_at);
```
Проявится при росте таблиц, но добавить дёшево.

#### 4.2 Пагинация
- Новый класс `src/Core/Paginator.php` (page, perPage, total, items, html())
- Применить в:
  - `AdminRepository::getAllAppointments` (сейчас LIMIT 200)
  - `AdminRepository::getReviews`
  - `ReviewRepository::getApprovedForDoctor`
  - `DoctorRepository::search`
  - `ArticleRepository::getAll`
- Обновить соответствующие views с компонентом пагинации

---

### Группа 5 — Router + middleware (impact: СРЕДНИЙ, effort: СРЕДНИЙ)

#### 5.1 Группировка маршрутов и middleware
- Расширить `src/Core/Router.php`:
  - `group(array $opts, callable)` — общий префикс + middleware
  - `Router::middleware(string|array)` — навешивается до dispatch
- Перенести из `public/index.php`:
  ```
  $router->group(['prefix' => '/admin', 'middleware' => ['auth:admin']], function($r) { ... });
  $router->group(['prefix' => '/doctor', 'middleware' => ['auth:doctor']], function($r) { ... });
  $router->group(['prefix' => '/patient', 'middleware' => ['auth:patient']], function($r) { ... });
  ```
- После этого можно удалить из контроллеров `requireRole()` в каждом методе.

#### 5.2 Дополнительные middleware
- `src/Middleware/CsrfMiddleware.php` — автоматически валидирует POST
- `src/Middleware/RateLimitMiddleware.php` — для `/login`, `/register`, `/contact`
- `src/Middleware/OwnerMiddleware.php` — проверка владельца ресурса для `/patient/visit/{id}/print` и т. п.

---

## Новые фичи — ранжировано по impact/effort

| # | Фича | Impact | Effort | Где живёт |
|---|---|---|---|---|
| F1 | **Notifications/email** напоминания пациенту за 24ч до приёма (cron + log таблица) | Высокий | Средний | `src/Services/NotificationService.php`, новая таблица `notifications`, `bin/cron.php` |
| F2 | **Поиск + пагинация статей** (полнотекстовый MATCH AGAINST по title+excerpt) | Средний | Низкий | `ArticleRepository::search`, `views/public/articles.php` |
| F3 | **Личный календарь врача** (FullCalendar.js или own grid) — обзор недели | Средний | Средний | `views/doctor/calendar.php`, новый эндпоинт |
| F4 | **Загрузка и просмотр документов** (результаты анализов как PDF/изображения) | Высокий | Средний | новая таблица `attachments`, `src/Services/UploadService.php`, security: тип, размер, рандом-имена |
| F5 | **Онлайн-консультация (чат врач↔пациент)** в рамках записи | Высокий | Высокий | таблица `messages`, polling/SSE, страница в кабинете врача и пациента |
| F6 | **Электронные рецепты с QR** (печать + scan) | Средний | Низкий | `views/patient/visit_print.php` уже есть, добавить QR через библиотеку endroid/qr-code |
| F7 | **Аналитика для админа** (графики выручки, нагрузки врачей, конверсия) | Высокий | Средний | `views/admin/analytics.php`, `StatisticsRepository`, Chart.js |
| F8 | **Карта клиник/филиалов** (если будут филиалы) | Низкий | Средний | таблица `branches`, привязка к `doctors` |
| F9 | **Программа лояльности** (баллы за приёмы, скидки) | Низкий | Высокий | таблицы `loyalty_*`, изменение бизнес-логики оплаты — пока нет |
| F10 | **Двухфакторная авторизация** через email-код | Средний | Средний | `AuthService`, новая таблица `auth_codes` |
| F11 | **API для мобильного приложения** (REST + JWT) | Средний | Высокий | `src/Api/`, отдельный entrypoint `public/api.php`, JWT-middleware |
| F12 | **i18n (русский / английский)** | Низкий | Средний | `src/Core/Lang.php`, `lang/ru.php`, `lang/en.php` |

**Рекомендуемая первая волна фич:** F2, F6, F4, F1, F7 — они дают максимум пользы при разумных трудозатратах и **не требуют правок ядра**.

**Не делать сейчас:** F5, F9, F11 — высокий effort, требуют сначала вычистить долг (Группы 1-5).

---

## Последовательность исполнения

```
Группа 1 (безопасность)
   ↓
Группа 2 (базовые абстракции: BaseController, BaseRepository, Enums, Validator)
   ↓
Группа 3 (дедупликация SQL и шаблонов)
   ↓
Группа 4 (индексы, пагинация)         ↘
                                          → готово к новым фичам
Группа 5 (router + middleware)        ↗
   ↓
Фичи: F2 → F6 → F1 → F4 → F7
```

---

## Критические файлы (точки входа для рефакторинга)

- `public/index.php` — переезд на router-группы (Группа 5)
- `src/Core/Router.php` — middleware support (Группа 5)
- `src/Core/Session.php` + `src/Services/AuthService.php` — session fixation (Группа 1)
- `src/Controllers/*.php` — все 5 контроллеров наследуют BaseController (Группа 2.1)
- `src/Repositories/*.php` — все 10 репозиториев наследуют BaseRepository (Группа 2.2)
- `database/migrations.sql` — индексы (Группа 4.1)
- `views/partials/` — новый каталог (Группа 3.4)
- `public/css/main.css` — чистка (Группа 3.5)

## Существующее, что переиспользовать (не плодить дубли)

- `App\Core\Session` — флэши и CSRF уже есть, не делать параллельно
- `App\Core\View::e/initials/stars` — хелперы корректные, расширять, не заменять
- `App\Middleware\AuthMiddleware::redirect/redirectToDashboard` — оставить как есть, использовать из BaseController
- `App\Services\AppointmentService::getSlots/getWorkingDays` — образцовая бизнес-логика, аналогично делать сервисы для новых фич
- `App\Services\DoctorService` — уже правильно проверяет владельца (паттерн для Group 1.5)

## Верификация

После каждой группы:

1. **Smoke-тест ролей:** залогиниться `admin@hospital.local`, `ivanov@hospital.local`, `alice@example.com` (все `password123`), пройти ключевые сценарии — запись, отмена, протокол приёма, отзыв.
2. **Сидер:** `php database/seeds.php` — должен работать без ошибок (truncate всех таблиц + новые).
3. **Миграция:** импорт `database/migrations.sql` на чистую БД проходит без warnings.
4. **Эррор-страницы:** перейти на несуществующий URL → 404; в dev режиме искусственно бросить исключение в контроллере → 500.
5. **CSRF:** попытаться отправить форму с подменённым/пустым токеном — должен быть редирект с flash-error.
6. **PHP error log** (XAMPP `apache/logs/error.log`) — после прохода smoke не должно появляться warnings/notices.

Тестов сейчас нет — после Группы 2 можно завести минимальный `tests/` с PHPUnit для `Validator` и `BaseRepository::findById/transaction`.
