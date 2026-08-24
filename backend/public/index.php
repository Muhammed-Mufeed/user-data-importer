<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\Connection;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Services\UserImporter;

// Set global CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Helper for JSON error response
function jsonError(string $message, int $statusCode = 400): void {
    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        'error'   => $message,
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

// Helper for JSON success response
function jsonSuccess(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode(array_merge(['success' => true], $data), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

try {
    // 1. Health Check
    if ($method === 'GET' && ($uri === '/' || $uri === '/api/health')) {
        jsonSuccess(['message' => 'User Data Importer API is active and healthy.']);
    }

    // 2. Create / Verify Table
    if ($method === 'POST' && $uri === '/api/create-table') {
        $pdo = Connection::get();
        $repo = new UserRepository($pdo);
        $repo->createTable();
        jsonSuccess(['message' => "PostgreSQL 'users' table created and verified."]);
    }

    // 3. Validate CSV (Dry-Run Preview)
    if ($method === 'POST' && $uri === '/api/validate') {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            jsonError('No CSV file uploaded or file upload error occurred.');
        }

        $tmpFile = $_FILES['file']['tmp_name'];
        $originalName = (string) $_FILES['file']['name'];

        if (!str_ends_with(strtolower($originalName), '.csv')) {
            jsonError('Invalid file format. Please upload a valid .csv file.');
        }

        // Use dummy repository during validation to avoid requiring live DB for dry-run preview
        $nullRepo = new class implements UserRepositoryInterface {
            public function createTable(): bool { return true; }
            public function insertUsersBatch(array $users): int { return 0; }
            public function emailExists(string $email): bool { return false; }
        };

        $importer = new UserImporter($nullRepo);
        $result   = $importer->process($tmpFile, dryRun: true);

        jsonSuccess([
            'filename'       => $originalName,
            'total_rows'     => $result['total_rows'],
            'valid_count'    => $result['valid_count'],
            'invalid_count'  => $result['invalid_count'],
            'is_dry_run'     => true,
            'records'        => $result['records'],
        ]);
    }

    // 4. Import Valid CSV Users into PostgreSQL
    if ($method === 'POST' && $uri === '/api/import') {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            jsonError('No CSV file uploaded or file upload error occurred.');
        }

        $tmpFile = $_FILES['file']['tmp_name'];
        $originalName = (string) $_FILES['file']['name'];

        if (!str_ends_with(strtolower($originalName), '.csv')) {
            jsonError('Invalid file format. Please upload a valid .csv file.');
        }

        $pdo      = Connection::get();
        $repo     = new UserRepository($pdo);
        $importer = new UserImporter($repo);
        $result   = $importer->process($tmpFile, dryRun: false);

        jsonSuccess([
            'filename'       => $originalName,
            'total_rows'     => $result['total_rows'],
            'valid_count'    => $result['valid_count'],
            'invalid_count'  => $result['invalid_count'],
            'imported_count' => $result['imported_count'],
            'is_dry_run'     => false,
            'records'        => $result['records'],
        ]);
    }

    // 5. Fetch Live Users List
    if ($method === 'GET' && $uri === '/api/users') {
        $pdo = Connection::get();
        $stmt = $pdo->query('SELECT id, name, surname, email FROM users ORDER BY id ASC');
        $users = $stmt->fetchAll();
        jsonSuccess(['users' => $users, 'count' => count($users)]);
    }

    // 404 Fallback
    jsonError("Endpoint '{$method} {$uri}' not found.", 404);

} catch (Throwable $e) {
    jsonError($e->getMessage(), 500);
}
