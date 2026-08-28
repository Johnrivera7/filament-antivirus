<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentAntivirus\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Translation\PotentiallyTranslatedString;
use JohnRivera7\FilamentAntivirus\Support\FileScanner;

final class AntivirusFileRule implements ValidationRule
{
    public function __construct(
        private readonly ?int $userId = null,
    ) {}

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            return;
        }

        $scanner = app(FileScanner::class);
        $result = $scanner->scanUpload($value, $this->userId ?? auth()->id(), 'upload');

        if ($result->skipped) {
            return;
        }

        if (! $result->clean) {
            $fail(__('filament-antivirus::plugin.validation.infected', [
                'signature' => $result->signature ?? 'UNKNOWN',
            ]));
        }
    }
}
