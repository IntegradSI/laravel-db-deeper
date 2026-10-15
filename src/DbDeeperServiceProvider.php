<?php

namespace Integrad\DbDeeper;

use Illuminate\Console\Scheduling\Schedule;
use Integrad\DbDeeper\Console\Commands\GenerateBackupKeyCommand;
use Integrad\DbDeeper\Console\Commands\GenerateDatabaseBackupCommand;
use Integrad\DbDeeper\Console\Commands\QueueDueBackupsCommand;
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
            ->hasMigrations([
                '2026_10_15_173659_create_db_deeper_backup_keys_table',
                '2026_10_15_173700_create_db_deeper_backup_schedules_table',
                '2026_10_15_173701_create_db_deeper_backups_table',
            ])
            ->runsMigrations()
            ->hasCommands([
                GenerateBackupKeyCommand::class,
                GenerateDatabaseBackupCommand::class,
                QueueDueBackupsCommand::class,
            ]);
    }

    public function packageBooted(): void
    {
        if (! config('db-deeper.backup.scheduler_enabled', true)) {
            return;
        }

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule->command('db-deeper:queue-due-backups')
                ->everyMinute()
                ->withoutOverlapping(5);
        });
    }
}
