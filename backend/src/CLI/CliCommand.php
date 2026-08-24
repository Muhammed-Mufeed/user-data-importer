<?php

declare(strict_types=1);

namespace App\CLI;

use App\Database\Connection;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Services\UserImporter;
use Throwable;

final class CliCommand
{
    public function __construct(
        private ?UserRepositoryInterface $userRepository = null,
        private ?UserImporter $userImporter = null
    ) {
    }

    /**
     * @param array<int, string> $argv
     * @return int Exit code (0 for success, 1 for failure)
     */
    public function run(array $argv): int
    {
        $args = array_slice($argv, 1);
        $parsed = $this->parseArguments($args);

        if (isset($parsed['error'])) {
            $this->printError($parsed['error']);
            $this->printHelp();
            return 1;
        }

        if (empty($args) || isset($parsed['help'])) {
            $this->printHelp();
            return 0;
        }

        $dryRun = isset($parsed['dry_run']) || isset($parsed['dry-run']);

        // Handle custom database credentials if passed via CLI flags
        $dbOverrides = [];
        if (isset($parsed['u'])) {
            $dbOverrides['username'] = $parsed['u'];
        }
        if (isset($parsed['p'])) {
            $dbOverrides['password'] = $parsed['p'];
        }
        if (isset($parsed['h'])) {
            $dbOverrides['host'] = $parsed['h'];
        }
        if (isset($parsed['d'])) {
            $dbOverrides['database'] = $parsed['d'];
        }

        // Action: --create_table / --create-table
        if (isset($parsed['create_table']) || isset($parsed['create-table'])) {
            try {
                $repo = $this->getOrCreateRepository($dbOverrides);
                $repo->createTable();
                $this->printSuccess("PostgreSQL 'users' table successfully created and verified.");
                return 0;
            } catch (Throwable $e) {
                $this->printError("Failed to create table: " . $e->getMessage());
                return 1;
            }
        }

        // Action: --file
        if (isset($parsed['file'])) {
            $filePath = (string) $parsed['file'];

            if (!file_exists($filePath)) {
                $this->printError("CSV file not found: '{$filePath}'.");
                return 1;
            }

            try {
                if ($this->userImporter === null) {
                    $repo = $this->getOrCreateRepository($dbOverrides, $dryRun);
                    $this->userImporter = new UserImporter($repo);
                }

                echo PHP_EOL . "Parsing and processing CSV: {$filePath}..." . PHP_EOL;

                $result = $this->userImporter->process($filePath, $dryRun);

                $this->renderSummary($result);

                return 0;
            } catch (Throwable $e) {
                $this->printError("Import process failed: " . $e->getMessage());
                return 1;
            }
        }

        $this->printError("No actionable directive provided. Use --file <csv_file> or --create_table.");
        $this->printHelp();
        return 1;
    }

    /**
     * @param array<string, mixed> $dbOverrides
     */
    private function getOrCreateRepository(array $dbOverrides, bool $allowDryRunFallback = false): UserRepositoryInterface
    {
        if ($this->userRepository !== null) {
            return $this->userRepository;
        }

        try {
            $pdo = Connection::get(!empty($dbOverrides) ? $dbOverrides : null);
            $this->userRepository = new UserRepository($pdo);
            return $this->userRepository;
        } catch (Throwable $e) {
            if ($allowDryRunFallback) {
                // In dry-run mode, provide an in-memory null repository if DB is offline
                return new class implements UserRepositoryInterface {
                    public function createTable(): bool { return true; }
                    public function insertUsersBatch(array $users): int { return 0; }
                    public function emailExists(string $email): bool { return false; }
                };
            }
            throw $e;
        }
    }

