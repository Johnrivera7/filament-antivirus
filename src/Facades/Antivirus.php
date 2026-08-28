<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentAntivirus\Facades;

use Illuminate\Support\Facades\Facade;
use JohnRivera7\FilamentAntivirus\Support\FileScanner;

/**
 * @method static \JohnRivera7\FilamentAntivirus\Data\ScanResult scanPath(string $absolutePath, ?int $userId = null, string $source = 'manual', bool $recordClean = false)
 * @method static \JohnRivera7\FilamentAntivirus\Data\ScanResult scanUpload(\Illuminate\Http\UploadedFile $file, ?int $userId = null, string $source = 'upload')
 * @method static array{available: bool, version: ?string, message: string, driver: string} engineStatus()
 *
 * @see FileScanner
 */
final class Antivirus extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FileScanner::class;
    }
}
