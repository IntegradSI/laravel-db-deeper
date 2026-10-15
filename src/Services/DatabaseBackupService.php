<?php

namespace Integrad\DbDeeper\Services;

use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use RuntimeException;

class DatabaseBackupService
{
    public function __construct(
        private FilesystemFactory $filesystem,
        private BackupEncryptionService $encryption,
        private BackupEncryptionKeyService $encryptionKeys,
    ) {}

    /**
     * @return array{path: string, connection: string, driver: string, bytes: int, chunks: int, checksum: string, key_id: int|null, key_fingerprint: string}
     */
    public function create(?string $connectionName = null, ?string $diskName = null, ?string $path = null): array
    {
        $connectionName ??= (string) config('db-deeper.backup.connection', config('database.default'));
        $diskName ??= (string) config('db-deeper.backup.disk', 'local');
        $destinationPath = trim((string) (($path !== null && $path !== '') ? $path : config('db-deeper.backup.path', 'database-backups')), '/');
        $connectionConfig = config("database.connections.{$connectionName}");

        if (! is_array($connectionConfig)) {
            throw new RuntimeException("A conexão [{$connectionName}] não está configurada.");
        }

        $driver = (string) ($connectionConfig['driver'] ?? '');

        if (! in_array($driver, ['mysql', 'mariadb', 'pgsql'], true)) {
            throw new RuntimeException("O driver [{$driver}] ainda não possui exportador de backup nesta versão.");
        }

        $activeKey = $this->encryptionKeys->active();

        $databaseName = $this->databaseName($connectionName, $driver, $connectionConfig);
        $timestamp = now()->utc()->format('Ymd\THis\Z-u');
        $filename = sprintf('%s-%s-%s-%s.sql.gz.enc', $this->safeFilename($databaseName), $driver, $timestamp, bin2hex(random_bytes(3)));
        $relativePath = trim($destinationPath.'/'.$filename, '/');

        if ($destinationPath !== '' && preg_match('#(^|[/\\\\])\.\.?([/\\\\]|$)#', $destinationPath) === 1) {
            throw new RuntimeException('O caminho do backup não pode conter segmentos relativos.');
        }
        $temporaryPath = tempnam(sys_get_temp_dir(), 'db-deeper-');
        $stderrPath = tempnam(sys_get_temp_dir(), 'db-deeper-error-');
        $credentialPath = null;
        $originalPgPassfile = getenv('PGPASSFILE');
        $process = null;
        $pipes = [];

        if ($temporaryPath === false || $stderrPath === false) {
            if (is_string($temporaryPath) && is_file($temporaryPath)) {
                @unlink($temporaryPath);
            }

            if (is_string($stderrPath) && is_file($stderrPath)) {
                @unlink($stderrPath);
            }

            throw new RuntimeException('Não foi possível criar arquivos temporários privados para o backup.');
        }

        @chmod($temporaryPath, 0600);
        @chmod($stderrPath, 0600);

        try {
            $command = $driver === 'pgsql'
                ? $this->postgresCommand($connectionConfig, $databaseName, $credentialPath)
                : $this->mysqlCommand($connectionConfig, $databaseName, $credentialPath);

            $process = proc_open(
                $command,
                [0 => ['file', '/dev/null', 'r'], 1 => ['pipe', 'w'], 2 => ['file', $stderrPath, 'w']],
                $pipes,
                null,
                null,
                ['bypass_shell' => true],
            );

            if (! is_resource($process)) {
                throw new RuntimeException('Não foi possível iniciar a ferramenta de dump do banco.');
            }

            $encryptedStream = fopen($temporaryPath, 'wb');
            if (! is_resource($encryptedStream)) {
                throw new RuntimeException('Não foi possível abrir o arquivo temporário do backup.');
            }

            try {
                $chunks = $this->encryption->writeEncryptedDump(
                    $pipes[1],
                    $encryptedStream,
                    $activeKey['secret'],
                    (int) config('db-deeper.backup.chunk_size', 1048576),
                );
            } finally {
                fclose($pipes[1]);
                fclose($encryptedStream);
            }

            $exitCode = proc_close($process);
            $process = null;

            if ($exitCode !== 0) {
                $details = trim((string) file_get_contents($stderrPath));
                throw new RuntimeException($this->safeProcessError($details, $driver));
            }

            $stream = fopen($temporaryPath, 'rb');
            $checksum = hash_file('sha256', $temporaryPath);

            if (! is_resource($stream)) {
                throw new RuntimeException('Não foi possível ler o backup criptografado para envio.');
            }

            if ($checksum === false) {
                fclose($stream);
                throw new RuntimeException('Não foi possível verificar a integridade local do backup criptografado.');
            }

            try {
                $stored = $this->filesystem->disk($diskName)->put($relativePath, $stream);
            } finally {
                fclose($stream);
            }

            if (! $stored) {
                throw new RuntimeException("Não foi possível enviar o backup para o disco [{$diskName}].");
            }

            return [
                'path' => $relativePath,
                'connection' => $connectionName,
                'driver' => $driver,
                'bytes' => (int) filesize($temporaryPath),
                'chunks' => $chunks,
                'checksum' => $checksum,
                'key_id' => $activeKey['key_id'],
                'key_fingerprint' => $activeKey['fingerprint'],
            ];
        } finally {
            foreach ($pipes as $pipe) {
                if (is_resource($pipe)) {
                    fclose($pipe);
                }
            }

            if (is_resource($process)) {
                proc_terminate($process);
                proc_close($process);
            }

            if (is_string($credentialPath) && is_file($credentialPath)) {
                @unlink($credentialPath);
            }

            if ($originalPgPassfile === false) {
                putenv('PGPASSFILE');
            } else {
                putenv('PGPASSFILE='.$originalPgPassfile);
            }

            @unlink($temporaryPath);
            @unlink($stderrPath);
        }
    }

