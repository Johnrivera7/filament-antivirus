<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentAntivirus\Scanners;

use JohnRivera7\FilamentAntivirus\Contracts\VirusScanner;
use JohnRivera7\FilamentAntivirus\Data\ScanResult;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class ClamAvScanner implements VirusScanner
{
    public function id(): string
    {
        return 'clamav';
    }

    public function isAvailable(): bool
    {
        return $this->status()['available'];
    }

    public function status(): array
    {
        if ((bool) config('filament-antivirus.clamav.prefer_clamdscan', true)) {
            $clamd = $this->probeBinary((string) config('filament-antivirus.clamav.clamdscan_binary', 'clamdscan'));

            if ($clamd['available']) {
                return $clamd;
            }
        }

        return $this->probeBinary((string) config('filament-antivirus.clamav.binary', 'clamscan'));
    }

    public function scanFile(string $absolutePath): ScanResult
    {
        if (! is_readable($absolutePath)) {
            return ScanResult::error($this->id(), 'File not readable: '.$absolutePath);
        }

        if ((bool) config('filament-antivirus.clamav.prefer_clamdscan', true)) {
            $result = $this->runScanner(
                (string) config('filament-antivirus.clamav.clamdscan_binary', 'clamdscan'),
                $absolutePath,
            );

            if ($result !== null) {
                return $result;
            }
        }

        $result = $this->runScanner(
            (string) config('filament-antivirus.clamav.binary', 'clamscan'),
            ['--no-summary', $absolutePath],
        );

        return $result ?? ScanResult::error($this->id(), 'ClamAV scanner unavailable');
    }

    /**
     * @param  list<string>|string  $extraArgs
     */
    private function runScanner(string $binary, array|string $extraArgs): ?ScanResult
    {
        $command = is_array($extraArgs)
            ? array_merge([$binary], $extraArgs)
            : [$binary, $extraArgs];

        $process = new Process(
            $command,
            null,
            null,
            null,
            (int) config('filament-antivirus.clamav.timeout', 120),
        );

        try {
            $process->run();
        } catch (ProcessFailedException $e) {
            return $this->parseOutput($process->getOutput().$process->getErrorOutput())
                ?? ScanResult::error($this->id(), $e->getMessage());
        }

        return $this->parseOutput($process->getOutput().$process->getErrorOutput())
            ?? ($process->isSuccessful()
                ? ScanResult::clean($this->id())
                : ScanResult::error($this->id(), trim($process->getErrorOutput() ?: $process->getOutput())));
    }

    private function parseOutput(string $output): ?ScanResult
    {
        if ($output === '') {
            return null;
        }

        if (preg_match('/: (.+) FOUND$/m', $output, $matches) === 1) {
            return ScanResult::infected($this->id(), trim($matches[1]));
        }

        if (str_contains($output, 'OK') || str_contains($output, 'OK$')) {
            return ScanResult::clean($this->id());
        }

        if (str_contains($output, 'FOUND')) {
            return ScanResult::infected($this->id(), 'UNKNOWN');
        }

        return null;
    }

    /**
     * @return array{available: bool, version: ?string, message: string}
     */
    private function probeBinary(string $binary): array
    {
        $process = new Process([$binary, '--version'], null, null, null, 10);
        $process->run();

        if (! $process->isSuccessful()) {
            return [
                'available' => false,
                'version' => null,
                'message' => $binary.' not available',
            ];
        }

        $output = trim($process->getOutput());
        preg_match('/ClamAV[\s\d./A-Za-z]+/', $output, $matches);

        return [
            'available' => true,
            'version' => $matches[0] ?? $output,
            'message' => $matches[0] ?? $output,
        ];
    }
}
