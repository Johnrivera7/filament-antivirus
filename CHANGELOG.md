# Changelog

All notable changes to `johnrivera7/filament-antivirus` will be documented in this file.

## [1.0.0] - 2026-08-28

### Added

- Initial release: ClamAV scanning (`clamdscan` / `clamscan`) with null driver fallback
- Filament Antivirus Dashboard (engine status, incidents, scan action)
- `AntivirusIncident` model + migration
- Quarantine manager (`storage/app/quarantine/`)
- `AntivirusFileRule` for upload validation
- Artisan commands: `antivirus:scan`, `antivirus:status`
- `Antivirus` facade / `FileScanner` service
- Upload policy: blocked extensions + max size
- ES / EN translations
