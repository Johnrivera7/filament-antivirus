<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentAntivirus;

use JohnRivera7\FilamentAntivirus\Commands\AntivirusScanCommand;
use JohnRivera7\FilamentAntivirus\Commands\AntivirusStatusCommand;
use JohnRivera7\FilamentAntivirus\Support\FileScanner;
use JohnRivera7\FilamentAntivirus\Support\QuarantineManager;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class FilamentAntivirusServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-antivirus')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews()
            ->hasMigrations([
                'create_antivirus_incidents_table',
            ])
            ->hasCommands([
                AntivirusScanCommand::class,
                AntivirusStatusCommand::class,
            ])
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('Johnrivera7/filament-antivirus');
            });
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(QuarantineManager::class);
        $this->app->singleton(FileScanner::class);
    }
}
