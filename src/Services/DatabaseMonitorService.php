<?php

namespace Integrad\DbDeeper\Services;

use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Throwable;

class DatabaseMonitorService
{
    private const SUPPORTED_DRIVERS = ['mysql', 'mariadb', 'pgsql', 'sqlite', 'sqlsrv'];

    public function __construct(private DatabaseManager $databaseManager) {}

    /**
     * @return array{
     *     connection_name: string,
     *     driver: string,
     *     driver_label: string,
     *     connected: bool,
     *     latency_ms: float|null,
     *     active_connections: int|null,
     *     active_queries: int|null,
     *     max_connections: int|null,
     *     connection_usage_percent: float|null,
     *     buffer_hit_percent: float|null,
     *     buffer_samples: int|null,
     *     deadlocks: int|null,
     *     transactions_committed: int|null,
     *     transactions_rolled_back: int|null,
     *     uptime_seconds: int|null,
     *     engine_slow_query_count: int|null,
     *     slow_query_threshold_ms: int,
     *     slow_query_pattern_count: int|null,
     *     slow_query_source: string|null,
     *     slow_query_period: string|null,
     *     slow_query_note: string|null,
     *     slow_queries: list<array{fingerprint: string|null, query: string|null, executions: int|null, average_ms: float|null, maximum_ms: float|null, total_ms: float|null, rows_examined: int|null, no_index_count: int|null}>,
     *     recommendations: list<array{level: string, title: string, detail: string}>
     * }
     */
    public function collect(string $connectionName): array
    {
        $driver = (string) config("database.connections.{$connectionName}.driver", 'unknown');
        $snapshot = $this->emptySnapshot($connectionName, $driver);

        try {
            $connection = $this->databaseManager->connection($connectionName);
            $driver = (string) $connection->getConfig('driver');
            $snapshot['driver'] = $driver;
            $snapshot['driver_label'] = $this->driverLabel($driver);

            $startedAt = hrtime(true);
            $connection->select('SELECT 1');
            $snapshot['latency_ms'] = round((hrtime(true) - $startedAt) / 1_000_000, 2);
            $snapshot['connected'] = true;

            if (! in_array($driver, self::SUPPORTED_DRIVERS, true)) {
                $snapshot['slow_query_note'] = 'Este driver não tem métricas de gestão detalhadas nesta versão.';

                return $this->withRecommendations($snapshot);
            }

            $driverStatistics = match ($driver) {
                'mysql', 'mariadb' => $this->collectMySqlStatistics($connection, $driver),
                'pgsql' => $this->collectPostgresStatistics($connection),
                'sqlsrv' => $this->collectSqlServerStatistics($connection),
                'sqlite' => [
                    'slow_query_note' => 'O PDO SQLite não mantém estatísticas agregadas por consulta. O tamanho e o inventário do arquivo continuam disponíveis na página Estatísticas do banco.',
                    'slow_query_period' => null,
                ],
            };

            $snapshot = array_replace($snapshot, $driverStatistics);
        } catch (Throwable) {
            $snapshot['slow_query_note'] = $snapshot['connected']
                ? 'A conexão respondeu, mas o usuário não tem acesso às métricas de gestão deste driver.'
                : 'Não foi possível conectar ao banco para coletar as métricas de gestão.';
        }

        return $this->withRecommendations($snapshot);
    }

