<?php

namespace Integrad\DbDeeper\Console\Commands;

use Illuminate\Console\Command;

class GenerateBackupKeyCommand extends Command
{
    protected $signature = 'db-deeper:backup-key';

    protected $description = 'Gera uma chave de 256 bits para criptografar backups do DB Deeper.';

    public function handle(): int
    {
        $this->components->warn('Guarde esta chave em um gerenciador de segredos. Backups existentes não poderão ser lidos sem ela.');
        $this->line(base64_encode(random_bytes(32)));

        return self::SUCCESS;
    }
}
