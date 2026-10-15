@extends('db-deeper::layouts.panel', [
    'activePage' => 'backups',
    'pageTitle' => 'Backups',
    'liveRefreshUrl' => $liveRefreshUrl,
    'refreshInterval' => $refreshInterval,
])

@section('content')
    <section class="mb-6 flex flex-col gap-3 border-b border-base-300 pb-5">
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Backups</h1>
        <p class="max-w-3xl text-sm leading-6 text-base-content/65">Acesse cada etapa separadamente: iniciar uma cópia, configurar horários, acompanhar arquivos e verificar a criptografia.</p>
    </section>

    @if (session('success'))
        <div role="status" class="alert alert-success mb-5"><i data-lucide="circle-check" class="size-5" aria-hidden="true"></i><span>{{ session('success') }}</span></div>
    @endif
    @if (session('error'))
        <div role="alert" class="alert alert-error mb-5 items-start"><i data-lucide="circle-alert" class="mt-0.5 size-5" aria-hidden="true"></i><div><h2 class="font-semibold">Ação não concluída</h2><p class="text-sm leading-5">{{ session('error') }}</p><p class="mt-1 text-xs">Use a referência no log do Laravel para localizar o diagnóstico. Nenhum segredo é exibido aqui.</p></div></div>
    @endif
    @if ($errors->any())
        <div role="alert" class="alert alert-error mb-5 items-start">
            <i data-lucide="circle-alert" class="mt-0.5 size-5" aria-hidden="true"></i>
            <div><h2 class="font-semibold">Revise os campos destacados</h2><ul class="mt-1 space-y-1 text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        </div>
    @endif

    <div data-backup-tabs role="tablist" aria-label="Seções de backup" class="tabs tabs-border mb-5 w-full flex-nowrap overflow-x-auto">
        <button id="backup-tab-manual" type="button" role="tab" aria-controls="backup-panel-manual" aria-selected="{{ $activeBackupTab === 'manual' ? 'true' : 'false' }}" tabindex="{{ $activeBackupTab === 'manual' ? '0' : '-1' }}" data-backup-tab="manual" class="tab gap-2 {{ $activeBackupTab === 'manual' ? 'tab-active' : '' }}"><i data-lucide="play" class="size-4" aria-hidden="true"></i>Executar</button>
        <button id="backup-tab-schedules" type="button" role="tab" aria-controls="backup-panel-schedules" aria-selected="{{ $activeBackupTab === 'schedules' ? 'true' : 'false' }}" tabindex="{{ $activeBackupTab === 'schedules' ? '0' : '-1' }}" data-backup-tab="schedules" class="tab gap-2 {{ $activeBackupTab === 'schedules' ? 'tab-active' : '' }}"><i data-lucide="clock-3" class="size-4" aria-hidden="true"></i>Rotinas <span class="badge badge-sm badge-ghost">{{ $schedules->count() }}</span></button>
        <button id="backup-tab-history" type="button" role="tab" aria-controls="backup-panel-history" aria-selected="{{ $activeBackupTab === 'history' ? 'true' : 'false' }}" tabindex="{{ $activeBackupTab === 'history' ? '0' : '-1' }}" data-backup-tab="history" class="tab gap-2 {{ $activeBackupTab === 'history' ? 'tab-active' : '' }}"><i data-lucide="list-ordered" class="size-4" aria-hidden="true"></i>Histórico</button>
        <button id="backup-tab-encryption" type="button" role="tab" aria-controls="backup-panel-encryption" aria-selected="{{ $activeBackupTab === 'encryption' ? 'true' : 'false' }}" tabindex="{{ $activeBackupTab === 'encryption' ? '0' : '-1' }}" data-backup-tab="encryption" class="tab gap-2 {{ $activeBackupTab === 'encryption' ? 'tab-active' : '' }}"><i data-lucide="shield-check" class="size-4" aria-hidden="true"></i>Criptografia <span class="badge badge-sm {{ $keyStatus['source'] === 'missing' ? 'badge-warning' : 'badge-success' }}">{{ $keyStatus['source'] === 'missing' ? 'Pendente' : 'Ativa' }}</span></button>
    </div>

    <section id="backup-panel-manual" role="tabpanel" aria-labelledby="backup-tab-manual" tabindex="0" class="min-w-0" @if ($activeBackupTab !== 'manual') hidden @endif>
        <div class="mb-4 flex flex-col gap-1">
            <h2 class="text-xl font-semibold tracking-tight">Executar backup</h2>
            <p class="text-sm text-base-content/60">Escolha o banco e o disco para iniciar uma cópia criptografada agora.</p>
        </div>

        @if ($keyStatus['source'] === 'missing')
            <div role="alert" class="alert alert-warning mb-4 items-start"><i data-lucide="circle-alert" class="mt-0.5 size-5" aria-hidden="true"></i><div><h3 class="font-semibold">É necessária uma chave ativa</h3><p class="text-sm">Crie uma chave na aba Criptografia antes de executar o backup.</p></div><button type="button" class="btn btn-sm" data-open-backup-tab="encryption">Abrir criptografia</button></div>
        @endif

        <section aria-labelledby="manual-backup-title" class="card card-border min-w-0 bg-base-100">
            <div class="card-body gap-4">
                <div class="flex items-start gap-3">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-field bg-base-200 text-base-content/70"><i data-lucide="archive" class="size-4" aria-hidden="true"></i></span>
                    <div><h3 id="manual-backup-title" class="card-title text-base">Cópia manual</h3><p class="mt-1 text-sm text-base-content/60">O arquivo será enviado ao destino selecionado. Acompanhe o resultado na aba Histórico.</p></div>
                </div>
                @if ($connections === [])
                    <div role="alert" class="alert alert-warning"><i data-lucide="circle-alert" class="size-5" aria-hidden="true"></i><span>Nenhuma conexão MySQL, MariaDB ou PostgreSQL está configurada para backup.</span></div>
                @elseif ($disks === [])
                    <div role="alert" class="alert alert-warning"><i data-lucide="hard-drive" class="size-5" aria-hidden="true"></i><span>Nenhum disco do Laravel está configurado para receber o arquivo.</span></div>
                @else
                    <form method="POST" action="{{ route('db-deeper.backups.run', ['tab' => 'manual']) }}" data-backup-preflight-form data-diagnostic-url="{{ route('db-deeper.backups.diagnose') }}" class="grid min-w-0 grid-cols-1 gap-4 sm:grid-cols-2">
                        @csrf
                        <fieldset class="fieldset">
                            <label for="manual-connection" class="label">Banco de dados</label>
                            <select id="manual-connection" name="connection_name" class="select select-sm w-full {{ $errors->has('connection_name') ? 'select-error' : '' }}" aria-invalid="{{ $errors->has('connection_name') ? 'true' : 'false' }}" @if ($errors->has('connection_name')) aria-describedby="manual-connection-error" @endif required>
                                @foreach ($connections as $connectionName => $connectionConfig)
                                    <option value="{{ $connectionName }}" @selected(old('connection_name', $databaseConnection) === $connectionName)>{{ $connectionName }} · {{ strtoupper($connectionConfig['driver']) }}</option>
                                @endforeach
                            </select>
                            @error('connection_name')<span id="manual-connection-error" class="text-sm text-error">{{ $message }}</span>@enderror
                        </fieldset>
                        <fieldset class="fieldset">
                            <label for="manual-disk" class="label">Disco de destino</label>
                            <select id="manual-disk" name="disk_name" class="select select-sm w-full {{ $errors->has('disk_name') ? 'select-error' : '' }}" aria-invalid="{{ $errors->has('disk_name') ? 'true' : 'false' }}" @if ($errors->has('disk_name')) aria-describedby="manual-disk-error" @endif required>
                                @foreach ($disks as $diskName)
                                    <option value="{{ $diskName }}" @selected(old('disk_name', config('db-deeper.backup.disk')) === $diskName)>{{ $diskName }}</option>
                                @endforeach
                            </select>
                            @error('disk_name')<span id="manual-disk-error" class="text-sm text-error">{{ $message }}</span>@enderror
                        </fieldset>
                        <fieldset class="fieldset sm:col-span-2">
                            <label for="manual-path" class="label">Pasta no disco <span class="font-normal text-base-content/55">(opcional)</span></label>
                            <input id="manual-path" name="destination_path" type="text" class="input input-sm w-full {{ $errors->has('destination_path') ? 'input-error' : '' }}" maxlength="500" value="{{ old('destination_path', config('db-deeper.backup.path', 'database-backups')) }}" placeholder="database-backups" aria-invalid="{{ $errors->has('destination_path') ? 'true' : 'false' }}" @if ($errors->has('destination_path')) aria-describedby="manual-path-error" @endif>
                            @error('destination_path')<span id="manual-path-error" class="text-sm text-error">{{ $message }}</span>@enderror
                        </fieldset>
                        <div class="sm:col-span-2 rounded-box border border-base-300 bg-base-200/40 p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-start gap-3">
                                    <i data-lucide="scan-search" class="mt-0.5 size-5 shrink-0 text-base-content/65" aria-hidden="true"></i>
                                    <div><h4 class="font-semibold">Verifique antes de executar</h4><p class="mt-1 max-w-2xl text-sm leading-5 text-base-content/65">O diagnóstico testa chave, conexão, ferramenta de exportação e escrita no destino. Ele não gera um backup.</p></div>
                                </div>
                                <button type="button" class="btn btn-sm" data-run-backup-diagnostic><i data-lucide="activity" class="size-4" aria-hidden="true"></i>Diagnosticar agora</button>
                            </div>
                            <p class="mt-3 text-xs leading-5 text-base-content/55">Para validar o destino, será criado e removido um arquivo temporário aleatório de 32 bytes na pasta selecionada. Nenhum dado do banco é escrito nele.</p>
                            <div class="mt-4 space-y-3" data-backup-diagnostic-results aria-live="polite" hidden>
                                <div role="status" class="alert" data-backup-diagnostic-summary></div>
                                <ul class="grid gap-2 sm:grid-cols-2" data-backup-diagnostic-checks></ul>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 sm:col-span-2">
                            <button type="submit" class="btn btn-primary btn-sm" data-backup-submit disabled><i data-lucide="play" class="size-4" aria-hidden="true"></i>Iniciar backup</button>
                            <span class="text-xs text-base-content/55">Drivers compatíveis: MySQL, MariaDB e PostgreSQL.</span>
                        </div>
                    </form>
                @endif
            </div>
        </section>
    </section>

    <section id="backup-panel-schedules" role="tabpanel" aria-labelledby="backup-tab-schedules" tabindex="0" class="min-w-0" @if ($activeBackupTab !== 'schedules') hidden @endif>
        <div class="mb-4 flex flex-col gap-1">
            <h2 class="text-xl font-semibold tracking-tight">Rotinas agendadas</h2>
            <p class="text-sm text-base-content/60">Defina frequência, fuso horário, conexão e destino de cada rotina.</p>
        </div>
        <div role="note" class="alert alert-info mb-4 items-start"><i data-lucide="info" class="mt-0.5 size-5" aria-hidden="true"></i><div><h3 class="font-semibold">O Scheduler precisa estar ativo no servidor</h3><p class="text-sm leading-5">Configure o cron do Laravel para executar <code class="font-mono">schedule:run</code> a cada minuto. Para bancos maiores, mantenha também um worker da fila ativo.</p></div></div>

        <section aria-labelledby="create-schedule-title" class="mb-6 card card-border min-w-0 bg-base-100">
            <div class="card-body gap-4">
                <div><h3 id="create-schedule-title" class="card-title text-base">Criar uma rotina</h3><p class="text-sm text-base-content/60">A próxima execução é calculada no fuso horário selecionado.</p></div>
                @if ($connections === [] || $disks === [])
                    <div role="alert" class="alert alert-warning"><i data-lucide="circle-alert" class="size-5" aria-hidden="true"></i><span>Configure um banco suportado e um disco no Laravel antes de criar uma rotina.</span></div>
                @else
                    @include('db-deeper::partials.backup-schedule-form', [
                        'action' => route('db-deeper.backups.schedules.store', ['tab' => 'schedules']),
                        'schedule' => null,
                        'formId' => 'new',
                        'connections' => $connections,
                        'disks' => $disks,
                    ])
                @endif
            </div>
        </section>

        <div class="mb-3 flex items-center justify-between gap-3"><h3 class="text-lg font-semibold">Rotinas configuradas</h3><span class="badge badge-ghost badge-sm">{{ $schedules->count() }}</span></div>
        @if ($schedules->isEmpty())
            <div class="flex items-start gap-3 border-y border-base-300 py-6"><i data-lucide="circle-help" class="mt-0.5 size-5 text-base-content/50" aria-hidden="true"></i><div><p class="font-medium">Nenhuma rotina criada</p><p class="mt-1 text-sm text-base-content/60">Use o formulário acima para definir quando e onde salvar os próximos backups.</p></div></div>
        @else
            <div class="max-w-full overflow-x-auto rounded-box border border-base-300 bg-base-100">
                <table class="table table-sm min-w-[58rem]">
                    <thead><tr><th scope="col">Rotina e banco</th><th scope="col">Frequência</th><th scope="col">Próxima execução</th><th scope="col">Destino</th><th scope="col">Estado</th><th scope="col"><span class="sr-only">Ações</span></th></tr></thead>
                    <tbody>
                        @foreach ($schedules as $schedule)
                            <tr class="hover:bg-base-200/60">
                                <th scope="row"><span class="font-medium">{{ $schedule->name }}</span><span class="mt-1 block text-xs font-normal text-base-content/55">{{ $schedule->connection_name }} · {{ strtoupper((string) config('database.connections.'.$schedule->connection_name.'.driver')) }}</span></th>
                                <td>{{ $frequencyService->description($schedule->frequency, $schedule->run_at, $schedule->day_of_week, $schedule->day_of_month) }}<span class="mt-1 block text-xs text-base-content/55">{{ $schedule->timezone }}</span></td>
                                <td class="whitespace-nowrap tabular-nums">{{ $schedule->next_run_at?->timezone($schedule->timezone)->format('d/m/Y H:i') ?? 'Pausada' }}</td>
                                <td><span class="font-mono text-xs">{{ $schedule->disk_name }}</span><span class="mt-1 block max-w-48 truncate text-xs text-base-content/55">{{ $schedule->destination_path ?: config('db-deeper.backup.path') }}</span></td>
                                <td><span class="badge badge-sm {{ $schedule->is_enabled ? 'badge-success' : 'badge-ghost' }}">{{ $schedule->is_enabled ? 'Ativa' : 'Pausada' }}</span></td>
                                <td><div class="flex flex-wrap justify-end gap-1">
                                    <a href="{{ route('db-deeper.backups.schedules.edit', ['schedule' => $schedule, 'tab' => 'schedules']) }}" class="btn btn-ghost btn-sm">Editar</a>
                                    <form method="POST" action="{{ route('db-deeper.backups.schedules.toggle', ['schedule' => $schedule, 'tab' => 'schedules']) }}">@csrf @method('PATCH')<button type="submit" class="btn btn-ghost btn-sm">{{ $schedule->is_enabled ? 'Pausar' : 'Ativar' }}</button></form>
                                    <form method="POST" action="{{ route('db-deeper.backups.schedules.destroy', ['schedule' => $schedule, 'tab' => 'schedules']) }}">@csrf @method('DELETE')<button type="submit" class="btn btn-ghost btn-sm text-error">Remover</button></form>
                                </div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section id="backup-panel-history" role="tabpanel" aria-labelledby="backup-tab-history" tabindex="0" class="min-w-0" @if ($activeBackupTab !== 'history') hidden @endif>
        <div class="mb-4 flex flex-col gap-1"><h2 class="text-xl font-semibold tracking-tight">Histórico de execuções</h2><p class="text-sm text-base-content/60">Confira o resultado, a integridade e baixe cada arquivo concluído.</p></div>
        <div id="backup-history-live" data-live-content aria-live="off">
            @include('db-deeper::partials.backups-live')
        </div>
    </section>

    <section id="backup-panel-encryption" role="tabpanel" aria-labelledby="backup-tab-encryption" tabindex="0" class="min-w-0" @if ($activeBackupTab !== 'encryption') hidden @endif>
        <div class="mb-4 flex flex-col gap-1"><h2 class="text-xl font-semibold tracking-tight">Criptografia e chaves</h2><p class="text-sm text-base-content/60">Veja qual chave protege os próximos backups e acesse o histórico de chaves.</p></div>
        @if ($keyStatus['source'] === 'missing')
            <div role="alert" class="alert alert-warning mb-4 items-start"><i data-lucide="circle-alert" class="mt-0.5 size-5" aria-hidden="true"></i><div><h3 class="font-semibold">Nenhuma chave ativa</h3><p class="text-sm">Crie uma chave para permitir novas execuções. O segredo será exibido uma única vez.</p></div></div>
        @endif
        <section class="card card-border min-w-0 bg-base-100">
            <div class="card-body gap-4">
                <div class="flex items-start gap-3"><span class="flex size-9 shrink-0 items-center justify-center rounded-field bg-base-200 text-base-content/70"><i data-lucide="shield-check" class="size-4" aria-hidden="true"></i></span><div><h3 class="card-title text-base">Chave usada nos próximos backups</h3><p class="text-sm text-base-content/60">{{ $keyStatus['name'] ?? 'Nenhuma chave configurada' }}</p></div></div>
                @if ($keyStatus['fingerprint'])
                    <div class="flex flex-wrap items-center gap-3"><span class="badge badge-success badge-sm">Ativa</span><span class="badge badge-ghost badge-sm">{{ $keyStatus['source'] === 'dashboard' ? 'Guardada cifrada no banco' : 'Definida na configuração' }}</span><code class="font-mono text-xs text-base-content/65">SHA-256 · {{ substr($keyStatus['fingerprint'], 0, 16) }}…</code></div>
                @else
                    <span class="badge badge-warning badge-sm">Configuração pendente</span>
                @endif
                <div role="note" class="border-y border-base-300 py-3 text-sm leading-6 text-base-content/65"><p>As chaves gerenciadas pelo painel são cifradas com a <code class="font-mono">APP_KEY</code>. Preserve essa chave fora do banco e do disco de backups. A rotação mantém as chaves antigas para que os arquivos existentes continuem recuperáveis.</p></div>
                <div class="card-actions"><a href="{{ route('db-deeper.backups.keys.index') }}" class="btn btn-primary btn-sm"><i data-lucide="shield-check" class="size-4" aria-hidden="true"></i>Gerenciar chaves</a></div>
            </div>
        </section>
    </section>
@endsection
