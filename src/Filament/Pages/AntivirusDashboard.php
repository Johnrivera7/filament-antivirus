<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentAntivirus\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use JohnRivera7\FilamentAntivirus\FilamentAntivirusPlugin;
use JohnRivera7\FilamentAntivirus\Models\AntivirusIncident;
use JohnRivera7\FilamentAntivirus\Support\FileScanner;
use UnitEnum;

class AntivirusDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static ?string $slug = 'antivirus-dashboard';

    protected string $view = 'filament-antivirus::filament.pages.antivirus-dashboard';

    /** @var array{available: bool, version: ?string, message: string, driver: string} */
    public array $engineStatus = [];

    /** @var list<AntivirusIncident> */
    public array $recentIncidents = [];

    public int $infected24h = 0;

    public int $quarantinedTotal = 0;

    public int $incidents24h = 0;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return FilamentAntivirusPlugin::get()->getNavigationGroup();
    }

    public static function getNavigationLabel(): string
    {
        $label = FilamentAntivirusPlugin::get()->getNavigationLabel();

        return is_string($label) ? $label : __('filament-antivirus::plugin.navigation_label');
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return FilamentAntivirusPlugin::get()->getNavigationIcon();
    }

    public static function getNavigationSort(): ?int
    {
        return FilamentAntivirusPlugin::get()->getNavigationSort();
    }

    public static function canAccess(): bool
    {
        return FilamentAntivirusPlugin::get()->canAccess();
    }

    public function getTitle(): string|Htmlable
    {
        return __('filament-antivirus::plugin.page_title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('filament-antivirus::plugin.page_subheading');
    }

    public function mount(FileScanner $scanner): void
    {
        $this->refreshStats($scanner);
    }

    public function runScan(FileScanner $scanner): void
    {
        foreach ((array) config('filament-antivirus.scan_paths', []) as $path) {
            if (is_dir($path)) {
                $scanner->scanDirectory($path);
            }
        }

        $this->refreshStats($scanner);

        Notification::make()
            ->title(__('filament-antivirus::plugin.scan_complete'))
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('scan')
                ->label(__('filament-antivirus::plugin.scan_now'))
                ->icon(Heroicon::OutlinedMagnifyingGlassCircle)
                ->color('danger')
                ->action('runScan'),
        ];
    }

    protected function refreshStats(FileScanner $scanner): void
    {
        $this->engineStatus = $scanner->engineStatus();

        $since = now()->subDay();

        $this->incidents24h = AntivirusIncident::query()->where('created_at', '>=', $since)->count();
        $this->infected24h = AntivirusIncident::query()
            ->where('verdict', 'infected')
            ->where('created_at', '>=', $since)
            ->count();
        $this->quarantinedTotal = AntivirusIncident::query()->where('status', 'quarantined')->count();

        $this->recentIncidents = AntivirusIncident::query()
            ->latest()
            ->limit(25)
            ->get()
            ->all();
    }
}
