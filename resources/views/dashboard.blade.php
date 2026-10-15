<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DB Deeper · Integrad</title>
    <link rel="stylesheet" href="{{ asset('vendor/db-deeper/app.css') }}">
</head>
<body>
    <main class="backup-shell">
        <header class="backup-header">
            <a class="backup-brand" href="{{ route('db-deeper.index') }}" aria-label="Integrad DB Deeper">
                <img class="backup-logo" src="{{ asset('vendor/db-deeper/logo.svg') }}" alt="Integrad DB Deeper">
            </a>
            <span class="backup-badge">Painel protegido</span>
        </header>

        <section class="backup-intro" aria-labelledby="page-title">
            <p class="backup-eyebrow">VISÃO GERAL</p>
            <h1 id="page-title">Backups do banco de dados</h1>
            <p>Configure o armazenamento e acompanhe as cópias de segurança da aplicação.</p>
        </section>

        <section class="backup-grid" aria-label="Configuração atual">
            <article class="backup-card">
                <span class="backup-label">Conexão do banco</span>
                <strong>{{ $databaseConnection }}</strong>
                <small>Conexão selecionada no Laravel</small>
            </article>
            <article class="backup-card">
                <span class="backup-label">Destino</span>
                <strong>{{ $backupDisk }}</strong>
                <small>{{ $backupPath }}</small>
            </article>
            <article class="backup-card">
                <span class="backup-label">Estado do mecanismo</span>
                <strong>Em desenvolvimento</strong>
                <small>O pacote ainda não executa backups</small>
            </article>
        </section>

        <section class="backup-notice" aria-labelledby="setup-title">
            <div class="backup-notice-icon" aria-hidden="true">i</div>
            <div>
                <h2 id="setup-title">O painel está instalado</h2>
                <p>O mecanismo de geração, envio, verificação e restauração está em desenvolvimento. Nenhum backup será iniciado por esta tela nesta versão.</p>
            </div>
        </section>
    </main>
</body>
</html>
