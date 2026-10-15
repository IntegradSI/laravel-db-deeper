@extends('db-deeper::layouts.panel', ['activePage' => 'overview', 'pageTitle' => 'Saúde do banco'])

@section('content')
    <section class="mb-6 flex flex-col gap-4 border-b border-base-300 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="mb-2 text-sm font-semibold text-base-content/60">Visão geral · {{ $monitoring['driver_label'] }}</p>
            <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Saúde do banco</h1>
            <p class="mt-3 max-w-3xl text-base leading-7 text-base-content/65">Capacidade, utilização do cache e consultas que merecem investigação. Os contadores vêm do próprio mecanismo do banco.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span @class(['badge badge-success badge-soft gap-2' => $monitoring['connected'], 'badge badge-error badge-soft gap-2' => ! $monitoring['connected']])>
                <i data-lucide="activity" class="size-3.5" aria-hidden="true"></i>
                {{ $monitoring['connected'] ? 'Conexão respondendo' : 'Banco indisponível' }}
            </span>
            <span class="badge badge-outline">{{ $databaseConnection }}</span>
        </div>
    </section>

    @if (! $monitoring['connected'])
        <div role="alert" class="alert alert-error mb-6">
            <i data-lucide="circle-alert" class="size-5" aria-hidden="true"></i>
            <div><h2 class="font-semibold">Não foi possível consultar o banco</h2><p class="text-sm">{{ $monitoring['slow_query_note'] }}</p></div>
        </div>
    @endif

    @if ($monitoring['recommendations'] !== [])
        <section aria-labelledby="attention-title" class="mb-7">
            <h2 id="attention-title" class="mb-3 text-lg font-semibold">Pontos para investigar</h2>
            <div class="grid gap-3">
                @foreach ($monitoring['recommendations'] as $recommendation)
                    <div role="alert" @class(['alert alert-error' => $recommendation['level'] === 'error', 'alert alert-warning' => $recommendation['level'] === 'warning'])>
                        <i data-lucide="circle-alert" class="size-5" aria-hidden="true"></i>
                        <div><h3 class="font-semibold">{{ $recommendation['title'] }}</h3><p class="text-sm">{{ $recommendation['detail'] }}</p></div>
                    </div>
                @endforeach
            </div>
        </section>
    @else
        <div role="status" class="mb-7 flex items-start gap-3 rounded-field border border-base-300 bg-base-100 p-4 text-sm">
            <i data-lucide="circle-check" class="mt-0.5 size-5 text-success" aria-hidden="true"></i>
            <p>Os indicadores disponíveis não cruzaram os limites de atenção configurados. Confira abaixo os dados que dependem de permissões ou recursos adicionais do servidor.</p>
        </div>
    @endif

    <section aria-labelledby="capacity-title" class="mb-7">
        <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
            <div><h2 id="capacity-title" class="text-lg font-semibold">Capacidade e desempenho</h2><p class="mt-1 text-sm text-base-content/60">Resposta observada agora e contadores acumulados pelo servidor.</p></div>
            <span class="text-xs text-base-content/55">Driver PDO: <span class="font-mono">{{ $monitoring['driver'] }}</span></span>
        </div>

        <div class="stats stats-vertical w-full border border-base-300 bg-base-100 sm:stats-horizontal">
            <div class="stat min-w-0">
                <div class="stat-figure text-base-content/60"><i data-lucide="gauge" class="size-5" aria-hidden="true"></i></div>
                <div class="stat-title">Latência da conexão</div>
                <div class="stat-value text-2xl tabular-nums">{{ $monitoring['latency_ms'] === null ? '—' : number_format($monitoring['latency_ms'], 2, ',', '.').' ms' }}</div>
                <div class="stat-desc">Consulta simples nesta página</div>
            </div>
            <div class="stat min-w-0">
                <div class="stat-figure text-base-content/60"><i data-lucide="layers-3" class="size-5" aria-hidden="true"></i></div>
                <div class="stat-title">Conexões em uso</div>
                <div class="stat-value text-2xl tabular-nums">{{ $monitoring['active_connections'] === null ? '—' : number_format($monitoring['active_connections'], 0, ',', '.') }}<span class="text-base font-medium text-base-content/55"> / {{ $monitoring['max_connections'] === null ? 'limite não informado' : number_format($monitoring['max_connections'], 0, ',', '.') }}</span></div>
                <div class="stat-desc">{{ $monitoring['connection_usage_percent'] === null ? 'O driver não expõe o limite' : number_format($monitoring['connection_usage_percent'], 1, ',', '.').'% do limite configurado' }}</div>
                @if ($monitoring['connection_usage_percent'] !== null)
                    <progress class="progress mt-2 w-full" value="{{ min(100, $monitoring['connection_usage_percent']) }}" max="100" aria-label="{{ number_format($monitoring['connection_usage_percent'], 1, ',', '.') }}% das conexões utilizadas"></progress>
                @endif
            </div>
            <div class="stat min-w-0">
                <div class="stat-figure text-base-content/60"><i data-lucide="database" class="size-5" aria-hidden="true"></i></div>
                <div class="stat-title">Cache de páginas</div>
                <div class="stat-value text-2xl tabular-nums">{{ $monitoring['buffer_hit_percent'] === null ? '—' : number_format($monitoring['buffer_hit_percent'], 2, ',', '.').'%' }}</div>
                <div class="stat-desc">{{ $monitoring['buffer_samples'] === null ? 'Não informado pelo driver' : number_format($monitoring['buffer_samples'], 0, ',', '.').' leituras contabilizadas' }}</div>
            </div>
            <div class="stat min-w-0">
                <div class="stat-figure text-base-content/60"><i data-lucide="clock-3" class="size-5" aria-hidden="true"></i></div>
                <div class="stat-title">Padrões lentos</div>
                <div class="stat-value text-2xl tabular-nums">{{ $monitoring['slow_query_pattern_count'] === null ? '—' : number_format($monitoring['slow_query_pattern_count'], 0, ',', '.') }}</div>
                <div class="stat-desc">Pico acima de {{ number_format($monitoring['slow_query_threshold_ms'], 0, ',', '.') }} ms</div>
            </div>
        </div>
    </section>

    <section class="mb-7 grid min-w-0 gap-4 lg:grid-cols-[minmax(0,1.4fr)_minmax(18rem,0.6fr)]">
        <article class="card card-border min-w-0 bg-base-100">
            <div class="card-body gap-0 p-0">
                <div class="flex flex-wrap items-end justify-between gap-3 border-b border-base-300 p-5 sm:px-6">
                    <div><h2 class="card-title text-base">Consultas que passaram do limite</h2><p class="mt-1 text-sm text-base-content/60">{{ $monitoring['slow_query_source'] ?? 'Histórico do mecanismo indisponível' }}</p></div>
                    <span class="badge badge-ghost badge-sm">{{ $monitoring['slow_query_pattern_count'] === null ? 'Sem telemetria' : 'Limite '.$monitoring['slow_query_threshold_ms'].' ms' }}</span>
                </div>

                @if ($monitoring['slow_query_note'])
                    <div role="alert" class="alert alert-warning m-4 sm:m-5">
                        <i data-lucide="circle-alert" class="size-5" aria-hidden="true"></i>
                        <div><h3 class="font-semibold">Estatísticas de consultas indisponíveis</h3><p class="text-sm">{{ $monitoring['slow_query_note'] }}</p></div>
                    </div>
                @elseif ($monitoring['slow_queries'] === [])
                    <div class="flex items-start gap-3 p-5 sm:p-6">
                        <i data-lucide="circle-check" class="mt-0.5 size-5 text-success" aria-hidden="true"></i>
                        <div><p class="font-medium">Nenhum padrão ultrapassou o limite</p><p class="mt-1 text-sm text-base-content/60">O servidor não registrou picos acima de {{ number_format($monitoring['slow_query_threshold_ms'], 0, ',', '.') }} ms no período disponível.</p></div>
                    </div>
                @else
                    <div class="max-w-full overflow-x-auto">
                        <table class="table table-sm">
                            <thead><tr><th scope="col">Consulta normalizada</th><th scope="col" class="text-right">Execuções</th><th scope="col" class="text-right">Média</th><th scope="col" class="text-right">Pico</th><th scope="col" class="text-right">Acumulado</th><th scope="col" class="text-right">Linhas lidas</th></tr></thead>
                            <tbody>
                                @foreach ($monitoring['slow_queries'] as $query)
                                    <tr>
                                        <th scope="row" class="min-w-64 max-w-xl whitespace-normal break-words font-mono text-xs">
                                            <span class="mb-1 block text-base-content/55">{{ $query['fingerprint'] ?? 'Assinatura indisponível' }}</span>
                                            {{ $query['query'] ?? 'Texto normalizado não informado pelo driver' }}
                                            @if ($query['no_index_count'] !== null && $query['no_index_count'] > 0)
                                                <span class="mt-2 block font-sans text-warning">{{ number_format($query['no_index_count'], 0, ',', '.') }} execuções registraram ausência de índice utilizável</span>
                                            @endif
                                        </th>
                                        <td class="whitespace-nowrap text-right tabular-nums">{{ $query['executions'] === null ? '—' : number_format($query['executions'], 0, ',', '.') }}</td>
                                        <td class="whitespace-nowrap text-right tabular-nums">{{ $query['average_ms'] === null ? '—' : number_format($query['average_ms'], 2, ',', '.').' ms' }}</td>
                                        <td class="whitespace-nowrap text-right font-semibold tabular-nums">{{ $query['maximum_ms'] === null ? '—' : number_format($query['maximum_ms'], 2, ',', '.').' ms' }}</td>
                                        <td class="whitespace-nowrap text-right tabular-nums">{{ $query['total_ms'] === null ? '—' : number_format($query['total_ms'], 0, ',', '.').' ms' }}</td>
                                        <td class="whitespace-nowrap text-right tabular-nums">{{ $query['rows_examined'] === null ? '—' : number_format($query['rows_examined'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="border-t border-base-200 px-5 py-3 text-xs text-base-content/55 sm:px-6">Valores literais e comentários SQL são mascarados. Use a assinatura e a consulta para localizar o trecho no código; revise o plano no ambiente apropriado antes de alterá-lo.</p>
                @endif
            </div>
        </article>

        <article class="card card-border bg-base-100">
            <div class="card-body p-5 sm:p-6">
                <div class="mb-4 flex items-center gap-3"><i data-lucide="server-cog" class="size-5 text-base-content/60" aria-hidden="true"></i><h2 class="card-title text-base">Integridade e carga</h2></div>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4 border-b border-base-200 pb-3"><dt class="text-base-content/60">Tabelas de usuário</dt><dd class="font-medium tabular-nums">{{ $databaseStats['table_count'] === null ? '—' : number_format($databaseStats['table_count'], 0, ',', '.') }}</dd></div>
                    <div class="flex items-center justify-between gap-4 border-b border-base-200 pb-3"><dt class="text-base-content/60">Espaço do banco</dt><dd class="font-medium tabular-nums">{{ $databaseStats['database_size_bytes'] === null ? '—' : \Illuminate\Support\Number::fileSize($databaseStats['database_size_bytes'], precision: 2) }}</dd></div>
                    <div class="flex items-center justify-between gap-4 border-b border-base-200 pb-3"><dt class="text-base-content/60">Deadlocks acumulados</dt><dd class="font-medium tabular-nums">{{ $monitoring['deadlocks'] === null ? '—' : number_format($monitoring['deadlocks'], 0, ',', '.') }}</dd></div>
                    @if (in_array($monitoring['driver'], ['mysql', 'mariadb'], true))
                        <div class="flex items-center justify-between gap-4 border-b border-base-200 pb-3"><dt class="text-base-content/60">Consultas lentas (limiar MySQL)</dt><dd class="font-medium tabular-nums">{{ $monitoring['engine_slow_query_count'] === null ? '—' : number_format($monitoring['engine_slow_query_count'], 0, ',', '.') }}</dd></div>
                    @endif
                    <div class="flex items-center justify-between gap-4"><dt class="text-base-content/60">Tempo desde inicialização</dt><dd class="font-medium tabular-nums">{{ $monitoring['uptime_seconds'] === null ? '—' : number_format(intdiv($monitoring['uptime_seconds'], 86400), 0, ',', '.').' d '.gmdate('H\h i\m', $monitoring['uptime_seconds']) }}</dd></div>
                </dl>
                @if ($monitoring['transactions_committed'] !== null)
                    <div class="mt-4 border-t border-base-200 pt-4">
                        <h3 class="mb-2 text-sm font-semibold">Transações acumuladas</h3>
                        <dl class="grid grid-cols-2 gap-3 text-sm">
                            <div><dt class="text-base-content/60">Confirmadas</dt><dd class="mt-1 font-medium tabular-nums">{{ number_format($monitoring['transactions_committed'], 0, ',', '.') }}</dd></div>
                            <div><dt class="text-base-content/60">Revertidas</dt><dd class="mt-1 font-medium tabular-nums">{{ number_format($monitoring['transactions_rolled_back'], 0, ',', '.') }}</dd></div>
                        </dl>
                    </div>
                @endif
            </div>
        </article>
    </section>

    <section aria-labelledby="monitoring-source-title" class="rounded-field border border-base-300 bg-base-100 p-4 sm:p-5">
        <div class="flex items-start gap-3">
            <i data-lucide="info" class="mt-0.5 size-5 shrink-0 text-base-content/60" aria-hidden="true"></i>
            <div class="min-w-0">
                <h2 id="monitoring-source-title" class="font-semibold">Origem e período dos indicadores</h2>
                <p class="mt-1 text-sm text-base-content/65">{{ $monitoring['slow_query_period'] ?? 'O driver não fornece período para este tipo de métrica.' }}</p>
                <p class="mt-1 text-sm text-base-content/65">Os registros de consultas são agregados pelo mecanismo e não guardam bindings. O limite lento atual é {{ number_format($monitoring['slow_query_threshold_ms'], 0, ',', '.') }} ms; ajuste em <span class="font-mono">config/db-deeper.php</span>.</p>
                <p class="mt-2 text-xs text-base-content/55">Armazenamento de backup configurado: <span class="font-mono">{{ $backupDisk }}</span> / <span class="font-mono">{{ $backupPath }}</span>. A execução e a restauração de backups ainda não estão disponíveis.</p>
            </div>
        </div>
    </section>
@endsection
