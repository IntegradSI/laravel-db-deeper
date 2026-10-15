<?php

namespace Integrad\DbDeeper\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\View\Factory as ViewFactory;
use Integrad\DbDeeper\Services\DatabaseMonitorService;
use Integrad\DbDeeper\Services\DatabaseStatsService;

class DashboardController
{
    private const DASHBOARD_VIEW = 'db-deeper::dashboard';

    public function __invoke(
        ViewFactory $viewFactory,
        DatabaseStatsService $databaseStatsService,
        DatabaseMonitorService $databaseMonitorService,
    ): View {
        $dashboardPath = $viewFactory->getFinder()->find(self::DASHBOARD_VIEW);
        $connectionName = config('db-deeper.backup.connection') ?? config('database.default');

        return $viewFactory->file($dashboardPath, [
            'backupDisk' => config('db-deeper.backup.disk'),
            'backupPath' => config('db-deeper.backup.path'),
            'databaseConnection' => $connectionName,
            'databaseStats' => $databaseStatsService->collect($connectionName),
            'monitoring' => $databaseMonitorService->collect($connectionName),
            'activePage' => 'overview',
            'pageTitle' => 'Saúde do banco',
        ]);
    }
}
