<?php

namespace Integrad\DbDeeper\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\View\Factory as ViewFactory;
use Integrad\DbDeeper\Services\DatabaseStatsService;

class DatabaseStatsController
{
    private const DATABASE_VIEW = 'db-deeper::database';

    public function __invoke(ViewFactory $viewFactory, DatabaseStatsService $databaseStatsService): View
    {
        $connectionName = (string) (config('db-deeper.backup.connection') ?? config('database.default'));
        $databasePath = $viewFactory->getFinder()->find(self::DATABASE_VIEW);

        return $viewFactory->file($databasePath, [
            'activePage' => 'database',
            'pageTitle' => 'Estatísticas do banco',
            'stats' => $databaseStatsService->collect($connectionName),
        ]);
    }
}
