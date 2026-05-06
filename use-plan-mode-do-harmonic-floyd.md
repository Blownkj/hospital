# План полного редизайна UI/UX Hospital IS

## Контекст

Проект — клиническая ИС на PHP 8.1 + MySQL с минимальным MVC. Бэкенд (роуты, контроллеры, сервисы, репозитории) и архитектура **не меняются**. Меняем только слой представлений (`views/`), CSS (`public/css/`) и при необходимости — поле `image_url` для двух сущностей.

**Текущие проблемы (по результатам аудита):**
- 482–497 inline-`style="..."` атрибутов в 32 view-файлах → невозможно поддерживать единообразие.
- Нет CSS-переменных (design tokens) — все цвета, размеры, отступы захардкожены десятками значений (`#777`, `#888`, `#999`, и т.д.).
- Спейсинговая шкала непоследовательна: `2/4/5/6/7/8/10/12/13/14/16/18/20/24/28/32/40/48/60`.
- Тип­ографика без ритма: `12/13/14/15/16/18/19/20/22/36`, нет иерархии.
- Шрифт — только system-ui; нет вариативного шрифта для бренд-ощущения.
- HTML-разметка: `<div>`-суп без `<section>`, `<article>`, `<header>`, `<footer>`, без ARIA.
- Только 2 брейкпоинта (`640px`, `768px`), desktop-first, mobile-перекрытия через `!important`.
- Эмодзи как иконки в навбаре, статусах, бэйджах, кнопках, фичах, специализациях, разделах админки → «AI-look» и нерасширяемо.
- Изображения: только `doctors.photo_url` (поле есть, но не заполняется в seed); услуги/специализации/статьи/hero — без визуала. Карточки врачей рендерят инициалы вместо фото.
- В админке тяжёлые таблицы и формы построены через inline-flex/grid, без ре­юзаб­л-классов.
- Нет общих модалок, компонента breadcrumbs, skeleton-состояний, единого empty-state.

**Цель:** перейти на единую дизайн-систему «современная медицинская клиника» с design tokens, BEM-неймингом, SVG-иконками (Lucide), профессиональными изображениями, семантической HTML-разметкой и mobile-first responsive — **без поломки текущей функциональности и без правок в `src/`**.

---

## Принятые решения

| Решение | Значение |
|---|---|
| Визуальное направление | Современная медицинская клиника (мятно-бирюзовый + тёплый белый, мягкие тени, скруглённые карточки) |
| Иконки | Inline SVG (Lucide), без CDN-зависимостей |
| Изображения в БД | Добавить `services.image_url` и `specializations.image_url` (nullable VARCHAR(500)). `doctors.photo_url` уже есть — заполнить через seed. Hero/статика — в `/public/images/` |
| CSS-архитектура | Один `main.css`, секционная организация, BEM-нейминг, CSS-переменные (design tokens). Никаких сборщиков и фреймворков. |

---

## Целевая дизайн-система

### Палитра (CSS-токены)

```css
/* Primary brand */
--color-primary-50:  #eff9f7;
--color-primary-100: #d2f0e9;
--color-primary-300: #6dd0ba;
--color-primary-500: #14b8a6;   /* основной бирюзовый */
--color-primary-600: #0d9488;
--color-primary-700: #0f766e;
/* Accent (тёплый) */
--color-accent-500:  #f59e0b;   /* акценты, рейтинги */
/* Neutrals (warm-gray) */
--color-bg:          #fafaf9;
--color-surface:     #ffffff;
--color-surface-2:   #f5f5f4;
--color-border:      #e7e5e4;
--color-border-strong:#d6d3d1;
--color-text:        #1c1917;
--color-text-muted:  #57534e;
--color-text-subtle: #78716c;
/* Semantic */
--color-success-bg:  #ecfdf5; --color-success: #047857; --color-success-border:#a7f3d0;
--color-warning-bg:  #fffbeb; --color-warning: #b45309; --color-warning-border:#fde68a;
--color-danger-bg:   #fef2f2; --color-danger:  #b91c1c; --color-danger-border: #fecaca;
--color-info-bg:     #eff6ff; --color-info:    #1d4ed8; --color-info-border:   #bfdbfe;
```

