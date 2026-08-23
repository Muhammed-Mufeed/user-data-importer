<?php

declare(strict_types=1);

use Dotenv\Dotenv;

$envPath = dirname(__DIR__);
if (file_exists($envPath . '/.env')) {
    $dotenv = Dotenv::createImmutable($envPath);
    $dotenv->safeLoad();
}

return [
    'host'     => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost',
    'port'     => (int) ($_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: 5432),
    'database' => $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'user_importer',
    'username' => $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'postgres',
    'password' => $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '',
];
