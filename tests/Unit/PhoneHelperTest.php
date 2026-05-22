<?php

namespace Tests\Unit;

use App\Support\PhoneHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PhoneHelperTest extends TestCase
{
    #[DataProvider('phoneFormatProvider')]
    public function test_format_phone_numbers(?string $input, string $expected): void
    {
        $this->assertSame($expected, PhoneHelper::format($input));
    }

    public static function phoneFormatProvider(): array
    {
        return [
            'empty string' => ['', ''],
            'null' => [null, ''],
            'full number' => ['6045551234', '604-555-1234'],
            'with formatting' => ['(604) 555-1234', '604-555-1234'],
            'partial three digits' => ['604', '604'],
            'partial six digits' => ['604555', '604-555'],
            'truncates extra digits' => ['16045551234', '160-455-5123'],
        ];
    }
}
