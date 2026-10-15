<?php

namespace Integrad\DbDeeper\Console\Commands;

use Illuminate\Console\Command;
use Integrad\DbDeeper\Services\DatabaseBackupService;
use Throwable;

class GenerateDatabaseBackupCommand extends Command
{
    protected $signature = 'db-deeper:backup {--connection= : Nome da conexão configurada no Laravel} {--disk= : Disco de arquivos configurado no Laravel} {--path= : Caminho opcional dentro do disco}';

    protected $description = 'Gera um backup criptografado do banco de dados e envia ao disco configurado.';

    public function handle(DatabaseBackupService $backups): int
    {
        try {
            $backup = $backups->create(
                $this->option('connection') ?: null,
                $this->option('disk') ?: null,
                $this->option('path') ?: null,
            );
        } catch (Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info('Backup criptografado enviado com sucesso.');
        $this->line('Arquivo: '.$backup['path']);
        $this->line('Conexão: '.$backup['connection'].' ('.$backup['driver'].')');
        $this->line('Tamanho: '.number_format($backup['bytes']).' bytes');

        return self::SUCCESS;
    }
}