### Типографика

```css
--font-sans: "Inter", system-ui, -apple-system, "Segoe UI", sans-serif;
--font-display: "Manrope", "Inter", system-ui, sans-serif;  /* заголовки */
/* Scale (1.250 major third) */
--text-xs: 12px; --text-sm: 14px; --text-base: 16px;
--text-lg: 18px; --text-xl: 20px; --text-2xl: 24px;
--text-3xl: 30px; --text-4xl: 38px; --text-5xl: 48px;
--leading-tight: 1.2; --leading-normal: 1.5; --leading-relaxed: 1.65;
--weight-regular: 400; --weight-medium: 500; --weight-semibold: 600; --weight-bold: 700;
```

Шрифты подключаются один раз `<link>` в `views/layout/public_header.php` (Google Fonts: Inter + Manrope, latin + cyrillic subset, `display=swap`).

### Спейсинг (4-пиксельная сетка)

```css
--space-1: 4px;  --space-2: 8px;  --space-3: 12px; --space-4: 16px;
--space-5: 20px; --space-6: 24px; --space-8: 32px; --space-10:40px;
--space-12:48px; --space-16:64px; --space-20:80px;
```

### Скругления, тени, движение

```css
--radius-sm:6px;  --radius-md:10px; --radius-lg:16px; --radius-xl:24px; --radius-pill:999px;
--shadow-sm: 0 1px 2px rgba(15,23,42,.04);
--shadow-md: 0 4px 12px rgba(15,23,42,.06);
--shadow-lg: 0 12px 32px rgba(15,23,42,.08);
--shadow-focus: 0 0 0 3px rgba(20,184,166,.25);
--ease: cubic-bezier(.4,0,.2,1);
--dur-fast:120ms; --dur-base:200ms;
```

### Брейкпоинты (mobile-first)

```css
/* sm 480, md 768, lg 1024, xl 1280 */
@media (min-width: 480px) { ... }
@media (min-width: 768px) { ... }
@media (min-width: 1024px){ ... }
```

---

## Компонентная библиотека (BEM)

| Компонент | Класс | Замечания |
|---|---|---|
| Кнопка | `.btn .btn--primary / --secondary / --ghost / --danger / --sm / --lg / --block` | 44px высота min, явный `:focus-visible` ринг |
| Карточка | `.card .card__media .card__body .card__title .card__meta .card__footer` | Универсальная, используется для врачей/услуг/статей |
| Карточка врача | `.doctor-card` (модификатор `.doctor-card--row`) | Фото 4:3 сверху, имя, специализация, рейтинг, кнопка |
| Карточка специализации | `.spec-card` | Фоновое изображение + полупрозрачный градиент + название |
| Карточка услуги | `.service-card` | Фото 16:9, название, описание, цена-pill |
| Форма | `.form .form__group .form__label .form__control .form__hint .form__error` | Высота input 44px, скругление `--radius-md` |
| Таблица | `.table .table--striped .table--compact` | Sticky `<thead>`, scroll-shadow на мобиле |
| Бэйдж | `.badge .badge--success / --warning / --pending / --danger / --info` | Без эмодзи, 22px высоты, цвет фона/текста из токенов |
| Алерт | `.alert .alert--success / --error / --warning / --info` | Slot для иконки слева |
| Навбар | `.navbar .navbar__brand .navbar__nav .navbar__link .navbar__cta` | Burger-меню < 768px |
| Hero | `.hero .hero__title .hero__lead .hero__cta .hero__media` | Левая колонка — текст, правая — изображение |
| Pagination | `.pagination .pagination__item .pagination__item--current` | Стрелки SVG, не эмодзи |
| Empty state | `.empty-state .empty-state__icon .empty-state__title .empty-state__cta` | SVG-иллюстрация |
| Stepper (бронирование) | `.stepper .stepper__step .stepper__step--done / --current` | Числовые шаги, без эмодзи |
| Date pill / time slot | `.date-pill .time-slot` | Чёткое active/disabled состояние |
| Stat card (admin) | `.stat-card .stat-card__value .stat-card__label .stat-card__delta` | KPI на дашборде |
| Tabs (профиль) | `.tabs .tabs__tab .tabs__panel` | Для профиля врача/пациента |
| Footer | `.site-footer .site-footer__col .site-footer__bottom` | 3-колоночный, контакты + ссылки + копирайт |

