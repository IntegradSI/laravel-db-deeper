<!doctype html>
<html lang="pt-BR" data-theme="db-deeper">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $pageTitle }} · DB Deeper</title>
    <link rel="stylesheet" href="{{ asset('vendor/db-deeper/app.css') }}">
    <script src="{{ asset('vendor/db-deeper/app.js') }}" defer></script>
</head>
<body class="min-h-screen bg-base-200 text-base-content">
    <div class="drawer lg:drawer-open">
        <input id="db-deeper-drawer" type="checkbox" class="drawer-toggle">
        <div class="drawer-content min-h-screen">
            <header class="navbar sticky top-0 z-10 flex min-h-16 flex-wrap items-center gap-y-2 border-b border-base-300 bg-base-100 px-4 py-2 sm:px-6 lg:px-8"
                data-live-panel
                data-refresh-url="{{ $liveRefreshUrl }}"
                data-refresh-interval="{{ $refreshInterval }}">
                <div class="navbar-start gap-3">
                    <label for="db-deeper-drawer" class="btn btn-square btn-ghost drawer-button lg:hidden" aria-label="Abrir menu lateral"><i data-lucide="menu" class="size-5" aria-hidden="true"></i></label>
                    <div>
                        <p class="text-xs font-medium text-base-content/55">DB Deeper · {{ config('app.name') }}</p>
                        <p class="text-sm font-semibold">{{ $pageTitle }}</p>
                    </div>
                </div>
                <div class="navbar-end flex-wrap gap-2">
                    <div class="flex items-center gap-2 text-xs text-base-content/60">
                        <span class="status status-success status-sm" data-refresh-dot aria-hidden="true"></span>
                        <span data-refresh-status role="status" aria-live="polite">Atualização automática ativa</span>
                        <span aria-hidden="true">·</span>
                        <span class="hidden sm:inline">Próxima em <span class="font-mono tabular-nums" data-refresh-countdown>{{ $refreshInterval }}s</span></span>
                    </div>
                    <time class="text-xs tabular-nums text-base-content/55" data-refresh-last-updated datetime="{{ now()->toIso8601String() }}">{{ now()->format('H:i:s') }}</time>
                    <button type="button" class="btn btn-ghost btn-sm" data-refresh-toggle aria-pressed="false" aria-label="Pausar atualização automática">
                        <i data-lucide="pause" data-refresh-icon class="size-4" aria-hidden="true"></i><span data-refresh-toggle-label>Pausar</span>
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" data-refresh-now>
                        <i data-lucide="refresh-cw" class="size-4" aria-hidden="true"></i><span>Atualizar</span>
                    </button>
                </div>
            </header>
            <main class="mx-auto w-full max-w-[100rem] px-4 py-6 sm:px-6 sm:py-8 lg:px-10">
                <p class="mb-4 hidden rounded-field border border-error/30 bg-error/5 px-4 py-3 text-sm text-error" data-refresh-error role="alert"></p>
                @yield('content')
                <footer class="mt-8 flex flex-col gap-2 border-t border-base-300 pt-5 text-xs text-base-content/50 sm:flex-row sm:items-center sm:justify-between">
                    <span>DB Deeper · Integrad</span>
                    <span>As configurações exibidas vêm da aplicação hospedeira.</span>
                </footer>
            </main>
        </div>
        <div class="drawer-side z-20">
            <label for="db-deeper-drawer" aria-label="Fechar menu lateral" class="drawer-overlay"></label>
            <aside class="flex min-h-full w-72 flex-col border-r border-base-300 bg-base-100 px-4 py-5 text-base-content">
                <a href="{{ route('db-deeper.index') }}" class="mb-8 block rounded-field focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-base-content" aria-label="DB Deeper, início do painel">
                    <img class="h-auto w-52 object-contain" src="{{ asset('vendor/db-deeper/logo.svg') }}" alt="Integrad DB Deeper" width="460" height="120">
                </a>
                <div class="mb-5 rounded-field border border-base-300 bg-base-200 p-3">
                    <p class="text-xs font-semibold text-base-content/55">CONEXÃO MONITORADA</p>
                    <p class="mt-1 flex items-center gap-2 text-sm font-medium"><i data-lucide="database" class="size-4 text-base-content/65" aria-hidden="true"></i>{{ config('db-deeper.backup.connection') ?: config('database.default') }}</p>
                    <p class="mt-1 text-xs text-base-content/55">{{ strtoupper((string) config('database.connections.'.(config('db-deeper.backup.connection') ?: config('database.default')).'.driver', 'PDO')) }} · {{ config('app.env') }}</p>
                </div>
                <p class="menu-title">Operação</p>
                <ul class="menu menu-sm w-full">
                    <li><a @class(['menu-active' => $activePage === 'overview']) href="{{ route('db-deeper.index') }}" @if ($activePage === 'overview') aria-current="page" @endif><i data-lucide="activity" class="size-4" aria-hidden="true"></i>Saúde e atividade</a></li>
                    <li><a @class(['menu-active' => $activePage === 'database']) href="{{ route('db-deeper.database') }}" @if ($activePage === 'database') aria-current="page" @endif><i data-lucide="database" class="size-4" aria-hidden="true"></i>Banco e tabelas</a></li>
                </ul>
                <div class="mt-auto border-t border-base-300 pt-4">
                    <div class="flex items-center gap-3 rounded-field px-3 py-2">
                        <span class="inline-flex size-9 items-center justify-center rounded-field bg-primary text-primary-content"><i data-lucide="shield-check" class="size-4" aria-hidden="true"></i></span>
                        <div><p class="text-sm font-semibold">DB Deeper</p><p class="text-xs text-base-content/55">Pacote da Integrad</p></div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</body>
</html>
