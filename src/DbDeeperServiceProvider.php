<?php

namespace Integrad\DbDeeper;

use Integrad\DbDeeper\Console\Commands\GenerateBackupKeyCommand;
use Integrad\DbDeeper\Console\Commands\GenerateDatabaseBackupCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DbDeeperServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('db-deeper')
            ->hasConfigFile()
            ->hasViews()
            ->hasAssets()
            ->hasRoutes('web')
            ->hasCommands([
                GenerateBackupKeyCommand::class,
                GenerateDatabaseBackupCommand::class,
            ]);
    }
}
