<?php

namespace Integrad\DbDeeper\Http\Controllers;

use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Integrad\DbDeeper\Http\Requests\RunBackupRequest;
use Integrad\DbDeeper\Models\Backup;
use Integrad\DbDeeper\Services\BackupRunService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class BackupController
{
    public function store(RunBackupRequest $request, BackupRunService $backupRuns): RedirectResponse
    {
        try {
            $backup = $backupRuns->queue(
                $request->string('connection_name')->toString(),
                $request->string('disk_name')->toString(),
                $request->input('destination_path'),
                (string) $request->user()?->getAuthIdentifier(),
            );
        } catch (Throwable) {
            return back()->with('error', 'Não foi possível iniciar o backup. Confira a chave, a fila e a conexão configuradas.');
        }

        return back()->with('success', "Execução #{$backup->id} enviada para processamento.");
    }

    public function download(Backup $backup, FilesystemFactory $filesystem): StreamedResponse
    {
        abort_unless($backup->status === Backup::STATUS_SUCCEEDED && is_string($backup->path), 404);

        $disk = $filesystem->disk($backup->disk_name);
        abort_unless($disk->exists($backup->path), 404);

        $stream = $disk->readStream($backup->path);
        abort_unless(is_resource($stream), 404);

        $filename = Str::of(basename($backup->path))->replace(['/', '\\'], '-')->toString();

        return response()->streamDownload(function () use ($stream): void {
            try {
                fpassthru($stream);
            } finally {
                fclose($stream);
            }
        }, $filename, [
            'Content-Type' => 'application/octet-stream',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
