<?php

namespace Core\Helpers;

class File
{
    public static function uniqueName(string $extension): string
    {
        return uniqid('file_', true) . '.' . $extension;
    }

    public static function isAllowedExtension(string $filename, array $allowed): bool
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $allowed);
    }

    public static function upload(array $file, string $targetDir, array $allowedExtensions, int $maxSizeMB = 5)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        if ($file['size'] > $maxSizeMB * 1024 * 1024) {
            return false;
        }

        if (!self::isAllowedExtension($file['name'], $allowedExtensions)) {
            return false;
        }

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newName = self::uniqueName($extension);

        $destination = rtrim($targetDir, '/') . '/' . $newName;

        return move_uploaded_file($file['tmp_name'], $destination) ? $newName : false;
    }
}
