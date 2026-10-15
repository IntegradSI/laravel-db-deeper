<?php

namespace Integrad\DbDeeper\Services;

use Illuminate\Support\Facades\DB;
use Integrad\DbDeeper\Models\BackupKey;
use RuntimeException;

class BackupEncryptionKeyService
{
    /**
     * @return array{key_id: int|null, secret: string, fingerprint: string, source: string}
     */
    public function active(): array
    {
        $key = BackupKey::query()->where('is_active', true)->first();

        if ($key) {
            return [
                'key_id' => $key->id,
                'secret' => $key->secret,
                'fingerprint' => $key->fingerprint,
                'source' => 'dashboard',
            ];
        }

        $secret = config('db-deeper.backup.encryption_key');

        if (! is_string($secret) || $secret === '') {
            throw new RuntimeException('Crie uma chave de criptografia na tela de backups antes de gerar backups.');
        }

        $decoded = base64_decode($secret, true);

        if ($decoded === false || strlen($decoded) !== 32) {
            throw new RuntimeException('A chave definida em db-deeper.backup.encryption_key não é uma chave Base64 válida de 256 bits.');
        }

        return [
            'key_id' => null,
            'secret' => $secret,
            'fingerprint' => hash('sha256', $decoded),
            'source' => 'config',
        ];
    }

    /**
     * @return array{key: BackupKey, secret: string}
     */
    public function rotate(string $name): array
    {
        $secret = base64_encode(random_bytes(32));
        $fingerprint = hash('sha256', base64_decode($secret, true));

        $key = DB::transaction(function () use ($name, $secret, $fingerprint): BackupKey {
            BackupKey::query()->where('is_active', true)->lockForUpdate()->first();
            BackupKey::query()->lockForUpdate()->update(['is_active' => false]);

            return BackupKey::query()->create([
                'name' => $name,
                'secret' => $secret,
                'fingerprint' => $fingerprint,
                'is_active' => true,
                'activated_at' => now(),
            ]);
        });

        return ['key' => $key, 'secret' => $secret];
    }

    /**
     * @return array{source: string, fingerprint: string|null, name: string|null, created_at: mixed}
     */
    public function currentStatus(): array
    {
        $key = BackupKey::query()->where('is_active', true)->first();

        if ($key) {
            return [
                'source' => 'dashboard',
                'fingerprint' => $key->fingerprint,
                'name' => $key->name,
                'created_at' => $key->created_at,
            ];
        }

        $secret = config('db-deeper.backup.encryption_key');
        $decoded = is_string($secret) ? base64_decode($secret, true) : false;

        return [
            'source' => $decoded !== false && strlen($decoded) === 32 ? 'config' : 'missing',
            'fingerprint' => $decoded !== false && strlen($decoded) === 32 ? hash('sha256', $decoded) : null,
            'name' => $decoded !== false && strlen($decoded) === 32 ? 'Chave legada da configuração' : null,
            'created_at' => null,
        ];
    }
}
