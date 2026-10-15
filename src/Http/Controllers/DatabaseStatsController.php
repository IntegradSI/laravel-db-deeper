<?php

namespace Integrad\DbDeeper\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\View\Factory as ViewFactory;
use Integrad\DbDeeper\Services\DashboardSnapshotService;

class DatabaseStatsController
{
    private const DATABASE_VIEW = 'db-deeper::database';

    public function __invoke(ViewFactory $viewFactory, DashboardSnapshotService $dashboardSnapshotService): View
    {
        $connectionName = (string) (config('db-deeper.backup.connection') ?? config('database.default'));
        $databasePath = $viewFactory->getFinder()->find(self::DATABASE_VIEW);
        $refreshInterval = max(15, (int) config('db-deeper.dashboard.refresh_interval_seconds', 30));

        return $viewFactory->file($databasePath, array_merge($dashboardSnapshotService->database($connectionName), [
            'activePage' => 'database',
            'pageTitle' => 'Estatísticas do banco',
            'liveRefreshUrl' => route('db-deeper.live.database'),
            'refreshInterval' => $refreshInterval,
        ]));
    }
}
