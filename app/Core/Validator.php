<?php
/**
 * Project: QazJumys
 * File: Validator.php
 * Author: Beck Sarbassov
 * Version: 1.1.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Provides small input validation helpers for forms.
 * RU: Предоставляет небольшие помощники проверки данных форм.
 */

declare(strict_types=1);

namespace QazJumys\Core;

final class Validator
{
    /**
     * EN: Trims and limits incoming string input.
     * RU: Обрезает пробелы и ограничивает строковый ввод.
     *
     * @param mixed $value Input value / Входное значение
     * @param int $maxLength Maximum length / Максимальная длина
     * @return string
     */
    public static function text(mixed $value, int $maxLength = 255): string
    {
        $clean = trim((string) $value);
        $clean = preg_replace('/\s+/u', ' ', $clean) ?? '';

        return mb_substr($clean, 0, $maxLength, 'UTF-8');
    }

    /**
     * EN: Validates a money value and returns it as a float.
     * RU: Проверяет денежное значение и возвращает его как float.
     *
     * @param mixed $value Input value / Входное значение
     * @return float|null
     */
    public static function money(mixed $value): ?float
    {
        if (!is_numeric($value)) {
            return null;
        }

        $amount = (float) $value;

        return $amount >= 0 ? round($amount, 2) : null;
    }

    /**
     * EN: Checks whether an email is syntactically valid.
     * RU: Проверяет синтаксическую корректность email.
     *
     * @param string $email Email address / Адрес email
     * @return bool
     */
    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
