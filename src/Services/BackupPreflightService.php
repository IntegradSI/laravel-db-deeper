<?php

namespace Integrad\DbDeeper\Services;

use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Throwable;

class BackupPreflightService
{
    public function __construct(
        private FilesystemFactory $filesystem,
        private BackupEncryptionKeyService $encryptionKeys,
    ) {}

    /**
     * @return array{diagnostic_id: string, ready: bool, checked_at: string, checks: list<array{id: string, label: string, status: string, message: string}>}
     */
    public function diagnose(string $connectionName, string $diskName, ?string $destinationPath): array
    {
        $diagnosticId = (string) Str::ulid();
        $connectionConfig = config("database.connections.{$connectionName}");
        $driver = is_array($connectionConfig) ? (string) ($connectionConfig['driver'] ?? '') : '';
        $checks = [
            $this->checkEncryptionKey($diagnosticId),
            $this->checkDatabase($diagnosticId, $connectionName),
            $this->checkDumpBinary($diagnosticId, $connectionName, $driver),
            $this->checkDisk($diagnosticId, $diskName, $destinationPath),
        ];

        return [
            'diagnostic_id' => $diagnosticId,
            'ready' => collect($checks)->every(static fn (array $check): bool => $check['status'] === 'passed'),
            'checked_at' => now()->toIso8601String(),
            'checks' => $checks,
        ];
    }

    /**
     * @return array{id: string, label: string, status: string, message: string}
     */
    private function checkEncryptionKey(string $diagnosticId): array
    {
        try {
            $this->encryptionKeys->active();

            return $this->result('key', 'Chave de criptografia', 'passed', 'Chave ativa encontrada.');
        } catch (Throwable $exception) {
            Log::warning('DB Deeper preflight could not resolve the active encryption key.', [
                'diagnostic_id' => $diagnosticId,
                'exception' => $exception::class,
            ]);

            return $this->result('key', 'Chave de criptografia', 'failed', 'Nenhuma chave ativa válida foi encontrada. Crie ou ative uma chave na aba Criptografia.');
        }
    }

    /**
     * @return array{id: string, label: string, status: string, message: string}
     */
    private function checkDatabase(string $diagnosticId, string $connectionName): array
    {
        try {
            DB::connection($connectionName)->getPdo();

            return $this->result('database', 'Conexão com o banco', 'passed', 'Conexão estabelecida com o banco selecionado.');
        } catch (Throwable $exception) {
            Log::warning('DB Deeper preflight could not connect to the selected database.', [
                'diagnostic_id' => $diagnosticId,
                'connection_name' => $connectionName,
                'exception' => $exception::class,
            ]);

            return $this->result('database', 'Conexão com o banco', 'failed', 'Não foi possível conectar. Confira host, porta, credenciais e disponibilidade do banco.');
        }
    }

    /**
     * @return array{id: string, label: string, status: string, message: string}
     */
    private function checkDumpBinary(string $diagnosticId, string $connectionName, string $driver): array
    {
        if (! in_array($driver, ['mysql', 'mariadb', 'pgsql'], true)) {
            return $this->result('dump', 'Ferramenta de exportação', 'failed', 'O driver selecionado não possui ferramenta de exportação compatível.');
        }

        $configKey = $driver === 'pgsql' ? 'pg_dump_binary' : 'mysql_dump_binary';
        $defaultBinary = $driver === 'pgsql' ? 'pg_dump' : 'mysqldump';
        $binary = (string) config("db-deeper.backup.{$configKey}", $defaultBinary);

        try {
            $process = new Process([$binary, '--version']);
            $process->setTimeout(5);
            $process->run();

            if ($process->isSuccessful()) {
                return $this->result('dump', 'Ferramenta de exportação', 'passed', 'Executável disponível ('.basename($binary).').');
            }
        } catch (Throwable $exception) {
            Log::warning('DB Deeper preflight could not verify the database dump executable.', [
                'diagnostic_id' => $diagnosticId,
                'connection_name' => $connectionName,
                'exception' => $exception::class,
            ]);
        }

        Log::warning('DB Deeper preflight found an unavailable database dump executable.', [
            'diagnostic_id' => $diagnosticId,
            'connection_name' => $connectionName,
            'driver' => $driver,
        ]);

        return $this->result('dump', 'Ferramenta de exportação', 'failed', "Não foi possível executar {$defaultBinary} --version. Instale a ferramenta ou ajuste db-deeper.backup.{$configKey}.");
    }

    /**
     * @return array{id: string, label: string, status: string, message: string}
     */
    private function checkDisk(string $diagnosticId, string $diskName, ?string $destinationPath): array
    {
        $basePath = trim((string) ($destinationPath ?: config('db-deeper.backup.path', 'database-backups')), '/');
        $probePath = trim($basePath.'/.db-deeper-diagnostic-'.Str::uuid().'.tmp', '/');
        $written = false;

        try {
            $disk = $this->filesystem->disk($diskName);
            $written = $disk->put($probePath, random_bytes(32));

            if (! $written) {
                throw new \RuntimeException('The selected disk rejected the diagnostic write.');
            }

            if (! $disk->delete($probePath)) {
                throw new \RuntimeException('The diagnostic marker could not be removed.');
            }

            return $this->result('disk', 'Escrita no disco', 'passed', 'O disco aceitou e removeu o arquivo temporário de diagnóstico.');
        } catch (Throwable $exception) {
            if ($written) {
                try {
                    $this->filesystem->disk($diskName)->delete($probePath);
                } catch (Throwable) {
                    // Preserve the original failure while making a best-effort cleanup.
                }
            }

            Log::warning('DB Deeper preflight could not verify the selected backup disk.', [
                'diagnostic_id' => $diagnosticId,
                'disk_name' => $diskName,
                'probe_path' => $probePath,
                'exception' => $exception::class,
            ]);

            return $this->result('disk', 'Escrita no disco', 'failed', 'Não foi possível gravar e remover o arquivo temporário. Confira credenciais, bucket, região e permissão de escrita/exclusão.');
        }
    }

    /**
     * @return array{id: string, label: string, status: string, message: string}
     */
    private function result(string $id, string $label, string $status, string $message): array
    {
        return compact('id', 'label', 'status', 'message');
    }
}
