<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use App\Services\UserImporter;
use PHPUnit\Framework\TestCase;

final class UserImporterTest extends TestCase
{
    public function testDryRunDoesNotInsertIntoDatabase(): void
    {
        $insertedUsers = [];

        $mockRepo = new class ($insertedUsers) implements UserRepositoryInterface {
            /** @param array<User> $inserted */
            public function __construct(public array &$inserted)
            {}
            public function createTable(): bool
            {
                return true; }
            public function insertUsersBatch(array $users): int
            {
                $this->inserted = $users;
                return count($users);
            }
            public function emailExists(string $email): bool
            {
                return false; }
        };

        $importer = new UserImporter($mockRepo);
        $tempFile = sys_get_temp_dir() . '/test_import_' . uniqid() . '.csv';

        try {
            file_put_contents(
                $tempFile,
                "name,surname,email\njohn,smith,john.smith@example.com\n"
            );

            $result = $importer->process($tempFile, dryRun: true);

            $this->assertTrue($result['is_dry_run']);
            $this->assertSame(1, $result['valid_count']);
            $this->assertSame(0, $result['imported_count']);
            $this->assertEmpty($insertedUsers, "Database insert was unexpectedly called in dry-run mode.");
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function testImportsOnlyValidRecords(): void
    {
        $insertedUsers = [];

        $mockRepo = new class ($insertedUsers) implements UserRepositoryInterface {
            /** @param array<User> $inserted */
            public function __construct(public array &$inserted)
            {}
            public function createTable(): bool
            {
                return true; }
            public function insertUsersBatch(array $users): int
            {
                $this->inserted = $users;
                return count($users);
            }
            public function emailExists(string $email): bool
            {
                return false; }
        };

        $importer = new UserImporter($mockRepo);
        $tempFile = sys_get_temp_dir() . '/test_valid_' . uniqid() . '.csv';

        try {
            file_put_contents(
                $tempFile,
                "name,surname,email\n" .
                "john,smith,john.smith@example.com\n" .
                "bad,user,invalid-email\n" .
                "jane,doe,jane.doe@example.com\n"
            );

            $result = $importer->process($tempFile, dryRun: false);

            $this->assertFalse($result['is_dry_run']);
            $this->assertSame(3, $result['total_rows']);
            $this->assertSame(2, $result['valid_count']);
            $this->assertSame(1, $result['invalid_count']);
            $this->assertSame(2, $result['imported_count']);

            $this->assertCount(2, $insertedUsers);
            $this->assertSame('John', $insertedUsers[0]->name);
            $this->assertSame('Jane', $insertedUsers[1]->name);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function testAccuratelyProcessesChallengeDataset(): void
    {
        $datasetPath = dirname(__DIR__, 3) . '/data/users.csv';

        if (!file_exists($datasetPath)) {
            $this->markTestSkipped("Challenge dataset file not found at: {$datasetPath}");
        }

        $dummyRepo = new class implements UserRepositoryInterface {
            public function createTable(): bool
            {
                return true;
            }
            public function insertUsersBatch(array $users): int
            {
                return count($users);
            }
            public function emailExists(string $email): bool
            {
                return false;
            }
        };

        $importer = new UserImporter($dummyRepo);
        $result = $importer->process($datasetPath, dryRun: true);

        // Assert exact numbers matching challenge dataset
        $this->assertSame(49, $result['total_rows']);
        $this->assertSame(41, $result['valid_count']);
        $this->assertSame(8, $result['invalid_count']);

        // Assert that exactly 8 records have status ERROR
        $errors = array_filter($result['records'], static fn($r) => $r['status'] === 'ERROR');
        $this->assertCount(8, $errors);
    }
}
