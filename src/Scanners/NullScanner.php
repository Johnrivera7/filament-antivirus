<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentAntivirus\Scanners;

use JohnRivera7\FilamentAntivirus\Contracts\VirusScanner;
use JohnRivera7\FilamentAntivirus\Data\ScanResult;

final class NullScanner implements VirusScanner
{
    public function id(): string
    {
        return 'null';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function status(): array
    {
        return [
            'available' => true,
            'version' => 'dev-null',
            'message' => 'Null scanner (no AV engine — development only)',
        ];
    }

    public function scanFile(string $absolutePath): ScanResult
    {
        if (! is_readable($absolutePath)) {
            return ScanResult::error($this->id(), 'File not readable');
        }

        return ScanResult::skipped($this->id(), 'Scan skipped (null driver)');
    }
}
