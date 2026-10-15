<div class="grid gap-6" data-live-fragment>
    @if (! $stats['connected'])
        <div role="alert" class="alert alert-error">
            <i data-lucide="circle-alert" class="size-5" aria-hidden="true"></i>
            <div><h2 class="font-semibold">Banco indisponível</h2><p class="text-sm">{{ $stats['statistics_note'] }}</p></div>
        </div>
    @elseif (! $stats['supported'])
        <div role="alert" class="alert alert-warning">
            <i data-lucide="circle-alert" class="size-5" aria-hidden="true"></i>
            <div><h2 class="font-semibold">Driver sem estatísticas detalhadas</h2><p class="text-sm">A conexão respondeu, mas o driver <span class="font-mono">{{ $stats['driver'] }}</span> não está entre os drivers PDO padrão cobertos.</p></div>
        </div>
    @endif

    @if ($stats['statistics_note'])
        <div role="alert" class="alert alert-info"><i data-lucide="info" class="size-5" aria-hidden="true"></i><p class="text-sm">{{ $stats['statistics_note'] }}</p></div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3 rounded-field border border-base-300 bg-base-100 px-4 py-3">
        <div class="flex items-center gap-2">
            <span @class(['status status-success status-sm' => $stats['connected'], 'status status-error status-sm' => ! $stats['connected']]) aria-hidden="true"></span>
            <p class="text-sm font-semibold">{{ $stats['connected'] ? 'Banco respondendo' : 'Banco sem resposta' }}</p>
            @if ($stats['latency_ms'] !== null)
                <span class="text-xs text-base-content/55">Latência {{ number_format($stats['latency_ms'], 2, ',', '.') }} ms</span>
            @endif
        </div>
        <p class="text-xs text-base-content/55">Leitura somente de metadados</p>
    </div>

    <section aria-labelledby="database-summary-title">
        <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
            <div><h2 id="database-summary-title" class="text-lg font-semibold">Resumo de capacidade</h2><p class="mt-1 text-sm text-base-content/60">Conexão Laravel <span class="font-mono">{{ $stats['connection_name'] }}</span> · {{ $stats['database_name'] ?? 'banco atual' }}</p></div>
            <span class="text-xs text-base-content/55">{{ $stats['server_version'] ?? 'Versão não informada' }}</span>
        </div>

        <div class="stats grid w-full grid-cols-2 overflow-hidden border border-base-300 bg-base-100 sm:grid-cols-4">
            <div class="stat min-w-0 border-b border-base-300 sm:border-r">
                <div class="stat-figure text-base-content/50"><i data-lucide="gauge" class="size-4" aria-hidden="true"></i></div>
                <div class="stat-title">Latência</div>
                <div class="stat-value text-2xl tabular-nums">{{ $stats['latency_ms'] === null ? '—' : number_format($stats['latency_ms'], 2, ',', '.').' ms' }}</div>
                <div class="stat-desc">Consulta de verificação</div>
            </div>
            <div class="stat min-w-0 border-b border-base-300 sm:border-r">
                <div class="stat-figure text-base-content/50"><i data-lucide="table-properties" class="size-4" aria-hidden="true"></i></div>
                <div class="stat-title">Tabelas</div>
                <div class="stat-value text-2xl tabular-nums">{{ $stats['table_count'] === null ? '—' : number_format($stats['table_count'], 0, ',', '.') }}</div>
                <div class="stat-desc">Objetos visíveis na conexão</div>
            </div>
            <div class="stat min-w-0 border-base-300 sm:border-r">
                <div class="stat-figure text-base-content/50"><i data-lucide="eye" class="size-4" aria-hidden="true"></i></div>
                <div class="stat-title">Views</div>
                <div class="stat-value text-2xl tabular-nums">{{ $stats['view_count'] === null ? '—' : number_format($stats['view_count'], 0, ',', '.') }}</div>
                <div class="stat-desc">Views visíveis na conexão</div>
            </div>
            <div class="stat min-w-0">
                <div class="stat-figure text-base-content/50"><i data-lucide="hard-drive" class="size-4" aria-hidden="true"></i></div>
                <div class="stat-title">Espaço total</div>
                <div class="stat-value text-2xl tabular-nums">{{ $stats['database_size_bytes'] === null ? '—' : \Illuminate\Support\Number::fileSize($stats['database_size_bytes'], precision: 2) }}</div>
                <div class="stat-desc">Informado pelo mecanismo</div>
            </div>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_minmax(20rem,0.8fr)]">
        <article class="card card-border bg-base-100">
            <div class="card-body p-4 sm:p-5">
                <div class="mb-4 flex items-center gap-3"><span class="flex size-9 items-center justify-center rounded-field bg-base-200 text-base-content/70"><i data-lucide="server-cog" class="size-4" aria-hidden="true"></i></span><div><h2 class="card-title text-base">Identificação do servidor</h2><p class="text-xs text-base-content/55">Origem dos metadados desta coleta</p></div></div>
                <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div><dt class="text-xs text-base-content/55">Motor PDO</dt><dd class="mt-1 font-medium">{{ $stats['driver_label'] }}</dd></div>
                    <div><dt class="text-xs text-base-content/55">Versão</dt><dd class="mt-1 break-words font-mono text-sm">{{ $stats['server_version'] ?? 'Não disponível' }}</dd></div>
                    <div><dt class="text-xs text-base-content/55">Banco selecionado</dt><dd class="mt-1 break-all font-mono text-sm">{{ $stats['database_name'] ?? 'Não disponível' }}</dd></div>
                    <div><dt class="text-xs text-base-content/55">Conexão Laravel</dt><dd class="mt-1 break-all font-mono text-sm">{{ $stats['connection_name'] }}</dd></div>
                </dl>
            </div>
        </article>

        <article class="card card-border bg-base-100">
            <div class="card-body p-4 sm:p-5">
                <div class="mb-4 flex items-center gap-3"><span class="flex size-9 items-center justify-center rounded-field bg-base-200 text-base-content/70"><i data-lucide="layers-3" class="size-4" aria-hidden="true"></i></span><div><h2 class="card-title text-base">Composição de espaço</h2><p class="text-xs text-base-content/55">Valores que o driver consegue medir</p></div></div>
                <dl class="space-y-4 text-sm">
                    <div><div class="flex items-center justify-between gap-4"><dt class="text-base-content/65">Dados</dt><dd class="font-medium tabular-nums">{{ $stats['data_size_bytes'] === null ? '—' : \Illuminate\Support\Number::fileSize($stats['data_size_bytes'], precision: 2) }}</dd></div></div>
                    <div><div class="flex items-center justify-between gap-4"><dt class="text-base-content/65">Índices</dt><dd class="font-medium tabular-nums">{{ $stats['index_size_bytes'] === null ? '—' : \Illuminate\Support\Number::fileSize($stats['index_size_bytes'], precision: 2) }}</dd></div></div>
                    <div><div class="flex items-center justify-between gap-4"><dt class="text-base-content/65">Livre ou reutilizável</dt><dd class="font-medium tabular-nums">{{ $stats['unused_size_bytes'] === null ? '—' : \Illuminate\Support\Number::fileSize($stats['unused_size_bytes'], precision: 2) }}</dd></div></div>
                    @if ($stats['log_size_bytes'] !== null)
                        <div class="flex items-center justify-between gap-4 border-t border-base-200 pt-3"><dt class="text-base-content/65">Log de transações</dt><dd class="font-medium tabular-nums">{{ \Illuminate\Support\Number::fileSize($stats['log_size_bytes'], precision: 2) }}</dd></div>
                    @endif
                </dl>
                @if ($stats['data_size_bytes'] !== null && $stats['index_size_bytes'] !== null && ($stats['data_size_bytes'] + $stats['index_size_bytes']) > 0)
                    @php($allocatedSpace = $stats['data_size_bytes'] + $stats['index_size_bytes'])
                    <div class="mt-5 border-t border-base-200 pt-4">
                        <div class="mb-2 flex justify-between text-xs text-base-content/60"><span>Dados · {{ number_format(($stats['data_size_bytes'] / $allocatedSpace) * 100, 1, ',', '.') }}%</span><span>Índices · {{ number_format(($stats['index_size_bytes'] / $allocatedSpace) * 100, 1, ',', '.') }}%</span></div>
                        <progress class="progress w-full" value="{{ ($stats['data_size_bytes'] / $allocatedSpace) * 100 }}" max="100" aria-label="{{ number_format(($stats['data_size_bytes'] / $allocatedSpace) * 100, 1, ',', '.') }} por cento do espaço alocado corresponde a dados"></progress>
                        <p class="mt-1 text-xs text-base-content/50">A barra compara apenas dados e índices. Espaço reutilizável não entra neste percentual.</p>
                    </div>
                @endif
            </div>
        </article>
    </section>

    <section aria-labelledby="tables-title" class="card card-border min-w-0 bg-base-100">
        <div class="card-body min-w-0 gap-0 p-0">
            <div class="flex flex-col gap-2 border-b border-base-300 p-4 sm:flex-row sm:items-end sm:justify-between sm:px-5">
                <div><h2 id="tables-title" class="card-title text-base">Tabelas com maior uso</h2><p class="mt-1 text-sm text-base-content/60">{{ count($stats['tables']) }} de até 25 tabelas visíveis nesta consulta.</p></div>
                @if (in_array($stats['driver'], ['mysql', 'mariadb', 'pgsql', 'sqlsrv'], true))
                    <span class="badge badge-ghost badge-sm">Contagem de linhas estimada</span>
                @endif
            </div>
            @if ($stats['tables'] === [])
                <div class="flex items-start gap-3 p-5"><i data-lucide="circle-help" class="mt-0.5 size-5 text-base-content/50" aria-hidden="true"></i><div><p class="font-medium">Nenhuma tabela disponível</p><p class="mt-1 text-sm text-base-content/60">O driver não retornou tabelas visíveis ou o usuário não tem acesso aos metadados.</p></div></div>
            @else
                <div class="max-w-full overflow-x-auto">
                    <table class="table table-sm">
                        <thead><tr><th scope="col">Tabela</th><th scope="col" class="text-right">Linhas</th><th scope="col" class="text-right">Dados</th><th scope="col" class="text-right">Índices</th><th scope="col" class="text-right">Total</th></tr></thead>
                        <tbody>
                            @foreach ($stats['tables'] as $table)
                                @php($tableSize = ($table['data_size_bytes'] ?? 0) + ($table['index_size_bytes'] ?? 0))
                                <tr>
                                    <th scope="row" class="max-w-72 break-all font-mono text-xs">{{ $table['name'] }}</th>
                                    <td class="whitespace-nowrap text-right tabular-nums">{{ $table['row_count'] === null ? '—' : number_format($table['row_count'], 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap text-right tabular-nums">{{ $table['data_size_bytes'] === null ? '—' : \Illuminate\Support\Number::fileSize($table['data_size_bytes'], precision: 2) }}</td>
                                    <td class="whitespace-nowrap text-right tabular-nums">{{ $table['index_size_bytes'] === null ? '—' : \Illuminate\Support\Number::fileSize($table['index_size_bytes'], precision: 2) }}</td>
                                    <td class="whitespace-nowrap text-right font-semibold tabular-nums">{{ $table['data_size_bytes'] === null && $table['index_size_bytes'] === null ? '—' : \Illuminate\Support\Number::fileSize($tableSize, precision: 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>

    <p class="text-xs leading-5 text-base-content/55">Tabelas e tamanhos são ordenados pelos metadados fornecidos pelo mecanismo. Contagens podem ser estimadas e não substituem uma consulta exata para auditoria.</p>
</div>
