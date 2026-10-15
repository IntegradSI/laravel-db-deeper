@extends('db-deeper::layouts.panel', [
    'activePage' => 'backups',
    'pageTitle' => 'Editar rotina de backup',
    'liveRefreshUrl' => $liveRefreshUrl,
    'refreshInterval' => $refreshInterval,
])

@section('content')
    <section class="mb-6 flex flex-col gap-4 border-b border-base-300 pb-5 sm:flex-row sm:items-end sm:justify-between">
        <div><h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Editar rotina</h1><p class="mt-2 text-sm text-base-content/65">Altere frequência, fuso horário, conexão e destino de “{{ $schedule->name }}”.</p></div>
        <a href="{{ route('db-deeper.backups.index', ['tab' => 'schedules']) }}" class="btn btn-ghost btn-sm">Voltar para rotinas</a>
    </section>

    @if ($errors->any())
        <div role="alert" class="alert alert-error mb-5 items-start"><i data-lucide="circle-alert" class="mt-0.5 size-5" aria-hidden="true"></i><div><h2 class="font-semibold">Não foi possível salvar a rotina</h2><ul class="mt-1 space-y-1 text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
    @endif

    <section class="card card-border min-w-0 bg-base-100">
        <div class="card-body gap-4">
            @include('db-deeper::partials.backup-schedule-form', [
                'action' => route('db-deeper.backups.schedules.update', ['schedule' => $schedule, 'tab' => 'schedules']),
                'schedule' => $schedule,
                'formId' => 'edit-'.$schedule->id,
                'connections' => $connections,
                'disks' => $disks,
            ])
        </div>
    </section>
@endsection
