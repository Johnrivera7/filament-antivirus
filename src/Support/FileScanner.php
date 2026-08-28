<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentAntivirus\Support;

use Illuminate\Http\UploadedFile;
use JohnRivera7\FilamentAntivirus\Contracts\VirusScanner;
use JohnRivera7\FilamentAntivirus\Data\ScanResult;
use JohnRivera7\FilamentAntivirus\Scanners\ClamAvScanner;
use JohnRivera7\FilamentAntivirus\Scanners\NullScanner;

final class FileScanner
{
    public function __construct(
        private readonly QuarantineManager $quarantine,
    ) {}

    public function scanner(): VirusScanner
    {
        $driver = (string) config('filament-antivirus.driver', 'clamav');

        if ($driver === 'null') {
            return new NullScanner;
        }

        $clam = new ClamAvScanner;

        return $clam->isAvailable() ? $clam : new NullScanner;
    }

    public function scanPath(
        string $absolutePath,
        ?int $userId = null,
        string $source = 'manual',
        bool $recordClean = false,
    ): ScanResult {
        if (! $this->passesPolicy($absolutePath)) {
            $result = ScanResult::infected($this->scanner()->id(), 'POLICY_BLOCKED');

            $this->quarantine->quarantine($absolutePath, 'POLICY_BLOCKED', $userId, $source, [
                'reason' => 'blocked_extension_or_size',
            ]);

            return $result;
        }

        $result = $this->scanner()->scanFile($absolutePath);

        if ($result->skipped) {
            return $result;
        }

        if (! $result->clean) {
            $this->quarantine->quarantine(
                $absolutePath,
                $result->signature ?? 'UNKNOWN',
                $userId,
                $source,
                ['message' => $result->message],
            );

            return $result;
        }

        if ($recordClean) {
            $this->quarantine->recordCleanScan($absolutePath, $userId, $source);
        }

        return $result;
    }

    public function scanUpload(UploadedFile $file, ?int $userId = null, string $source = 'upload'): ScanResult
    {
        return $this->scanPath($file->getRealPath() ?: $file->getPathname(), $userId, $source);
    }

    /**
     * @return array{scanned: int, infected: int, errors: int}
     */
    public function scanDirectory(string $directory): array
    {
        $stats = ['scanned' => 0, 'infected' => 0, 'errors' => 0];

        if (! is_dir($directory)) {
            return $stats;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $stats['scanned']++;
            $result = $this->scanPath($file->getPathname(), source: 'bulk', recordClean: false);

            if ($result->signature === 'SCAN_ERROR') {
                $stats['errors']++;
            } elseif (! $result->clean && ! $result->skipped) {
                $stats['infected']++;
            }
        }

        return $stats;
    }

    /**
     * @return array{available: bool, version: ?string, message: string, driver: string}
     */
    public function engineStatus(): array
    {
        $status = $this->scanner()->status();

        return [
            ...$status,
            'driver' => (string) config('filament-antivirus.driver', 'clamav'),
        ];
    }

    private function passesPolicy(string $absolutePath): bool
    {
        $max = (int) config('filament-antivirus.max_upload_bytes', 50 * 1024 * 1024);

        if ($max > 0 && is_readable($absolutePath) && filesize($absolutePath) > $max) {
            return false;
        }

        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        /** @var list<string> $blocked */
        $blocked = config('filament-antivirus.blocked_extensions', []);

        return ! in_array($extension, $blocked, true);
    }
}
