# ТЕХНИЧЕСКИЙ АНАЛИЗ ПРОЕКТА: Информационная система медицинской клиники

**Автор анализа:** Senior Full-Stack Developer / Tech Architect  
**Дата анализа:** 2026-05-09  
**Версия системы:** актуальная (ветка main)  
**Технологический стек:** PHP 8.1, MySQL 8.0, Apache, Docker

---

## 1. Общее описание проекта

Проект представляет собой информационную систему медицинской клиники (hospital information system), реализованную на языке PHP версии 8.1 с использованием MySQL 8.0 в качестве реляционной СУБД. Система построена на базе кастомного минималистичного MVC-стека без использования фреймворков — все ключевые инфраструктурные компоненты (маршрутизатор, слой БД, сессии, шаблонизатор, логгер, пагинатор, валидатор) написаны с нуля и расположены в директории `src/Core/`.

Система предназначена для автоматизации работы небольшой частной клиники: ведения записей пациентов к врачам, управления расписанием врачей, ведения протоколов медицинских приёмов, управления назначениями и рецептами, модерации отзывов пациентов, а также для административного управления всей структурой клиники. Проект реализует трёхролевую систему доступа: администратор, врач, пациент — каждая роль работает в собственном изолированном разделе интерфейса.

С архитектурной точки зрения это компактный, но технически грамотный monolith: без REST API, без фронтенд-фреймворков, без ORM. Весь рендеринг происходит на сервере (SSR), JavaScript используется минимально — только для UX-улучшений (выбор слотов, аккордеон FAQ, диаграммы). Такой подход соответствует принципу KISS и позволяет команде небольшого размера эффективно поддерживать кодовую базу.

Проект контейнеризован с помощью Docker Compose (три сервиса: PHP+Apache, MySQL 8.0, phpMyAdmin). Для разработки предусмотрен скрипт сидирования базы данных `database/seeds.php`, создающий тестовые данные: 10 специализаций, 32 врача, множество пациентов, расписания, записи, визиты, назначения и отзывы.

---

## 2. Назначение системы и решаемые задачи

Система решает следующие конкретные бизнес-задачи медицинской клиники:

**Управление записями (appointments):** Пациенты могут самостоятельно записываться к врачам через личный кабинет. Система отображает доступные специализации, врачей, рабочие дни и свободные временные слоты. Предусмотрена защита от race condition при одновременном бронировании одного слота двумя пациентами через механизм `SELECT FOR UPDATE` в транзакции и уникальный индекс в БД на generated column `active_slot`.

**Запись на лабораторные анализы:** Отдельный поток записи для лабораторных исследований (appointment_type = 'lab_test'). Лаборатория работает по фиксированному расписанию Пн-Пт 08:00-18:00 со слотом 15 минут. Пациент выбирает анализ из каталога, сгруппированного по категориям.

**Ведение медицинских приёмов:** Врач открывает страницу приёма, нажимает «Начать приём» — создаётся запись в таблице `visits` со временем начала, статус записи переводится в `in_progress`. Врач заполняет протокол (жалобы, осмотр, диагноз) и добавляет назначения (препараты, процедуры, направления). При нажатии «Завершить» устанавливается `ended_at` и статус меняется на `completed`.

**Медицинская карта пациента:** Пациент видит полную историю своих визитов с диагнозами и назначениями, может распечатать любой визит в удобном формате.

**Система рейтингов и отзывов:** После завершённого приёма пациент может оставить отзыв (1–5 звёзд + текст). Отзыв проходит модерацию администратором. Одобренные отзывы отображаются на странице врача и влияют на его средний рейтинг. Администратор может отвечать на отзывы от имени клиники.

**Административное управление:** Администратор управляет составом врачей (создание, редактирование, активация/деактивация), расписанием каждого врача (по дням недели, с указанием времени начала, окончания и длительности слота), записями пациентов (отмена, перенос), отзывами, каталогом услуг клиники и списком лабораторных анализов. Также доступен экспорт всех записей в CSV-файл.

**Статистика и аналитика:** Дашборд администратора отображает ключевые метрики (число пациентов, количество записей на сегодня, завершённых в месяце), динамику записей за 14 дней (линейный график) и топ-5 врачей по числу приёмов (столбчатый график).

---

## 3. Цели и задачи проекта

**Технические цели:**

- Реализация защищённой многоролевой системы аутентификации и авторизации на основе PHP-сессий с надёжными cookie-параметрами (httponly, samesite=Strict, secure при HTTPS).
- Полная защита от CSRF-атак через глобальный middleware и токены, генерируемые `bin2hex(random_bytes(32))`.
- Защита от SQL-инъекций через PDO prepared statements с отключёнными эмулируемыми параметрами (`ATTR_EMULATE_PREPARES = false`).
- Защита от XSS через принудительное экранирование всего пользовательского вывода методом `View::e()`.
- Защита от race condition при бронировании через транзакции с `SELECT FOR UPDATE` и уникальный индекс на generated column.
- Rate limiting для критичных эндпоинтов (логин: 5 попыток/15 минут, регистрация: 5 попыток/час).
- Логирование всех значимых событий (входы, ошибки, действия администратора) с ротацией по 30 дней.
- Контейнеризация окружения разработки через Docker Compose.

**Бизнес-цели:**

- Автоматизация процесса записи пациентов с исключением ручного ведения журнала.
- Цифровизация медицинской документации (протоколы приёмов, назначения).
- Предоставление пациентам доступа к своей медицинской истории онлайн.
- Инструментарий для управления репутацией клиники (модерация отзывов).
- Административный контроль и аналитика по работе клиники в реальном времени.

---

## 4. Архитектура приложения

### 4.1 Паттерн MVC

Приложение строго следует паттерну MVC (Model-View-Controller), реализованному без использования фреймворков:

- **Model** — слой данных представлен двумя уровнями: DTO-моделями (`src/Models/`) и репозиториями (`src/Repositories/`). DTO `User` и `DoctorProfile` — иммутабельные объекты с readonly-свойствами, гидратируемые из массивов БД статическим методом `fromRow()`. Репозитории инкапсулируют все SQL-запросы и наследуют от `BaseRepository`.
- **View** — PHP-шаблоны в директории `views/`, рендеримые через `View::render()`. Метод `extract()` делает переданные переменные доступными в шаблоне. Экранирование обязательно через `View::e()`.
- **Controller** — классы в `src/Controllers/`, наследующие `BaseController`. Контроллеры принимают HTTP-запросы, вызывают сервисы/репозитории и передают данные во View.

### 4.2 Слоистая архитектура

Запрос проходит следующий путь:

```
HTTP Request
    ↓
public/index.php (bootstrap: env, headers, session, router)
    ↓
Router::dispatch() (URL parsing, route matching)
    ↓
GlobalMiddleware (CsrfMiddleware::handle())
    ↓
GroupMiddleware (AuthMiddleware::requireRole())
    ↓
RouteMiddleware (RateLimitMiddleware, OwnerMiddleware)
    ↓
Controller::method() (HTTP layer, input reading, flash)
    ↓
Service (бизнес-логика, валидация, оркестрация)
    ↓
Repository (SQL-запросы через PDO)
    ↓
Database::getInstance() (PDO singleton, MySQL 8.0)
    ↓
View::render() (PHP template + View::e() escaping)
    ↓
HTTP Response
```

### 4.3 Middleware архитектура

Middleware реализованы как callable-замыкания, выполняемые последовательно перед вызовом контроллера. Router поддерживает три уровня middleware:

1. **Глобальные** (`$router->use()`): `CsrfMiddleware::handle()` — выполняется для каждого POST-запроса.
2. **Групповые** (`$router->group(..., [$mw])`): например, `AuthMiddleware::requireRole('patient')` для всей группы `/patient/*`.
3. **Маршрутные** (третий аргумент `$router->get/post(..., [..., $mw])`): например, `RateLimitMiddleware::make('login', 5, 900)` только для `POST /login`, или `OwnerMiddleware::visit()` для печати визита.

### 4.4 Архитектурная диаграмма (текстовая)

```
┌─────────────────────────────────────────────────────────────┐
│                    public/index.php                         │
│  • Загрузка .env (vlucas/phpdotenv)                        │
│  • HTTP Security Headers                                    │
│  • Session::start()                                         │
│  • Регистрация маршрутов на Router                          │
│  • $router->dispatch()                                      │
└────────────────────────┬────────────────────────────────────┘
                         │
              ┌──────────▼──────────┐
              │  src/Core/Router    │
              │  • URL matching     │
              │  • Param extraction │
              │  • MW chain exec    │
              └──────────┬──────────┘
                         │
         ┌───────────────┼───────────────┐
         ▼               ▼               ▼
   Middleware       Controller       View::render()
   (CSRF, Auth,    (Auth/Patient/    (views/*.php)
   RateLimit,      Doctor/Admin)     View::e()
   Owner)          │
                   ├──► Service (Auth/Doctor/Appointment)
                   │         │
                   └──► Repository ──► Database (PDO Singleton)
                         (Base/User/                │
                         Appt/Doctor/        MySQL 8.0
                         Patient/Visit/
                         Review/Admin...)
```

---

## 5. Анализ структуры проекта

