<?php

namespace App\Helpers;

class Str {

    public static function slug(string $text): string {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }

    public static function limit(string $text, int $limit = 100): string {
        return strlen($text) <= $limit ? $text : substr($text, 0, $limit) . '...';
    }

    public static function clean(string $text): string {
        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
