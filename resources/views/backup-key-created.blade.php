@extends('db-deeper::layouts.panel', [
    'activePage' => 'backup-keys',
    'pageTitle' => 'Nova chave de backup',
    'liveRefreshUrl' => $liveRefreshUrl,
    'refreshInterval' => $refreshInterval,
])

@section('content')
    <section class="mx-auto w-full max-w-3xl">
        <div role="status" class="alert alert-success mb-5"><i data-lucide="circle-check" class="size-5" aria-hidden="true"></i><div><h1 class="font-semibold">Chave criada e ativada</h1><p class="text-sm">{{ $keyName }} será usada nos próximos backups.</p></div></div>
        <div class="card card-border bg-base-100">
            <div class="card-body gap-4">
                <h2 class="card-title text-base">Copie e guarde agora</h2>
                <p class="text-sm leading-5 text-base-content/65">Esta é a única exibição do segredo. A cópia no banco fica cifrada com a APP_KEY, mas guarde a chave e a APP_KEY fora do servidor para uma recuperação completa.</p>
                <code id="generated-backup-key" class="break-all rounded-field border border-base-300 bg-base-200 p-4 font-mono text-sm leading-6">{{ $backupKey }}</code>
                <p class="font-mono text-xs text-base-content/55">SHA-256 · {{ substr($keyFingerprint, 0, 16) }}…</p>
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" class="btn btn-primary btn-sm" data-copy-target="generated-backup-key"><i data-lucide="copy" class="size-4" aria-hidden="true"></i>Copiar chave</button>
                    <a href="{{ route('db-deeper.backups.keys.index') }}" class="btn btn-ghost btn-sm">Concluir</a>
                </div>
                <p class="text-sm text-base-content/65" data-copy-status role="status" aria-live="polite"></p>
            </div>
        </div>
    </section>
@endsection