    /**
     * @return array<string, mixed>
     */
    private function collectMySqlStatistics(Connection $connection, string $driver): array
    {
        $status = $this->mysqlVariables($connection, 'GLOBAL STATUS', [
            'Threads_connected',
            'Threads_running',
            'Uptime',
            'Slow_queries',
            'Innodb_buffer_pool_read_requests',
            'Innodb_buffer_pool_reads',
        ]);
        $variables = $this->mysqlVariables($connection, 'GLOBAL VARIABLES', ['max_connections']);
        $activeConnections = $this->integerOrNull($status['threads_connected'] ?? null);
        $maxConnections = $this->integerOrNull($variables['max_connections'] ?? null);
        $bufferReads = $this->integerOrNull($status['innodb_buffer_pool_reads'] ?? null);
        $bufferRequests = $this->integerOrNull($status['innodb_buffer_pool_read_requests'] ?? null);
        $slowQueries = [];
        $slowCount = null;
        $slowQueryNote = null;

        try {
            $thresholdInPicoseconds = $this->slowQueryThresholdMs() * 1_000_000_000;
            $slowCount = (int) $connection->selectOne(
                'SELECT COUNT(*) AS pattern_count
                 FROM performance_schema.events_statements_summary_by_digest
                 WHERE SCHEMA_NAME = DATABASE() AND DIGEST IS NOT NULL AND MAX_TIMER_WAIT >= ?',
                [$thresholdInPicoseconds],
            )->pattern_count;
            $slowQueries = $this->mapSlowQueries($connection->select(
                'SELECT DIGEST AS fingerprint, DIGEST_TEXT AS query_text, COUNT_STAR AS executions,
                    SUM_TIMER_WAIT / 1000000000 AS total_ms,
                    AVG_TIMER_WAIT / 1000000000 AS average_ms,
                    MAX_TIMER_WAIT / 1000000000 AS maximum_ms,
                    SUM_ROWS_EXAMINED AS rows_examined,
                    SUM_NO_INDEX_USED AS no_index_count
                 FROM performance_schema.events_statements_summary_by_digest
                 WHERE SCHEMA_NAME = DATABASE() AND DIGEST IS NOT NULL AND MAX_TIMER_WAIT >= ?
                 ORDER BY MAX_TIMER_WAIT DESC
                 LIMIT '.$this->slowQueryLimit(),
                [$thresholdInPicoseconds],
            ));
        } catch (Throwable) {
            $slowQueryNote = $this->performanceSchemaNote($connection);
        }

        $bufferSamples = $bufferRequests;
        $bufferHitPercent = $bufferRequests !== null && $bufferRequests > 0 && $bufferReads !== null
            ? round((($bufferRequests - $bufferReads) / $bufferRequests) * 100, 2)
            : null;

        return [
            'active_connections' => $activeConnections,
            'active_queries' => $this->integerOrNull($status['threads_running'] ?? null),
            'max_connections' => $maxConnections,
            'connection_usage_percent' => $this->percentage($activeConnections, $maxConnections),
            'buffer_hit_percent' => $bufferHitPercent,
            'buffer_samples' => $bufferSamples,
            'deadlocks' => null,
            'transactions_committed' => null,
            'transactions_rolled_back' => null,
            'uptime_seconds' => $this->integerOrNull($status['uptime'] ?? null),
            'engine_slow_query_count' => $this->integerOrNull($status['slow_queries'] ?? null),
            'slow_query_pattern_count' => $slowCount,
            'slow_query_source' => $driver === 'mariadb'
                ? 'MariaDB Performance Schema · digests normalizados'
                : 'MySQL Performance Schema · digests normalizados',
            'slow_query_period' => 'Contadores do servidor; podem reiniciar com o serviço ou com a limpeza manual das estatísticas.',
            'slow_query_note' => $slowQueryNote,
            'slow_queries' => $slowQueries,
        ];
    }

    /**
     * @param  list<string>  $names
     * @return array<string, string>
     */
    private function mysqlVariables(Connection $connection, string $scope, array $names): array
    {
        $quotedNames = implode(', ', array_map(
            fn (string $name): string => "'".str_replace("'", "''", $name)."'",
            $names,
        ));
        $rows = $connection->select(
            "SHOW {$scope} WHERE Variable_name IN ({$quotedNames})",
        );

        $values = [];

        foreach ($rows as $row) {
            $values[strtolower((string) $row->Variable_name)] = (string) $row->Value;
        }

        return array_change_key_case($values, CASE_LOWER) + array_combine(
            array_map('strtolower', $names),
            array_fill(0, count($names), ''),
        );
    }

