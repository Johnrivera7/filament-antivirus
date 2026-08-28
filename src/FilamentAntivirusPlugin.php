<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentAntivirus;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Assets\Css;
use JohnRivera7\FilamentAntivirus\Filament\Pages\AntivirusDashboard;

final class FilamentAntivirusPlugin implements Plugin
{
    protected bool $registerDashboard = true;

    protected ?string $navigationGroup = null;

    protected ?string $navigationLabel = null;

    protected ?string $navigationIcon = null;

    protected ?int $navigationSort = null;

    /** @var Closure(): bool|null */
    protected ?Closure $permission = null;

    public static function make(): static
    {
        return app(self::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'filament-antivirus';
    }

    public function register(Panel $panel): void
    {
        $panel->assets([
            Css::make('filament-antivirus', __DIR__.'/../resources/dist/filament-antivirus.css'),
        ]);

        if ($this->hasDashboard()) {
            $panel->pages([
                AntivirusDashboard::class,
            ]);
        }
    }

    public function boot(Panel $panel): void {}

    public function registerDashboard(bool $condition = true): static
    {
        $this->registerDashboard = $condition;

        return $this;
    }

    public function hasDashboard(): bool
    {
        return $this->registerDashboard && (bool) config('filament-antivirus.register_dashboard', true);
    }

    public function navigationGroup(?string $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function navigationLabel(?string $label): static
    {
        $this->navigationLabel = $label;

        return $this;
    }

    public function navigationIcon(?string $icon): static
    {
        $this->navigationIcon = $icon;

        return $this;
    }

    public function navigationSort(?int $sort): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    /**
     * @param  Closure(): bool  $callback
     */
    public function permission(Closure $callback): static
    {
        $this->permission = $callback;

        return $this;
    }

    public function getNavigationGroup(): ?string
    {
        return $this->navigationGroup ?? config('filament-antivirus.navigation.group');
    }

    public function getNavigationLabel(): ?string
    {
        return $this->navigationLabel ?? config('filament-antivirus.navigation.label');
    }

    public function getNavigationIcon(): ?string
    {
        return $this->navigationIcon ?? config('filament-antivirus.navigation.icon');
    }

    public function getNavigationSort(): ?int
    {
        return $this->navigationSort ?? config('filament-antivirus.navigation.sort');
    }

    public function canAccess(): bool
    {
        $configured = config('filament-antivirus.permission');

        if (is_callable($configured)) {
            return (bool) $configured();
        }

        if ($this->permission !== null) {
            return (bool) ($this->permission)();
        }

        return true;
    }
}