```
/home/andrey/hospital/hospital/
├── public/                    # Web root (Apache DocumentRoot)
│   ├── index.php              # Единственная точка входа (front controller)
│   ├── .htaccess              # mod_rewrite: всё → index.php
│   ├── css/
│   │   └── main.css           # Единый файл стилей (~1500+ строк, BEM + CSS Layers)
│   ├── js/
│   │   ├── app.js             # Vanilla JS: слоты, аккордеон, подтверждения
│   │   └── admin-charts.js    # Chart.js графики для admin/dashboard
│   └── images/                # Статика: фото врачей, иконки специализаций
│
├── src/                       # Application code (PSR-4: App\ → src/)
│   ├── Core/                  # Инфраструктура
│   │   ├── Router.php         # Маршрутизатор с группами и middleware
│   │   ├── Database.php       # PDO Singleton
│   │   ├── Session.php        # Сессии, CSRF, flash-сообщения
│   │   ├── View.php           # Шаблонизатор: render(), e(), initials(), stars()
│   │   ├── Logger.php         # Ротируемый файловый логгер
│   │   ├── Paginator.php      # Пагинация с диапазоном страниц
│   │   └── Validator.php      # Статические валидаторы
│   │
│   ├── Controllers/           # HTTP-контроллеры
│   │   ├── BaseController.php # validateCsrf(), redirectWith(), input()
│   │   ├── AuthController.php # Вход, регистрация, выход
│   │   ├── PublicController.php # Публичные страницы
│   │   ├── PatientController.php # Кабинет пациента
│   │   ├── DoctorController.php  # Кабинет врача
│   │   └── AdminController.php   # Панель администратора
│   │
│   ├── Services/              # Бизнес-логика
│   │   ├── AuthService.php         # Аутентификация и регистрация
│   │   ├── DoctorService.php       # Логика приёмов, протоколов, назначений
│   │   └── AppointmentService.php  # Генерация слотов, бронирование
│   │
│   ├── Repositories/          # Слой данных (PDO queries)
│   │   ├── BaseRepository.php      # findById, findAll, count, delete, transaction
│   │   ├── UserRepository.php      # users: поиск, создание, смена пароля
│   │   ├── PatientRepository.php   # patients: профиль, обновление
│   │   ├── DoctorRepository.php    # doctors: поиск, рейтинг, расписание
│   │   ├── AppointmentRepository.php # appointments: слоты, CRUD, статистика
│   │   ├── VisitRepository.php     # visits + prescriptions: CRUD
│   │   ├── ReviewRepository.php    # reviews: CRUD, рейтинги
│   │   ├── AdminRepository.php     # Запросы для admin-панели
│   │   ├── StatisticsRepository.php # Агрегированная статистика
│   │   ├── ServiceRepository.php   # services: CRUD
│   │   ├── LabTestRepository.php   # lab_tests: CRUD, бронирование
│   │   └── ArticleRepository.php   # articles: публикации
│   │
│   ├── Models/                # DTO
│   │   ├── User.php           # Иммутабельный объект пользователя
│   │   └── DoctorProfile.php  # Иммутабельный профиль врача с fromRow()
│   │
│   ├── Enums/                 # PHP 8.1 Backed Enums
│   │   ├── Role.php           # patient | doctor | admin
│   │   ├── AppointmentStatus.php # pending|confirmed|in_progress|completed|cancelled
│   │   ├── PrescriptionType.php  # drug | procedure | referral
│   │   └── ReviewState.php    # Pending=0 | Approved=1
│   │
│   ├── Middleware/            # HTTP-middleware
│   │   ├── AuthMiddleware.php  # requireAuth, requireRole, requireGuest, redirect
│   │   ├── CsrfMiddleware.php  # Глобальная CSRF-валидация POST
│   │   ├── RateLimitMiddleware.php # Счётчики попыток в сессии
│   │   └── OwnerMiddleware.php # Проверка принадлежности ресурса
│   │
│   └── Exceptions/            # Доменные исключения
│       ├── DomainException.php
│       ├── ForbiddenException.php
│       └── NotFoundException.php
│
├── views/                     # PHP-шаблоны
│   ├── layout/                # Общие обёртки (header/footer)
│   ├── partials/              # Переиспользуемые компоненты
│   ├── auth/                  # Страницы входа и регистрации
│   ├── public/                # Публичный сайт
│   ├── patient/               # Кабинет пациента
│   ├── doctor/                # Кабинет врача
│   ├── admin/                 # Панель администратора
│   └── errors/                # 404, 500
│
├── database/
│   ├── migrations.sql         # Полная схема + ALTER-миграции
│   └── seeds.php              # Dev seed: 32 врача, 10 специализаций и т.д.
│
├── logs/                      # Ротируемые логи (app-YYYY-MM-DD.log)
├── docker/                    # Dockerfile для PHP+Apache
├── docker-compose.yml         # Конфигурация окружения
├── composer.json              # Зависимости: phpdotenv, htmlpurifier
└── .env                       # Секреты (не коммитится)
```

---

## 6. Анализ Backend части

### 6.1 Ядро системы (src/Core/)

#### Router

`src/Core/Router.php` — кастомный маршрутизатор, реализующий паттерн Front Controller. Поддерживает регистрацию GET и POST маршрутов, группировку с префиксами, многоуровневую middleware-цепочку.

Path parameters извлекаются регулярным выражением: `{id}` компилируется в паттерн `([^/]+)`, имена групп захвата сохраняются для передачи контроллеру как именованные аргументы:

```php
preg_match_all('/\{([^}]+)\}/', $routePath, $paramNames);
$pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);

if ($paramNames !== []) {
    $named = array_combine($paramNames, $matches);
    $controller->$method_name(...$named);
}
```

Группировка с наследованием middleware:

```php
$this->groupPrefix      = $prevPrefix . $prefix;
$this->groupMiddlewares = array_merge($prevMw, $middlewares);
```

При несовпадении ни одного маршрута возвращается HTTP 404 и рендерится `errors/404`. Исключения `NotFoundException` и `ForbiddenException` перехватываются в `dispatch()` и также приводят к 404/403.

#### Database

`src/Core/Database.php` — реализация паттерна Singleton для PDO. Гарантирует единственное соединение с БД на протяжении всего запроса:

```php
private static ?PDO $instance = null;

public static function getInstance(): PDO
{
    if (self::$instance === null) {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', ...);
        self::$instance = new PDO($dsn, ..., [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return self::$instance;
}
```

Параметры PDO: режим ошибок `EXCEPTION` (все ошибки БД бросают `PDOException`), режим выборки `FETCH_ASSOC` (всегда ассоциативные массивы), `EMULATE_PREPARES = false` — критично для безопасности, т.к. реальные prepared statements полностью исключают SQL-инъекции.

Статический метод `transaction()` оборачивает callback в BEGIN/COMMIT/ROLLBACK:

```php
public static function transaction(callable $callback): mixed
{
    $pdo = self::getInstance();
    $pdo->beginTransaction();
    try {
        $result = $callback($pdo);
        $pdo->commit();
        return $result;
    } catch (\Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
```

#### Session

`src/Core/Session.php` — статический класс для управления PHP-сессиями. Ключевые особенности безопасности:

- Куки сессии: `httponly=true`, `samesite=Strict`, `secure` включается только при HTTPS.
- CSRF-токен генерируется через `bin2hex(random_bytes(32))` — криптографически стойкий случайный токен длиной 64 hex-символа.
- Сравнение токенов через `hash_equals()` — защита от timing attacks.
- Flash-сообщения читаются единожды: `getFlash()` сразу удаляет сообщение из сессии.
- `destroy()` корректно аннулирует сессионную куку в браузере через `setcookie()` с отрицательным lifetime.

```php
public static function generateCsrfToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

public static function validateCsrfToken(string $token): bool
{
    $stored = $_SESSION['csrf_token'] ?? '';
    return hash_equals($stored, $token);
}
```

#### View

`src/Core/View.php` — минималистичный шаблонизатор. Метод `render()` находит PHP-файл по имени шаблона, делает `extract($data, EXTR_SKIP)` (флаг `EXTR_SKIP` защищает от перезаписи существующих переменных) и подключает файл через `require`.

Вспомогательные методы:
- `View::e($value)` — `htmlspecialchars()` с `ENT_QUOTES | ENT_SUBSTITUTE` и кодировкой UTF-8. Используется везде в шаблонах для вывода пользовательских данных.
- `View::initials($name)` — извлекает две первые заглавные буквы из полного имени для аватаров.
- `View::stars($rating)` — рендерит строку из символов ★☆ для отображения рейтинга.

#### Logger

`src/Core/Logger.php` — PSR-3-подобный ротируемый файловый логгер без внешних зависимостей. Реализован как Singleton. Поддерживает три уровня: `info()`, `warning()`, `error()`. Логи записываются в файлы вида `logs/app-YYYY-MM-DD.log`. При инициализации выполняется `prune()` — удаление файлов старше 30 дней.

Формат записи: `[2026-05-09 14:23:01] INFO: User logged in {"user_id":42,"role":"patient"}`.

#### Paginator

`src/Core/Paginator.php` — класс для расчёта параметров пагинации. Принимает total, perPage, currentPage в конструктор (readonly-свойства). Вычисляет `totalPages`, `offset`. Методы `hasPrev()`, `hasNext()`, `prevPage()`, `nextPage()`, `pages(around=2)` — последний возвращает диапазон номеров страниц вокруг текущей для рендеринга кнопок.

#### Validator

`src/Core/Validator.php` — набор статических методов валидации:
- `email()` — `filter_var(FILTER_VALIDATE_EMAIL)`.
- `password()` — минимальная длина 8 символов.
- `phone()` — очистка от пробелов и скобок, проверка regex `+?[0-9]{7,15}`.
- `dateInFuture()` / `dateInPast()` — проверка относительно текущего времени.
- `time()` — формат HH:MM с валидным диапазоном (00:00–23:59).
- `nonEmpty()` — проверка на минимальную длину после trim (mb_strlen).

### 6.2 Контроллеры (src/Controllers/)

#### BaseController

Абстрактный базовый класс с защищёнными методами-хелперами, доступными всем контроллерам:

```php
protected function validateCsrf(): void
{
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        http_response_code(419);
        throw new \RuntimeException('CSRF-токен недействителен.');
    }
}

protected function redirectWith(string $path, string $type, string $msg): never { ... }
protected function redirectBack(string $fallback = '/'): never { ... }
protected function requireOwner(bool $condition, string $msg): void { ... }
protected function input(string $key, mixed $default = null): mixed { ... }
```