Все компоненты — в `main.css`, секции с комментариями-разделителями.

---

## SVG-иконки (Lucide), замена эмодзи

Создаётся **один partial-файл** `views/partials/icon.php`, который принимает имя иконки и опц. размер/класс и возвращает inline-SVG. Иконки берутся из Lucide (MIT) и копируются как статичный набор в `public/icons/` либо встраиваются прямо в partial через switch.

Использование:
```php
<?php View::icon('calendar', ['size' => 20, 'class' => 'icon icon--muted']); ?>
```

### Карта замен «эмодзи → иконка Lucide»

| Эмодзи | Контекст | Иконка |
|---|---|---|
| 🏥 | бренд навбара | `stethoscope` или wordmark-логотип SVG |
| 📅 / 🕐 | дата/время в карточках | `calendar`, `clock` |
| 🧪 | анализы | `flask-conical` |
| 💊 | назначения | `pill` |
| 🔬 | лаборатория | `microscope` |
| 🩺 | врачи / приёмы | `stethoscope` |
| 👨‍⚕️ / 👤 | пациент/доктор | `user-round` |
| ⭐ | рейтинг | `star` (filled — `--color-accent-500`) |
| ✅ / ✓ | success/confirmed | `check`, `check-circle-2` |
| ❌ / ✕ | cancel | `x`, `x-circle` |
| ⏳ | pending | `clock-3` |
| ▶ | in-progress | `play-circle` |
| ⚠️ | warning | `alert-triangle` |
| ℹ️ | info | `info` |
| ❤️ 🧠 🔪 👁️ 🦴 🌸 ⚗️ | специализации | `heart`, `brain`, `scissors`, `eye`, `bone`, `flower`, `flask-conical` |
| 🔒 | приватность | `shield-check` |
| 💾 | сохранить | `save` |
| 📋 | список | `clipboard-list` |
| 📈 / 🏆 | графики/топ | `trending-up`, `trophy` |

Для **статус-бэйджей** (`partials/status-badge.php`) эмодзи **полностью убираются**: только цвет + точка-индикатор `<span class="badge__dot">` слева.

---

## Изменения в БД (минимальные)

Файл: `database/migrations.sql`

```sql
ALTER TABLE services        ADD COLUMN image_url VARCHAR(500) NULL AFTER description;
ALTER TABLE specializations ADD COLUMN image_url VARCHAR(500) NULL AFTER name;
-- doctors.photo_url уже существует
```

`database/seeds.php`: проставить пути в созданные сущности (например `/hospital/public/images/services/cardiology.jpg`). **Сами картинки** кладутся в `public/images/{doctors,services,specializations,hero,about}/`. В контроллерах **ничего не меняется** — репозитории уже выполняют `SELECT *`, новые поля автоматически попадут в массив.

В представлениях: показывать `image_url` если непустой, иначе fallback (см. ниже).

### Fallback-стратегия отсутствующих изображений

- Доктор без `photo_url` → SVG-аватар с инициалами на градиентном фоне (текущая логика, переоформить).
- Услуга/специализация без `image_url` → нейтральный SVG-паттерн + название.
- Hero/about — статичные изображения в `public/images/`, никакой БД-зависимости.

### Стандарты изображений

