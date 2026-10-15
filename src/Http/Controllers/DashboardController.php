<?php

namespace Integrad\LaravelDatabaseBackup\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\View\Factory as ViewFactory;

class DashboardController
{
    private const DASHBOARD_VIEW = 'database-backup::dashboard';

    public function __invoke(ViewFactory $viewFactory): View
    {
        $dashboardPath = $viewFactory->getFinder()->find(self::DASHBOARD_VIEW);

        return $viewFactory->file($dashboardPath, [
            'backupDisk' => config('database-backup.backup.disk'),
            'backupPath' => config('database-backup.backup.path'),
            'databaseConnection' => config('database-backup.backup.connection')
                ?? config('database.default'),
        ]);
    }
}
