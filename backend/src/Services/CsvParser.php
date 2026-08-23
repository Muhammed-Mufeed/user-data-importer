<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;

final class CsvParser
{
    /**
     * @return array<int, array{name: string, surname: string, email: string}>
     */
    public function parse(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("CSV file not found: {$filePath}");
        }

        if (!is_readable($filePath)) {
            throw new RuntimeException("CSV file is not readable: {$filePath}");
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new RuntimeException("Unable to open CSV file: {$filePath}");
        }

        try {
            // Strip potential UTF-8 Byte Order Mark (BOM)
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $header = fgetcsv($handle);
            if ($header === false || empty(array_filter($header))) {
                throw new InvalidArgumentException("CSV file is empty or missing headers.");
            }

            $normalizedHeader = array_map(
                static fn(mixed $col): string => strtolower(trim((string) $col)),
                $header
            );

            $requiredColumns = ['name', 'surname', 'email'];
            foreach ($requiredColumns as $col) {
                if (!in_array($col, $normalizedHeader, true)) {
                    throw new InvalidArgumentException("CSV missing required header column: '{$col}'.");
                }
            }

            $nameIdx    = (int) array_search('name', $normalizedHeader, true);
            $surnameIdx = (int) array_search('surname', $normalizedHeader, true);
            $emailIdx   = (int) array_search('email', $normalizedHeader, true);

            $rows = [];
            $rowNum = 1;

            while (($data = fgetcsv($handle)) !== false) {
                $rowNum++;

                // Skip completely empty lines
                if ($data === [null] || empty(array_filter($data, static fn($v) => trim((string) $v) !== ''))) {
                    continue;
                }

                $rows[] = [
                    'name'    => (string) ($data[$nameIdx] ?? ''),
                    'surname' => (string) ($data[$surnameIdx] ?? ''),
                    'email'   => (string) ($data[$emailIdx] ?? ''),
                    '_line'   => $rowNum,
                ];
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }
}