| Сущность | Размер исходника | CSS aspect-ratio | object-fit |
|---|---|---|---|
| Hero | 1600×900 (.webp + .jpg) | `16/9` | `cover` |
| Доктор (карточка) | 800×600 | `4/3` | `cover` (фокус сверху, `object-position: center 20%`) |
| Доктор (профиль) | 800×800 | `1/1` | `cover` |
| Услуга | 1200×675 | `16/9` | `cover` |
| Специализация (плитка) | 800×600 | `4/3` | `cover` |
| Статья (превью) | 1200×675 | `16/9` | `cover` |

Универсальный класс `.media`:

```css
.media { display:block; width:100%; aspect-ratio: var(--media-ratio, 16/9);
  border-radius: var(--radius-lg); overflow:hidden; background:var(--color-surface-2); }
.media__img { width:100%; height:100%; object-fit:cover; display:block;
  transition: transform var(--dur-base) var(--ease); }
.card:hover .media__img { transform: scale(1.03); }
```

`<img>` всегда с `loading="lazy"`, `decoding="async"`, осмысленным `alt`.

---

## CSS-архитектура

`public/css/main.css` переписывается с нуля. Один файл, организован секциями:

```
/* 1. Tokens (CSS variables) */
/* 2. Reset & base (html, body, headings, links, focus-visible) */
/* 3. Layout primitives (.container, .stack, .grid, .row, .cluster) */
/* 4. Typography utilities (.text-*, .heading-*) */
/* 5. Components
     5.1 Button
     5.2 Card / Doctor / Service / Spec / Article cards
     5.3 Form (input, select, textarea, checkbox, radio)
     5.4 Table
     5.5 Badge / Alert / Toast
     5.6 Navbar / Footer
     5.7 Hero / Section header
     5.8 Stepper / Date pill / Time slot
     5.9 Pagination / Tabs / Breadcrumbs
     5.10 Modal (CSS-only через <dialog>)
     5.11 Empty state / Skeleton
     5.12 Stat card (admin)
     5.13 Media wrapper
     5.14 Icon
*/
/* 6. Utilities (.mt-*, .gap-*, .visually-hidden, .text-truncate, .sr-only) */
/* 7. Pages overrides (минимум — только то, что не вынести в компоненты) */
/* 8. Print (для /patient/visit/{id}/print) */
/* 9. Responsive overrides (mobile-first @media min-width) */
```

Новый файл — `public/css/main.css`. Старый сохраняется во время миграции рядом как `public/css/main.legacy.css` и **подгружается только** на тех страницах, что ещё не переведены (через переменную в layout).

### Конвенция нейминга (BEM, lower-kebab)

```
.block
.block__element
.block--modifier
.block__element--modifier
```

Примеры: `.doctor-card`, `.doctor-card__media`, `.doctor-card__title`, `.doctor-card--row`, `.btn--primary`.

Утилиты — отдельным префиксом: `.u-mt-4`, `.u-text-center`, `.u-visually-hidden`.

### Стратегия выноса inline-стилей

Каждый inline `style="..."` заменяется одним из:
1. **Готовый компонентный класс** (большая часть): `.doctor-card`, `.form__group`, `.stack`, `.cluster`, `.grid-3` и т. д.
2. **Утилита**: `.u-mt-4`, `.u-gap-3`, `.u-text-muted`.
3. **Локальный модификатор**: если конструкция действительно уникальна для одной view — добавить страничный класс `.page-admin-appointments` и описать правила в секции 7.

`<style>`-блоки в `views/public/article.php` и `views/patient/print_visit.php` переезжают: статьи — в секцию 7 `main.css` (или новый `article.css`), print — в секцию 8 `main.css` под `@media print`.

---

## Семантическая HTML-разметка

Вводится во всех layout-файлах и страничных view:

```html
<body>
  <a class="u-skip-link" href="#main">Перейти к содержимому</a>
  <header class="navbar" role="banner"> ... </header>
  <main id="main"> ... </main>
  <footer class="site-footer" role="contentinfo"> ... </footer>
</body>
```

