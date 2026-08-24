<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Services\CsvParser;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CsvParserTest extends TestCase
{
    private CsvParser $parser;
    private string $tempFile;

    protected function setUp(): void
    {
        $this->parser = new CsvParser();
        $this->tempFile = sys_get_temp_dir() . '/test_' . uniqid('', true) . '.csv';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    public function testParsesValidCsvFile(): void
    {
        file_put_contents(
            $this->tempFile,
            "name,surname,email\njohn,smith,john.smith@example.com\njane,doe,jane.doe@example.com\n"
        );

        $rows = $this->parser->parse($this->tempFile);

        $this->assertCount(2, $rows);
        $this->assertSame('john', $rows[0]['name']);
        $this->assertSame('smith', $rows[0]['surname']);
        $this->assertSame('john.smith@example.com', $rows[0]['email']);
        $this->assertSame(2, $rows[0]['_line']);

        $this->assertSame('jane', $rows[1]['name']);
        $this->assertSame('doe', $rows[1]['surname']);
        $this->assertSame('jane.doe@example.com', $rows[1]['email']);
        $this->assertSame(3, $rows[1]['_line']);
    }

    public function testThrowsExceptionForNonExistentFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->parser->parse('/path/to/non_existent_file.csv');
    }

    public function testThrowsExceptionWhenRequiredHeadersAreMissing(): void
    {
        file_put_contents($this->tempFile, "name,surname\njohn,smith\n");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("CSV missing required header column: 'email'.");

        $this->parser->parse($this->tempFile);
    }

    public function testSkipsEmptyLines(): void
    {
        file_put_contents(
            $this->tempFile,
            "name,surname,email\njohn,smith,john.smith@example.com\n\n   \n\njane,doe,jane.doe@example.com\n"
        );

        $rows = $this->parser->parse($this->tempFile);

        $this->assertCount(2, $rows);
        $this->assertSame('john.smith@example.com', $rows[0]['email']);
        $this->assertSame('jane.doe@example.com', $rows[1]['email']);
    }
}
