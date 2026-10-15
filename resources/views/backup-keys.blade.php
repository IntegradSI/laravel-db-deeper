@extends('db-deeper::layouts.panel', [
    'activePage' => 'backup-keys',
    'pageTitle' => 'Criptografia dos backups',
    'liveRefreshUrl' => $liveRefreshUrl,
    'refreshInterval' => $refreshInterval,
])

@section('content')
    <section class="mb-6 flex flex-col gap-4 border-b border-base-300 pb-5 sm:flex-row sm:items-end sm:justify-between">
        <div><h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Chaves de criptografia</h1><p class="mt-2 max-w-3xl text-sm leading-6 text-base-content/65">Gerencie a chave usada nos próximos backups e mantenha as anteriores para preservar o acesso aos arquivos já criados.</p></div>
        <a href="{{ route('db-deeper.backups.index') }}" class="btn btn-ghost btn-sm">Voltar para backups</a>
    </section>

    @if ($keyStatus['source'] === 'missing')
        <div role="alert" class="alert alert-warning mb-5"><i data-lucide="circle-alert" class="size-5" aria-hidden="true"></i><div><h2 class="font-semibold">Nenhuma chave ativa</h2><p class="text-sm">Crie uma chave para permitir novas execuções de backup.</p></div></div>
    @else
        <section class="mb-5 flex flex-col gap-3 border-y border-base-300 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div><h2 class="font-semibold">Chave usada nas próximas execuções</h2><p class="mt-1 text-sm text-base-content/60">{{ $keyStatus['name'] }} · {{ $keyStatus['source'] === 'dashboard' ? 'guardada cifrada no banco' : 'definida pela configuração do pacote' }}</p><p class="mt-1 font-mono text-xs text-base-content/55">SHA-256 · {{ substr($keyStatus['fingerprint'], 0, 16) }}…</p></div>
            <span class="badge badge-success badge-sm">Ativa</span>
        </section>
    @endif

    <div role="alert" class="alert alert-warning mb-6 items-start">
        <i data-lucide="shield-check" class="mt-0.5 size-5" aria-hidden="true"></i>
        <div><h2 class="font-semibold">Preserve a APP_KEY e as chaves de backup</h2><p class="mt-1 text-sm leading-5">Chaves gerenciadas por esta tela ficam cifradas com a APP_KEY da aplicação. Guarde a APP_KEY e um backup separado dela fora do banco e do mesmo disco de backups. Sem essas chaves, os arquivos cifrados não poderão ser recuperados. A rotação não reescreve nem remove backups antigos.</p></div>
    </div>

    <div class="grid min-w-0 grid-cols-1 gap-6 xl:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">
        <section aria-labelledby="rotate-key-title" class="card card-border min-w-0 bg-base-100">
            <div class="card-body gap-4">
                <div class="flex items-center gap-3"><span class="flex size-9 shrink-0 items-center justify-center rounded-field bg-base-200 text-base-content/70"><i data-lucide="shield-check" class="size-4" aria-hidden="true"></i></span><div><h2 id="rotate-key-title" class="card-title text-base">Criar e ativar uma chave</h2><p class="text-sm text-base-content/60">A chave anterior será preservada no histórico.</p></div></div>
                <form method="POST" action="{{ route('db-deeper.backups.keys.store') }}" class="grid gap-4">
                    @csrf
                    <fieldset class="fieldset">
                        <label for="key-name" class="label">Nome para identificar a chave</label>
                        <input id="key-name" name="name" type="text" class="input input-sm w-full" maxlength="120" required value="{{ old('name') }}" placeholder="Ex.: Chave 2026-10">
                        @error('name') <span class="text-sm text-error">{{ $message }}</span> @enderror
                    </fieldset>
                    <p class="text-sm leading-5 text-base-content/60">O sistema gera uma chave aleatória de 256 bits. Ela será mostrada uma única vez após salvar.</p>
                    <button type="submit" class="btn btn-primary btn-sm w-fit"><i data-lucide="refresh-cw" class="size-4" aria-hidden="true"></i>Criar e ativar</button>
                </form>
            </div>
        </section>

        <section aria-labelledby="key-history-title" class="min-w-0">
            <div class="mb-3"><h2 id="key-history-title" class="text-lg font-semibold">Histórico de chaves</h2><p class="mt-1 text-sm text-base-content/60">A chave ativa e as anteriores permanecem cifradas para manter acesso aos respectivos backups.</p></div>
            @if ($keyStatus['source'] === 'config')
                <div class="mb-3 flex flex-col gap-2 border-y border-base-300 py-3 sm:flex-row sm:items-center sm:justify-between"><div><p class="font-medium">Chave legada da configuração</p><p class="font-mono text-xs text-base-content/55">SHA-256 · {{ substr($keyStatus['fingerprint'], 0, 16) }}…</p></div><span class="badge badge-success badge-sm">Ativa</span></div>
            @endif
            @if ($keys->isEmpty() && $keyStatus['source'] !== 'config')
                <div class="flex items-start gap-3 border-y border-base-300 py-6"><i data-lucide="circle-help" class="mt-0.5 size-5 text-base-content/50" aria-hidden="true"></i><div><p class="font-medium">Nenhuma chave registrada</p><p class="mt-1 text-sm text-base-content/60">Crie a primeira chave para iniciar os backups cifrados.</p></div></div>
            @else
                <ul class="divide-y divide-base-300 border-y border-base-300">
                    @foreach ($keys as $key)
                        <li class="flex flex-col gap-2 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div><p class="font-medium">{{ $key->name }}</p><p class="mt-1 font-mono text-xs text-base-content/55">SHA-256 · {{ substr($key->fingerprint, 0, 16) }}…</p><p class="mt-1 text-xs text-base-content/55">Criada em {{ $key->created_at?->format('d/m/Y H:i') }}</p></div>
                            <span class="badge badge-sm {{ $key->is_active ? 'badge-success' : 'badge-ghost' }}">{{ $key->is_active ? 'Ativa' : 'Preservada' }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
