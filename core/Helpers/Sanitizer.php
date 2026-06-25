<?php

namespace Core\Helpers;

class Sanitizer
{
    public static function string(?string $value): string
    {
        return trim(filter_var($value, FILTER_SANITIZE_STRING));
    }

    public static function email(?string $value): string
    {
        return trim(filter_var($value, FILTER_SANITIZE_EMAIL));
    }

    public static function cleanHTML(string $html): string
    {
        return strip_tags($html);
    }

    public static function int($value): int
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
