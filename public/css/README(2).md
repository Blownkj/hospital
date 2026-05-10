# Handoff: Hospital IS — Visual Refresh (v2)

Цель: после этого пакета сайт выглядит **точно как макеты в `Redesigned Screens.html`**: широкая раскладка 1280px, hero с боковой панелью записи, нумерованный список направлений, KPI-строка, убраны цветные плашки и большие буквы.

CSS уже подменён. Этого недостаточно — нужны **точечные правки шаблонов**. Ниже — конкретные блоки HTML, которые нужно вставить.

---

## Файл, который читает Claude Code

Открой Claude Code, добавь репозиторий и оба файла в контекст:

- `handoff/main.css` — уже актуальный, ничего не редактируй.
- `handoff/README.md` (этот файл) — твоя инструкция.
- `Redesigned Screens.html` (если можешь приложить из проекта) — эталон визуала.

**Промпт Claude Code:**
> Следуй `handoff/README.md` шаг за шагом. Меняй только то, что указано. После каждого раздела показывай diff и жди подтверждения. Не добавляй классы, которых нет в `main.css`.

---

## 0. Что уже сделано

В `handoff/main.css`:
- `--max-width: 1280px` — контент шире, чем был
- добавлены классы для v2-главной: `.public-hero`, `.public-hero__panel`, `.public-meta-row`, `.public-meta`, `.spec-list`, `.spec-item`, `.public-section`
- все старые классы (`.stats-grid`, `.spec-grid`, `.feature-card`) сохранены и стилизованы под новый ink-only вид

## 1. Подключить шрифты

