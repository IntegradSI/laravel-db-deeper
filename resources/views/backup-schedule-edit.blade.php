@extends('db-deeper::layouts.panel', [
    'activePage' => 'backups',
    'pageTitle' => 'Editar rotina de backup',
    'liveRefreshUrl' => $liveRefreshUrl,
    'refreshInterval' => $refreshInterval,
])

@section('content')
    <section class="mb-6 flex flex-col gap-4 border-b border-base-300 pb-5 sm:flex-row sm:items-end sm:justify-between">
        <div><h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Editar rotina</h1><p class="mt-2 text-sm text-base-content/65">Altere frequência, fuso horário, conexão e destino de “{{ $schedule->name }}”.</p></div>
        <a href="{{ route('db-deeper.backups.index') }}" class="btn btn-ghost btn-sm">Voltar para backups</a>
    </section>

    <section class="card card-border min-w-0 bg-base-100">
        <div class="card-body gap-4">
            @include('db-deeper::partials.backup-schedule-form', [
                'action' => route('db-deeper.backups.schedules.update', $schedule),
                'schedule' => $schedule,
                'formId' => 'edit-'.$schedule->id,
                'connections' => $connections,
                'disks' => $disks,
            ])
        </div>
    </section>
@endsection
