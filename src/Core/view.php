<?php
declare(strict_types=1);

namespace App\Core;

class View
{
    /**
     * Рендерит PHP-шаблон, передавая данные как переменные.
     *
     * Пример: View::render('auth/login', ['error' => 'Неверный пароль'])
     * Ищет файл: views/auth/login.php
     */
    public static function render(string $template, array $data = []): void
    {
        $file = ROOT_PATH . '/views/' . $template . '.php';

        if (!file_exists($file)) {
            die("Шаблон не найден: {$file}");
        }

        // Делаем переменные из массива доступными в шаблоне
        extract($data, EXTR_SKIP);

        require $file;
    }

    /**
     * Экранирование для вывода в HTML — используй везде, где выводишь данные из БД.
     * Пример: <?= View::e($user['full_name']) ?>
     */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    /**
    

     * Инициалы из полного имени.
     * "Иванов Алексей Петрович" → "ИА"
     */
    public static function initials(string $name): string
    {
        $parts = explode(' ', trim($name));
        $ini   = '';
        foreach (array_slice($parts, 0, 2) as $p) {
            $ini .= mb_strtoupper(mb_substr($p, 0, 1));
        }
        return $ini;
    }

    /**
     * Рендер звёзд рейтинга.
     * 4.3 → "★★★★☆"
     */
    public static function stars(float $rating): string
    {
        $full  = (int) round($rating);
        $empty = 5 - $full;
        return str_repeat('★', $full) . str_repeat('☆', $empty);
    }
}