`redirectBack()` безопасно следует Referer только если он совпадает по хосту с текущим доменом — защита от Open Redirect.

#### AuthController

Управляет аутентификацией: `showLogin()`, `doLogin()`, `showRegister()`, `doRegister()`, `logout()`.

В `doLogin()` CSRF-токен проверяется вручную повторно (defence in depth — даже если глобальный middleware был обойдён). После успешного входа вызывается `AuthMiddleware::redirectToDashboard()` — перенаправление в зависимости от роли.

В `doRegister()` при ошибках валидации форма перерендеривается с ошибками и старыми значениями полей (`'old' => $data`). При успехе происходит автоматический вход и редирект на `/patient/dashboard`.

#### PatientController

Самый объёмный контроллер (486 строк). Управляет всем кабинетом пациента. Ключевые методы:

- `dashboard()` — показывает предстоящие записи (фильтрует статусы `pending`/`confirmed` и время >= now).
- `book()` — многошаговый процесс выбора специализации → врача → даты → слота. Использует `AppointmentService::getWorkingDays()` и `getSlots()`.
- `bookAnalysis()` — аналогичный флоу для записи на анализы с жёстким расписанием лаборатории (08:00–18:00, слот 15 минут).
- `printVisit()` — защищённая OwnerMiddleware страница печати визита.
- `reviews()` / `submitReview()` — просмотр и отправка отзывов.
- `currentPatient()` — приватный метод, извлекающий профиль пациента из сессии. При отсутствии профиля в БД уничтожает сессию и редиректит на вход (защита от orphan-сессий).

#### DoctorController

Управляет кабинетом врача (243 строки):

- `dashboard()` — показывает профиль, расписание на сегодня, последние приёмы, статистику (через `AppointmentRepository::getStatsForDoctor()`).
- `appointment()` — страница отдельного приёма: данные пациента, визит, назначения, история предыдущих визитов.
- `startAppointment()` / `saveProtocol()` / `addPrescription()` / `deletePrescription()` — делегируют бизнес-логику в `DoctorService`, перехватывают `DomainException` и `ForbiddenException`.
- `history()` — полная история завершённых приёмов врача.
- `currentDoctorId()` — приватный метод маппинга `user_id → doctor_id`.

#### AdminController

Самый объёмный по набору функций (596 строк). Управляет всей административной частью:

- Статистика (`dashboard()`): метрики, графики через Chart.js.
- Записи (`appointments()`): пагинированный список с фильтрами по статусу и дате, отмена и перенос записей.
- Расписание (`schedule()`, `saveSchedule()`): выбор врача, редактирование расписания по дням недели через `upsertSchedule()`.
- Отзывы (`reviews()`, `approveReview()`, `deleteReview()`, `replyToReview()`): модерация и ответы.
- Врачи (`doctors()`, `createDoctorForm()`, `createDoctor()`, `editDoctorForm()`, `updateDoctor()`, `deactivateDoctor()`, `activateDoctor()`): полный CRUD.
- Услуги (`services()`, `createService()`, `updateService()`, `deleteService()`): управление прайс-листом.
- Анализы (`labTests()`, `createLabTest()`, `updateLabTest()`, `deleteLabTest()`): управление каталогом.
- Экспорт (`exportCsv()`): выгрузка записей в CSV с BOM для Excel, с фильтрацией по датам.

### 6.3 Сервисы (src/Services/)

#### AuthService

Бизнес-логика аутентификации (136 строк). Ключевые особенности:

**Защита от timing attacks при неверном email:** даже когда пользователь не найден, выполняется dummy-хеширование:
```php
password_verify($password, '$2y$12$invaliddummyhashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
```
Это уравнивает время ответа для существующих и несуществующих email-адресов.

**Автоматический rehash:** если алгоритм хеширования устарел (`password_needs_rehash()`), пароль перехешируется при следующем успешном входе.

**Session fixation protection:** после успешного входа вызывается `session_regenerate_id(true)` — ID сессии меняется, старая сессия уничтожается.

**Проверка активности врача:** перед установкой сессии проверяется флаг `is_active` в таблице `doctors`.

**Логирование:** все попытки входа (успешные и неудачные), выходы, регистрации — записываются в лог.

#### DoctorService

Бизнес-логика работы врача (174 строки). Реализует логику медицинского приёма:

- `startAppointment()` — проверяет принадлежность приёма врачу, валидирует статус, создаёт визит и меняет статус в одной транзакции.
- `saveProtocol()` — обновляет поля визита (complaints, examination, diagnosis), при флаге `finish` — завершает визит и приём в транзакции.
- `addPrescription()` — проверяет все условия (активный приём, начатый визит, допустимый тип) перед вставкой назначения.
- `deletePrescription()` — проверяет принадлежность перед удалением.

Исключения `DomainException` и `ForbiddenException` (кастомные, из `src/Exceptions/`) используются для передачи ошибок бизнес-логики наверх в контроллер.

#### AppointmentService

Бизнес-логика записи пациентов (169 строк):

- `getSlots()` — генерирует слоты для врача на дату: проверяет исключения (отпуска), загружает расписание по дню недели, получает занятые слоты, итерирует от start_time до end_time с шагом slot_duration_min.
- `getWorkingDays()` — ищет ближайшие N рабочих дней врача в горизонте 60 дней.
- `book()` — многоуровневая защита бронирования: валидация формата → проверка даты → проверка доступности слота → транзакция с `SELECT FOR UPDATE` → `uq_appt_doctor_active_slot` как последняя страховка от PDOException 23000.

### 6.4 Репозитории (src/Repositories/)

#### BaseRepository

Абстрактный класс (66 строк) — основа для всех репозиториев:

```php
abstract class BaseRepository
{
    protected const DEFAULT_LIMIT  = 20;
    protected const HISTORY_LIMIT  = 30;
    protected const DASHBOARD_LIMIT = 10;

    protected PDO $db;
    protected string $table = '';

    public function __construct() { $this->db = Database::getInstance(); }
    public function findById(int $id): mixed { ... }
    public function findAll(int $limit = 100, int $offset = 0): array { ... }
    public function count(): int { ... }
    public function delete(int $id): bool { ... }
    public function transaction(callable $fn): mixed { ... }
}
```

#### UserRepository

Управляет таблицей `users`. Ключевые методы:
- `findByEmail()` и `findById()` — возвращают объект `User` (не массив).
- `createPatient()` — создаёт запись в `users` и `patients` в одной транзакции через `Database::transaction()`.
- `changePassword()` — проверяет текущий пароль через `password_verify()` перед изменением.
- `isDoctorActive()` — проверка флага `is_active` в таблице `doctors` по `user_id`.
- `emailExists()` — проверка уникальности email.
- `rehashPassword()` — обновление устаревшего хеша при входе.

#### AppointmentRepository

Центральный репозиторий (330 строк). Работает с таблицей `appointments` и смежными:
- `getBookedTimes()` — занятые слоты врача на дату (исключая cancelled).
- `lockSlot()` — `SELECT ... FOR UPDATE` для блокировки строки в транзакции.
- `create()` — создание записи со статусом `confirmed`.
- `getByPatientId()` — все записи пациента включая анализы (`appointment_type`), с JOIN на `lab_tests`.
- `findByIdWithPatient()` — запись с полными данными пациента; опциональная проверка `doctor_id`.
- `getStatsForDoctor()` — агрегированная статистика врача (COUNT с условиями, AVG рейтинга).
- `getTodayForDoctor()` — расписание на сегодня с данными пациентов.
- `cancelByPatient()` — UPDATE с условием `patient_id AND status IN (pending, confirmed)`.
- `getHistoryForDoctor()` / `getRecentForDoctor()` — история с JOIN на `visits`.
- `getAllForExport()` — выборка для CSV с фильтрацией по датам.

#### DoctorRepository

Работает с таблицей `doctors`. Центральный метод `doctorSelectBase()` строит базовый SELECT с JOIN на `specializations`, `users`, `reviews` (с AVG и COUNT для рейтинга). Используется в `getAllWithRating()`, `findById()`, `search()`.

Метод `search()` поддерживает полнотекстовый поиск по полному имени (CONCAT_WS) и специализации через LIKE-запросы с пагинацией.

Возвращает типизированные объекты `DoctorProfile` через `DoctorProfile::fromRow()`.

#### VisitRepository

Управляет таблицей `visits` и связанными `prescriptions`:
- `create()` — создание визита с `started_at = NOW()`.
- `updateProtocol()` — обновление complaints, examination, diagnosis.
- `finish()` — установка `ended_at = NOW()`.
- `getPrescriptions()` — все назначения визита.
- `addPrescription()` / `deletePrescription()` — CRUD назначений.
- `getFullHistoryForPatient()` — эффективная загрузка всей истории: сначала все визиты, затем одним запросом все назначения (IN-список), потом group в PHP — избегает N+1 проблемы.
- `findByIdForPatient()` — полный JOIN для страницы печати с проверкой принадлежности.

#### ReviewRepository

Управляет таблицей `reviews`:
- `existsByAppointment()` — проверка уникальности отзыва на приём.
- `create()` — создание с `is_approved = 0`.
- `getCompletedWithoutReview()` — завершённые приёмы без отзыва (LEFT JOIN, r.id IS NULL).
- `getApprovedForDoctor()` — одобренные отзывы с ответами администратора.
- `ratingsByDoctorIds()` — батч-загрузка рейтингов для массива doctor_id через IN.

#### AdminRepository

