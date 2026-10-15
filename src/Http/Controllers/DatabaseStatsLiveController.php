<?php

namespace Integrad\DbDeeper\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\View\Factory as ViewFactory;
use Integrad\DbDeeper\Services\DashboardSnapshotService;

class DatabaseStatsLiveController
{
    public function __invoke(ViewFactory $viewFactory, DashboardSnapshotService $dashboardSnapshotService): View
    {
        $connectionName = (string) (config('db-deeper.backup.connection') ?? config('database.default'));

        return $viewFactory->make('db-deeper::partials.database-live', $dashboardSnapshotService->database($connectionName));
    }
}
