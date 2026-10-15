@extends('db-deeper::layouts.panel', ['activePage' => 'overview', 'pageTitle' => 'Visão geral'])

@section('content')
    <section class="mb-8 flex flex-col gap-4 border-b border-base-300 pb-7 sm:flex-row sm:items-end sm:justify-between">
        <div class="max-w-2xl">
            <p class="mb-2 text-sm font-semibold text-primary">Visão geral</p>
            <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Backups do banco de dados</h1>
            <p class="mt-3 text-base leading-7 text-base-content/65">Confira a conexão e o destino configurados para proteger os dados desta aplicação.</p>
        </div>
        <div class="flex items-center gap-2 text-sm text-base-content/60">
            <span class="status status-sm status-warning" aria-hidden="true"></span>
            <span>Mecanismo em desenvolvimento</span>
        </div>
    </section>

    <section aria-labelledby="configuration-title" class="mb-7">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-2">
            <div>
                <h2 id="configuration-title" class="text-lg font-semibold">Configuração atual</h2>
                <p class="mt-1 text-sm text-base-content/60">Valores lidos da configuração ativa do Laravel.</p>
            </div>
            <span class="badge badge-ghost badge-sm">Somente leitura</span>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <article class="card card-border bg-base-100">
                <div class="card-body gap-4 p-5 sm:p-6">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex size-10 items-center justify-center rounded-field bg-primary/10 text-primary"><i data-lucide="database" class="size-5" aria-hidden="true"></i></span>
                        <span class="badge badge-outline badge-sm">Selecionada</span>
                    </div>
                    <div>
                        <h2 class="text-sm font-medium text-base-content/60">Conexão do banco</h2>
                        <p class="mt-1 break-all font-mono text-xl font-semibold">{{ $databaseConnection }}</p>
                        <p class="mt-2 text-sm text-base-content/60">Conexão selecionada no Laravel.</p>
                    </div>
                </div>
            </article>
            <article class="card card-border bg-base-100">
                <div class="card-body gap-4 p-5 sm:p-6">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex size-10 items-center justify-center rounded-field bg-primary/10 text-primary"><i data-lucide="hard-drive" class="size-5" aria-hidden="true"></i></span>
                        <span class="badge badge-outline badge-sm">Configurado</span>
                    </div>
                    <div>
                        <h2 class="text-sm font-medium text-base-content/60">Destino de armazenamento</h2>
                        <p class="mt-1 break-all font-mono text-xl font-semibold">{{ $backupDisk }}</p>
                        <p class="mt-2 break-all text-sm text-base-content/60">Caminho: <span class="font-mono">{{ $backupPath }}</span></p>
                    </div>
                </div>
            </article>
            <article class="card card-border bg-base-100 md:col-span-2 xl:col-span-1">
                <div class="card-body gap-4 p-5 sm:p-6">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex size-10 items-center justify-center rounded-field bg-warning/15 text-warning"><i data-lucide="activity" class="size-5" aria-hidden="true"></i></span>
                        <span class="badge badge-warning badge-soft badge-sm">Em desenvolvimento</span>
                    </div>
                    <div>
                        <h2 class="text-sm font-medium text-base-content/60">Mecanismo de backup</h2>
                        <p class="mt-1 text-xl font-semibold">Ainda não disponível</p>
                        <p class="mt-2 text-sm text-base-content/60">O pacote ainda não executa backups.</p>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <section role="status" aria-labelledby="readiness-title" class="rounded-box border border-primary/20 bg-primary/5 p-5 sm:p-6">
        <div class="flex items-start gap-4">
            <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-field bg-base-100 text-primary"><i data-lucide="circle-help" class="size-5" aria-hidden="true"></i></span>
            <div class="max-w-3xl">
                <h2 id="readiness-title" class="font-semibold">O painel está instalado</h2>
                <p class="mt-1 text-sm leading-6 text-base-content/70">O mecanismo para gerar, enviar, verificar e restaurar cópias ainda está em desenvolvimento. Nesta versão, a tela apenas apresenta a configuração; nenhum backup será iniciado.</p>
            </div>
        </div>
    </section>
@endsection
