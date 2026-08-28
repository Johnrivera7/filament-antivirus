<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentAntivirus\Tests\Unit;

use JohnRivera7\FilamentAntivirus\Scanners\NullScanner;
use PHPUnit\Framework\TestCase;

final class NullScannerTest extends TestCase
{
    public function test_scans_readable_file_as_skipped(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'fav');
        file_put_contents($path, 'clean content');

        $scanner = new NullScanner;
        $result = $scanner->scanFile($path);

        unlink($path);

        $this->assertTrue($result->clean);
        $this->assertTrue($result->skipped);
    }

    public function test_status_is_always_available(): void
    {
        $status = (new NullScanner)->status();

        $this->assertTrue($status['available']);
        $this->assertSame('dev-null', $status['version']);
    }
}
