<?php

namespace App\Helpers;

class Validator {

    public static function email(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function minLen(string $value, int $len): bool {
        return strlen($value) >= $len;
    }

    public static function alphaNum(string $value): bool {
        return preg_match('/^[A-Za-z0-9]+$/', $value);
    }

    public static function safeId(string $value): bool {
        return preg_match('/^[A-Za-z0-9\-_.]{3,50}$/', $value);
    }
}
