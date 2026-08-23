<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use PDO;
use Throwable;

final class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function createTable(): bool
    {
        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            surname VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL
        );
        SQL;

        $this->pdo->exec($sql);
        return true;
    }

    public function insertUsersBatch(array $users): int
    {
        if (empty($users)) {
            return 0;
        }

        $sql = <<<SQL
        INSERT INTO users (name, surname, email)
        VALUES (:name, :surname, :email)
        ON CONFLICT (email) DO NOTHING;
        SQL;

        $insertedCount = 0;

        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare($sql);

            foreach ($users as $user) {
                if (!$user instanceof User) {
                    continue;
                }

                $stmt->execute([
                    ':name'    => $user->name,
                    ':surname' => $user->surname,
                    ':email'   => strtolower(trim($user->email)),
                ]);

                if ($stmt->rowCount() > 0) {
                    $insertedCount++;
                }
            }

            $this->pdo->commit();
            return $insertedCount;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function emailExists(string $email): bool
    {
        $sql = 'SELECT 1 FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1;';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':email' => strtolower(trim($email)),
        ]);

        return (bool) $stmt->fetchColumn();
    }
}
