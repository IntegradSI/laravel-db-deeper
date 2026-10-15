<div class="grid gap-6" data-live-fragment>
    @if (! $monitoring['connected'])
        <div role="alert" class="alert alert-error">
            <i data-lucide="circle-alert" class="size-5" aria-hidden="true"></i>
            <div><h2 class="font-semibold">Banco sem resposta</h2><p class="text-sm">{{ $monitoring['slow_query_note'] }}</p></div>
        </div>
    @endif

    @if ($monitoring['recommendations'] !== [])
        <section aria-labelledby="attention-title">
            <div class="mb-3 flex items-center gap-2">
                <i data-lucide="circle-alert" class="size-4 text-warning" aria-hidden="true"></i>
                <h2 id="attention-title" class="text-sm font-semibold">Atenção operacional</h2>
                <span class="badge badge-warning badge-soft badge-sm">{{ count($monitoring['recommendations']) }} {{ count($monitoring['recommendations']) === 1 ? 'ponto' : 'pontos' }}</span>
            </div>
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
        <div role="status" class="flex items-start gap-3 rounded-field border border-base-300 bg-base-100 p-4 text-sm">
            <i data-lucide="circle-check" class="mt-0.5 size-5 text-success" aria-hidden="true"></i>
            <div><p class="font-medium">Indicadores dentro dos limites configurados</p><p class="mt-1 text-base-content/60">Esta leitura não encontrou sinais de atenção entre as métricas disponíveis.</p></div>
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3 rounded-field border border-base-300 bg-base-100 px-4 py-3">
        <div class="flex items-center gap-2">
            <span @class(['status status-success status-sm' => $monitoring['connected'], 'status status-error status-sm' => ! $monitoring['connected']]) aria-hidden="true"></span>
            <p class="text-sm font-semibold">{{ $monitoring['connected'] ? 'Banco respondendo' : 'Banco sem resposta' }}</p>
            <span class="text-xs text-base-content/55">{{ $monitoring['connected'] ? 'Latência de '.number_format($monitoring['latency_ms'], 2, ',', '.').' ms' : 'Última leitura sem sucesso' }}</span>
        </div>
        <p class="text-xs text-base-content/55">{{ $monitoring['driver_label'] }} · fonte: servidor conectado</p>
    </div>

    <section aria-labelledby="live-metrics-title">
        <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
            <div><h2 id="live-metrics-title" class="text-lg font-semibold">Atividade em tempo real</h2><p class="mt-1 text-sm text-base-content/60">{{ $monitoring['driver_label'] }} · conexão <span class="font-mono">{{ $databaseConnection }}</span></p></div>
            <span class="text-xs text-base-content/55">Amostra atual do servidor</span>
        </div>

        <div class="stats grid w-full grid-cols-2 overflow-hidden border border-base-300 bg-base-100 sm:grid-cols-3 xl:grid-cols-6">
            <div class="stat min-w-0 border-b border-base-300 sm:border-r">
                <div class="stat-figure text-base-content/50"><i data-lucide="gauge" class="size-4" aria-hidden="true"></i></div>
                <div class="stat-title">Latência</div>
                <div class="stat-value text-2xl tabular-nums">{{ $monitoring['latency_ms'] === null ? '—' : number_format($monitoring['latency_ms'], 2, ',', '.').' ms' }}</div>
                <div class="stat-desc">Consulta de verificação</div>
            </div>
            <div class="stat min-w-0 border-b border-base-300 xl:border-r">
                <div class="stat-figure text-base-content/50"><i data-lucide="layers-3" class="size-4" aria-hidden="true"></i></div>
                <div class="stat-title">Conexões abertas</div>
                <div class="stat-value text-2xl tabular-nums">{{ $monitoring['active_connections'] === null ? '—' : number_format($monitoring['active_connections'], 0, ',', '.') }}<span class="text-sm font-medium text-base-content/50"> / {{ $monitoring['max_connections'] === null ? '—' : number_format($monitoring['max_connections'], 0, ',', '.') }}</span></div>
                <div class="stat-desc">{{ $monitoring['connection_usage_percent'] === null ? 'Limite indisponível' : number_format($monitoring['connection_usage_percent'], 1, ',', '.').'% do limite' }}</div>
                @if ($monitoring['connection_usage_percent'] !== null)
                    <progress class="progress mt-2 w-full" value="{{ min(100, $monitoring['connection_usage_percent']) }}" max="100" aria-label="{{ number_format($monitoring['connection_usage_percent'], 1, ',', '.') }} por cento das conexões em uso"></progress>
                @endif
            </div>
            <div class="stat min-w-0 border-b border-base-300 sm:border-r xl:border-r">
                <div class="stat-figure text-base-content/50"><i data-lucide="activity" class="size-4" aria-hidden="true"></i></div>
                <div class="stat-title">Consultas ativas</div>
                <div class="stat-value text-2xl tabular-nums">{{ $monitoring['active_queries'] === null ? '—' : number_format($monitoring['active_queries'], 0, ',', '.') }}</div>
                <div class="stat-desc">{{ in_array($monitoring['driver'], ['mysql', 'mariadb'], true) ? 'Threads executando agora' : 'Execuções neste banco' }}</div>
            </div>
            <div class="stat min-w-0 border-b border-base-300 xl:border-r">
                <div class="stat-figure text-base-content/50"><i data-lucide="user-round-check" class="size-4" aria-hidden="true"></i></div>
                <div class="stat-title">Usuários logados</div>
                <div class="stat-value text-2xl tabular-nums">{{ $sessionActivity['active_users'] === null ? '—' : number_format($sessionActivity['active_users'], 0, ',', '.') }}</div>
                <div class="stat-desc">{{ $sessionActivity['available'] ? number_format($sessionActivity['active_sessions'], 0, ',', '.').' sessões · últimos '.$sessionActivity['activity_window_minutes'].' min' : 'Contagem indisponível' }}</div>
            </div>
            <div class="stat min-w-0 border-b border-base-300 sm:border-r xl:border-r">
                <div class="stat-figure text-base-content/50"><i data-lucide="clock-3" class="size-4" aria-hidden="true"></i></div>
                <div class="stat-title">Padrões lentos</div>
                <div class="stat-value text-2xl tabular-nums">{{ $monitoring['slow_query_pattern_count'] === null ? '—' : number_format($monitoring['slow_query_pattern_count'], 0, ',', '.') }}</div>
                <div class="stat-desc">Pico acima de {{ number_format($monitoring['slow_query_threshold_ms'], 0, ',', '.') }} ms</div>
            </div>
            <div class="stat min-w-0 border-base-300">
                <div class="stat-figure text-base-content/50"><i data-lucide="database" class="size-4" aria-hidden="true"></i></div>
                <div class="stat-title">Cache de páginas</div>
                <div class="stat-value text-2xl tabular-nums">{{ $monitoring['buffer_hit_percent'] === null ? '—' : number_format($monitoring['buffer_hit_percent'], 2, ',', '.').'%' }}</div>
                <div class="stat-desc">{{ $monitoring['buffer_samples'] === null ? 'Sem amostra disponível' : number_format($monitoring['buffer_samples'], 0, ',', '.').' leituras' }}</div>
            </div>
        </div>
    </section>

    <section class="grid min-w-0 gap-4 2xl:grid-cols-[minmax(0,1.65fr)_minmax(19rem,0.65fr)]">
        <article class="card card-border min-w-0 bg-base-100">
            <div class="card-body min-w-0 gap-0 p-0">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-base-300 p-4 sm:px-5">
                    <div class="flex items-center gap-3"><span class="flex size-9 items-center justify-center rounded-field bg-base-200 text-base-content/70"><i data-lucide="clock-3" class="size-4" aria-hidden="true"></i></span><div><h2 class="card-title text-base">Consultas lentas</h2><p class="mt-0.5 text-xs text-base-content/55">{{ $monitoring['slow_query_source'] ?? 'Telemetria não disponível' }}</p></div></div>
                    <span class="badge badge-ghost badge-sm">{{ $monitoring['slow_query_pattern_count'] === null ? 'Sem telemetria' : 'Limite '.$monitoring['slow_query_threshold_ms'].' ms' }}</span>
                </div>

                @if ($monitoring['slow_query_note'])
                    <div role="alert" class="alert alert-warning m-4 sm:m-5">
                        <i data-lucide="circle-alert" class="size-5" aria-hidden="true"></i>
                        <div><h3 class="font-semibold">Estatísticas de consultas indisponíveis</h3><p class="text-sm">{{ $monitoring['slow_query_note'] }}</p></div>
                    </div>
                @elseif ($monitoring['slow_queries'] === [])
                    <div class="flex items-start gap-3 p-5">
                        <i data-lucide="circle-check" class="mt-0.5 size-5 text-success" aria-hidden="true"></i>
                        <div><p class="font-medium">Nenhum padrão ultrapassou o limite</p><p class="mt-1 text-sm text-base-content/60">O motor não registrou picos acima de {{ number_format($monitoring['slow_query_threshold_ms'], 0, ',', '.') }} ms no período disponível.</p></div>
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
                                                <span class="mt-2 block font-sans font-medium text-warning">{{ number_format($query['no_index_count'], 0, ',', '.') }} execuções sem índice utilizável</span>
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
                    <p class="border-t border-base-200 px-4 py-3 text-xs text-base-content/55 sm:px-5">Literais e comentários SQL são mascarados. O histórico depende do mecanismo e pode reiniciar com o servidor ou com a limpeza das estatísticas.</p>
                @endif
            </div>
        </article>

        <article class="card card-border bg-base-100">
            <div class="card-body p-4 sm:p-5">
                <div class="mb-4 flex items-center gap-3"><span class="flex size-9 items-center justify-center rounded-field bg-base-200 text-base-content/70"><i data-lucide="server-cog" class="size-4" aria-hidden="true"></i></span><div><h2 class="card-title text-base">Resumo do servidor</h2><p class="text-xs text-base-content/55">Capacidade, integridade e período</p></div></div>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4 border-b border-base-200 pb-3"><dt class="text-base-content/60">Banco</dt><dd class="max-w-48 break-all text-right font-mono text-xs">{{ $databaseStats['database_name'] ?? $databaseConnection }}</dd></div>
                    <div class="flex items-center justify-between gap-4 border-b border-base-200 pb-3"><dt class="text-base-content/60">Tabelas visíveis</dt><dd class="font-medium tabular-nums">{{ $databaseStats['table_count'] === null ? '—' : number_format($databaseStats['table_count'], 0, ',', '.') }}</dd></div>
                    <div class="flex items-center justify-between gap-4 border-b border-base-200 pb-3"><dt class="text-base-content/60">Espaço do banco</dt><dd class="font-medium tabular-nums">{{ $databaseStats['database_size_bytes'] === null ? '—' : \Illuminate\Support\Number::fileSize($databaseStats['database_size_bytes'], precision: 2) }}</dd></div>
                    <div class="flex items-center justify-between gap-4 border-b border-base-200 pb-3"><dt class="text-base-content/60">Deadlocks acumulados</dt><dd class="font-medium tabular-nums">{{ $monitoring['deadlocks'] === null ? '—' : number_format($monitoring['deadlocks'], 0, ',', '.') }}</dd></div>
                    @if (in_array($monitoring['driver'], ['mysql', 'mariadb'], true))
                        <div class="flex items-center justify-between gap-4 border-b border-base-200 pb-3"><dt class="text-base-content/60">Slow queries (servidor)</dt><dd class="font-medium tabular-nums">{{ $monitoring['engine_slow_query_count'] === null ? '—' : number_format($monitoring['engine_slow_query_count'], 0, ',', '.') }}</dd></div>
                    @endif
                    <div class="flex items-center justify-between gap-4"><dt class="text-base-content/60">Uptime</dt><dd class="font-medium tabular-nums">{{ $monitoring['uptime_seconds'] === null ? '—' : number_format(intdiv($monitoring['uptime_seconds'], 86400), 0, ',', '.').' d '.gmdate('H\h i\m', $monitoring['uptime_seconds']) }}</dd></div>
                </dl>
                @if ($monitoring['transactions_committed'] !== null)
                    <div class="mt-4 border-t border-base-200 pt-4"><h3 class="mb-2 text-xs font-semibold text-base-content/60">TRANSAÇÕES DESDE O ÚLTIMO RESET</h3><dl class="grid grid-cols-2 gap-3 text-sm"><div><dt class="text-base-content/60">Confirmadas</dt><dd class="mt-1 font-semibold tabular-nums">{{ number_format($monitoring['transactions_committed'], 0, ',', '.') }}</dd></div><div><dt class="text-base-content/60">Revertidas</dt><dd class="mt-1 font-semibold tabular-nums">{{ number_format($monitoring['transactions_rolled_back'], 0, ',', '.') }}</dd></div></dl></div>
                @endif
            </div>
        </article>
    </section>

    <section aria-labelledby="telemetry-title" class="grid gap-3 rounded-field border border-base-300 bg-base-100 p-4 sm:grid-cols-2 sm:p-5">
        <div class="flex items-start gap-3"><i data-lucide="info" class="mt-0.5 size-4 shrink-0 text-base-content/55" aria-hidden="true"></i><div><h2 id="telemetry-title" class="text-sm font-semibold">Como ler esta tela</h2><p class="mt-1 text-xs leading-5 text-base-content/60">{{ $monitoring['slow_query_period'] ?? 'O driver não fornece período para este tipo de métrica.' }} O limite de consulta lenta está em {{ number_format($monitoring['slow_query_threshold_ms'], 0, ',', '.') }} ms e pode ser ajustado em <span class="font-mono">config/db-deeper.php</span>.</p></div></div>
        <div class="flex items-start gap-3 sm:border-l sm:border-base-300 sm:pl-5"><i data-lucide="user-round-check" class="mt-0.5 size-4 shrink-0 text-base-content/55" aria-hidden="true"></i><div><h3 class="text-sm font-semibold">Usuários da aplicação</h3><p class="mt-1 text-xs leading-5 text-base-content/60">{{ $sessionActivity['note'] }}</p></div></div>
        <p class="text-xs leading-5 text-base-content/55 sm:col-span-2">Backup configurado: <span class="font-mono">{{ $backupDisk }}</span> / <span class="font-mono">{{ $backupPath }}</span>. Geração e restauração ainda não estão disponíveis.</p>
    </section>
</div>
