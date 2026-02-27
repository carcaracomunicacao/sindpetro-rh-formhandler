<?php

spl_autoload_register(function ($class) {
    // Converte namespace em caminho
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/src/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return; // não é uma classe do namespace LIT
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

$envPath = __DIR__ . '/.env'; // ajuste conforme seu projeto
$env = [];

if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if (!$line || str_starts_with($line, '#')) continue;

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, "\"'"); // remove aspas se houver

        $env[$key] = $value;
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
} else {
    die(".env não encontrado em $envPath");
}