@php
    $backupStateSignature = $backups->getCollection()
        ->map(static fn ($backup): string => $backup->id.':'.$backup->status)
        ->implode('|');
    $backupStateSignature = implode(':', [$queuedCount, $succeededCount, $failedCount, $backupStateSignature]);
@endphp

<div class="mb-4 grid min-w-0 grid-cols-1 gap-3 sm:grid-cols-3" data-live-fragment data-backup-live-fragment data-backup-active="{{ $queuedCount > 0 ? 'true' : 'false' }}" data-backup-state="{{ $backupStateSignature }}">
    <div class="card card-border bg-base-100">
        <div class="card-body gap-1 px-4 py-3">
            <div class="flex items-center gap-2 text-sm text-base-content/65"><i data-lucide="clock-3" class="size-4" aria-hidden="true"></i>Em processamento</div>
            <div class="text-2xl font-semibold tabular-nums">{{ number_format($queuedCount, 0, ',', '.') }}</div>
            <p class="text-xs text-base-content/55">Na fila ou gerando arquivo</p>
        </div>
    </div>
    <div class="card card-border bg-base-100">
        <div class="card-body gap-1 px-4 py-3">
            <div class="flex items-center gap-2 text-sm text-base-content/65"><i data-lucide="circle-check" class="size-4" aria-hidden="true"></i>Concluídos</div>
            <div class="text-2xl font-semibold tabular-nums">{{ number_format($succeededCount, 0, ',', '.') }}</div>
            <p class="text-xs text-base-content/55">Arquivos disponíveis para download</p>
        </div>
    </div>
    <div class="card card-border bg-base-100">
        <div class="card-body gap-1 px-4 py-3">
            <div class="flex items-center gap-2 text-sm text-base-content/65"><i data-lucide="circle-alert" class="size-4" aria-hidden="true"></i>Falhas</div>
            <div class="text-2xl font-semibold tabular-nums">{{ number_format($failedCount, 0, ',', '.') }}</div>
            <p class="text-xs text-base-content/55">Verifique conexão, chave e disco</p>
        </div>
    </div>

@if ($backups->isEmpty())
    <div class="flex items-start gap-3 border-y border-base-300 py-6"><i data-lucide="circle-help" class="mt-0.5 size-5 text-base-content/50" aria-hidden="true"></i><div><p class="font-medium">Nenhuma execução registrada</p><p class="mt-1 text-sm text-base-content/60">Inicie um backup agora ou aguarde a próxima rotina agendada para criar o primeiro ponto de recuperação.</p></div></div>
@else
    <div class="min-w-0 max-w-full overflow-x-auto rounded-box border border-base-300 bg-base-100">
        <table class="table table-sm min-w-[62rem]">
            <thead><tr><th scope="col">Execução</th><th scope="col">Origem</th><th scope="col">Conexão</th><th scope="col">Arquivo</th><th scope="col">Tamanho</th><th scope="col">Integridade</th><th scope="col">Estado</th><th scope="col"><span class="sr-only">Download</span></th></tr></thead>
            <tbody>
                @foreach ($backups as $backup)
                    @php
                        $statusLabel = match ($backup->status) {
                            'queued' => 'Na fila',
                            'running' => 'Gerando',
                            'succeeded' => 'Concluído',
                            'failed' => 'Falhou',
                            default => 'Desconhecido',
                        };
                        $statusClass = match ($backup->status) {
                            'succeeded' => 'badge-success',
                            'failed' => 'badge-error',
                            'running' => 'badge-info',
                            default => 'badge-ghost',
                        };
                    @endphp
                    <tr class="hover:bg-base-200/60">
                        <th scope="row" class="whitespace-nowrap tabular-nums">#{{ $backup->id }}<span class="mt-1 block text-xs font-normal text-base-content/55">{{ $backup->created_at?->format('d/m/Y H:i:s') }}</span></th>
                        <td>{{ $backup->schedule?->name ?? 'Manual' }}</td>
                        <td><span class="font-medium">{{ $backup->connection_name }}</span><span class="mt-1 block text-xs text-base-content/55">{{ strtoupper($backup->driver) }} · {{ $backup->disk_name }}</span></td>
                        <td class="max-w-64 break-all font-mono text-xs">{{ $backup->path ?? $backup->destination_path ?? 'Aguardando execução' }}
                            @if ($backup->error_message)<span class="mt-1 block max-w-64 font-sans text-xs text-error">{{ $backup->error_message }}</span>@endif
                        </td>
                        <td class="whitespace-nowrap tabular-nums">{{ $backup->size_bytes === null ? '—' : \Illuminate\Support\Number::fileSize($backup->size_bytes, precision: 2) }}</td>
                        <td class="font-mono text-xs">{{ $backup->checksum ? substr($backup->checksum, 0, 12).'…' : '—' }}</td>
                        <td><span class="badge badge-sm {{ $statusClass }}">{{ $statusLabel }}</span></td>
                        <td class="text-right">
                            @if ($backup->status === 'succeeded')
                                <a href="{{ route('db-deeper.backups.download', $backup) }}" class="btn btn-ghost btn-sm"><i data-lucide="archive" class="size-4" aria-hidden="true"></i><span>Baixar</span></a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($backups->hasPages())
        <div class="mt-4">{{ $backups->links() }}</div>
    @endif
@endif
</div>
