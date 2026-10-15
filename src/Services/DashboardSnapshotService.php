<?php

namespace Integrad\DbDeeper\Services;

class DashboardSnapshotService
{
    public function __construct(
        private DatabaseStatsService $databaseStatsService,
        private DatabaseMonitorService $databaseMonitorService,
        private LaravelSessionActivityService $sessionActivityService,
    ) {}

    /**
     * @return array{
     *     databaseStats: array<string, mixed>,
     *     monitoring: array<string, mixed>,
     *     sessionActivity: array<string, mixed>
     * }
     */
    public function overview(string $connectionName): array
    {
        return [
            'databaseStats' => $this->databaseStatsService->collect($connectionName),
            'monitoring' => $this->databaseMonitorService->collect($connectionName),
            'sessionActivity' => $this->sessionActivityService->collect(),
        ];
    }

    /**
     * @return array{stats: array<string, mixed>}
     */
    public function database(string $connectionName): array
    {
        return [
            'stats' => $this->databaseStatsService->collect($connectionName),
        ];
    }
}