Сводный репозиторий для нужд администратора (409 строк). Содержит методы:
- `getAllAppointmentsPaginated()` — пагинированный список записей с фильтрами.
- `countAppointments()` — подсчёт для пагинатора.
- `getStats()` — 5 отдельных COUNT-запросов для дашборда.
- `getAppointmentsByDay()` — динамика за 14 дней (GROUP BY DATE).
- `getTopDoctors()` — топ-5 по завершённым приёмам.
- `createDoctor()` / `updateDoctor()` / `findDoctorById()` / `deactivateDoctor()` / `activateDoctor()` — CRUD врачей.
- `upsertSchedule()` — INSERT ... ON DUPLICATE KEY UPDATE для расписания.
- `saveReply()` / `approveReview()` / `deleteReview()` — модерация отзывов.
- `getPendingReviews()` / `getApprovedReviewsPaginated()` — выборки для модерации.

#### StatisticsRepository, ServiceRepository, LabTestRepository, ArticleRepository

Вспомогательные репозитории:
- `StatisticsRepository` — агрегированная статистика для публичной страницы (число пациентов, одобренных отзывов, последние отзывы).
- `ServiceRepository` — CRUD прайс-листа, группировка по специализации.
- `LabTestRepository` — CRUD анализов, группировка по категории, генерация расписания лаборатории, запись на анализ через `bookTest()` (INSERT в `appointments` с `appointment_type = 'lab_test'`).
- `ArticleRepository` — работа со статьями (публичный блог).

### 6.5 Модели и DTO (src/Models/)

#### User

Иммутабельный DTO (readonly constructor promotion):
```php
class User
{
    public function __construct(
        public readonly int    $id,
        public readonly string $email,
        public readonly string $passwordHash,
        public readonly string $role,
        public readonly string $createdAt,
    ) {}
}
```
Используется в `UserRepository` и `AuthService`. Отделяет сущность пользователя от сырого массива БД.

#### DoctorProfile

Богатый иммутабельный DTO (50 строк). Кроме базовых полей содержит вычисляемое `fullName` (CONCAT_WS из трёх частей), `avgRating`, `reviewCount`, `isActive`, `photoUrl`. Статический фабричный метод `fromRow()` выполняет все приведения типов и обработку NULL. Позволяет работать с врачом как с типизированным объектом, а не ассоциативным массивом.

### 6.6 Перечисления (src/Enums/)

PHP 8.1 Backed Enums обеспечивают типобезопасность для ключевых доменных понятий:

```php
enum AppointmentStatus: string
{
    case Pending    = 'pending';
    case Confirmed  = 'confirmed';
    case InProgress = 'in_progress';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';
}

enum PrescriptionType: string
{
    case Drug      = 'drug';
    case Procedure = 'procedure';
    case Referral  = 'referral';
}

enum ReviewState: int
{
    case Pending  = 0;
    case Approved = 1;
}

enum Role: string
{
    case Patient = 'patient';
    case Doctor  = 'doctor';
    case Admin   = 'admin';
}
```

Enums определены в коде, но в базе данных используются ENUM-столбцы MySQL с теми же значениями. Соответствие между PHP-перечислениями и DB-значениями поддерживается вручную.

---

## 7. Анализ Frontend части

### 7.1 Шаблонизация (PHP views)

Шаблоны — обычные PHP-файлы с минимальным встроенным PHP. Принцип: вся логика в контроллерах/сервисах, шаблоны только отображают переданные переменные. Каждый шаблон получает строго типизированные данные через `extract()`.

Типичный паттерн шаблона:
```php
<?php use App\Core\View; ?>
<?php require ROOT_PATH . '/views/layout/public_header.php'; ?>
<?php require ROOT_PATH . '/views/partials/icon.php'; ?>

<h1><?= View::e($pageTitle) ?></h1>
<?php include ROOT_PATH . '/views/partials/flash.php'; ?>
<!-- ... контент ... -->

<?php require ROOT_PATH . '/views/layout/public_footer.php'; ?>
```

### 7.2 Layout система (header/footer)

Два набора layouts: `layout/header.php` + `layout/footer.php` для авторизованных областей, `layout/public_header.php` + `layout/public_footer.php` для публичного сайта. Layouts подключаются через `require` в начале и конце шаблонов — без output buffering и наследования шаблонов.

Заголовок (`views/layout/header.php`) содержит: DOCTYPE, meta-теги, шрифты Google Fonts (Inter + Manrope), подключение `main.css`. Публичный заголовок включает полноценный navbar с бургер-меню (CSS-only через checkbox) и навигационные ссылки.

### 7.3 Reusable партиалы (views/partials/)

- `flash.php` — универсальный компонент flash-сообщений: четыре типа (success, error, warning, info), каждый со своим SVG-иконкой и CSS-классом. ARIA-атрибуты `role="alert"` и `aria-live="polite"`.
- `icon.php` — библиотека из 50+ Lucide SVG-иконок, встроенных inline. Функция `icon($name, $size, $class)` выводит SVG с `aria-hidden="true"`. Все пути иконок определены в статическом массиве.
- `status-badge.php` — цветная бейдж-метка для статусов записи. Маппинг статусов на CSS-модификаторы: `pending → badge--warning`, `confirmed/completed → badge--success`, `in_progress → badge--info`, `cancelled → badge--danger`.
- `appointment-row.php` — строка таблицы с данными записи.
- `doctor-card.php` — карточка врача для публичного списка.
- `pagination.php` — компонент пагинации с кнопками prev/next и диапазоном страниц.
- `empty-state.php` — заглушка для пустых списков.

### 7.4 CSS архитектура (BEM, утилиты, переменные)

CSS построен по современным принципам с использованием `@layer` cascade layers:

```css
@layer reset, components, utilities;
```

Три уровня каскада: `reset` (нормализация, базовые стили), `components` (UI-компоненты), `utilities` (вспомогательные классы). Позже объявленные слои имеют приоритет, что обеспечивает предсказуемость.

**Дизайн-токены:** все значения вынесены в CSS custom properties в `:root`:
- Цвета: `oklch()` для primary (teal), accent (amber), нейтралов, семантических цветов (success/warning/danger/info).
- Типографика: семейства Inter (текст) и Manrope (заголовки), fluid-типографика через `clamp()` для display-размеров.
- Отступы: 4px-сетка (`--space-1` = 4px .. `--space-20` = 80px).
- Скругления: `--radius-sm` (6px) .. `--radius-pill` (999px).
- Тени, анимации (`--ease`, `--dur-fast`, `--dur-base`).

**BEM-нейминг:** `.navbar`, `.navbar__container`, `.navbar__brand`, `.navbar__link`, `.navbar__link--active`, `.btn`, `.btn--primary`, `.btn--ghost`, `.card`, `.card__body`, `.card__title`, `.badge`, `.badge--warning`, `.stat-card`, `.alert`, `.alert--success` и т.д.

**Мобильная адаптивность:** navbar с бургер-меню на CSS checkbox (без JavaScript), responsive grid через `@media` brakepoints (768px, 1024px).

**Доступность:** skip-link (`.u-skip-link`), focus-visible стили, `prefers-reduced-motion` медиа-запрос.

### 7.5 JavaScript функциональность

JavaScript минималистичен — только Vanilla JS, 62 строки в `app.js`:

- **Выбор временного слота:** по клику на `.slot-btn` снимает `active` со всех кнопок, устанавливает на текущую, обновляет скрытое поле и показывает блок подтверждения.
- **Подтверждение отправки форм** с `data-confirm` атрибутом: `window.confirm()` перед submit.
- **Перенос записи (admin):** toggle-блоков по `data-toggle-reschedule`.
- **FAQ аккордеон:** открытие/закрытие ответов с ARIA-атрибутами (`aria-expanded`).
- **Выбор анализа:** `selectLabTest()` — глобальная функция для выбора лабораторного теста.

Отдельный файл `admin-charts.js` подключает Chart.js и рисует линейный график динамики записей и столбчатую диаграмму топ-врачей, считывая данные из `data-labels` и `data-counts` атрибутов элементов `<canvas>`.

---

## 8. Анализ базы данных

### 8.1 Схема данных (все таблицы с полями)

| Таблица | Ключевые поля | Описание |
|---------|--------------|----------|
| `specializations` | id, name, description, image_url | Справочник специализаций (10 шт.) |
| `users` | id, email, password_hash, role ENUM('patient','doctor','admin'), created_at | Единая таблица авторизации |
| `patients` | id, user_id, last_name, first_name, middle_name, birth_date, phone, gender ENUM('m','f','other'), address, chronic_diseases | Профили пациентов |
| `doctors` | id, user_id, last_name, first_name, middle_name, specialization_id, bio, photo_url, is_active | Профили врачей |
| `schedules` | id, doctor_id, day_of_week TINYINT(1-7), start_time, end_time, slot_duration_min | Рабочее расписание |
| `schedule_exceptions` | id, doctor_id, exception_date, is_day_off, note | Отпуска и исключения |
| `services` | id, name, price DECIMAL(10,2), specialization_id, description, image_url | Прайс-лист услуг |
| `lab_tests` | id, name, category, description, preparation, price, duration_min | Каталог анализов |
| `appointments` | id, patient_id, appointment_type ENUM('doctor','lab_test'), doctor_id NULL, lab_test_id NULL, scheduled_at, status ENUM(5 значений), created_at, active_slot GENERATED | Записи на приём |
| `visits` | id, appointment_id UNIQUE, started_at, ended_at, complaints, examination, diagnosis | Протоколы приёмов (1:1 с appointments) |
| `prescriptions` | id, visit_id, type ENUM('drug','procedure','referral'), name, dosage, notes | Назначения врача |
| `reviews` | id, patient_id, doctor_id, appointment_id UNIQUE, rating TINYINT(1-5), review_text, is_approved, admin_reply, admin_reply_at, created_at | Отзывы |
| `articles` | id, slug UNIQUE, title, excerpt, body, category, read_time, published_at, is_published, author_id, image_url | Статьи блога |

### 8.2 Связи между таблицами (ERD текстом)

