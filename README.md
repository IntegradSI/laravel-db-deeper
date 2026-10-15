<p align="center">
    <img src="resources/dist/logo.svg" alt="Integrad DB Deeper — database backup for Laravel" width="460">
</p>

<p align="center">
    Database backup and recovery for Laravel.
</p>

DB Deeper is an independent Laravel package in early development. It provides a protected dashboard at `/db-deeper`, manual encrypted backups, and managed backup schedules. Retention and restore workflows are still planned.

## Requirements

- PHP 8.3 or newer
- Laravel 11, 12, or 13

## Development setup

```bash
git clone https://github.com/integrad/laravel-db-deeper.git
cd laravel-db-deeper
composer install
composer test
```

Orchestra Testbench provides the Laravel application used during local development and tests.

For interface changes, follow the [DB Deeper UI/UX process](UI_UX.md), adapted from the PediManga design workflow and the DaisyUI Blueprint MCP.

## Install in a Laravel application

When a tagged release is available on Packagist, install it in a Laravel application:

```bash
composer require integrad/laravel-db-deeper
php artisan vendor:publish --tag=db-deeper-config
php artisan vendor:publish --tag=db-deeper-assets
```

The dashboard defaults to `/db-deeper`. Access is protected by Laravel's `auth` middleware and the `viewDbDeeper` Gate ability. Define that ability in the host application and allow only trusted operators:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewDbDeeper', function ($user): bool {
    return $user->is_admin;
});
```

The route, middleware, and dashboard path can be changed in `config/db-deeper.php`. Set `dashboard.enabled` to `false` to disable the panel.

## Storage destinations

The backup workflow uses a filesystem disk from the host Laravel application, selected with `db-deeper.backup.disk`. Configure the disk in the host application's `config/filesystems.php`; storage credentials belong in the host application's environment or secret manager. Laravel disks can use S3-compatible storage (including Cloudflare R2), FTP, or SFTP.

Laravel's FTP and SFTP filesystem adapters are optional dependencies. Install the adapter required by the host application:

```bash
composer require league/flysystem-ftp:^3.0
# or
composer require league/flysystem-sftp-v3:^3.0
```

Set `db-deeper.backup.disk` to the configured disk name, such as `backup-ftp` or `backup-sftp`.

## Create a manual backup

The first backup version supports MySQL/MariaDB with `mysqldump` and PostgreSQL with `pg_dump`. Install the matching client utility on the machine that runs Artisan. Backups are streamed through gzip compression and chunked AES-256-GCM encryption, then uploaded to the selected Laravel filesystem disk. The temporary artifact and temporary database credential file are restricted to the current OS user and removed after the command finishes.

Generate a key once and save it in the host application's secret manager:

```bash
php artisan db-deeper:backup-key
```

Set the printed Base64 value as `db-deeper.backup.encryption_key` in the host application's published `config/db-deeper.php`. Keep the key separately from the backup destination. Losing it makes those backups unreadable. Do not commit the key or include it in logs.

Run a backup using the configured connection and disk:

```bash
php artisan db-deeper:backup
```

Or choose a configured connection and disk explicitly:

```bash
php artisan db-deeper:backup --connection=mysql --disk=backup-s3
```

The command prints the resulting object path, driver, and encrypted file size. SQLite and SQL Server connections currently report that their dump drivers are unsupported. The package does not yet provide decryption, restore, remote read-back verification, or retention; validate recovery with an isolated restore workflow before relying on backups.

## Scheduled backups and queue workers

The package registers due backup routines with Laravel's Scheduler. The Scheduler checks the routine table and dispatches the `RunDatabaseBackup` job to the host application's `default` queue. The host application remains responsible for running both Laravel's scheduler and a queue worker. See the [operations guide](docs/operations.md) for production cron, local development, worker, and Horizon configuration.

## Development status

- Package skeleton and Composer metadata
- Configurable, protected dashboard
- Manual MySQL/MariaDB and PostgreSQL dumps
- Streaming gzip compression and chunked AES-256-GCM encryption
- Laravel filesystem uploads, including configured S3-compatible, FTP, and SFTP disks
- Dashboard-managed backup schedules dispatched through Laravel's Scheduler and queue
- SQLite and SQL Server dump drivers: planned
- Remote integrity verification, retention, restore, and notifications
- Retention and restore workflow: planned

Backups contain sensitive production data. Before relying on this package for recovery, completed uploads must be verified and restores exercised against an isolated database.

## License

This project is licensed under the PolyForm Noncommercial License 1.0.0. Commercial use requires prior written authorization from Integrad at josue.gomes@integrad.com.br. Read the full [license terms](LICENSE.md). This is source-available software and is not an OSI-approved open source license.

Required Notice: Copyright Integrad. For commercial licensing, obtain prior written authorization at josue.gomes@integrad.com.br.

The license does not guarantee that a backup is complete or restorable. Read the operational [backup and recovery disclaimer](DISCLAIMER.md), and verify backups with integrity checks and restore drills before relying on them.
