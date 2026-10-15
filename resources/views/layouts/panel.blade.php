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
            <header class="navbar sticky top-0 z-10 border-b border-base-300 bg-base-100 px-4 sm:px-6 lg:px-8">
                <div class="navbar-start gap-3">
                    <label for="db-deeper-drawer" class="btn btn-square btn-ghost drawer-button lg:hidden" aria-label="Abrir menu lateral"><i data-lucide="menu" class="size-5" aria-hidden="true"></i></label>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-[0.14em] text-base-content/55">DB Deeper</p>
                        <p class="text-sm font-semibold">{{ $pageTitle }}</p>
                    </div>
                </div>
                <div class="navbar-end gap-2">
                    <span class="badge badge-outline badge-sm hidden sm:inline-flex">Aplicação hospedeira</span>
                    <span class="inline-flex items-center gap-2 text-sm text-base-content/70"><i data-lucide="shield-check" class="size-4 text-primary" aria-hidden="true"></i>Protegido</span>
                </div>
            </header>
            <main class="mx-auto w-full max-w-7xl px-4 py-7 sm:px-6 sm:py-9 lg:px-10">
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
                <a href="{{ route('db-deeper.index') }}" class="mb-8 block rounded-field focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary" aria-label="DB Deeper, início do painel">
                    <img class="h-auto w-52 object-contain" src="{{ asset('vendor/db-deeper/logo.svg') }}" alt="Integrad DB Deeper">
                </a>
                <p class="menu-title">Monitoramento</p>
                <ul class="menu menu-sm w-full">
                    <li><a @class(['menu-active' => $activePage === 'overview']) href="{{ route('db-deeper.index') }}" @if ($activePage === 'overview') aria-current="page" @endif><i data-lucide="layout-dashboard" class="size-4" aria-hidden="true"></i>Visão geral</a></li>
                    <li><a @class(['menu-active' => $activePage === 'database']) href="{{ route('db-deeper.database') }}" @if ($activePage === 'database') aria-current="page" @endif><i data-lucide="database" class="size-4" aria-hidden="true"></i>Estatísticas do banco</a></li>
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
