# Antivirus

**Filament Antivirus** — ClamAV upload scanning, quarantine and file security dashboard for Laravel.

> **Stable `^1.0` (MIT).** Sibling of [filament-cybersecurity](https://github.com/Johnrivera7/filament-cybersecurity) (read-only posture). This plugin **acts on files**: scan, block uploads, quarantine.

## Features

- **Antivirus Dashboard** — engine status, incidents, quarantine stats
- **ClamAV** via `clamdscan` / `clamscan` (configurable)
- **Null driver** for local dev without ClamAV
- **Upload validation** — `AntivirusFileRule` for Filament / Laravel forms
- **Quarantine** — move infected files to `storage/app/quarantine/`
- **Incident log** — `antivirus_incidents` table
- Artisan: `antivirus:scan`, `antivirus:status`
- Facade: `Antivirus::scanPath()`, `Antivirus::scanUpload()`
- ES / EN translations

## Requirements

| Stack | Versions |
| --- | --- |
| PHP | 8.2+ |
| Laravel | 11.28+ / 12+ / 13+ |
| Filament | **5.x** |
| ClamAV | optional (recommended in production) |

## Installation

```bash
composer require johnrivera7/filament-antivirus:^1.0
php artisan vendor:publish --tag="filament-antivirus-migrations"
php artisan migrate
php artisan vendor:publish --tag="filament-antivirus-config"
```

Register on your Filament panel:

```php
use JohnRivera7\FilamentAntivirus\FilamentAntivirusPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentAntivirusPlugin::make()
                ->navigationGroup('Configuraciones')
                ->navigationLabel('Antivirus')
                ->navigationSort(7)
                ->permission(fn (): bool => auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false),
        ]);
}
```

## Configuration

```env
ANTIVIRUS_DRIVER=clamav          # clamav | null
ANTIVIRUS_QUARANTINE=true
CLAMAV_BINARY=clamscan
CLAMAV_CLAMDSCAN_BINARY=clamdscan
ANTIVIRUS_PREFER_CLAMDSCAN=true
```

## Upload validation

```php
use JohnRivera7\FilamentAntivirus\Rules\AntivirusFileRule;

FileUpload::make('document')
    ->rules([new AntivirusFileRule()]);
```

## CLI

```bash
php artisan antivirus:status
php artisan antivirus:scan
php artisan antivirus:scan /path/to/dir --purge
```

## License

MIT © John Rivera
