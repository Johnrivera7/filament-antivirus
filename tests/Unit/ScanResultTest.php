<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentAntivirus\Tests\Unit;

use JohnRivera7\FilamentAntivirus\Data\ScanResult;
use PHPUnit\Framework\TestCase;

final class ScanResultTest extends TestCase
{
    public function test_clean_factory(): void
    {
        $result = ScanResult::clean('null');

        $this->assertTrue($result->clean);
        $this->assertSame('null', $result->scanner);
        $this->assertNull($result->signature);
    }

    public function test_infected_factory(): void
    {
        $result = ScanResult::infected('clamav', 'Eicar-Test-Signature');

        $this->assertFalse($result->clean);
        $this->assertSame('Eicar-Test-Signature', $result->signature);
    }

    public function test_skipped_is_clean(): void
    {
        $result = ScanResult::skipped('null', 'dev');

        $this->assertTrue($result->clean);
        $this->assertTrue($result->skipped);
    }
}
