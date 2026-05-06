<?php
declare(strict_types=1);

namespace App\Core;

class Validator
{
    public static function email(string $value): bool
    {
        return (bool)filter_var(trim($value), FILTER_VALIDATE_EMAIL);
    }

    public static function password(string $value): bool
    {
        return strlen($value) >= 8;
    }

    public static function phone(string $value): bool
    {
        $clean = preg_replace('/[\s\-\(\)]/', '', $value);
        return $clean === '' || (bool)preg_match('/^\+?[0-9]{7,15}$/', $clean);
    }

    public static function dateInFuture(string $value): bool
    {
        if ($value === '' || !strtotime($value)) {
            return false;
        }
        return strtotime($value) > time();
    }

    public static function dateInPast(string $value): bool
    {
        if ($value === '' || !strtotime($value)) {
            return false;
        }
        return strtotime($value) < time();
    }

    public static function time(string $value): bool
    {
        return (bool)preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value);
    }

    public static function nonEmpty(string $value, int $minLen = 1): bool
    {
        return mb_strlen(trim($value)) >= $minLen;
    }
}