```
specializations ←──── doctors (specialization_id, RESTRICT)
                ←──── services (specialization_id, SET NULL)

users ──────────────► patients (user_id, CASCADE) [1:1]
users ──────────────► doctors  (user_id, CASCADE) [1:1]
users ──────────────► articles (author_id, SET NULL)

doctors ────────────► schedules           (doctor_id, CASCADE) [1:N]
doctors ────────────► schedule_exceptions (doctor_id, CASCADE) [1:N]

lab_tests ──────────► appointments (lab_test_id, SET NULL)

patients ───────────► appointments (patient_id, CASCADE) [1:N]
doctors  ───────────► appointments (doctor_id,  CASCADE) [1:N]

appointments ───────► visits       (appointment_id, CASCADE) [1:1]
visits ─────────────► prescriptions (visit_id,      CASCADE) [1:N]

patients ───────────► reviews (patient_id,     CASCADE) [1:N]
doctors  ───────────► reviews (doctor_id,      CASCADE) [1:N]
appointments ───────► reviews (appointment_id, CASCADE) [1:1, UNIQUE]
```

Ключевой паттерн: `users` — единая таблица авторизации для всех ролей, профили пациентов и врачей хранятся в отдельных таблицах (`patients`, `doctors`) со связью 1:1 через `user_id`.

### 8.3 Индексы и оптимизация

Таблица `appointments` имеет наибольшее количество индексов (6), что отражает её центральную роль:

```sql
INDEX idx_appt_patient          (patient_id)
INDEX idx_appt_doctor           (doctor_id)
INDEX idx_appt_scheduled        (scheduled_at)
INDEX idx_appt_status_scheduled (status, scheduled_at)  -- составной
INDEX idx_appt_doctor_status    (doctor_id, status)     -- составной
INDEX idx_appt_patient_status   (patient_id, status)    -- составной
UNIQUE uq_appt_doctor_active_slot (doctor_id, active_slot)  -- защита от дублей
```

**Generated column `active_slot`:** это инженерное решение заслуживает отдельного внимания:

```sql
ALTER TABLE appointments
    ADD COLUMN active_slot DATETIME GENERATED ALWAYS AS (
        IF(status <> 'cancelled', scheduled_at, NULL)
    ) STORED;

ALTER TABLE appointments
    ADD UNIQUE KEY uq_appt_doctor_active_slot (doctor_id, active_slot);
```

Уникальный индекс на этом столбце игнорирует NULL (стандартное поведение SQL), поэтому отменённые записи (`status = 'cancelled'`) не блокируют слот. Для активных записей индекс гарантирует уникальность (doctor_id, scheduled_at), предотвращая двойное бронирование даже при ошибке на уровне приложения.

Прочие индексы:
- `doctors`: `idx_doctors_spec (specialization_id)` — для фильтрации по специализации.
- `reviews`: `idx_reviews_doctor_approved (doctor_id, is_approved)` — составной для частого запроса "одобренные отзывы врача".
- `schedule_exceptions`: `idx_sch_exc_doctor_date (doctor_id, exception_date)` — для быстрой проверки исключений.
- `lab_tests`: `idx_lab_tests_category (category)` — для группировки.

CHECK constraints добавлены через ALTER TABLE:
- `chk_reviews_rating`: `rating BETWEEN 1 AND 5`.
- `chk_schedules_times`: `start_time < end_time`.

---

## 9. Анализ маршрутизации

| Метод | Путь | Контроллер::Метод | Middleware |
|-------|------|-------------------|-----------|
| GET | / | PublicController::home | — |
| GET | /doctors | PublicController::doctors | — |
| GET | /doctors/{id} | PublicController::doctor | — |
| GET | /services | PublicController::services | — |
| GET | /contact | PublicController::contact | — |
| POST | /contact | PublicController::contactSend | RateLimit(5/1h) |
| GET | /about | PublicController::about | — |
| GET | /faq | PublicController::faq | — |
| GET | /articles | PublicController::articles | — |
| GET | /articles/{slug} | PublicController::article | — |
| GET | /login | AuthController::showLogin | requireGuest |
| POST | /login | AuthController::doLogin | requireGuest + RateLimit(5/15m) |
| GET | /register | AuthController::showRegister | requireGuest |
| POST | /register | AuthController::doRegister | requireGuest + RateLimit(5/1h) |
| POST | /logout | AuthController::logout | CSRF |
| GET | /patient/dashboard | PatientController::dashboard | requireRole(patient) |
| GET | /patient/book | PatientController::book | requireRole(patient) |
| POST | /patient/book | PatientController::doBook | requireRole(patient) |
| GET | /patient/book/analysis | PatientController::bookAnalysis | requireRole(patient) |
| POST | /patient/book/analysis | PatientController::doBookAnalysis | requireRole(patient) |
| GET | /patient/appointments | PatientController::appointments | requireRole(patient) |
| POST | /patient/appointments/cancel | PatientController::cancelAppointment | requireRole(patient) |
| GET | /patient/medical-record | PatientController::medicalRecord | requireRole(patient) |
| GET | /patient/profile | PatientController::profile | requireRole(patient) |
| POST | /patient/profile | PatientController::updateProfile | requireRole(patient) |
| POST | /patient/profile/password | PatientController::changePassword | requireRole(patient) |
| GET | /patient/reviews | PatientController::reviews | requireRole(patient) |
| POST | /patient/reviews/submit | PatientController::submitReview | requireRole(patient) |
| GET | /patient/visit/{visitId}/print | PatientController::printVisit | requireRole(patient) + OwnerMiddleware |
| GET | /doctor/dashboard | DoctorController::dashboard | requireRole(doctor) |
| GET | /doctor/appointment/{id} | DoctorController::appointment | requireRole(doctor) |
| POST | /doctor/appointment/{id}/start | DoctorController::startAppointment | requireRole(doctor) |
| POST | /doctor/appointment/{id}/protocol | DoctorController::saveProtocol | requireRole(doctor) |
| POST | /doctor/appointment/{id}/prescription/add | DoctorController::addPrescription | requireRole(doctor) |
| POST | /doctor/appointment/{id}/prescription/delete | DoctorController::deletePrescription | requireRole(doctor) |
| GET | /doctor/history | DoctorController::history | requireRole(doctor) |
| GET | /doctor/profile | DoctorController::profile | requireRole(doctor) |
| GET | /admin/dashboard | AdminController::dashboard | requireRole(admin) |
| GET | /admin/appointments | AdminController::appointments | requireRole(admin) |
| GET | /admin/appointments/export | AdminController::exportCsv | requireRole(admin) |
| POST | /admin/appointment/{id}/cancel | AdminController::cancelAppointment | requireRole(admin) |
| POST | /admin/appointment/{id}/reschedule | AdminController::rescheduleAppointment | requireRole(admin) |
| GET | /admin/schedule | AdminController::schedule | requireRole(admin) |
| POST | /admin/schedule/{doctorId}/save | AdminController::saveSchedule | requireRole(admin) |
| GET | /admin/reviews | AdminController::reviews | requireRole(admin) |
| POST | /admin/review/{id}/approve | AdminController::approveReview | requireRole(admin) |
| POST | /admin/review/{id}/delete | AdminController::deleteReview | requireRole(admin) |
| POST | /admin/review/{id}/reply | AdminController::replyToReview | requireRole(admin) |
| GET | /admin/doctors | AdminController::doctors | requireRole(admin) |
| GET | /admin/doctors/create | AdminController::createDoctorForm | requireRole(admin) |
| POST | /admin/doctors/create | AdminController::createDoctor | requireRole(admin) |
| GET | /admin/doctors/{id}/edit | AdminController::editDoctorForm | requireRole(admin) |
| POST | /admin/doctors/{id}/edit | AdminController::updateDoctor | requireRole(admin) |
| POST | /admin/doctors/{id}/deactivate | AdminController::deactivateDoctor | requireRole(admin) |
| POST | /admin/doctors/{id}/activate | AdminController::activateDoctor | requireRole(admin) |
| GET | /admin/services | AdminController::services | requireRole(admin) |
| POST | /admin/services/create | AdminController::createService | requireRole(admin) |
| POST | /admin/services/{id}/update | AdminController::updateService | requireRole(admin) |
| POST | /admin/services/{id}/delete | AdminController::deleteService | requireRole(admin) |
| GET | /admin/lab-tests | AdminController::labTests | requireRole(admin) |
| POST | /admin/lab-tests/create | AdminController::createLabTest | requireRole(admin) |
| POST | /admin/lab-tests/{id}/update | AdminController::updateLabTest | requireRole(admin) |
| POST | /admin/lab-tests/{id}/delete | AdminController::deleteLabTest | requireRole(admin) |

Глобальный middleware: `CsrfMiddleware::handle()` применяется ко всем POST-запросам через `$router->use()`.

---

## 10. Анализ авторизации и аутентификации

### 10.1 Система ролей (Role enum)

Три роли определены в `src/Enums/Role.php` и в ENUM-столбце таблицы `users.role`:

- **patient** — пациент: доступ к `/patient/*`. Регистрируется самостоятельно через форму регистрации.
- **doctor** — врач: доступ к `/doctor/*`. Создаётся только администратором через `/admin/doctors/create`.
- **admin** — администратор: полный доступ к `/admin/*`. Создаётся через seed-скрипт (admin@hospital.local).

### 10.2 Сессионная аутентификация

При успешном входе в сессию записываются три ключа:

```php
Session::set('user_id',    $user->id);
Session::set('user_role',  $user->role);
Session::set('user_email', $user->email);
```

Перед установкой сессионных данных вызывается `session_regenerate_id(true)` — защита от Session Fixation атаки.

Сессии хранятся в PHP-сессионном хранилище. Параметры cookie: `lifetime=0` (до закрытия браузера), `httponly=true`, `samesite=Strict`, `secure` = только при HTTPS.

### 10.3 Middleware защита маршрутов

`AuthMiddleware` (`src/Middleware/AuthMiddleware.php`) реализует три сценария:

