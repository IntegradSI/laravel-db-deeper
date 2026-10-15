@php
    $scheduleValue = $schedule ?? null;
    $formName = 'schedule-name-'.$formId;
    $formFrequency = 'schedule-frequency-'.$formId;
    $formTime = 'schedule-time-'.$formId;
    $formDay = 'schedule-day-'.$formId;
    $formMonthDay = 'schedule-month-day-'.$formId;
    $formTimezone = 'schedule-timezone-'.$formId;
    $formConnection = 'schedule-connection-'.$formId;
    $formDisk = 'schedule-disk-'.$formId;
    $formPath = 'schedule-path-'.$formId;
@endphp

<form method="POST" action="{{ $action }}" class="grid min-w-0 grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
    @csrf
    @if ($scheduleValue)
        @method('PATCH')
    @endif

    <fieldset class="fieldset">
        <label class="label" for="{{ $formName }}">Nome da rotina</label>
        <input id="{{ $formName }}" name="name" type="text" class="input input-sm w-full" maxlength="120" required value="{{ old('name', $scheduleValue?->name ?? '') }}" placeholder="Ex.: Backup diário">
        @error('name') <span class="text-sm text-error">{{ $message }}</span> @enderror
    </fieldset>

    <fieldset class="fieldset">
        <label class="label" for="{{ $formFrequency }}">Frequência</label>
        <select id="{{ $formFrequency }}" name="frequency" class="select select-sm w-full" required>
            @foreach (['hourly' => 'A cada hora', 'daily' => 'Diariamente', 'weekly' => 'Semanalmente', 'monthly' => 'Mensalmente'] as $frequencyValue => $frequencyLabel)
                <option value="{{ $frequencyValue }}" @selected(old('frequency', $scheduleValue?->frequency ?? 'daily') === $frequencyValue)>{{ $frequencyLabel }}</option>
            @endforeach
        </select>
        @error('frequency') <span class="text-sm text-error">{{ $message }}</span> @enderror
    </fieldset>

    <fieldset class="fieldset">
        <label class="label" for="{{ $formTime }}">Horário local</label>
        <input id="{{ $formTime }}" name="run_at" type="time" class="input input-sm w-full" value="{{ old('run_at', substr((string) ($scheduleValue?->run_at ?? '02:00'), 0, 5)) }}">
        <span class="label">Usado nas frequências diária, semanal e mensal.</span>
        @error('run_at') <span class="text-sm text-error">{{ $message }}</span> @enderror
    </fieldset>

    <fieldset class="fieldset">
        <label class="label" for="{{ $formDay }}">Dia da semana</label>
        <select id="{{ $formDay }}" name="day_of_week" class="select select-sm w-full">
            @foreach (['domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado'] as $dayValue => $dayLabel)
                <option value="{{ $dayValue }}" @selected((string) old('day_of_week', $scheduleValue?->day_of_week ?? 1) === (string) $dayValue)>{{ ucfirst($dayLabel) }}</option>
            @endforeach
        </select>
        <span class="label">Usado somente na frequência semanal.</span>
        @error('day_of_week') <span class="text-sm text-error">{{ $message }}</span> @enderror
    </fieldset>

    <fieldset class="fieldset">
        <label class="label" for="{{ $formMonthDay }}">Dia do mês</label>
        <input id="{{ $formMonthDay }}" name="day_of_month" type="number" class="input input-sm w-full" min="1" max="28" value="{{ old('day_of_month', $scheduleValue?->day_of_month ?? 1) }}">
        <span class="label">Usado somente na frequência mensal. Escolha de 1 a 28.</span>
        @error('day_of_month') <span class="text-sm text-error">{{ $message }}</span> @enderror
    </fieldset>

    <fieldset class="fieldset">
        <label class="label" for="{{ $formTimezone }}">Fuso horário</label>
        <input id="{{ $formTimezone }}" name="timezone" type="text" class="input input-sm w-full" maxlength="64" required value="{{ old('timezone', $scheduleValue?->timezone ?? config('app.timezone', 'UTC')) }}" placeholder="America/Fortaleza">
        @error('timezone') <span class="text-sm text-error">{{ $message }}</span> @enderror
    </fieldset>

    <fieldset class="fieldset">
        <label class="label" for="{{ $formConnection }}">Conexão do banco</label>
        <select id="{{ $formConnection }}" name="connection_name" class="select select-sm w-full" required>
            @foreach ($connections as $connectionName => $connectionConfig)
                <option value="{{ $connectionName }}" @selected(old('connection_name', $scheduleValue?->connection_name ?? config('db-deeper.backup.connection', config('database.default'))) === $connectionName)>{{ $connectionName }} · {{ strtoupper($connectionConfig['driver']) }}</option>
            @endforeach
        </select>
        @error('connection_name') <span class="text-sm text-error">{{ $message }}</span> @enderror
    </fieldset>

    <fieldset class="fieldset">
        <label class="label" for="{{ $formDisk }}">Disco de destino</label>
        <select id="{{ $formDisk }}" name="disk_name" class="select select-sm w-full" required>
            @foreach ($disks as $diskName)
                <option value="{{ $diskName }}" @selected(old('disk_name', $scheduleValue?->disk_name ?? config('db-deeper.backup.disk')) === $diskName)>{{ $diskName }}</option>
            @endforeach
        </select>
        @error('disk_name') <span class="text-sm text-error">{{ $message }}</span> @enderror
    </fieldset>

    <fieldset class="fieldset">
        <label class="label" for="{{ $formPath }}">Pasta no disco</label>
        <input id="{{ $formPath }}" name="destination_path" type="text" class="input input-sm w-full" maxlength="500" value="{{ old('destination_path', $scheduleValue?->destination_path ?? config('db-deeper.backup.path', 'database-backups')) }}" placeholder="database-backups">
        @error('destination_path') <span class="text-sm text-error">{{ $message }}</span> @enderror
    </fieldset>

    <fieldset class="fieldset md:col-span-2 xl:col-span-3">
        <legend class="fieldset-legend">Estado da rotina</legend>
        <label class="label w-fit cursor-pointer gap-3">
            <input type="hidden" name="is_enabled" value="0">
            <input type="checkbox" name="is_enabled" value="1" class="checkbox checkbox-sm" @checked((bool) old('is_enabled', $scheduleValue?->is_enabled ?? true))>
            <span>Ativa e disponível para o Laravel Scheduler</span>
        </label>
    </fieldset>

    <div class="flex flex-wrap items-center gap-2 md:col-span-2 xl:col-span-3">
        <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="save" class="size-4" aria-hidden="true"></i>{{ $scheduleValue ? 'Salvar rotina' : 'Criar rotina' }}</button>
        @if ($scheduleValue)
            <a href="{{ route('db-deeper.backups.index') }}" class="btn btn-ghost btn-sm">Cancelar</a>
        @endif
    </div>
</form>