В `<head>` базового шаблона (тот, который extend'ят все страницы), **выше** `main.css`:

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700&family=Instrument+Serif:ital@0;1&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
```

## 2. Container — расширить контентную область

Найди в шаблонах все обёртки `<main>` или `<div class="container">`. Если у тебя в `base.html` (или аналоге) контент завёрнут так:

```html
<main>
  {% block content %}{% endblock %}
</main>
```

— ничего не меняй. Новый CSS уже задаёт `max-width: 1280px` и горизонтальные отступы. Если контент **по-прежнему смотрится узко**, скорее всего у тебя есть свой собственный `.container` или Bootstrap. Тогда:

- **Bootstrap:** замени `class="container"` на `class="container-xl"` или `class="container-fluid"` в `base.html`.
- **Своя обёртка:** найди в твоём CSS правило вроде `.container { max-width: 1140px }` и удали его — в `main.css` уже есть `.container { max-width: var(--max-width) }`.

## 3. Главная страница — критическое переустройство

Сейчас у тебя на главной:
- маленький тёмный hero без боковой панели
- статистика тремя отдельными карточками
- огромные буквенные плашки направлений (АТ, БК, БЕ, ВЛ)

Нужно превратить в раскладку из v2.

### 3.1. Hero — заменить целиком

Найди шаблон главной (вероятно `templates/home.html` или `templates/index.html`). Найди блок hero — что-то вроде:

```html
<section class="hero">
  <h1>Ваше здоровье — наш приоритет</h1>
  <p>...</p>
  <div class="hero__actions">...</div>
</section>
```

**Замени** на:

```html
<section class="public-hero">
  <div>
    <div class="public-hero__eyebrow">
      <span class="dot"></span>Принимаем · 9 врачей сейчас
    </div>
    <h1 class="public-hero__title">
      Здоровье — это <em>системная работа,</em><br>
      а не разовый визит.
    </h1>
    <p class="public-hero__lead">
      Многопрофильная клиника: 32 специалиста, собственная лаборатория,
      прозрачные цены, запись онлайн без звонка.
    </p>
    <div class="public-hero__cta">
      <a href="{% url 'booking' %}" class="btn btn--primary btn--lg">Записаться на приём →</a>
      <a href="{% url 'doctors' %}" class="btn btn--secondary btn--lg">Все врачи</a>
    </div>
  </div>

  <aside class="public-hero__panel">
    <h4>Запись онлайн</h4>
    <form class="public-hero__quick" action="{% url 'booking' %}" method="get">
      <div>
        <label>Направление</label>
        <select name="spec">
          <option value="">Выберите специалиста…</option>
          {% for s in specializations %}
            <option value="{{ s.id }}">{{ s.name }}</option>
          {% endfor %}
        </select>
      </div>
      <div>
        <label>Дата</label>
        <input type="date" name="date">
      </div>
      <div>
        <label>Время</label>
        <select name="time">
          <option value="">Любое</option>
          <option value="morning">Утро (9–12)</option>
          <option value="day">День (12–16)</option>
          <option value="evening">Вечер (16–20)</option>
        </select>
      </div>
      <button type="submit" class="btn btn--primary btn--block">Найти слот</button>
    </form>
  </aside>
</section>
```

**Замечания:**
- `{% url ... %}` и `{% for ... %}` — Django-синтаксис. Если у тебя Flask/Jinja, замени на свой эквивалент. Если нет переменной `specializations` в контексте — захардкодь 5–6 опций для первого подхода, потом пробросишь из view.
- Если у тебя **не Django**, скажи Claude Code: «использую Flask с Jinja2, замени `{% url 'booking' %}` на `{{ url_for('booking') }}`».

### 3.2. Статистика — заменить разметку

Найди блок «28 пациентов / 32 специалистов / 113 отзывов». Сейчас он выглядит как:

```html
<div class="stats">
  <div class="stat-box">
    <div class="num">28</div>
    <div class="label">Пациентов</div>
  </div>
  ...
</div>
```

(или похоже). **Замени на:**

```html
<div class="public-meta-row">
  <div class="public-meta">
    <div class="public-meta__label">Специалистов</div>
    <div class="public-meta__value">{{ doctors_count }}</div>
  </div>
  <div class="public-meta">
    <div class="public-meta__label">Направлений</div>
    <div class="public-meta__value">{{ specs_count }}</div>
  </div>
  <div class="public-meta">
    <div class="public-meta__label">Пациентов · 2025</div>
    <div class="public-meta__value">{{ patients_count }}</div>
  </div>
  <div class="public-meta">
    <div class="public-meta__label">Средний рейтинг</div>
    <div class="public-meta__value">
      {{ avg_rating }}
      <span style="font-family:var(--font-mono); font-size:13px; color:var(--color-text-subtle)">· {{ reviews_count }} отз.</span>
    </div>
  </div>
</div>
```

Важно: значения сверху не должны иметь иконок и круглых плашек — только число + лейбл.

### 3.3. Список направлений — заменить разметку

Найди блок «Наши специалисты» с большими буквенными аватарами (АТ, БК, БЕ, ВЛ). Сейчас он выглядит примерно так:

```html
<div class="spec-grid">
  {% for spec in specializations %}
    <a class="spec-card" href="...">
      <div class="spec-card__avatar">{{ spec.name|slice:":2"|upper }}</div>
      <div class="spec-card__name">{{ spec.name }}</div>
    </a>
  {% endfor %}
</div>
```

**Замени на:**

```html
<section class="public-section">
  <div class="public-section__head">
    <h2 class="public-section__title">
      Направления, <em>в которых мы работаем</em>
    </h2>
    <a class="public-section__more" href="{% url 'specializations' %}">Все направления →</a>
  </div>

  <div class="spec-list">
    {% for spec in specializations %}
      <a class="spec-item" href="{% url 'doctors' %}?spec={{ spec.id }}">
        <div class="spec-item__num">{{ forloop.counter|stringformat:"02d" }}</div>
        <div class="spec-item__name">{{ spec.name }}</div>
        <div class="spec-item__doctors">
          {% if spec.doctors_count %}{{ spec.doctors_count }} врач(а/ей){% else %}скоро{% endif %}
        </div>
      </a>
    {% endfor %}
  </div>
</section>
```

**Что важно:**
- Никаких `<div class="spec-card__avatar">` — буквенные плашки уйдут полностью.
- Номер `01, 02, 03` — это `forloop.counter` с форматом `02d`. В Jinja: `{{ "%02d"|format(loop.index) }}`.
- На каждое направление нужно поле `doctors_count` (количество врачей по этой специальности). Если такого поля нет — попроси Claude Code добавить аннотацию во view: `Specialization.objects.annotate(doctors_count=Count('doctors'))`.

## 4. Дашборд врача / пациента

На скриншоте дашборд уже выглядит близко к v2 — KPI-строка корректная, hairline-разделители, серифный заголовок. Если хочешь подтянуть на 100%:

- Убедись, что блок «Приёмы сегодня» и «Последние приёмы» завёрнут в `<div class="card">…</div>` — это даст рамку 1px и скруглённые углы.
- Кнопки «Открыть» переведи на `class="btn btn--secondary btn--sm"`, чтобы они были компактнее.
- Зелёный бейдж «Завершён» уже стилизован — оставь как есть (`class="badge badge--success"`).

## 5. Навбар — чёрная кнопка «Личный кабинет»

В скриншоте всё уже правильно — кнопка чёрная, ссылки серые, активная подчёркнута. Если у тебя ссылка «Личный кабинет» имеет класс `btn btn--primary`, замени на `navbar__cta` — она сделана в нужный размер:

```html
<a href="{% url 'cabinet' %}" class="navbar__cta">Личный кабинет</a>
```

## 6. Чек-лист — что должно получиться

После применения шагов 1–5, открой главную и сравни:

| Элемент | До | После |
|---|---|---|
| Контент | ширина ~1140px, узко | ширина 1280px, шире |
| Hero | тёмная плашка без панели | две колонки: текст + панель «Запись онлайн» |
| Статистика | 3 отдельных карточки | один прямоугольник с тонкими разделителями |
| Заголовок «Наши специалисты» | без курсива | «Направления, *в которых мы работаем*» с курсивным хвостом |
| Направления | большие буквы АТ, БК, БЕ | строки с номерами 01, 02, 03 + название серифом + кол-во врачей |

## 7. Если что-то сломалось

- **Hero сжался в одну колонку на десктопе** → у тебя `<aside>` либо вне `.public-hero`, либо родитель имеет `display: flex; flex-direction: column`. Проверь, что разметка точно как в 3.1.
- **`{% url %}` ругается** → у тебя нет такого route. Замени на `#` для теста, потом подключишь к реальному.
- **Числа в статистике обрезаются** → проверь, что значение не обёрнуто в `<h2>` или `<strong>` — `.public-meta__value` сам задаёт размер.

## 8. Откат

Если что-то пошло не так:
```bash
git checkout -- templates/home.html
git checkout -- static/css/main.css
```

Шаблоны меняются find-and-replace'ом, всё ревёрсится одной командой.

---

**Главное:** Главная страница — это самая большая разница между текущей версией и v2. Дашборд уже почти на месте. Начни с шага 3 для главной, и сайт сразу будет выглядеть как макеты.
