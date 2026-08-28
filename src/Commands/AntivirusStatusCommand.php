<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentAntivirus\Commands;

use Illuminate\Console\Command;
use JohnRivera7\FilamentAntivirus\Models\AntivirusIncident;
use JohnRivera7\FilamentAntivirus\Support\FileScanner;

final class AntivirusStatusCommand extends Command
{
    protected $signature = 'antivirus:status';

    protected $description = 'Show ClamAV engine status and recent incident summary';

    public function handle(FileScanner $scanner): int
    {
        $status = $scanner->engineStatus();

        $this->info('Antivirus engine');
        $this->table(
            ['Key', 'Value'],
            [
                ['Driver', $status['driver']],
                ['Available', $status['available'] ? 'yes' : 'no'],
                ['Version', $status['version'] ?? '—'],
                ['Message', $status['message']],
            ],
        );

        $since = now()->subDay();
        $recent = AntivirusIncident::query()->where('created_at', '>=', $since)->count();
        $infected = AntivirusIncident::query()
            ->where('verdict', 'infected')
            ->where('created_at', '>=', $since)
            ->count();
        $quarantined = AntivirusIncident::query()->where('status', 'quarantined')->count();

        $this->newLine();
        $this->info('Incidents (24h / total quarantined)');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Recent incidents', $recent],
                ['Infected (24h)', $infected],
                ['Quarantined (active)', $quarantined],
            ],
        );

        return self::SUCCESS;
    }
}
