<?php

namespace Integrad\DbDeeper\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\View\Factory as ViewFactory;
use Integrad\DbDeeper\Services\DashboardSnapshotService;

class DashboardController
{
    private const DASHBOARD_VIEW = 'db-deeper::dashboard';

    public function __invoke(
        ViewFactory $viewFactory,
        DashboardSnapshotService $dashboardSnapshotService,
    ): View {
        $dashboardPath = $viewFactory->getFinder()->find(self::DASHBOARD_VIEW);
        $connectionName = config('db-deeper.backup.connection') ?? config('database.default');

        $refreshInterval = max(15, (int) config('db-deeper.dashboard.refresh_interval_seconds', 30));

        return $viewFactory->file($dashboardPath, array_merge($dashboardSnapshotService->overview($connectionName), [
            'databaseConnection' => $connectionName,
            'activePage' => 'overview',
            'pageTitle' => 'Saúde do banco',
            'liveRefreshUrl' => route('db-deeper.live.overview'),
            'refreshInterval' => $refreshInterval,
        ]));
    }
}
