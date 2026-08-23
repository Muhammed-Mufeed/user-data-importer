<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

final class UserValidator
{
    public function normalizeName(string $name): string
    {
        return ucfirst(strtolower(trim($name)));
    }

    public function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    public function isValidEmail(string $email): bool
    {
        $email = trim($email);

        if ($email === '' || str_contains($email, ' ')) {
            return false;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @param array{name: string, surname: string, email: string, _line?: int} $row
     * @param array<string> $seenEmails
     * @return array{
     *     valid: bool,
     *     user: User|null,
     *     error: string|null,
     *     row: int,
     *     raw: array{name: string, surname: string, email: string}
     * }
     */
    public function validateRow(array $row, int $rowNumber, array &$seenEmails): array
    {
        $rawName    = $row['name'] ?? '';
        $rawSurname = $row['surname'] ?? '';
        $rawEmail   = $row['email'] ?? '';

        $raw = [
            'name'    => $rawName,
            'surname' => $rawSurname,
            'email'   => $rawEmail,
        ];

        if (trim($rawName) === '') {
            return [
                'valid' => false,
                'user'  => null,
                'error' => 'Missing or empty name field.',
                'row'   => $rowNumber,
                'raw'   => $raw,
            ];
        }

        if (trim($rawSurname) === '') {
            return [
                'valid' => false,
                'user'  => null,
                'error' => 'Missing or empty surname field.',
                'row'   => $rowNumber,
                'raw'   => $raw,
            ];
        }

        if (trim($rawEmail) === '') {
            return [
                'valid' => false,
                'user'  => null,
                'error' => 'Missing or empty email field.',
                'row'   => $rowNumber,
                'raw'   => $raw,
            ];
        }

        $cleanEmail = $this->normalizeEmail($rawEmail);

        if (!$this->isValidEmail($cleanEmail)) {
            return [
                'valid' => false,
                'user'  => null,
                'error' => "Invalid email format: '{$rawEmail}'.",
                'row'   => $rowNumber,
                'raw'   => $raw,
            ];
        }

        if (in_array($cleanEmail, $seenEmails, true)) {
            return [
                'valid' => false,
                'user'  => null,
                'error' => "Duplicate email in CSV batch: '{$cleanEmail}'.",
                'row'   => $rowNumber,
                'raw'   => $raw,
            ];
        }

        $seenEmails[] = $cleanEmail;

        $user = new User(
            name: $this->normalizeName($rawName),
            surname: $this->normalizeName($rawSurname),
            email: $cleanEmail
        );

        return [
            'valid' => true,
            'user'  => $user,
            'error' => null,
            'row'   => $rowNumber,
            'raw'   => $raw,
        ];
    }
}
