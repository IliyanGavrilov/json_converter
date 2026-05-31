<?php
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        $parts = explode('=', $line, 2);
        $key = trim($parts[0]);
        $val = isset($parts[1]) ? trim($parts[1]) : '';
        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $val;
        }
    }
}

return (object) [
    'DB_SERVERNAME'   => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'DB_USERNAME'     => $_ENV['DB_USER'] ?? 'root',
    'DB_PASSWORD'     => $_ENV['DB_PASS'] ?? '',
    'DB_NAME'         => $_ENV['DB_NAME'] ?? 'json_converter',
    'HTTP_URL_PREFIX' => $_ENV['APP_URL'] ?? 'http://localhost/json_converter',
];
