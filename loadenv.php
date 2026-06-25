<?php

function loadEnv(string $file): void
{
    if (!file_exists($file)) return;

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Remove inline comments
        $line = preg_replace('/\s*#.*$/', '', $line);
        if (empty($line)) continue;

        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // Remove quotes
        $value = trim($value, "'\"");

        $_ENV[$name] = $value;
        putenv("$name=$value"); // optional
    }
}
