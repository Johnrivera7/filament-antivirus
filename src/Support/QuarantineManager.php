<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentAntivirus\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JohnRivera7\FilamentAntivirus\Models\AntivirusIncident;

final class QuarantineManager
{
    public function quarantine(
        string $absolutePath,
        string $signature,
        ?int $userId = null,
        string $source = 'scan',
        ?array $meta = null,
    ): AntivirusIncident {
        $disk = (string) config('filament-antivirus.quarantine.disk', 'local');
        $directory = trim((string) config('filament-antivirus.quarantine.directory', 'quarantine'), '/');
        $sha256 = is_readable($absolutePath) ? hash_file('sha256', $absolutePath) : null;
        $size = is_readable($absolutePath) ? filesize($absolutePath) : null;
        $basename = basename($absolutePath);
        $target = $directory.'/'.now()->format('Y/m/d').'/'.Str::uuid()->toString().'_'.$basename;

        $quarantinePath = null;

        if ((bool) config('filament-antivirus.quarantine.enabled', true) && is_readable($absolutePath)) {
            $contents = file_get_contents($absolutePath);
            Storage::disk($disk)->put($target, $contents);
            $quarantinePath = $target;
            @unlink($absolutePath);
        }

        return AntivirusIncident::query()->create([
            'status' => 'quarantined',
            'verdict' => 'infected',
            'signature' => $signature,
            'original_path' => $absolutePath,
            'quarantine_path' => $quarantinePath,
            'sha256' => $sha256,
            'file_size' => $size !== false ? $size : null,
            'user_id' => $userId,
            'source' => $source,
            'meta' => $meta,
        ]);
    }

    public function recordCleanScan(string $absolutePath, ?int $userId = null, string $source = 'scan'): AntivirusIncident
    {
        return AntivirusIncident::query()->create([
            'status' => 'clean',
            'verdict' => 'clean',
            'original_path' => $absolutePath,
            'sha256' => is_readable($absolutePath) ? hash_file('sha256', $absolutePath) : null,
            'file_size' => is_readable($absolutePath) ? (filesize($absolutePath) ?: null) : null,
            'user_id' => $userId,
            'source' => $source,
        ]);
    }

    public function purgeExpired(): int
    {
        $days = (int) config('filament-antivirus.quarantine.retention_days', 30);
        $disk = (string) config('filament-antivirus.quarantine.disk', 'local');
        $deleted = 0;

        AntivirusIncident::query()
            ->where('status', 'quarantined')
            ->where('created_at', '<', now()->subDays($days))
            ->each(function (AntivirusIncident $incident) use ($disk, &$deleted): void {
                if ($incident->quarantine_path && Storage::disk($disk)->exists($incident->quarantine_path)) {
                    Storage::disk($disk)->delete($incident->quarantine_path);
                }

                $incident->update(['status' => 'purged']);
                $deleted++;
            });

        return $deleted;
    }
}
