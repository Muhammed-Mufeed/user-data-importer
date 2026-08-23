<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    public function createTable(): bool;

    /**
     * @param array<User> $users
     */
    public function insertUsersBatch(array $users): int;

    public function emailExists(string $email): bool;
}
