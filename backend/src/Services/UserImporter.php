<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepositoryInterface;

final class UserImporter
{
    private CsvParser $csvParser;
    private UserValidator $userValidator;

    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        ?CsvParser $csvParser = null,
        ?UserValidator $userValidator = null
    ) {
        $this->csvParser     = $csvParser ?? new CsvParser();
        $this->userValidator = $userValidator ?? new UserValidator();
    }

    /**
     * @return array{
     *     total_rows: int,
     *     valid_count: int,
     *     invalid_count: int,
     *     imported_count: int,
     *     is_dry_run: bool,
     *     records: array<int, array{
     *         row: int,
     *         status: string,
     *         name: string,
     *         surname: string,
     *         email: string,
     *         error: string|null
     *     }>
     * }
     */
    public function process(string $filePath, bool $dryRun = false): array
    {
        $rows = $this->csvParser->parse($filePath);

        $seenEmails   = [];
        $validUsers   = [];
        $records      = [];
        $validCount   = 0;
        $invalidCount = 0;

        foreach ($rows as $index => $row) {
            $lineNum = (int) ($row['_line'] ?? ($index + 2));
            $result  = $this->userValidator->validateRow($row, $lineNum, $seenEmails);

            if ($result['valid'] && $result['user'] instanceof User) {
                $validCount++;
                $validUsers[] = $result['user'];

                $records[] = [
                    'row'     => $lineNum,
                    'status'  => 'VALID',
                    'name'    => $result['user']->name,
                    'surname' => $result['user']->surname,
                    'email'   => $result['user']->email,
                    'error'   => null,
                ];
            } else {
                $invalidCount++;

                $records[] = [
                    'row'     => $lineNum,
                    'status'  => 'ERROR',
                    'name'    => $result['raw']['name'],
                    'surname' => $result['raw']['surname'],
                    'email'   => $result['raw']['email'],
                    'error'   => $result['error'],
                ];
            }
        }

        $importedCount = 0;
        if (!$dryRun && !empty($validUsers)) {
            $importedCount = $this->userRepository->insertUsersBatch($validUsers);
        }

        return [
            'total_rows'     => count($rows),
            'valid_count'    => $validCount,
            'invalid_count'  => $invalidCount,
            'imported_count' => $importedCount,
            'is_dry_run'     => $dryRun,
            'records'        => $records,
        ];
    }
}
