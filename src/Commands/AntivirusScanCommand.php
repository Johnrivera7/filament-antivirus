<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentAntivirus\Commands;

use Illuminate\Console\Command;
use JohnRivera7\FilamentAntivirus\Support\FileScanner;
use JohnRivera7\FilamentAntivirus\Support\QuarantineManager;

final class AntivirusScanCommand extends Command
{
    protected $signature = 'antivirus:scan
                            {path? : Directory to scan (defaults to configured paths)}
                            {--purge : Purge quarantine files past retention period}';

    protected $description = 'Scan files for malware using ClamAV';

    public function handle(FileScanner $scanner, QuarantineManager $quarantine): int
    {
        if ($this->option('purge')) {
            $purged = $quarantine->purgeExpired();
            $this->info("Purged {$purged} expired quarantine record(s).");
        }

        $paths = $this->argument('path')
            ? [(string) $this->argument('path')]
            : (array) config('filament-antivirus.scan_paths', []);

        $totals = ['scanned' => 0, 'infected' => 0, 'errors' => 0];

        foreach ($paths as $path) {
            if (! is_dir($path)) {
                $this->warn("Skipping missing path: {$path}");

                continue;
            }

            $this->info("Scanning: {$path}");
            $stats = $scanner->scanDirectory($path);

            foreach ($stats as $key => $value) {
                $totals[$key] += $value;
            }
        }

        $this->table(
            ['Metric', 'Count'],
            collect($totals)->map(fn (int $v, string $k) => [$k, $v])->values()->all(),
        );

        return $totals['infected'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