    /**
     * @param  array<string, mixed>  $config
     * @return list<string>
     */
    private function mysqlCommand(array $config, string $database, ?string &$credentialPath): array
    {
        $credentials = [];

        foreach (['host' => 'host', 'port' => 'port', 'username' => 'user', 'password' => 'password', 'unix_socket' => 'socket'] as $source => $target) {
            if (isset($config[$source]) && $config[$source] !== '') {
                $value = (string) $config[$source];

                if (str_contains($value, "\n") || str_contains($value, "\r")) {
                    throw new RuntimeException('A configuração da conexão contém quebra de linha inválida.');
                }

                $credentials[] = $target.'="'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
            }
        }

        $credentialPath = $this->writePrivateFile("[client]\n".implode("\n", $credentials)."\n");

        return [
            (string) config('db-deeper.backup.mysql_dump_binary', 'mysqldump'),
            '--defaults-extra-file='.$credentialPath,
            '--single-transaction',
            '--quick',
            '--hex-blob',
            '--routines',
            '--triggers',
            '--events',
            '--databases='.$database,
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return list<string>
     */
    private function postgresCommand(array $config, string $database, ?string &$credentialPath): array
    {
        $command = [(string) config('db-deeper.backup.pg_dump_binary', 'pg_dump'), '--format=plain', '--no-owner', '--no-privileges'];

        foreach (['host' => '--host', 'port' => '--port', 'username' => '--username'] as $source => $option) {
            if (isset($config[$source]) && $config[$source] !== '') {
                $command[] = $option;
                $command[] = (string) $config[$source];
            }
        }

        if (! empty($config['password'])) {
            $host = (string) (($config['host'] ?? '') ?: 'localhost');
            $port = (string) (($config['port'] ?? '') ?: '5432');
            $username = (string) (($config['username'] ?? '') ?: '');
            $password = (string) $config['password'];

            foreach ([$host, $port, $database, $username, $password] as $value) {
                if (str_contains($value, "\n") || str_contains($value, "\r")) {
                    throw new RuntimeException('A configuração da conexão contém quebra de linha inválida.');
                }
            }

            $credentialPath = $this->writePrivateFile(implode(':', array_map($this->escapePgPass(...), [
                $host,
                $port,
                $database,
                $username,
                $password,
            ]))."\n");
            putenv('PGPASSFILE='.$credentialPath);
        }

        $command[] = '--dbname='.$database;

        return $command;
    }

    /** @param array<string, mixed> $config */
    private function databaseName(string $connectionName, string $driver, array $config): string
    {
        if ($driver === 'pgsql') {
            return (string) ($config['database'] ?? $connectionName);
        }

        return (string) ($config['database'] ?? $connectionName);
    }

    private function writePrivateFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'db-deeper-auth-');

        if ($path === false || file_put_contents($path, $contents, LOCK_EX) === false) {
            if (is_string($path) && is_file($path)) {
                @unlink($path);
            }

            throw new RuntimeException('Não foi possível criar o arquivo temporário de autenticação.');
        }

        @chmod($path, 0600);

        return $path;
    }

    private function escapePgPass(string $value): string
    {
        return str_replace(['\\', ':'], ['\\\\', '\\:'], $value);
    }

    private function safeFilename(string $value): string
    {
        $filename = preg_replace('/[^A-Za-z0-9._-]+/', '-', $value);

        return trim((string) $filename, '-_.') ?: 'database';
    }

    private function safeProcessError(string $details, string $driver): string
    {
        $details = preg_replace_callback(
            '/(?:password|passfile|defaults-extra-file)\s*[:=]\s*[^\s]+/i',
            static fn (array $matches): string => preg_replace('/=.*/', '=[redacted]', $matches[0]) ?? '[redacted]',
            $details,
        ) ?? '';
        $details = trim($details);

        if ($details === '') {
            return "A ferramenta de backup [{$driver}] terminou com erro. Confira se ela está instalada e se a conexão tem acesso ao banco.";
        }

        return mb_substr($details, 0, 1000);
    }
}
