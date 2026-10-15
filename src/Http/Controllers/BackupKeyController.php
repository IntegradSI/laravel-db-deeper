<?php

namespace Integrad\DbDeeper\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\View\Factory as ViewFactory;
use Integrad\DbDeeper\Http\Requests\StoreBackupKeyRequest;
use Integrad\DbDeeper\Models\BackupKey;
use Integrad\DbDeeper\Services\BackupEncryptionKeyService;
use Symfony\Component\HttpFoundation\Response;

class BackupKeyController
{
    public function index(ViewFactory $views, BackupEncryptionKeyService $keys): View
    {
        return $views->make('db-deeper::backup-keys', [
            'activePage' => 'backup-keys',
            'pageTitle' => 'Criptografia dos backups',
            'autoRefresh' => false,
            'liveRefreshUrl' => '',
            'refreshInterval' => 30,
            'databaseConnection' => config('db-deeper.backup.connection') ?: config('database.default'),
            'keyStatus' => $keys->currentStatus(),
            'keys' => BackupKey::query()->select(['id', 'name', 'fingerprint', 'is_active', 'activated_at', 'created_at'])->latest()->get(),
        ]);
    }

    public function store(StoreBackupKeyRequest $request, BackupEncryptionKeyService $keys): Response
    {
        $created = $keys->rotate($request->string('name')->toString());

        return response()->view('db-deeper::backup-key-created', [
            'activePage' => 'backup-keys',
            'pageTitle' => 'Nova chave de backup',
            'autoRefresh' => false,
            'liveRefreshUrl' => '',
            'refreshInterval' => 30,
            'databaseConnection' => config('db-deeper.backup.connection') ?: config('database.default'),
            'backupKey' => $created['secret'],
            'keyName' => $created['key']->name,
            'keyFingerprint' => $created['key']->fingerprint,
        ], 200, [
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }
}
