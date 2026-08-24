<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Services\UserValidator;
use PHPUnit\Framework\TestCase;

final class UserValidatorTest extends TestCase
{
    private UserValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new UserValidator();
    }

    public function testCapitalizesNamesProperly(): void
    {
        $this->assertSame('John', $this->validator->normalizeName('john'));
        $this->assertSame('Jane', $this->validator->normalizeName('JANE'));
        $this->assertSame('Sarah', $this->validator->normalizeName('  sarah  '));
        $this->assertSame('David', $this->validator->normalizeName('dAvId'));
    }

    public function testLowercasesEmails(): void
    {
        $this->assertSame(
            'john.smith@example.com',
            $this->validator->normalizeEmail('JOHN.SMITH@EXAMPLE.COM')
        );
        $this->assertSame(
            'jane.doe@example.com',
            $this->validator->normalizeEmail('  Jane.Doe@Example.Com  ')
        );
    }

    public function testRejectsMissingFields(): void
    {
        $seen = [];

        // Missing Name
        $res1 = $this->validator->validateRow(['name' => '', 'surname' => 'Smith', 'email' => 'a@b.com'], 2, $seen);
        $this->assertFalse($res1['valid']);
        $this->assertSame('Missing or empty name field.', $res1['error']);

        // Missing Surname
        $res2 = $this->validator->validateRow(['name' => 'John', 'surname' => '', 'email' => 'a@b.com'], 3, $seen);
        $this->assertFalse($res2['valid']);
        $this->assertSame('Missing or empty surname field.', $res2['error']);

        // Missing Email
        $res3 = $this->validator->validateRow(['name' => 'John', 'surname' => 'Smith', 'email' => ''], 4, $seen);
        $this->assertFalse($res3['valid']);
        $this->assertSame('Missing or empty email field.', $res3['error']);
    }

    public function testRejectsInvalidEmailFormats(): void
    {
        $seen = [];
        $invalidEmails = [
            'invalid-email',
            'missing@',
            '@nodomain.com',
            'bad@@example.com',
            'spaces inside@example.com',
        ];

        foreach ($invalidEmails as $idx => $email) {
            $res = $this->validator->validateRow(
                ['name' => 'John', 'surname' => 'Smith', 'email' => $email],
                $idx + 2,
                $seen
            );
            $this->assertFalse($res['valid'], "Expected '{$email}' to be rejected as invalid.");
            $this->assertStringContainsString("Invalid email format", $res['error'] ?? '');
        }
    }

    public function testRejectsDuplicateEmailsInBatch(): void
    {
        $seen = [];

        // First occurrence -> VALID
        $res1 = $this->validator->validateRow(
            ['name' => 'John', 'surname' => 'Smith', 'email' => 'john.smith@example.com'],
            2,
            $seen
        );
        $this->assertTrue($res1['valid']);
        $this->assertNull($res1['error']);

        // Second occurrence with different casing -> REJECTED DUPLICATE
        $res2 = $this->validator->validateRow(
            ['name' => 'Another', 'surname' => 'User', 'email' => 'JOHN.SMITH@EXAMPLE.COM'],
            45,
            $seen
        );
        $this->assertFalse($res2['valid']);
        $this->assertSame("Duplicate email in CSV batch: 'john.smith@example.com'.", $res2['error']);
    }
}