1. `requireAuth()` — проверка наличия `user_id` в сессии. При отсутствии — flash + redirect `/login`.
2. `requireRole(string $role)` — вызывает `requireAuth()`, затем сравнивает `user_role`. При несовпадении — redirect `/`.
3. `requireGuest()` — для страниц логина/регистрации. Если пользователь уже вошёл — `redirectToDashboard()`.

`redirectToDashboard()` использует `match ($role)` для направления в нужный дашборд по роли.

`redirect()` формирует URL с учётом base path скрипта — важно при развёртывании в поддиректории.

### 10.4 Rate limiting для auth эндпоинтов

`RateLimitMiddleware` (`src/Middleware/RateLimitMiddleware.php`) хранит счётчики в PHP-сессии:

```php
public static function make(string $key, int $limit = 5, int $windowSeconds = 900): callable
```

Применяется к:
- `POST /login` — 5 попыток за 15 минут (`login`, 5, 900).
- `POST /register` — 5 попыток за 1 час (`register`, 5, 3600).
- `POST /contact` — 5 запросов за 1 час (`contact`, 5, 3600).

Механизм: счётчик `rl_{key}_count` и время сброса `rl_{key}_reset` хранятся в `$_SESSION`. При превышении лимита — flash-сообщение с оставшимся временем ожидания и redirect на предыдущую страницу (только если Referer совпадает с текущим хостом).

**Ограничение:** rate limiting на основе сессии легко обходится очисткой cookie. Для продакшена рекомендуется Redis/Memcached-based rate limiting по IP.

---

## 11. Анализ безопасности

### 11.1 CSRF защита (механизм, реализация)

Двухуровневая защита:

**Уровень 1 — Глобальный middleware:**
```php
$router->use(static fn() => CsrfMiddleware::handle());
```
`CsrfMiddleware::handle()` валидирует токен для каждого POST-запроса, сравнивая `$_POST['csrf_token']` с `$_SESSION['csrf_token']` через `hash_equals()`.

**Уровень 2 — Повторная проверка в контроллерах** (defence in depth):
- `BaseController::validateCsrf()` вызывается в большинстве POST-обработчиков.
- В `AuthController` токен проверяется вручную даже для `doLogin()`.

Каждая форма обязана содержать:
```php
<input type="hidden" name="csrf_token" value="<?= View::e(Session::generateCsrfToken()) ?>">
```

Токен — 64-символьная hex-строка из `bin2hex(random_bytes(32))`, хранится в сессии. Токен постоянный (не меняется при каждом запросе) — это упрощает работу с несколькими вкладками, но допустимо при `SameSite=Strict`.

### 11.2 SQL Injection защита

Все запросы используют PDO prepared statements:

```php
$stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
```

Критически важный параметр: `PDO::ATTR_EMULATE_PREPARES => false`. При `true` PDO эмулирует prepared statements в PHP, при `false` — реальные prepared statements на стороне MySQL, что полностью исключает SQL-инъекции.

Нет ни одного места в коде, где значения от пользователя конкатенируются прямо в SQL-строку. Динамические WHERE-условия строятся через накопление `$where[]` и параметров `$params[]`.

### 11.3 XSS защита

Весь вывод данных в шаблонах проходит через `View::e()`:

```php
public static function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
```

`ENT_QUOTES` экранирует как одинарные, так и двойные кавычки. `ENT_SUBSTITUTE` заменяет невалидные байты вместо возврата пустой строки. Кодировка UTF-8 указана явно.

Данные для передачи в JavaScript (атрибуты `data-*` в `admin/dashboard.php`) экранируются через `htmlspecialchars(json_encode(...), ENT_QUOTES)`:
```php
data-labels="<?= htmlspecialchars(json_encode(array_column($byDay, 'day')), ENT_QUOTES) ?>"
```

HTTP-заголовок `Content-Security-Policy` ограничивает исполнение скриптов только из `'self'`, что предотвращает XSS даже при потенциальном пропуске экранирования.

### 11.4 Хеширование паролей

`password_hash($password, PASSWORD_DEFAULT)` — текущий дефолтный алгоритм (bcrypt/argon2id в зависимости от версии PHP). `PASSWORD_DEFAULT` автоматически обновляется при появлении более сильных алгоритмов.

`password_needs_rehash()` — автоматический rehash при входе если алгоритм устарел.

Dummy-хеш в `AuthService::login()` при несуществующем email:
```php
password_verify($password, '$2y$12$invaliddummyhashXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
```
Обеспечивает одинаковое время ответа независимо от существования email.

### 11.5 Rate Limiting

Описан в разделе 10.4. Применяется к критичным эндпоинтам: логин, регистрация, контакт.

### 11.6 Secure cookies

```php
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Strict',
]);
```

- `httponly=true` — недоступность куки для JavaScript (защита от XSS-кражи сессии).
- `samesite=Strict` — кука не отправляется в cross-site запросах (защита от CSRF).
- `secure` — только по HTTPS в продакшене.

### 11.7 HTTP Security Headers

Установлены в `public/index.php` для каждого запроса:

```php
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; style-src 'self' https://fonts.googleapis.com; ...");
```

`X-Frame-Options: DENY` — запрет встраивания в iframe (защита от clickjacking). `X-Content-Type-Options: nosniff` — запрет MIME-sniffing. CSP ограничивает источники контента.

### 11.8 OwnerMiddleware

Проверка принадлежности ресурса перед его отображением:

```php
public static function visit(): callable
{
    return static::make(
        '#/visit/(\d+)/#',
        static function (int $visitId, int $userId): bool {
            $stmt = Database::getInstance()->prepare(
                'SELECT 1 FROM visits v
                 JOIN appointments a ON a.id = v.appointment_id
                 JOIN patients p     ON p.id = a.patient_id
                 WHERE v.id = ? AND p.user_id = ?'
            );
            $stmt->execute([$visitId, $userId]);
            return (bool) $stmt->fetch();
        }
    );
}
```

Выполняется до контроллера — пациент не может просмотреть визиты других пациентов.

---

## 12. Анализ бизнес-логики

### 12.1 Запись пациента к врачу (полный флоу)

1. **Выбор специализации** (`GET /patient/book`): отображается список специализаций с количеством врачей. Данные агрегируются из `DoctorRepository::getAllWithRating()` в контроллере.

2. **Выбор врача** (`?spec_id=N`): фильтрация врачей по специализации, отображение карточек с рейтингом.

3. **Выбор даты** (`?doctor_id=N`): `AppointmentService::getWorkingDays()` строит список ближайших рабочих дней врача (до 14 дней из ближайших 60). Для каждого дня проверяется расписание (`schedules`) и исключения (`schedule_exceptions`).

4. **Выбор слота** (`?date=YYYY-MM-DD`): `AppointmentService::getSlots()` генерирует временные слоты:
   - Получает расписание врача на день недели.
   - Получает занятые слоты (`getBookedTimes()` — все активные записи).
   - Генерирует слоты от `start_time` до `end_time` с шагом `slot_duration_min`.
   - Помечает прошедшие и занятые слоты как недоступные.

5. **Подтверждение** (`POST /patient/book`): `AppointmentService::book()` с тройной защитой:
   - Проверка формата данных и даты в будущем.
   - Проверка доступности слота (повторный getSlots).
   - Транзакция: `SELECT ... FOR UPDATE` + проверка дублей + INSERT.
   - Страховка: перехват `PDOException` с кодом `23000` от unique index.

### 12.2 Управление расписанием врача

Расписание хранится в таблице `schedules`: по одной записи на каждый рабочий день недели (day_of_week 1-7). Атрибуты: start_time, end_time, slot_duration_min.

Исключения хранятся в `schedule_exceptions`: конкретная дата, флаг `is_day_off`, необязательная заметка.

Администратор редактирует расписание через форму с чекбоксами (активен ли день) и полями времени. `AdminController::saveSchedule()` итерирует по 7 дням недели и либо вызывает `upsertSchedule()` (INSERT ... ON DUPLICATE KEY UPDATE), либо `deleteScheduleDay()`.

### 12.3 Ведение медицинского приёма (visit lifecycle)

Полный жизненный цикл через `DoctorService`:

```
appointment.status = 'confirmed'/'pending'
    ↓ [Начать приём] startAppointment()
visits.started_at = NOW()
appointments.status = 'in_progress'
    ↓ [Автосохранение] saveProtocol(finish=false)
visits.complaints/examination/diagnosis = ...
    ↓ [Завершить] saveProtocol(finish=true)
visits.ended_at = NOW()
appointments.status = 'completed'
```

Транзакционность: создание визита и смена статуса выполняются в одной транзакции. Завершение визита (finish) также транзакционно.

### 12.4 Система рецептов

Назначения добавляются только когда приём `in_progress`. Три типа (`PrescriptionType`): `drug` (медикамент), `procedure` (процедура), `referral` (направление).

`DoctorService::addPrescription()` проверяет:
- Принадлежность приёма врачу (`ForbiddenException`).
- Статус `in_progress` (`DomainException`).
- Существование визита.
- Допустимый тип назначения.
- Непустое название.

Удаление возможно только для назначений текущего открытого визита. После завершения приёма изменение назначений технически не ограничено на уровне кода, но UI не предоставляет такой возможности.

### 12.5 Система отзывов (модерация)

Цикл отзыва:

1. Пациент видит завершённые приёмы без отзыва (`ReviewRepository::getCompletedWithoutReview()` — LEFT JOIN, `r.id IS NULL`).
2. Пациент заполняет форму (оценка 1-5, текст минимум 10 символов).
3. `PatientController::submitReview()` проверяет: владение приёмом, статус `completed`, отсутствие дубля через `existsByAppointment()`.
4. Создаётся отзыв с `is_approved = 0` (на модерации).
5. Администратор видит все неодобренные отзывы, может одобрить или удалить.
6. После одобрения отзыв влияет на `avg_rating` врача (вычисляется на лету через AVG в SELECT).
7. Администратор может добавить ответ от клиники (`admin_reply`, `admin_reply_at`).