- Большие блоки страниц → `<section aria-labelledby="...">` с `<h2 id="...">`.
- Карточки врачей/статей → `<article>`.
- Боковые блоки (например, фильтры в админке) → `<aside>`.
- Таблицы: `<caption class="u-visually-hidden">`, `<th scope="col">`, `<th scope="row">`.
- Формы: `<fieldset>`/`<legend>` для группировок (например, шаги бронирования).
- Все интерактивные элементы — нативные `<button>` / `<a>` (не `<div onclick>`).
- ARIA: `aria-current="page"` для активного пункта навбара, `aria-live="polite"` для flash, `aria-expanded` для accordion FAQ.

---

## Critical-файлы для модификации

### Layout (этапы 1–2)
- `views/layout/public_header.php` — навбар, подключение шрифтов и нового CSS, skip-link, `<main>` с id
- `views/layout/public_footer.php` — добавить `<footer class="site-footer">` (сейчас отсутствует)
- `views/layout/header.php`, `views/layout/footer.php` — обёртка для auth-страниц
- `public/css/main.css` — полностью новый
- `views/partials/icon.php` — **новый** (inline SVG icon set)

### Партиалы (этап 2)
- `views/partials/doctor-card.php` — новая разметка с `.doctor-card`, `<img>` или fallback-аватар
- `views/partials/appointment-row.php` — иконки SVG вместо 📅/🕐, бэйдж без эмодзи
- `views/partials/flash.php` — иконки SVG, классы `.alert--*`
- `views/partials/status-badge.php` — точечный индикатор + текст
- `views/partials/empty-state.php` — SVG-иллюстрация, классы `.empty-state__*`

### Public-страницы (этап 3)
- `views/public/home.php` — hero, фичи, специализации, карточки врачей, отзывы, статьи
- `views/public/doctors.php` — сетка `.doctor-card`
- `views/public/doctor.php` — герой-блок врача с фото, табы (Об опыте / Расписание / Отзывы)
- `views/public/services.php` — сетка `.service-card`
- `views/public/contact.php`, `about.php`, `faq.php`, `articles.php`, `article.php`

### Auth (этап 3)
- `views/auth/login.php`, `views/auth/register.php` — центрированная карточка, brand-блок слева (опц.)

### Patient (этап 4)
- `views/patient/dashboard.php` — stat-cards, ближайшая запись, быстрые действия
- `views/patient/book.php` + `book_analysis.php` — `.stepper`, `.spec-card`, `.doctor-card`, `.date-pill`, `.time-slot`
- `views/patient/appointments.php` — списком из `.appointment-row` + фильтры
- `views/patient/medical_record.php` — таймлайн визитов
- `views/patient/profile.php` — табы + `.form`
- `views/patient/reviews.php` — карточка с рейтингом-звёздами
- `views/patient/print_visit.php` — print-стили в `main.css` секция 8

### Doctor (этап 5)
- `views/doctor/dashboard.php` — расписание дня, карточки приёмов
- `views/doctor/appointment.php` — две колонки (пациент/протокол), табличные блоки → `.info-table`, форма назначений → `.form` + `.table`
- `views/doctor/profile.php` — фото 1:1, табы

### Admin (этап 6)
- `views/admin/dashboard.php` — `.stat-card` сетка, графики (Chart.js уже в проекте — не трогаем), 6-tile навигация
- `views/admin/appointments.php` — фильтр-бар + sticky-таблица + `.pagination`
- `views/admin/doctors.php`, `services.php`, `reviews.php`, `schedule.php`, `lab_tests.php`

### БД (этап 7, опционально)
- `database/migrations.sql` — два `ALTER TABLE`
- `database/seeds.php` — пути к изображениям

### Изображения (этап 7)
- `public/images/hero/`, `doctors/`, `services/`, `specializations/`, `about/` — статичные ассеты
- `public/icons/` — если решим не инлайнить, а подгружать (но по умолчанию inline в `icon.php`)

