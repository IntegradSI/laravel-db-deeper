<?php

namespace Integrad\DbDeeper\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\View\Factory as ViewFactory;

class DashboardController
{
    private const DASHBOARD_VIEW = 'db-deeper::dashboard';

    public function __invoke(ViewFactory $viewFactory): View
    {
        $dashboardPath = $viewFactory->getFinder()->find(self::DASHBOARD_VIEW);

        return $viewFactory->file($dashboardPath, [
            'backupDisk' => config('db-deeper.backup.disk'),
            'backupPath' => config('db-deeper.backup.path'),
            'databaseConnection' => config('db-deeper.backup.connection')
                ?? config('database.default'),
            'activePage' => 'overview',
            'pageTitle' => 'Visão geral',
        ]);
    }
}
