<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentAntivirus\Contracts;

use JohnRivera7\FilamentAntivirus\Data\ScanResult;

interface VirusScanner
{
    public function id(): string;

    public function isAvailable(): bool;

    /**
     * @return array{available: bool, version: ?string, message: string}
     */
    public function status(): array;

    public function scanFile(string $absolutePath): ScanResult;
}