Уникальный ключ `uq_reviews_appointment` гарантирует единственность отзыва на каждый приём на уровне БД.

### 12.6 Аналитика и статистика (admin)

`AdminRepository::getStats()` выполняет 5 отдельных COUNT-запросов:
- `total_patients` — всего пациентов.
- `total_appointments` — всего записей.
- `appointments_today` — записи за сегодня.
- `pending_count` — ожидающие подтверждения.
- `completed_this_month` — завершённые в текущем месяце.

`getAppointmentsByDay()` — GROUP BY DATE(scheduled_at) за 14 дней — данные для линейного графика.

`getTopDoctors()` — GROUP BY doctor_id с COUNT за завершённые приёмы, LIMIT 5 — данные для столбчатого графика.

Данные передаются в шаблон, JavaScript считывает их из `data-*` атрибутов `<canvas>` и строит диаграммы через Chart.js.

### 12.7 Запись на анализы

Лаборатория работает по фиксированному расписанию (Пн-Пт 08:00-18:00, слот 15 мин). Вся логика генерации слотов реализована прямо в `PatientController::bookAnalysis()`:

```php
for ($i = 0; $i < 14; $i++) {
    $ts = strtotime("+$i days");
    $dow = (int)date('N', $ts);
    if ($dow <= 5) {
        $availableDates[] = date('Y-m-d', $ts);
    }
}

$current = strtotime($date . ' 08:00');
$end     = strtotime($date . ' 18:00');
while ($current + 900 <= $end) {
    $t = date('H:i', $current);
    $booked = $this->labTests->getBookedTimes($date);
    $slots[] = ['time' => $t, 'available' => !$isPast && !in_array($t, $booked)];
    $current += 900;
}
```

Запись создаётся через `LabTestRepository::bookTest()` — INSERT в `appointments` с `appointment_type = 'lab_test'` и `doctor_id = NULL`. Это позволяет хранить записи к врачу и на анализы в единой таблице с полиморфной связью.

---

## 13. Анализ UI/UX

### 13.1 Дизайн-система и цветовая схема

Дизайн-система построена на CSS custom properties. Основной цвет — teal (бирюзовый), определён в `oklch()` для точного управления цветовым пространством. Акцентный — amber (янтарный). Нейтральная шкала — warm-gray/stone.

Семантические цвета: success (зелёный), warning (жёлтый), danger (красный), info (синий) — используются в бейджах, алертах, статусных индикаторах.

Типографика: два шрифта — Inter для текста (читабельность), Manrope для заголовков (характер и вес). Fluid-типографика через `clamp()` для крупных размеров.

### 13.2 Компоненты интерфейса

Ключевые UI-компоненты с BEM-именованием:
- `.btn`, `.btn--primary`, `.btn--ghost`, `.btn--sm`, `.btn--danger` — кнопки с вариантами.
- `.card`, `.card__body`, `.card__title` — карточки контента.
- `.stat-card` — статистические плитки с иконкой, значением и подписью.
- `.badge`, `.badge--success`, `.badge--warning` — статусные бейджи с цветной точкой.
- `.alert`, `.alert--success`, `.alert--error`, `.alert--warning`, `.alert--info` — flash-сообщения.
- `.navbar`, `.navbar__brand`, `.navbar__list`, `.navbar__link` — навигация.
- `.form-group`, `.form-label`, `.form-control` — элементы форм.
- `.table` — таблицы данных.
- `.slot-grid`, `.slot-btn` — сетка временных слотов для записи.

### 13.3 Flash-сообщения и обратная связь пользователю

Паттерн PRG (Post-Redirect-Get): все POST-обработчики после выполнения действия устанавливают flash-сообщение через `Session::setFlash()` и выполняют redirect. Следующий GET-запрос рендерит страницу с флашем.

Flash-сообщения исчезают при чтении (`getFlash()` удаляет их из сессии) — не накапливаются при обновлении страницы. Четыре типа: success (зелёный с checkmark), error (красный с X), warning (жёлтый с треугольником), info (синий с i).

### 13.4 Адаптивный дизайн

Три breakpoint: mobile (< 768px), tablet (768px), desktop (1024px+). Navbar сворачивается в бургер-меню на мобильных — CSS-only через checkbox toggle без JavaScript.

```css
@media (prefers-reduced-motion: reduce) {
    html { scroll-behavior: auto; }
    *, *::before, *::after { animation-duration: .01ms !important; }
}
```

### 13.5 Иконки (Lucide SVG система)

50+ иконок из библиотеки Lucide встроены inline как SVG-пути в PHP-функцию `icon()`. Преимущества: нет внешних зависимостей, нет HTTP-запросов за иконками, полное управление размером и цветом через CSS. Все иконки имеют `aria-hidden="true"` — не читаются скринридерами (декоративные).

---

## 14. Анализ производительности и оптимизации

### 14.1 PDO Singleton паттерн

Единственное соединение с базой данных на протяжении всего PHP-запроса. Устраняет overhead на переподключение. `Database::getInstance()` возвращает один и тот же объект PDO при любом количестве вызовов.

### 14.2 Пагинация данных

Административный список записей пагинирован (25/страница) через `Paginator`. Подсчёт через `countAppointments()` и выборка через `getAllAppointmentsPaginated()` с `LIMIT ? OFFSET ?`.

Страница модерации отзывов (20/страница) также пагинирована.

### 14.3 Индексы базы данных

Подробно описаны в разделе 8.3. Составные индексы на `appointments` покрывают наиболее частые запросы: поиск по врачу и статусу, по пациенту и статусу, по дате.

### 14.4 Батч-загрузка

В `VisitRepository::getFullHistoryForPatient()` реализована эффективная загрузка истории без N+1:

```php
// Одним запросом все визиты
$visits = $stmt->fetchAll();

// Одним запросом все назначения для найденных визитов
$pStmt = $this->db->prepare(
    "SELECT * FROM prescriptions WHERE visit_id IN ($placeholders)"
);
$pStmt->execute($visitIds);

// Группировка в PHP
foreach ($pStmt->fetchAll() as $p) {
    $byVisit[$p['visit_id']][] = $p;
}
```

### 14.5 Логирование

Logger пишет в файл асинхронно через `error_log($line, 3, $file)`. Ротация — удаление файлов старше 30 дней при каждой инициализации логгера. Прунинг происходит только при создании экземпляра (Singleton — один раз за запрос).

---

## 15. Используемые технологии и зависимости

| Технология | Версия | Назначение |
|-----------|--------|-----------|
| PHP | >= 8.1 | Основной язык: Enums, readonly properties, constructor promotion, first-class callable syntax, named arguments, fibers |
| MySQL | 8.0 | СУБД: поддержка generated columns, window functions, CHECK constraints |
| Apache | 2.4 | Web-сервер с mod_rewrite |
| Docker | — | Контейнеризация |
| Docker Compose | — | Оркестрация контейнеров |
| vlucas/phpdotenv | ^5.6 | Загрузка .env файла |
| ezyang/htmlpurifier | ^4.19 | Санитизация HTML (установлена, но не используется в основном коде) |
| Chart.js | CDN | Диаграммы на дашборде администратора |
| Google Fonts | CDN | Шрифты Inter и Manrope |
| Lucide | Inline SVG | Иконки |
| Composer | — | Менеджер PHP-зависимостей |

Зависимостей минимум — только phpdotenv и htmlpurifier. Это осознанное решение: нет ORM (Doctrine/Eloquent), нет DI-контейнера, нет фреймворка (Symfony/Laravel). Весь boilerplate написан вручную.

---

## 16. Контейнеризация (Docker)

`docker-compose.yml` определяет три сервиса:

```yaml
services:
  web:
    build:
      context: .
      dockerfile: docker/Dockerfile
    ports:
      - "8000:80"
    volumes:
      - .:/var/www/html
    depends_on:
      - db

  db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: hospital_is
      MYSQL_USER: user
      MYSQL_PASSWORD: password
      MYSQL_ROOT_PASSWORD: root
    ports:
      - "3306:3306"
    volumes:
      - db_data:/var/lib/mysql
      - ./database/migrations.sql:/docker-entrypoint-initdb.d/1-migrations.sql

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    ports:
      - "8081:80"
    environment:
      PMA_HOST: db
      PMA_USER: user
      PMA_PASSWORD: password
```

- **web** — PHP 8.1 + Apache. Собирается из `docker/Dockerfile`. Маппинг порта 8000 → 80. Bind mount всего репозитория в `/var/www/html`.
- **db** — MySQL 8.0. Автоматическая инициализация из `migrations.sql` при первом старте (через `docker-entrypoint-initdb.d`). Named volume `db_data` для персистентного хранения.
- **phpmyadmin** — веб-интерфейс для администрирования БД на порту 8081.

Примечательно: `migrations.sql` монтируется в `docker-entrypoint-initdb.d` — MySQL автоматически выполняет его при первом запуске контейнера, если база данных ещё не инициализирована.

---

## 17. Пользовательские сценарии

### 17.1 Сценарий пациента (регистрация → запись → приём → отзыв)

**Шаг 1: Регистрация.** Пациент открывает `/register`, заполняет форму (email, пароль x2, ФИО, дата рождения, пол). `AuthService::register()` валидирует данные, проверяет уникальность email, создаёт записи в `users` и `patients` в транзакции. При успехе — автоматический вход и редирект на дашборд.

**Шаг 2: Запись к врачу.** В `/patient/book` пациент выбирает специализацию → врача → дату → временной слот. Нажимает «Записаться», форма отправляется POST на `/patient/book`. `AppointmentService::book()` выполняет все проверки и создаёт запись со статусом `confirmed`.

