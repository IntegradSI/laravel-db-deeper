<?php

namespace Integrad\DbDeeper\Services;

use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Throwable;

class DatabaseStatsService
{
    private const TABLE_LIMIT = 25;

    private const SUPPORTED_DRIVERS = ['mysql', 'mariadb', 'pgsql', 'sqlite', 'sqlsrv'];

    public function __construct(private DatabaseManager $databaseManager) {}

    /**
     * @return array{
     *     connection_name: string,
     *     driver: string,
     *     driver_label: string,
     *     supported: bool,
     *     connected: bool,
     *     latency_ms: float|null,
     *     database_name: string|null,
     *     server_version: string|null,
     *     table_count: int|null,
     *     view_count: int|null,
     *     database_size_bytes: int|null,
     *     data_size_bytes: int|null,
     *     index_size_bytes: int|null,
     *     unused_size_bytes: int|null,
     *     log_size_bytes: int|null,
     *     tables: list<array{name: string, row_count: int|null, data_size_bytes: int|null, index_size_bytes: int|null}>,
     *     statistics_available: bool,
     *     statistics_note: string|null
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
            $snapshot['supported'] = in_array($driver, self::SUPPORTED_DRIVERS, true);

            $startedAt = hrtime(true);
            $connection->select('SELECT 1');
            $snapshot['latency_ms'] = round((hrtime(true) - $startedAt) / 1_000_000, 2);
            $snapshot['connected'] = true;

            if (! $snapshot['supported']) {
                $snapshot['statistics_note'] = 'Este driver não possui coleta detalhada nesta versão.';

                return $snapshot;
            }

            $snapshot = array_replace($snapshot, $this->collectDriverStatistics($connection, $driver));
            $snapshot['statistics_available'] = true;
        } catch (Throwable) {
            $snapshot['statistics_note'] = $snapshot['connected']
                ? 'A conexão respondeu, mas o usuário do banco não tem acesso a todos os metadados necessários.'
                : 'Não foi possível consultar esta conexão. Verifique a configuração e a disponibilidade do banco.';
        }

        return $snapshot;
    }

    /**
     * @return array{
     *     connection_name: string,
     *     driver: string,
     *     driver_label: string,
     *     supported: bool,
     *     connected: bool,
     *     latency_ms: float|null,
     *     database_name: string|null,
     *     server_version: string|null,
     *     table_count: int|null,
     *     view_count: int|null,
     *     database_size_bytes: int|null,
     *     data_size_bytes: int|null,
     *     index_size_bytes: int|null,
     *     unused_size_bytes: int|null,
     *     log_size_bytes: int|null,
     *     tables: list<array{name: string, row_count: int|null, data_size_bytes: int|null, index_size_bytes: int|null}>,
     *     statistics_available: bool,
     *     statistics_note: string|null
     * }
     */
    private function emptySnapshot(string $connectionName, string $driver): array
    {
        return [
            'connection_name' => $connectionName,
            'driver' => $driver,
            'driver_label' => $this->driverLabel($driver),
            'supported' => in_array($driver, self::SUPPORTED_DRIVERS, true),
            'connected' => false,
            'latency_ms' => null,
            'database_name' => null,
            'server_version' => null,
            'table_count' => null,
            'view_count' => null,
            'database_size_bytes' => null,
            'data_size_bytes' => null,
            'index_size_bytes' => null,
            'unused_size_bytes' => null,
            'log_size_bytes' => null,
            'tables' => [],
            'statistics_available' => false,
            'statistics_note' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function collectDriverStatistics(Connection $connection, string $driver): array
    {
        return match ($driver) {
            'mysql', 'mariadb' => $this->collectMySqlStatistics($connection),
            'pgsql' => $this->collectPostgresStatistics($connection),
            'sqlite' => $this->collectSqliteStatistics($connection),
            'sqlsrv' => $this->collectSqlServerStatistics($connection),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function collectMySqlStatistics(Connection $connection): array
    {
        $database = $connection->selectOne('SELECT DATABASE() AS database_name, VERSION() AS server_version');
        $summary = $connection->selectOne(
            'SELECT COUNT(*) AS table_count,
                COALESCE(SUM(DATA_LENGTH), 0) AS data_size_bytes,
                COALESCE(SUM(INDEX_LENGTH), 0) AS index_size_bytes,
                COALESCE(SUM(DATA_FREE), 0) AS unused_size_bytes
             FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_type = ?',
            ['BASE TABLE'],
        );
        $views = $connection->selectOne(
            'SELECT COUNT(*) AS view_count FROM information_schema.views WHERE table_schema = DATABASE()',
        );
        $tables = $connection->select(
            'SELECT table_name AS name, table_rows AS row_count,
                data_length AS data_size_bytes, index_length AS index_size_bytes
             FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_type = ?
             ORDER BY (data_length + index_length) DESC, table_name
             LIMIT '.self::TABLE_LIMIT,
            ['BASE TABLE'],
        );

        $dataSize = (int) $summary->data_size_bytes;
        $indexSize = (int) $summary->index_size_bytes;

        return [
            'database_name' => $database->database_name,
            'server_version' => $database->server_version,
            'table_count' => (int) $summary->table_count,
            'view_count' => (int) $views->view_count,
            'database_size_bytes' => $dataSize + $indexSize,
            'data_size_bytes' => $dataSize,
            'index_size_bytes' => $indexSize,
            'unused_size_bytes' => (int) $summary->unused_size_bytes,
            'tables' => array_map(fn (object $table): array => $this->normalizeTable($table), $tables),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function collectPostgresStatistics(Connection $connection): array
    {
        $database = $connection->selectOne(
            'SELECT current_database() AS database_name, current_setting(?) AS server_version, pg_database_size(current_database()) AS database_size_bytes',
            ['server_version'],
        );
        $summary = $connection->selectOne(
            "SELECT COUNT(*) AS table_count
             FROM pg_class AS tables
             INNER JOIN pg_namespace AS schemas ON schemas.oid = tables.relnamespace
             WHERE tables.relkind IN ('r', 'p')
               AND schemas.nspname NOT IN ('pg_catalog', 'information_schema')
               AND schemas.nspname NOT LIKE 'pg_toast%'",
        );
        $views = $connection->selectOne(
            "SELECT COUNT(*) AS view_count
             FROM pg_class AS views
             INNER JOIN pg_namespace AS schemas ON schemas.oid = views.relnamespace
             WHERE views.relkind IN ('v', 'm')
               AND schemas.nspname NOT IN ('pg_catalog', 'information_schema')
               AND schemas.nspname NOT LIKE 'pg_toast%'",
        );
        $tables = $connection->select(
            "SELECT schemas.nspname || '.' || tables.relname AS name,
                CASE WHEN tables.reltuples < 0 THEN NULL ELSE tables.reltuples::bigint END AS row_count,
                pg_table_size(tables.oid) AS data_size_bytes,
                pg_indexes_size(tables.oid) AS index_size_bytes
             FROM pg_class AS tables
             INNER JOIN pg_namespace AS schemas ON schemas.oid = tables.relnamespace
             WHERE tables.relkind IN ('r', 'p')
               AND schemas.nspname NOT IN ('pg_catalog', 'information_schema')
               AND schemas.nspname NOT LIKE 'pg_toast%'
             ORDER BY pg_total_relation_size(tables.oid) DESC, name
             LIMIT ".self::TABLE_LIMIT,
        );

        return [
            'database_name' => $database->database_name,
            'server_version' => $database->server_version,
            'table_count' => (int) $summary->table_count,
            'view_count' => (int) $views->view_count,
            'database_size_bytes' => (int) $database->database_size_bytes,
            'data_size_bytes' => null,
            'index_size_bytes' => null,
            'unused_size_bytes' => null,
            'tables' => array_map(fn (object $table): array => $this->normalizeTable($table), $tables),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function collectSqliteStatistics(Connection $connection): array
    {
        $database = $connection->selectOne('SELECT sqlite_version() AS server_version');
        $pageCount = (int) $connection->selectOne('PRAGMA page_count')->page_count;
        $pageSize = (int) $connection->selectOne('PRAGMA page_size')->page_size;
        $freePages = (int) $connection->selectOne('PRAGMA freelist_count')->freelist_count;
        $summary = $connection->selectOne(
            "SELECT
                SUM(CASE WHEN type = 'table' AND name NOT LIKE 'sqlite_%' THEN 1 ELSE 0 END) AS table_count,
                SUM(CASE WHEN type = 'view' THEN 1 ELSE 0 END) AS view_count
             FROM sqlite_master",
        );
        $tables = $connection->select(
            "SELECT name FROM sqlite_master
             WHERE type = 'table' AND name NOT LIKE 'sqlite_%'
             ORDER BY name
             LIMIT ".self::TABLE_LIMIT,
        );

        return [
            'database_name' => $this->sqliteDatabaseLabel($connection->getDatabaseName()),
            'server_version' => $database->server_version,
            'table_count' => (int) $summary->table_count,
            'view_count' => (int) $summary->view_count,
            'database_size_bytes' => $pageCount * $pageSize,
            'data_size_bytes' => null,
            'index_size_bytes' => null,
            'unused_size_bytes' => $freePages * $pageSize,
            'tables' => array_map(fn (object $table): array => [
                'name' => $table->name,
                'row_count' => null,
                'data_size_bytes' => null,
                'index_size_bytes' => null,
            ], $tables),
            'statistics_note' => 'O SQLite informa o espaço do arquivo e páginas livres. O driver PDO padrão não estima linhas nem divide o tamanho por tabela.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function collectSqlServerStatistics(Connection $connection): array
    {
        $database = $connection->selectOne(
            'SELECT DB_NAME() AS database_name,
                CAST(SERVERPROPERTY(?) AS nvarchar(128)) AS server_version',
            ['ProductVersion'],
        );
        $summary = $connection->selectOne(
            'SELECT COUNT(*) AS table_count FROM sys.tables WHERE is_ms_shipped = 0',
        );
        $views = $connection->selectOne(
            'SELECT COUNT(*) AS view_count FROM sys.views WHERE is_ms_shipped = 0',
        );
        $sizes = $connection->selectOne(
            'SELECT
                COALESCE(SUM(CASE WHEN type = 0 THEN size ELSE 0 END), 0) * 8192 AS data_size_bytes,
                COALESCE(SUM(CASE WHEN type = 1 THEN size ELSE 0 END), 0) * 8192 AS log_size_bytes
             FROM sys.database_files',
        );

        try {
            $tables = $connection->select(
                'SELECT TOP ('.self::TABLE_LIMIT.')
                    QUOTENAME(SCHEMA_NAME(tables.schema_id)) + ? + QUOTENAME(tables.name) AS name,
                    SUM(CASE WHEN partitions.index_id IN (0, 1) THEN partitions.row_count ELSE 0 END) AS row_count,
                    SUM(CASE WHEN partitions.index_id IN (0, 1) THEN
                        partitions.in_row_data_page_count + partitions.lob_used_page_count + partitions.row_overflow_used_page_count
                        ELSE 0 END) * 8192 AS data_size_bytes,
                    SUM(CASE WHEN partitions.index_id > 1 THEN partitions.used_page_count ELSE 0 END) * 8192 AS index_size_bytes
                 FROM sys.tables AS tables
                 INNER JOIN sys.dm_db_partition_stats AS partitions ON partitions.object_id = tables.object_id
                 WHERE tables.is_ms_shipped = 0
                 GROUP BY tables.schema_id, tables.name
                 ORDER BY SUM(partitions.used_page_count) DESC, tables.name',
                ['.'],
            );
            $statisticsNote = null;
        } catch (Throwable) {
            $tables = array_map(fn (object $table): array => [
                'name' => $table->name,
                'row_count' => null,
                'data_size_bytes' => null,
                'index_size_bytes' => null,
            ], $connection->select(
                'SELECT TOP ('.self::TABLE_LIMIT.')
                    QUOTENAME(SCHEMA_NAME(schema_id)) + ? + QUOTENAME(name) AS name
                 FROM sys.tables WHERE is_ms_shipped = 0 ORDER BY name',
                ['.'],
            ));
            $statisticsNote = 'A conta conectou, mas o SQL Server restringiu as estatísticas detalhadas das partições.';
        }

        $dataSize = (int) $sizes->data_size_bytes;
        $logSize = (int) $sizes->log_size_bytes;

        return [
            'database_name' => $database->database_name,
            'server_version' => $database->server_version,
            'table_count' => (int) $summary->table_count,
            'view_count' => (int) $views->view_count,
            'database_size_bytes' => $dataSize + $logSize,
            'data_size_bytes' => $dataSize,
            'index_size_bytes' => null,
            'unused_size_bytes' => null,
            'log_size_bytes' => $logSize,
            'tables' => array_map(fn (object|array $table): array => is_array($table)
                ? $table
                : $this->normalizeTable($table), $tables),
            'statistics_note' => $statisticsNote,
        ];
    }

    /**
     * @return array{name: string, row_count: int|null, data_size_bytes: int|null, index_size_bytes: int|null}
     */
    private function normalizeTable(object $table): array
    {
        return [
            'name' => (string) $table->name,
            'row_count' => $table->row_count === null ? null : (int) $table->row_count,
            'data_size_bytes' => $table->data_size_bytes === null ? null : (int) $table->data_size_bytes,
            'index_size_bytes' => $table->index_size_bytes === null ? null : (int) $table->index_size_bytes,
        ];
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

    private function sqliteDatabaseLabel(string $database): string
    {
        if ($database === ':memory:') {
            return 'Memória';
        }

        return basename(str_replace('\\', '/', $database));
    }
}
