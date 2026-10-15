<?php

namespace Integrad\DbDeeper\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\View\Factory as ViewFactory;
use Integrad\DbDeeper\Services\BackupHistoryService;

class BackupLiveController
{
    public function __invoke(ViewFactory $views, BackupHistoryService $history): View
    {
        return $views->make('db-deeper::partials.backups-live', $history->snapshot());
    }
}
