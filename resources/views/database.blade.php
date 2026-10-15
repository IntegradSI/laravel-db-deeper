@extends('db-deeper::layouts.panel', ['activePage' => 'database', 'pageTitle' => 'Estatísticas do banco'])

@section('content')
    <section class="mb-7 flex flex-col gap-3 border-b border-base-300 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="mb-2 text-sm font-semibold text-primary">Banco de dados</p>
            <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Estatísticas avançadas</h1>
            <p class="mt-3 max-w-3xl text-base leading-7 text-base-content/65">Leitura de metadados do driver configurado. A coleta evita contagens completas de linhas e não altera dados.</p>
        </div>
        <span class="badge badge-outline gap-2 py-3"><i data-lucide="database" class="size-4" aria-hidden="true"></i>{{ $stats['driver_label'] }}</span>
    </section>

    @if (! $stats['connected'])
        <div role="alert" class="alert alert-error mb-6">
            <i data-lucide="circle-alert" class="size-5" aria-hidden="true"></i>
            <div><h2 class="font-semibold">Banco indisponível</h2><p class="text-sm">{{ $stats['statistics_note'] }}</p></div>
        </div>
    @elseif (! $stats['supported'])
        <div role="alert" class="alert alert-warning mb-6">
            <i data-lucide="circle-alert" class="size-5" aria-hidden="true"></i>
            <div><h2 class="font-semibold">Driver sem estatísticas detalhadas</h2><p class="text-sm">A conexão respondeu, mas o driver <span class="font-mono">{{ $stats['driver'] }}</span> não está entre os drivers PDO padrão suportados.</p></div>
        </div>
    @endif

    <section aria-labelledby="connection-title" class="mb-7">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-2">
            <div>
                <h2 id="connection-title" class="text-lg font-semibold">Conexão e capacidade</h2>
                <p class="mt-1 text-sm text-base-content/60">Fonte: conexão <span class="font-mono">{{ $stats['connection_name'] }}</span> do Laravel.</p>
            </div>
            <span @class(['badge badge-success badge-soft' => $stats['connected'], 'badge badge-error badge-soft' => ! $stats['connected']])>{{ $stats['connected'] ? 'Conectado' : 'Sem conexão' }}</span>
        </div>

        <div class="stats stats-vertical w-full border border-base-300 bg-base-100 shadow-sm sm:stats-horizontal">
            <div class="stat min-w-0">
                <div class="stat-figure text-primary"><i data-lucide="gauge" class="size-5" aria-hidden="true"></i></div>
                <div class="stat-title">Resposta do banco</div>
                <div class="stat-value text-2xl tabular-nums">{{ $stats['latency_ms'] === null ? '—' : number_format($stats['latency_ms'], 2, ',', '.').' ms' }}</div>
                <div class="stat-desc">Tempo de uma consulta simples</div>
            </div>
            <div class="stat min-w-0">
                <div class="stat-figure text-primary"><i data-lucide="table-properties" class="size-5" aria-hidden="true"></i></div>
                <div class="stat-title">Tabelas</div>
                <div class="stat-value text-2xl tabular-nums">{{ $stats['table_count'] === null ? '—' : number_format($stats['table_count'], 0, ',', '.') }}</div>
                <div class="stat-desc">Tabelas de usuário visíveis</div>
            </div>
            <div class="stat min-w-0">
                <div class="stat-figure text-primary"><i data-lucide="eye" class="size-5" aria-hidden="true"></i></div>
                <div class="stat-title">Views</div>
                <div class="stat-value text-2xl tabular-nums">{{ $stats['view_count'] === null ? '—' : number_format($stats['view_count'], 0, ',', '.') }}</div>
                <div class="stat-desc">Views visíveis nesta conexão</div>
            </div>
            <div class="stat min-w-0">
                <div class="stat-figure text-primary"><i data-lucide="hard-drive" class="size-5" aria-hidden="true"></i></div>
                <div class="stat-title">Espaço total</div>
                <div class="stat-value text-2xl tabular-nums">{{ $stats['database_size_bytes'] === null ? '—' : \Illuminate\Support\Number::fileSize($stats['database_size_bytes'], precision: 2) }}</div>
                <div class="stat-desc">{{ $stats['database_name'] ?? 'Banco atual' }}</div>
            </div>
        </div>
    </section>

    @if ($stats['statistics_note'])
        <div role="alert" class="alert alert-info mb-7"><i data-lucide="info" class="size-5" aria-hidden="true"></i><p class="text-sm">{{ $stats['statistics_note'] }}</p></div>
    @endif

    <section aria-labelledby="engine-details-title" class="mb-7 grid gap-4 lg:grid-cols-[minmax(0,1.3fr)_minmax(18rem,0.7fr)]">
        <article class="card card-border bg-base-100">
            <div class="card-body p-5 sm:p-6">
                <div class="mb-4 flex items-center gap-3"><i data-lucide="server-cog" class="size-5 text-primary" aria-hidden="true"></i><h2 id="engine-details-title" class="card-title text-base">Detalhes do servidor</h2></div>
                <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div><dt class="text-sm text-base-content/60">Motor</dt><dd class="mt-1 font-medium">{{ $stats['driver_label'] }}</dd></div>
                    <div><dt class="text-sm text-base-content/60">Versão</dt><dd class="mt-1 break-words font-mono text-sm">{{ $stats['server_version'] ?? 'Não disponível' }}</dd></div>
                    <div><dt class="text-sm text-base-content/60">Banco</dt><dd class="mt-1 break-all font-mono text-sm">{{ $stats['database_name'] ?? 'Não disponível' }}</dd></div>
                    <div><dt class="text-sm text-base-content/60">Driver PDO</dt><dd class="mt-1 font-mono text-sm">{{ $stats['driver'] }}</dd></div>
                </dl>
            </div>
        </article>

        <article class="card card-border bg-base-100">
            <div class="card-body p-5 sm:p-6">
                <div class="mb-4 flex items-center gap-3"><i data-lucide="layers-3" class="size-5 text-primary" aria-hidden="true"></i><h2 class="card-title text-base">Distribuição de espaço</h2></div>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4 border-b border-base-200 pb-3"><dt class="text-base-content/60">Dados</dt><dd class="font-medium tabular-nums">{{ $stats['data_size_bytes'] === null ? '—' : \Illuminate\Support\Number::fileSize($stats['data_size_bytes'], precision: 2) }}</dd></div>
                    <div class="flex items-center justify-between gap-4 border-b border-base-200 pb-3"><dt class="text-base-content/60">Índices</dt><dd class="font-medium tabular-nums">{{ $stats['index_size_bytes'] === null ? '—' : \Illuminate\Support\Number::fileSize($stats['index_size_bytes'], precision: 2) }}</dd></div>
                    <div class="flex items-center justify-between gap-4 border-b border-base-200 pb-3"><dt class="text-base-content/60">Espaço livre ou reutilizável</dt><dd class="font-medium tabular-nums">{{ $stats['unused_size_bytes'] === null ? '—' : \Illuminate\Support\Number::fileSize($stats['unused_size_bytes'], precision: 2) }}</dd></div>
                    @if ($stats['log_size_bytes'] !== null)
                        <div class="flex items-center justify-between gap-4"><dt class="text-base-content/60">Log de transações</dt><dd class="font-medium tabular-nums">{{ \Illuminate\Support\Number::fileSize($stats['log_size_bytes'], precision: 2) }}</dd></div>
                    @endif
                </dl>
            </div>
        </article>
    </section>

    <section aria-labelledby="tables-title" class="card card-border min-w-0 bg-base-100">
        <div class="card-body min-w-0 gap-0 p-0">
            <div class="flex flex-col gap-2 border-b border-base-300 p-5 sm:flex-row sm:items-end sm:justify-between sm:px-6">
                <div><h2 id="tables-title" class="card-title text-base">Tabelas com maior uso</h2><p class="mt-1 text-sm text-base-content/60">{{ count($stats['tables']) }} de até 25 tabelas mostradas nesta consulta.</p></div>
                @if (in_array($stats['driver'], ['mysql', 'mariadb', 'pgsql', 'sqlsrv'], true))
                    <span class="badge badge-ghost badge-sm">Linhas estimadas pelo servidor</span>
                @endif
            </div>
            @if ($stats['tables'] === [])
                <div class="p-6 text-sm text-base-content/60">Nenhuma tabela visível ou estatística disponível nesta conexão.</div>
            @else
                <div class="max-w-full overflow-x-auto">
                    <table class="table table-sm">
                        <thead><tr><th scope="col">Tabela</th><th scope="col" class="text-right">Linhas</th><th scope="col" class="text-right">Dados</th><th scope="col" class="text-right">Índices</th></tr></thead>
                        <tbody>
                            @foreach ($stats['tables'] as $table)
                                <tr>
                                    <th scope="row" class="whitespace-nowrap font-mono text-xs">{{ $table['name'] }}</th>
                                    <td class="whitespace-nowrap text-right tabular-nums">{{ $table['row_count'] === null ? '—' : number_format($table['row_count'], 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap text-right tabular-nums">{{ $table['data_size_bytes'] === null ? '—' : \Illuminate\Support\Number::fileSize($table['data_size_bytes'], precision: 2) }}</td>
                                    <td class="whitespace-nowrap text-right tabular-nums">{{ $table['index_size_bytes'] === null ? '—' : \Illuminate\Support\Number::fileSize($table['index_size_bytes'], precision: 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
@endsection
