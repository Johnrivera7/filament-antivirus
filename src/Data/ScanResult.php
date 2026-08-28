<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentAntivirus\Data;

final readonly class ScanResult
{
    public function __construct(
        public bool $clean,
        public string $scanner,
        public ?string $signature = null,
        public ?string $message = null,
        public bool $skipped = false,
    ) {}

    public static function clean(string $scanner, ?string $message = null): self
    {
        return new self(clean: true, scanner: $scanner, message: $message);
    }

    public static function infected(string $scanner, string $signature, ?string $message = null): self
    {
        return new self(clean: false, scanner: $scanner, signature: $signature, message: $message);
    }

    public static function skipped(string $scanner, string $reason): self
    {
        return new self(clean: true, scanner: $scanner, message: $reason, skipped: true);
    }

    public static function error(string $scanner, string $message): self
    {
        return new self(clean: false, scanner: $scanner, message: $message, signature: 'SCAN_ERROR');
    }
}