    /**
     * @param array<int, string> $args
     * @return array<string, mixed>
     */
    private function parseArguments(array $args): array
    {
        $parsed = [];
        $count = count($args);

        for ($i = 0; $i < $count; $i++) {
            $arg = $args[$i];

            if ($arg === '--help') {
                $parsed['help'] = true;
            } elseif ($arg === '--create_table' || $arg === '--create-table') {
                $parsed['create_table'] = true;
            } elseif ($arg === '--dry_run' || $arg === '--dry-run') {
                $parsed['dry_run'] = true;
            } elseif ($arg === '--file') {
                if (!isset($args[$i + 1]) || str_starts_with($args[$i + 1], '-')) {
                    return ['error' => "The '--file' directive requires a filename argument."];
                }
                $parsed['file'] = $args[++$i];
            } elseif (str_starts_with($arg, '--file=')) {
                $parsed['file'] = substr($arg, 7);
            } elseif (in_array($arg, ['-u', '-p', '-h', '-d'], true)) {
                $flag = substr($arg, 1);
                if (!isset($args[$i + 1]) || str_starts_with($args[$i + 1], '-')) {
                    return ['error' => "The '-{$flag}' directive requires a value."];
                }
                $parsed[$flag] = $args[++$i];
            } else {
                return ['error' => "Unknown directive: '{$arg}'."];
            }
        }

        return $parsed;
    }

    /**
     * @param array{
     *     total_rows: int,
     *     valid_count: int,
     *     invalid_count: int,
     *     imported_count: int,
     *     is_dry_run: bool,
     *     records: array<int, array{row: int, status: string, name: string, surname: string, email: string, error: string|null}>
     * } $result
     */
    private function renderSummary(array $result): void
    {
        echo PHP_EOL;
        echo "========================================================" . PHP_EOL;
        echo "                 IMPORT SUMMARY REPORT                  " . PHP_EOL;
        echo "========================================================" . PHP_EOL;
        echo sprintf("  Total Rows Processed : %d", $result['total_rows']) . PHP_EOL;
        echo sprintf("  Valid Records        : %d", $result['valid_count']) . PHP_EOL;
        echo sprintf("  Invalid Records      : %d", $result['invalid_count']) . PHP_EOL;

        if ($result['is_dry_run']) {
            echo "  Mode                 : [DRY RUN] (No DB modifications)" . PHP_EOL;
            echo "  Rows Inserted to DB  : 0" . PHP_EOL;
        } else {
            echo sprintf("  Rows Inserted to DB  : %d", $result['imported_count']) . PHP_EOL;
        }
        echo "========================================================" . PHP_EOL . PHP_EOL;

        if ($result['invalid_count'] > 0) {
            echo "--------------------------------------------------------" . PHP_EOL;
            echo "                 REJECTED ROWS & ERRORS                 " . PHP_EOL;
            echo "--------------------------------------------------------" . PHP_EOL;

            foreach ($result['records'] as $record) {
                if ($record['status'] === 'ERROR') {
                    echo sprintf(
                        "  [Line %d] '%s %s' (%s) -> %s",
                        $record['row'],
                        $record['name'] ?: '<empty>',
                        $record['surname'] ?: '<empty>',
                        $record['email'] ?: '<empty>',
                        $record['error']
                    ) . PHP_EOL;
                }
            }
            echo "--------------------------------------------------------" . PHP_EOL . PHP_EOL;
        }
    }

    private function printHelp(): void
    {
        echo <<<HELP

User Data Importer CLI Tool (user_upload.php)

USAGE:
  php user_upload.php [DIRECTIVES]

DIRECTIVES:
  --file <csv_file>     The name of the CSV file to be parsed and imported.
  --create_table        Builds the PostgreSQL 'users' table and exits.
  --dry_run             Executes parsing and validation without altering database.
  -u <username>         PostgreSQL database username.
  -p <password>         PostgreSQL database password.
  -h <host>             PostgreSQL database host.
  -d <database>         PostgreSQL database name.
  --help                Display directive instructions and exit.

EXAMPLES:
  php user_upload.php --help
  php user_upload.php --create_table
  php user_upload.php --file data/users.csv --dry_run
  php user_upload.php --file data/users.csv
  php user_upload.php --file data/users.csv -h localhost -d user_importer -u postgres -p secret

HELP . PHP_EOL;
    }

    private function printSuccess(string $message): void
    {
        echo PHP_EOL . "[SUCCESS] " . $message . PHP_EOL . PHP_EOL;
    }

    private function printError(string $message): void
    {
        echo PHP_EOL . "[ERROR] " . $message . PHP_EOL . PHP_EOL;
    }
}