---

## Поэтапный план миграции (минимизация риска)

Каждый этап атомарен: после него сайт работает на старой и новой системах одновременно. Откатить можно одним коммитом.

### Этап 0. Подготовка (без изменений вёрстки)
1. Создать `public/css/main.css.new` рядом со старым.
2. Прописать в нём только секции 1–2 (токены, reset, базовая типографика).
3. В `views/layout/public_header.php` подключить **оба**: `main.css` (легаси) **и** `main.css.new`. Новый идёт **после** — переопределит только то, что в нём явно описано.
4. Подключить шрифты Google Fonts (`Inter`, `Manrope`) с `display=swap`.
5. Создать `views/partials/icon.php` с минимальным набором (10–15 иконок: calendar, clock, user, stethoscope, check, x, star, alert-triangle, info, chevron-down/right, search, phone, mail, map-pin).
6. **Проверка:** сайт визуально не меняется (или меняется только шрифт/общий фон).

### Этап 1. Layout, навбар, футер
1. Переписать `views/layout/public_header.php`: семантика (`<header><nav>`), skip-link, новый класс `.navbar`. Эмодзи 🏥 → SVG-логотип/иконка.
2. Добавить полноценный `<footer class="site-footer">` в `public_footer.php` (контакты, ссылки, копирайт).
3. Описать в `main.css.new` блоки `.navbar`, `.site-footer`, `.container`, `.main`.
4. Бургер-меню для < 768px (CSS-only через `<input type="checkbox">` или `<details>`).
5. **Проверка:** обойти все публичные страницы — навбар/футер выглядят одинаково и работают.

### Этап 2. Базовые компоненты + партиалы
1. Описать в CSS: `.btn`, `.card`, `.form`, `.alert`, `.badge`, `.table`, `.empty-state`, `.media`.
2. Переписать партиалы: `flash.php`, `status-badge.php`, `empty-state.php`, `appointment-row.php`, `doctor-card.php`. Эмодзи в них — выкинуть, заменить на SVG.
3. **Проверка:** во всех страницах, где они используются, новые партиалы корректно отображаются.

### Этап 3. Public-страницы + auth
1. По одной переводить `home.php` → `doctors.php` → `doctor.php` → `services.php` → `about.php` → `faq.php` → `contact.php` → `articles.php` → `article.php`.
2. Удалять inline-стили в каждой странице, заменяя на классы.
3. Удалить `<style>`-блок из `article.php` (правила переехали в `main.css`).
4. Перевести `auth/login.php`, `register.php`.
5. **Проверка:** клиент-флоу (регистрация → логин → главная → врачи → конкретный врач → услуги → контакты).

### Этап 4. Patient-кабинет
1. `dashboard.php` → `book.php` → `book_analysis.php` → `appointments.php` → `medical_record.php` → `profile.php` → `reviews.php`.
2. Переписать `print_visit.php`, перенести `<style>` в `main.css` (`@media print`).
3. **Проверка:** полный пользовательский путь — записаться, посмотреть запись, отменить, посмотреть мед.карту, оставить отзыв, распечатать визит.

### Этап 5. Doctor-кабинет
1. `dashboard.php` → `appointment.php` (наиболее тяжёлый — много inline) → `profile.php`.
2. **Проверка:** врач — стартует приём, заполняет протокол, добавляет/удаляет назначения, завершает.

### Этап 6. Admin-кабинет
1. `dashboard.php` (графики оставить как есть, обернуть в `.stat-card` и `.card`) → `appointments.php` → `doctors.php` → `schedule.php` → `services.php` → `reviews.php` → `lab_tests.php`.
2. Sticky `<thead>`, фильтр-бары, пагинация — общие классы.
3. **Проверка:** все CRUD-операции, экспорт CSV, модерация отзывов.

