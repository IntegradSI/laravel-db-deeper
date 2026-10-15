@extends('db-deeper::layouts.panel', [
    'activePage' => 'backups',
    'pageTitle' => 'Backups',
    'liveRefreshUrl' => $liveRefreshUrl,
    'refreshInterval' => $refreshInterval,
])

@section('content')
    <section class="mb-6 flex flex-col gap-4 border-b border-base-300 pb-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Backups e rotinas</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-base-content/65">Agende cópias cifradas, acompanhe cada execução e baixe os arquivos pelo painel.</p>
        </div>
        <a href="{{ route('db-deeper.backups.keys.index') }}" class="btn btn-outline btn-sm"><i data-lucide="shield-check" class="size-4" aria-hidden="true"></i>Gerenciar criptografia</a>
    </section>

    @if (session('success'))
        <div role="status" class="alert alert-success mb-5"><i data-lucide="circle-check" class="size-5" aria-hidden="true"></i><span>{{ session('success') }}</span></div>
    @endif
    @if (session('error'))
        <div role="alert" class="alert alert-error mb-5"><i data-lucide="circle-alert" class="size-5" aria-hidden="true"></i><span>{{ session('error') }}</span></div>
    @endif

    @if ($keyStatus['source'] === 'missing')
        <div role="alert" class="alert alert-warning mb-5 items-start">
            <i data-lucide="circle-alert" class="mt-0.5 size-5" aria-hidden="true"></i>
            <div><h2 class="font-semibold">Configure uma chave antes de executar backups</h2><p class="mt-1 text-sm">A criptografia está sem chave. Crie uma na área de criptografia para liberar novas execuções.</p></div>
            <a href="{{ route('db-deeper.backups.keys.index') }}" class="btn btn-sm">Abrir chaves</a>
        </div>
    @endif

    <div class="mb-8 grid min-w-0 grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1.25fr)_minmax(18rem,0.75fr)]">
        <section aria-labelledby="manual-backup-title" class="card card-border min-w-0 bg-base-100">
            <div class="card-body gap-4">
                <div class="flex items-start gap-3">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-field bg-base-200 text-base-content/70"><i data-lucide="archive" class="size-4" aria-hidden="true"></i></span>
                    <div><h2 id="manual-backup-title" class="card-title text-base">Executar backup agora</h2><p class="mt-1 text-sm text-base-content/60">O arquivo será cifrado e enviado ao disco selecionado.</p></div>
                </div>
                @if ($connections === [])
                    <div role="alert" class="alert alert-warning"><i data-lucide="circle-alert" class="size-5" aria-hidden="true"></i><span>Nenhuma conexão MySQL, MariaDB ou PostgreSQL está configurada para backup.</span></div>
                @else
                    <form method="POST" action="{{ route('db-deeper.backups.run') }}" class="grid min-w-0 grid-cols-1 gap-4 sm:grid-cols-2">
                        @csrf
                        <fieldset class="fieldset">
                            <label for="manual-connection" class="label">Banco de dados</label>
                            <select id="manual-connection" name="connection_name" class="select select-sm w-full" required>
                                @foreach ($connections as $connectionName => $connectionConfig)
                                    <option value="{{ $connectionName }}" @selected($databaseConnection === $connectionName)>{{ $connectionName }} · {{ strtoupper($connectionConfig['driver']) }}</option>
                                @endforeach
                            </select>
                        </fieldset>
                        <fieldset class="fieldset">
                            <label for="manual-disk" class="label">Disco de destino</label>
                            <select id="manual-disk" name="disk_name" class="select select-sm w-full" required>
                                @foreach ($disks as $diskName)
                                    <option value="{{ $diskName }}" @selected(config('db-deeper.backup.disk') === $diskName)>{{ $diskName }}</option>
                                @endforeach
                            </select>
                        </fieldset>
                        <fieldset class="fieldset sm:col-span-2">
                            <label for="manual-path" class="label">Pasta no disco</label>
                            <input id="manual-path" name="destination_path" type="text" class="input input-sm w-full" maxlength="500" value="{{ config('db-deeper.backup.path', 'database-backups') }}" placeholder="database-backups">
                        </fieldset>
                        <div class="sm:col-span-2"><button type="submit" class="btn btn-primary btn-sm" @disabled($keyStatus['source'] === 'missing' || $connections === [] || $disks === [])><i data-lucide="play" class="size-4" aria-hidden="true"></i>Iniciar backup</button></div>
                    </form>
                @endif
            </div>
        </section>

        <section aria-labelledby="encryption-status-title" class="card card-border min-w-0 bg-base-100">
            <div class="card-body gap-3">
                <div class="flex items-center gap-3">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-field bg-base-200 text-base-content/70"><i data-lucide="shield-check" class="size-4" aria-hidden="true"></i></span>
                    <div><h2 id="encryption-status-title" class="card-title text-base">Chave ativa</h2><p class="text-sm text-base-content/60">{{ $keyStatus['name'] ?? 'Nenhuma chave configurada' }}</p></div>
                </div>
                @if ($keyStatus['fingerprint'])
                    <p class="font-mono text-xs text-base-content/65">SHA-256 · {{ substr($keyStatus['fingerprint'], 0, 16) }}…</p>
                    <span class="badge badge-sm badge-ghost">{{ $keyStatus['source'] === 'dashboard' ? 'Gerenciada pelo painel' : 'Configurada externamente' }}</span>
                @else
                    <span class="badge badge-sm badge-warning">Pendente</span>
                @endif
                <p class="text-xs leading-5 text-base-content/55">As chaves guardadas pelo painel são cifradas com a APP_KEY. Preserve a APP_KEY fora do banco junto ao plano de recuperação.</p>
                <a href="{{ route('db-deeper.backups.keys.index') }}" class="link link-primary w-fit text-sm">Ver chaves e histórico</a>
            </div>
        </section>
    </div>

    <section aria-labelledby="schedules-title" class="mb-8">
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div><h2 id="schedules-title" class="text-xl font-semibold tracking-tight">Rotinas agendadas</h2><p class="mt-1 text-sm text-base-content/60">Mantenha o Laravel Scheduler executando a cada minuto com <span class="font-mono">schedule:run</span>. A fila padrão processa cada execução.</p></div>
            <span class="badge badge-ghost badge-sm">{{ $schedules->count() }} {{ $schedules->count() === 1 ? 'rotina' : 'rotinas' }}</span>
        </div>

        <div class="mb-4 card card-border min-w-0 bg-base-100">
            <div class="card-body gap-4">
                <h3 class="card-title text-base">Criar rotina</h3>
                @if ($connections === [] || $disks === [])
                    <p class="text-sm text-base-content/60">Configure ao menos um banco suportado e um disco no Laravel para criar uma rotina.</p>
                @else
                    @include('db-deeper::partials.backup-schedule-form', [
                        'action' => route('db-deeper.backups.schedules.store'),
                        'schedule' => null,
                        'formId' => 'new',
                        'connections' => $connections,
                        'disks' => $disks,
                    ])
                @endif
            </div>
        </div>

        @if ($schedules->isEmpty())
            <div class="flex items-start gap-3 border-y border-base-300 py-6"><i data-lucide="circle-help" class="mt-0.5 size-5 text-base-content/50" aria-hidden="true"></i><div><p class="font-medium">Nenhuma rotina criada</p><p class="mt-1 text-sm text-base-content/60">Use o formulário acima para definir frequência, conexão e destino dos próximos backups.</p></div></div>
        @else
            <div class="overflow-x-auto rounded-box border border-base-300 bg-base-100">
                <table class="table table-sm">
                    <thead><tr><th scope="col">Rotina</th><th scope="col">Frequência</th><th scope="col">Próxima execução</th><th scope="col">Destino</th><th scope="col">Estado</th><th scope="col"><span class="sr-only">Ações</span></th></tr></thead>
                    <tbody>
                        @foreach ($schedules as $schedule)
                            <tr>
                                <th scope="row"><span class="font-medium">{{ $schedule->name }}</span><span class="mt-1 block text-xs font-normal text-base-content/55">{{ $schedule->connection_name }} · {{ strtoupper((string) config('database.connections.'.$schedule->connection_name.'.driver')) }}</span></th>
                                <td class="min-w-48">{{ $frequencyService->description($schedule->frequency, $schedule->run_at, $schedule->day_of_week, $schedule->day_of_month) }}<span class="mt-1 block text-xs text-base-content/55">{{ $schedule->timezone }}</span></td>
                                <td class="whitespace-nowrap tabular-nums">{{ $schedule->next_run_at?->timezone($schedule->timezone)->format('d/m/Y H:i') ?? 'Pausada' }}</td>
                                <td class="max-w-56 break-all"><span class="font-mono text-xs">{{ $schedule->disk_name }}</span><span class="mt-1 block truncate text-xs text-base-content/55">{{ $schedule->destination_path ?: config('db-deeper.backup.path') }}</span></td>
                                <td><span class="badge badge-sm {{ $schedule->is_enabled ? 'badge-success' : 'badge-ghost' }}">{{ $schedule->is_enabled ? 'Ativa' : 'Pausada' }}</span></td>
                                <td><div class="flex flex-wrap justify-end gap-1">
                                    <a href="{{ route('db-deeper.backups.schedules.edit', $schedule) }}" class="btn btn-ghost btn-sm">Editar</a>
                                    <form method="POST" action="{{ route('db-deeper.backups.schedules.toggle', $schedule) }}">@csrf @method('PATCH')<button type="submit" class="btn btn-ghost btn-sm">{{ $schedule->is_enabled ? 'Pausar' : 'Ativar' }}</button></form>
                                    <form method="POST" action="{{ route('db-deeper.backups.schedules.destroy', $schedule) }}">@csrf @method('DELETE')<button type="submit" class="btn btn-ghost btn-sm text-error">Remover</button></form>
                                </div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section aria-labelledby="backup-history-title">
        <div class="mb-4"><h2 id="backup-history-title" class="text-xl font-semibold tracking-tight">Histórico de execuções</h2><p class="mt-1 text-sm text-base-content/60">Status, integridade e download dos arquivos cifrados.</p></div>
        <div id="backup-history-live" data-live-content aria-live="off">
            @include('db-deeper::partials.backups-live')
        </div>
    </section>
@endsection
