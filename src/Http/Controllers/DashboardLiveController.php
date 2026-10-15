<?php

namespace Integrad\DbDeeper\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\View\Factory as ViewFactory;
use Integrad\DbDeeper\Services\DashboardSnapshotService;

class DashboardLiveController
{
    public function __invoke(ViewFactory $viewFactory, DashboardSnapshotService $dashboardSnapshotService): View
    {
        $connectionName = (string) (config('db-deeper.backup.connection') ?? config('database.default'));

        return $viewFactory->make('db-deeper::partials.dashboard-live', array_merge(
            $dashboardSnapshotService->overview($connectionName),
            [
                'databaseConnection' => $connectionName,
            ],
        ));
    }
}
