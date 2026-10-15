<?php

namespace Integrad\DbDeeper\Services;

use Illuminate\Database\DatabaseManager;
use Throwable;

class LaravelSessionActivityService
{
    private const ACTIVITY_WINDOW_SECONDS = 300;

    public function __construct(private DatabaseManager $databaseManager) {}

    /**
     * @return array{
     *     available: bool,
     *     driver: string,
     *     active_users: int|null,
     *     active_sessions: int|null,
     *     activity_window_minutes: int,
     *     note: string|null
     * }
     */
    public function collect(): array
    {
        $driver = (string) config('session.driver', 'unknown');
        $snapshot = [
            'available' => false,
            'driver' => $driver,
            'active_users' => null,
            'active_sessions' => null,
            'activity_window_minutes' => 5,
            'note' => null,
        ];

        if ($driver !== 'database') {
            $snapshot['note'] = "As sessões Laravel usam o driver {$driver}; a contagem de usuários ativos não é exposta pelo banco.";

            return $snapshot;
        }

        $connectionName = (string) (config('session.connection') ?: config('database.default'));
        $tableName = (string) config('session.table', 'sessions');

        try {
            $connection = $this->databaseManager->connection($connectionName);
            $schema = $connection->getSchemaBuilder();

            if (! $schema->hasTable($tableName)) {
                $snapshot['note'] = "A tabela de sessões configurada ({$tableName}) não existe na conexão {$connectionName}.";

                return $snapshot;
            }

            if (! $schema->hasColumns($tableName, ['user_id', 'last_activity'])) {
                $snapshot['note'] = 'A tabela de sessões não tem as colunas user_id e last_activity necessárias para a contagem.';

                return $snapshot;
            }

            $activeSessions = $connection->table($tableName)
                ->where('last_activity', '>=', time() - self::ACTIVITY_WINDOW_SECONDS)
                ->whereNotNull('user_id');

            $snapshot['available'] = true;
            $snapshot['active_sessions'] = (clone $activeSessions)->count();
            $snapshot['active_users'] = $activeSessions->distinct()->count('user_id');
            $snapshot['note'] = 'Usuários únicos com sessão autenticada registrada nos últimos 5 minutos; visitantes sem login são excluídos.';
        } catch (Throwable) {
            $snapshot['note'] = 'Não foi possível ler a tabela de sessões. Verifique se a conexão configurada para sessões está acessível.';
        }

        return $snapshot;
    }
}
