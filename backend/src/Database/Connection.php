<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;
use RuntimeException;

final class Connection
{
    private static ?PDO $instance = null;

    private function __construct()
    {
    }

    public static function get(?array $config = null): PDO
    {
        if (self::$instance !== null && $config === null) {
            return self::$instance;
        }

        if ($config === null) {
            $config = require dirname(__DIR__, 2) . '/config/database.php';
        }

        $host     = (string) ($config['host'] ?? 'localhost');
        $port     = (int) ($config['port'] ?? 5432);
        $database = (string) ($config['database'] ?? 'user_importer');
        $username = (string) ($config['username'] ?? 'postgres');
        $password = (string) ($config['password'] ?? '');

        $dsn = "pgsql:host={$host};port={$port};dbname={$database}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $username, $password, $options);

            if ($config === null) {
                self::$instance = $pdo;
            }

            return $pdo;
        } catch (PDOException $e) {
            throw new RuntimeException(
                "Database connection failed: " . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
