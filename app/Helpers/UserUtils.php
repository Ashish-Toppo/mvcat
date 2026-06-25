<?php

namespace App\Helpers;

class UserUtils {

    public static function firstName(string $fullName): string {
        return explode(' ', trim($fullName))[0] ?? '';
    }

    public static function displayName(array $user): string {
        return $user['name'] ?? $user['email'] ?? 'User';
    }
}
