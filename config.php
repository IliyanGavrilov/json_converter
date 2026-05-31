<?php
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        [$key, $val] = array_map('trim', explode('=', $line, 2));
        if (!array_key_exists($key, $_ENV)) {
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