### Этап 7. Изображения и БД
1. Применить `ALTER TABLE` к `services` и `specializations`.
2. Дополнить `database/seeds.php` путями.
3. Подложить картинки в `public/images/` (минимум — hero + 2–3 услуги + 2–3 специализации + фото врачей из seed).
4. В картах (`service-card`, `spec-card`, `doctor-card`) включить вывод `image_url` с fallback.

### Этап 8. Финал
1. Удалить `public/css/main.css` (легаси), переименовать `main.css.new` → `main.css`.
2. Убрать двойное подключение в layout.
3. Прогнать поиск по `style="` — должно остаться 0 совпадений (или 2–3 точечных, обоснованных).
4. Прогнать поиск по эмодзи (юникодные диапазоны) — 0 совпадений в `views/`.
5. Проверить Lighthouse: a11y > 90, best-practices > 90.

---

## План верификации

### Ручная проверка (после каждого этапа)
- XAMPP запущен, открыть `http://localhost/hospital/public`.
- Логиниться под тремя ролями (`admin@hospital.local`, `doctor@hospital.local`, `patient@hospital.local` — пароль `password123`, см. `seeds.php`).
- Пройти основные сценарии (см. этапы 3–6).
- Проверить адаптив: DevTools → 360px / 480px / 768px / 1024px / 1440px.

### Автоматическая проверка
- `php -l` по всем изменённым view-файлам (синтаксис).
- Поиск регрессий: `Grep style=" views/` после каждого этапа — счётчик должен монотонно убывать.
- Lighthouse в Chrome DevTools на 3 страницах (home, /doctors, /admin/dashboard).
- W3C HTML validator на тех же 3 страницах.

### A11y-чеклист
- Tab-навигация проходит по всем интерактивным элементам в логичном порядке.
- `:focus-visible` ринг виден на каждом focusable-элементе.
- Контрастность текста ≥ 4.5:1 (проверить через DevTools).
- Все `<img>` имеют `alt`; декоративные — `alt=""`.
- Формы: каждый `<input>` связан с `<label>` (через `for` или вложение).
- Live-region для flash-сообщений.

### Безопасность (без изменений, проверка)
- Все output по-прежнему через `View::e()`.
- CSRF-токены в формах не тронуты.
- Никаких `eval`/`<?= $_GET[...] ?>` не появилось.

---

## Что **не** делается в рамках этого плана

- Не меняются `src/Controllers/`, `src/Services/`, `src/Repositories/`, `src/Core/`, `public/index.php`, `src/Middleware/`.
- Не вводятся i18n-фреймворки, новые языки, переключатели.
- Не переписывается `public/js/app.js` (только если в шаге адаптива потребуется бургер-меню — добавляется минимальный обработчик; CSS-only-вариант предпочтительнее).
- Не меняется логика бронирования, протокола, модерации отзывов, расчёта рейтингов.
- Не вводятся сборщики (Vite/Webpack) — всё работает «как есть» на XAMPP.
- Не добавляются внешние UI-фреймворки (Bootstrap, Tailwind, Bulma).

---

## Оценка объёма работ

| Этап | Файлов затрагивается | Сложность |
|---|---|---|
| 0. Токены + шрифты + icon partial | 3 | низкая |
| 1. Layout + навбар + футер | 4 | средняя |
| 2. Базовые компоненты + партиалы | 5 (CSS) + 5 (partials) | средняя |
| 3. Public + auth | 11 | средняя |
| 4. Patient | 8 | средняя–высокая |
| 5. Doctor | 3 (но `appointment.php` — большой) | высокая |
| 6. Admin | 7 | высокая |
| 7. Изображения + БД | 2 SQL + ~20 ассетов | низкая |
| 8. Финал-чистка | 2 (CSS + layout) | низкая |
| **Итого** | **~45 файлов представлений + 1 CSS + 2 SQL** | |

После каждого этапа можно делать коммит и катить отдельно. План сознательно построен так, что **сайт остаётся рабочим после любого этапа**.
