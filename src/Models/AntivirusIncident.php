<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentAntivirus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AntivirusIncident extends Model
{
    protected $fillable = [
        'status',
        'verdict',
        'signature',
        'original_path',
        'quarantine_path',
        'sha256',
        'file_size',
        'mime_type',
        'user_id',
        'source',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'file_size' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