    private function performanceSchemaNote(Connection $connection): string
    {
        try {
            $enabled = (bool) $connection->selectOne('SELECT @@performance_schema AS enabled')->enabled;

            if (! $enabled) {
                return 'O Performance Schema está desativado no servidor. Ative o recurso e a coleta de statements para habilitar o histórico de consultas.';
            }
        } catch (Throwable) {
            // Some compatible servers do not expose this setting under the MySQL name.
        }

        return 'O Performance Schema está ativo, mas a conta não pode ler as estatísticas de consultas. Conceda acesso somente de leitura ao schema performance_schema.';
    }

    /**
     * @return array<string, mixed>
     */
    private function collectPostgresStatistics(Connection $connection): array
    {
        $database = $connection->selectOne(
            'SELECT numbackends AS active_connections,
                blks_hit, blks_read, deadlocks,
                xact_commit AS transactions_committed,
                xact_rollback AS transactions_rolled_back,
                EXTRACT(EPOCH FROM (now() - pg_postmaster_start_time()))::bigint AS uptime_seconds,
                stats_reset::text AS statistics_reset_at
             FROM pg_stat_database WHERE datname = current_database()',
        );
        $maxConnections = (int) $connection->selectOne(
            'SELECT current_setting(?)::integer AS max_connections',
            ['max_connections'],
        )->max_connections;
        $activeQueries = (int) $connection->selectOne(
            "SELECT COUNT(*) AS active_queries
             FROM pg_stat_activity
             WHERE datname = current_database() AND state = 'active' AND pid <> pg_backend_pid()",
        )->active_queries;
        $bufferReads = (int) $database->blks_read;
        $bufferHits = (int) $database->blks_hit;
        $bufferSamples = $bufferReads + $bufferHits;
        $slowQueries = [];
        $slowCount = null;
        $slowQueryNote = null;

        try {
            $serverVersion = (int) $connection->selectOne('SHOW server_version_num')->server_version_num;
            $timeColumns = $serverVersion >= 130000
                ? 'total_exec_time AS total_ms, mean_exec_time AS average_ms, max_exec_time AS maximum_ms'
                : 'total_time AS total_ms, mean_time AS average_ms, max_time AS maximum_ms';
            $threshold = $this->slowQueryThresholdMs();
            $scope = 'dbid = (SELECT oid FROM pg_database WHERE datname = current_database()) AND calls > 0';
            $slowCount = (int) $connection->selectOne(
                "SELECT COUNT(*) AS pattern_count FROM pg_stat_statements WHERE {$scope} AND ".($serverVersion >= 130000 ? 'max_exec_time' : 'max_time').' >= ?',
                [$threshold],
            )->pattern_count;
            $slowQueries = $this->mapSlowQueries($connection->select(
                "SELECT queryid::text AS fingerprint, query AS query_text, calls AS executions,
                    {$timeColumns}, rows AS rows_examined,
                    0 AS no_index_count
                 FROM pg_stat_statements
                 WHERE {$scope} AND ".($serverVersion >= 130000 ? 'max_exec_time' : 'max_time').' >= ?'.
                    ' ORDER BY '.($serverVersion >= 130000 ? 'max_exec_time' : 'max_time').' DESC LIMIT '.$this->slowQueryLimit(),
                [$threshold],
            ));
        } catch (Throwable) {
            $slowQueryNote = 'O pg_stat_statements não está carregado nesta conexão ou a conta não tem permissão para ler suas estatísticas. Habilite a extensão e conceda leitura da view.';
        }

        return [
            'active_connections' => (int) $database->active_connections,
            'active_queries' => $activeQueries,
            'max_connections' => $maxConnections,
            'connection_usage_percent' => $this->percentage((int) $database->active_connections, $maxConnections),
            'buffer_hit_percent' => $bufferSamples > 0 ? round(($bufferHits / $bufferSamples) * 100, 2) : null,
            'buffer_samples' => $bufferSamples,
            'deadlocks' => (int) $database->deadlocks,
            'transactions_committed' => (int) $database->transactions_committed,
            'transactions_rolled_back' => (int) $database->transactions_rolled_back,
            'uptime_seconds' => (int) $database->uptime_seconds,
            'engine_slow_query_count' => null,
            'slow_query_pattern_count' => $slowCount,
            'slow_query_source' => 'PostgreSQL pg_stat_statements · queries normalizadas',
            'slow_query_period' => $database->statistics_reset_at
                ? 'Estatísticas do banco reiniciadas em '.$database->statistics_reset_at.'. O período de pg_stat_statements pode ser diferente.'
                : 'Período de coleta do servidor não informado.',
            'slow_query_note' => $slowQueryNote,
            'slow_queries' => $slowQueries,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function collectSqlServerStatistics(Connection $connection): array
    {
        $sessions = $connection->selectOne(
            'SELECT COUNT(*) AS active_connections FROM sys.dm_exec_sessions WHERE is_user_process = 1',
        );
        $configuredMax = (int) $connection->selectOne(
            'SELECT value_in_use AS max_connections FROM sys.configurations WHERE name = ?',
            ['user connections'],
        )->max_connections;
        $activeQueries = null;

        try {
            $activeQueries = (int) $connection->selectOne(
                'SELECT COUNT(*) AS active_queries FROM sys.dm_exec_requests WHERE database_id = DB_ID() AND session_id <> @@SPID',
            )->active_queries;
        } catch (Throwable) {
            // The rest of the dashboard remains available without request-state visibility.
        }
        $activeConnections = (int) $sessions->active_connections;
        $maxConnections = $configuredMax > 0 ? $configuredMax : null;
        $uptimeSeconds = null;

        try {
            $uptimeSeconds = (int) $connection->selectOne(
                'SELECT DATEDIFF_BIG(SECOND, sqlserver_start_time, SYSDATETIME()) AS uptime_seconds FROM sys.dm_os_sys_info',
            )->uptime_seconds;
        } catch (Throwable) {
            // The main monitoring panels remain useful without server uptime permission.
        }

        $slowQueries = [];
        $slowCount = null;
        $slowQueryNote = null;

        try {
            $thresholdInMicroseconds = $this->slowQueryThresholdMs() * 1000;
            $slowRows = $connection->select(
                'SELECT TOP ('.$this->slowQueryLimit().')
                    CONVERT(varchar(18), queries.query_hash, 1) AS fingerprint,
                    queries.execution_count AS executions,
                    queries.total_elapsed_time / 1000.0 AS total_ms,
                    queries.total_elapsed_time / NULLIF(queries.execution_count, 0) / 1000.0 AS average_ms,
                    queries.max_elapsed_time / 1000.0 AS maximum_ms,
                    SUBSTRING(statement_text.text,
                        (queries.statement_start_offset / 2) + 1,
                        ((CASE queries.statement_end_offset
                            WHEN -1 THEN DATALENGTH(statement_text.text)
                            ELSE queries.statement_end_offset
                          END - queries.statement_start_offset) / 2) + 1
                    ) AS query_text,
                    COUNT(*) OVER() AS pattern_count
                 FROM sys.dm_exec_query_stats AS queries
                 CROSS APPLY sys.dm_exec_sql_text(queries.sql_handle) AS statement_text
                 WHERE statement_text.dbid = DB_ID() AND queries.max_elapsed_time >= ?
                 ORDER BY queries.max_elapsed_time DESC',
                [$thresholdInMicroseconds],
            );
            $slowCount = isset($slowRows[0]) ? (int) $slowRows[0]->pattern_count : 0;
            $slowQueries = $this->mapSlowQueries($slowRows);
        } catch (Throwable) {
            $slowQueryNote = 'A conta não pode consultar as estatísticas de execução do SQL Server. Conceda VIEW SERVER STATE (até SQL Server 2019) ou VIEW SERVER PERFORMANCE STATE (SQL Server 2022+).';
        }

        $bufferHitPercent = null;
        $bufferSamples = null;

        try {
            $cacheCounters = $connection->selectOne(
                'SELECT
                    MAX(CASE WHEN counter_name = ? THEN cntr_value END) AS cache_hits,
                    MAX(CASE WHEN counter_name = ? THEN cntr_value END) AS cache_base
                 FROM sys.dm_os_performance_counters
                 WHERE object_name LIKE ? AND counter_name IN (?, ?)',
                ['Buffer cache hit ratio', 'Buffer cache hit ratio base', '%Buffer Manager%', 'Buffer cache hit ratio', 'Buffer cache hit ratio base'],
            );
            $cacheHits = $this->integerOrNull($cacheCounters->cache_hits);
            $cacheBase = $this->integerOrNull($cacheCounters->cache_base);

            if ($cacheBase !== null && $cacheBase > 0 && $cacheHits !== null) {
                $bufferHitPercent = round(($cacheHits / $cacheBase) * 100, 2);
                $bufferSamples = $cacheBase;
            }
        } catch (Throwable) {
            // Buffer cache counters require separate server-state visibility.
        }

        return [
            'active_connections' => $activeConnections,
            'active_queries' => $activeQueries,
            'max_connections' => $maxConnections,
            'connection_usage_percent' => $this->percentage($activeConnections, $maxConnections),
            'buffer_hit_percent' => $bufferHitPercent,
            'buffer_samples' => $bufferSamples,
            'deadlocks' => null,
            'transactions_committed' => null,
            'transactions_rolled_back' => null,
            'uptime_seconds' => $uptimeSeconds,
            'engine_slow_query_count' => null,
            'slow_query_pattern_count' => $slowCount,
            'slow_query_source' => 'SQL Server DMV · cache de planos compilados',
            'slow_query_period' => 'O DMV cobre planos que ainda estão no cache; reinicializações e invalidação de planos alteram o período.',
            'slow_query_note' => $slowQueryNote,
            'slow_queries' => $slowQueries,
        ];
    }

    /**
     * @param  list<object>  $rows
     * @return list<array{fingerprint: string|null, query: string|null, executions: int|null, average_ms: float|null, maximum_ms: float|null, total_ms: float|null, rows_examined: int|null, no_index_count: int|null}>
     */
    private function mapSlowQueries(array $rows): array
    {
        return array_map(fn (object $row): array => [
            'fingerprint' => $row->fingerprint === null ? null : (string) $row->fingerprint,
            'query' => isset($row->query_text) ? $this->redactQuery((string) $row->query_text) : null,
            'executions' => $this->integerOrNull($row->executions ?? null),
            'average_ms' => $this->floatOrNull($row->average_ms ?? null),
            'maximum_ms' => $this->floatOrNull($row->maximum_ms ?? null),
            'total_ms' => $this->floatOrNull($row->total_ms ?? null),
            'rows_examined' => $this->integerOrNull($row->rows_examined ?? null),
            'no_index_count' => $this->integerOrNull($row->no_index_count ?? null),
        ], $rows);
    }

    private function redactQuery(string $query): string
    {
        $query = preg_replace('/\/\*.*?\*\/|--[^\r\n]*/s', ' ', $query) ?? '';
        $query = preg_replace("/'(?:''|\\\\.|[^'])*'/s", "'?'", $query) ?? '';
        $query = preg_replace('/"(?:""|\\\\.|[^"])*"/s', '"?"', $query) ?? '';
        $query = preg_replace('/(?<![A-Za-z0-9_$])[-+]?\d+(?:\.\d+)?(?![A-Za-z0-9_$])/', '?', $query) ?? '';
        $query = preg_replace('/\s+/', ' ', trim($query)) ?? '';

        return mb_substr($query, 0, 500);
    }

    /**
     * @param  array<string, mixed>  $snapshot
     * @return array<string, mixed>
     */
    private function withRecommendations(array $snapshot): array
    {
        $recommendations = [];

        if (! $snapshot['connected']) {
            $recommendations[] = [
                'level' => 'error',
                'title' => 'Banco sem resposta',
                'detail' => $snapshot['slow_query_note'],
            ];
        } elseif ($snapshot['connection_usage_percent'] !== null && $snapshot['connection_usage_percent'] >= 80) {
            $recommendations[] = [
                'level' => 'warning',
                'title' => 'Poucas conexões disponíveis',
                'detail' => 'O uso está em '.number_format($snapshot['connection_usage_percent'], 1, ',', '.').'% do limite configurado. Revise conexões persistentes e o pool da aplicação.',
            ];
        }

        if ($snapshot['slow_query_pattern_count'] !== null && $snapshot['slow_query_pattern_count'] > 0) {
            $recommendations[] = [
                'level' => 'warning',
                'title' => 'Consultas acima do limite',
                'detail' => number_format($snapshot['slow_query_pattern_count'], 0, ',', '.').' padrões excederam o pico de '.number_format($snapshot['slow_query_threshold_ms'], 0, ',', '.').' ms. Revise plano de execução, filtros e índices antes de alterar a consulta.',
            ];
        }

        if ($snapshot['buffer_hit_percent'] !== null && $snapshot['buffer_samples'] >= 1000 && $snapshot['buffer_hit_percent'] < 95) {
            $recommendations[] = [
                'level' => 'warning',
                'title' => 'Cache de páginas abaixo da referência',
                'detail' => 'A taxa está abaixo de 95% em uma amostra de '.number_format($snapshot['buffer_samples'], 0, ',', '.').' leituras. Verifique o conjunto de dados ativo e a memória disponível; a referência varia conforme a carga.',
            ];
        }

        if ($snapshot['deadlocks'] !== null && $snapshot['deadlocks'] > 0) {
            $recommendations[] = [
                'level' => 'warning',
                'title' => 'Deadlocks registrados',
                'detail' => number_format($snapshot['deadlocks'], 0, ',', '.').' deadlocks aparecem nos contadores desde o último reset das estatísticas. Revise a ordem de acesso às linhas e transações concorrentes.',
            ];
        }

        return array_replace($snapshot, ['recommendations' => $recommendations]);
    }

    /**
     * @return array<string, mixed>
     */
    private function emptySnapshot(string $connectionName, string $driver): array
    {
        return [
            'connection_name' => $connectionName,
            'driver' => $driver,
            'driver_label' => $this->driverLabel($driver),
            'connected' => false,
            'latency_ms' => null,
            'active_connections' => null,
            'active_queries' => null,
            'max_connections' => null,
            'connection_usage_percent' => null,
            'buffer_hit_percent' => null,
            'buffer_samples' => null,
            'deadlocks' => null,
            'transactions_committed' => null,
            'transactions_rolled_back' => null,
            'uptime_seconds' => null,
            'engine_slow_query_count' => null,
            'slow_query_threshold_ms' => $this->slowQueryThresholdMs(),
            'slow_query_pattern_count' => null,
            'slow_query_source' => null,
            'slow_query_period' => null,
            'slow_query_note' => null,
            'slow_queries' => [],
            'recommendations' => [],
        ];
    }

    private function slowQueryThresholdMs(): int
    {
        return max(1, (int) config('db-deeper.monitoring.slow_query_threshold_ms', 500));
    }

    private function slowQueryLimit(): int
    {
        return min(50, max(1, (int) config('db-deeper.monitoring.slow_query_limit', 10)));
    }

    private function percentage(?int $value, ?int $max): ?float
    {
        return $value === null || $max === null || $max <= 0
            ? null
            : round(($value / $max) * 100, 2);
    }

    private function integerOrNull(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }

    private function floatOrNull(mixed $value): ?float
    {
        return $value === null || $value === '' ? null : (float) $value;
    }

    private function driverLabel(string $driver): string
    {
        return match ($driver) {
            'mysql' => 'MySQL',
            'mariadb' => 'MariaDB',
            'pgsql' => 'PostgreSQL',
            'sqlite' => 'SQLite',
            'sqlsrv' => 'SQL Server',
            default => strtoupper($driver),
        };
    }
}