**Шаг 3: Ожидание приёма.** На дашборде `/patient/dashboard` видны предстоящие записи. Пациент может отменить запись через `/patient/appointments/cancel` (только статусы `pending`/`confirmed`).

**Шаг 4: Медицинский приём.** Врач начинает приём — статус меняется на `in_progress`. Врач заполняет протокол, добавляет назначения, завершает приём — статус `completed`.

**Шаг 5: Медицинская карта.** Пациент видит завершённый визит в `/patient/medical-record` с диагнозом и назначениями. Может распечатать через `/patient/visit/{id}/print`.

**Шаг 6: Отзыв.** В `/patient/reviews` появляется возможность оставить отзыв на завершённый приём. После отправки отзыв ожидает модерации администратора.

### 17.2 Сценарий врача (вход → просмотр расписания → ведение приёма → рецепты)

**Вход.** Врач входит через `/login` с email и паролем. `AuthService` проверяет пароль и активность врача (`is_active`). После входа — редирект на `/doctor/dashboard`.

**Дашборд.** Показывает расписание на сегодня (`getTodayForDoctor()`), статистику (всего приёмов, завершённых, в этом месяце, средний рейтинг).

**Страница приёма** `/doctor/appointment/{id}`. Врач видит данные пациента (ФИО, дата рождения, телефон, пол, адрес, хронические заболевания), историю предыдущих визитов, текущий протокол.

**Ведение приёма.** Нажимает «Начать приём» → POST `/doctor/appointment/{id}/start` → `DoctorService::startAppointment()`. Заполняет жалобы, осмотр, диагноз. Добавляет назначения. Нажимает «Завершить» → POST `/doctor/appointment/{id}/protocol` с флагом `finish=true` → транзакционное завершение.

**История.** `/doctor/history` — полный список завершённых приёмов с диагнозами.

### 17.3 Сценарий администратора (управление врачами → расписание → статистика)

**Дашборд.** Метрики, графики динамики записей и топ-врачей через Chart.js.

**Создание врача.** `/admin/doctors/create` → форма с email, паролем, ФИО, специализацией, биографией. `AdminController::createDoctor()` валидирует, проверяет уникальность email, хеширует пароль, создаёт записи в `users` и `doctors`.

**Расписание.** `/admin/schedule` с выбором врача. Форма с 7 строками (по дням недели): чекбокс активности, время начала/окончания, длительность слота. `saveSchedule()` итерирует дни, вызывает `upsertSchedule()` или `deleteScheduleDay()`.

**Управление записями.** `/admin/appointments` с пагинацией, фильтрами по статусу и дате. Возможность отмены и переноса (указывается новая дата и время).

**Модерация отзывов.** `/admin/reviews`: список ожидающих модерации (is_approved=0) и одобренных. Одобрение, удаление, ответ от клиники.

**Экспорт.** `GET /admin/appointments/export` — CSV с BOM для Excel, с возможностью фильтрации по диапазону дат.

---

## 18. Инженерные и архитектурные решения

**1. Кастомный MVC без фреймворка.** Выбор объясняется стремлением к минимализму и полному контролю над кодом. Каждый компонент написан под конкретные нужды проекта, нет "лишнего" кода фреймворка. Недостаток — необходимость поддерживать инфраструктурный код самостоятельно.

**2. PDO Singleton с prepared statements.** Минималистичное решение для слоя данных без ORM. Сохраняет полный контроль над SQL и производительностью. `ATTR_EMULATE_PREPARES = false` — критический параметр безопасности.

**3. Generated column + уникальный индекс против race condition.** Элегантное решение: вместо advisory locks или очередей, используется встроенный механизм MySQL. Сгенерированный столбец `active_slot` содержит `scheduled_at` для активных записей и NULL для отменённых. Уникальный индекс на (doctor_id, active_slot) автоматически блокирует дубли на уровне БД.

**4. SELECT FOR UPDATE в транзакции.** Пессимистическая блокировка слота при бронировании. Параллельные транзакции будут ждать освобождения блокировки, что исключает race condition при одновременной записи.

**5. Трёхуровневая CSRF-защита.** Глобальный middleware + повторная проверка в контроллерах + форма с hidden input. Избыточность обоснована — defence in depth.

**6. Dummy password_verify для timing attack mitigation.** При несуществующем email выполняется фиктивный `password_verify` для выравнивания времени ответа. Предотвращает Oracle-атаку на существование email.

**7. PHP 8.1 Backed Enums.** Использование нативных перечислений для ключевых доменных понятий. Enum-значения строго типизированы, IDE-поддержка, невозможность опечаток в строковых константах.

**8. Иммутабельные DTO.** `User` и `DoctorProfile` — readonly objects, гидратируемые через фабричный метод `fromRow()`. Предотвращают случайную мутацию данных между слоями.

**9. @layer в CSS.** Явное управление каскадом через CSS Cascade Layers — современная практика, позволяющая избежать specificity wars. Reset имеет самый низкий приоритет, utilities — самый высокий.

**10. Полиморфная таблица appointments.** Единая таблица для двух типов записей (врач + анализ) через `appointment_type` ENUM и nullable `doctor_id`/`lab_test_id`. Упрощает отчётность и историю пациента.

---

## 19. Сильные стороны проекта

**Безопасность.** Реализован полный спектр базовых защит: CSRF, XSS, SQL-injection, timing attacks, session fixation, secure cookies, rate limiting, HTTP security headers, owner checks. Для учебного/малого проекта — отличный уровень безопасности.

**Race condition protection.** Трёхуровневая защита от двойного бронирования (SELECT FOR UPDATE + generated column + уникальный индекс) — продакшен-уровень решение.

**Чистая архитектура.** Чёткое разделение на слои (Controller → Service → Repository → Database), иммутабельные модели, строгая типизация (`declare(strict_types=1)` везде), readonly properties.

**Минимализм зависимостей.** Только два composer-пакета (phpdotenv, htmlpurifier). Нет overhead тяжёлых фреймворков. Простота деплоя и понимания.

**Полнофункциональность.** Несмотря на минимализм стека — полный цикл работы клиники: регистрация → запись → приём → протокол → назначения → отзыв → модерация → статистика.

**Современный CSS.** CSS Custom Properties, oklch() цвета, cascade layers, fluid typography, CSS-only бургер-меню, prefers-reduced-motion — следование современным стандартам.

**Контейнеризация.** Docker Compose с автоматической инициализацией БД из migrations.sql — простота запуска окружения разработки.

**Логирование.** PSR-3-подобный ротируемый логгер — все значимые события фиксируются без внешних зависимостей.

---

## 20. Возможные улучшения и направления развития

**1. Redis-based Rate Limiting.** Текущий rate limiting на основе PHP-сессий легко обходится очисткой cookie. Замена на Redis/Memcached с ключами по IP+User-Agent даст надёжную защиту.

**2. Email-уведомления.** Отправка подтверждений на email при записи, напоминания за день до приёма — стандартный функционал медицинских систем. Потребует интеграции PHPMailer или аналога.

**3. Двухфакторная аутентификация.** Для врачей и администраторов — TOTP (Google Authenticator) повысит безопасность учётных записей.

**4. API для мобильного приложения.** Добавление JSON API (REST или GraphQL) позволит разработать мобильные приложения для пациентов и врачей.

**5. Очередь задач.** Для фоновых задач (отправка email, генерация PDF, экспорт больших данных) потребуется очередь (RabbitMQ, Redis Queue).

**6. Кеширование.** Список специализаций, расписания врачей, публичные страницы — кандидаты для кеширования в Redis. Снизит нагрузку на БД.

**7. Улучшение Rate Limiting.** IP-based ограничения вместо сессионных. Защита от распределённых атак.

**8. Audit log.** Полная история изменений данных (кто и когда изменил статус записи, данные пациента) — критично для медицинских систем с регуляторными требованиями.

**9. Загрузка фотографий врачей.** Форма загрузки изображения с валидацией формата/размера, хранение в файловой системе или облаке.

**10. CI/CD пайплайн.** Автоматические тесты (PHPUnit), статический анализ (PHPStan/Psalm), автодеплой.

**11. PHPStan / Psalm.** Статический анализ кода улучшит типобезопасность и обнаружит потенциальные ошибки. Проект уже имеет хорошую типизацию, что упростит внедрение.

**12. Миграционный инструмент.** Замена `migrations.sql` на миграционный инструмент (Phinx, Doctrine Migrations) с версионированием и откатом.

---

## 21. Заключение

Проект представляет собой технически грамотную реализацию медицинской информационной системы на кастомном PHP-стеке. Несмотря на отказ от фреймворков, код демонстрирует зрелые архитектурные паттерны: слоистую MVC-архитектуру с чётким разделением ответственности, строгую типизацию, иммутабельные модели, полноценную защиту безопасности.

Ключевые технические достижения проекта:

- **Трёхуровневая защита от race condition** при бронировании слотов через `SELECT FOR UPDATE`, generated column и уникальный индекс.
- **Полный стек мер безопасности**: CSRF, XSS, SQLi, timing attacks, session fixation, rate limiting, owner checks — уровень выше среднего для проектов без фреймворков.
- **Минималистичный, но расширяемый инфраструктурный слой**: Router, Database, Session, View, Logger, Paginator, Validator написаны с нуля и покрывают все нужды проекта.
- **Применение современных возможностей PHP 8.1**: readonly properties, Backed Enums, constructor property promotion, named arguments, first-class callable syntax.
- **Современный CSS-стек**: CSS Custom Properties с oklch(), Cascade Layers, fluid typography — уровень production-систем.

Проект является отличной основой для учебного или малого продакшен-сценария. При необходимости масштабирования ключевые направления — добавление Redis для кеширования и rate limiting, email-уведомления, мобильное API и audit log для медицинского комплаенса.

---

*Анализ выполнен на основе прямого чтения исходного кода без запуска приложения. Все конкретные примеры кода взяты из реальных файлов проекта.*
