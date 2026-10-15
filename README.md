# Integrad Laravel Database Backup

An independent Laravel package for creating, protecting, storing, and restoring database backups. Storage is configured through the host Laravel application's filesystem disks, including S3-compatible storage such as Cloudflare R2, FTP, and SFTP.

The package is in early development. The current version provides the package foundation and a protected dashboard at `/database-backup`. It does **not** create, upload, prune, or restore backups yet.

## Requirements

- PHP 8.3 or newer
- Laravel 11, 12, or 13

## Local package development

```bash
git clone https://github.com/integrad/laravel-database-backup.git
cd laravel-database-backup
composer install
composer test
```

The package uses Orchestra Testbench as its host Laravel application for development and tests.

## Install in a Laravel application

Once a tagged release is available on Packagist:

```bash
composer require integrad/laravel-database-backup
php artisan vendor:publish --tag=database-backup-config
php artisan vendor:publish --tag=database-backup-assets
```

The dashboard defaults to `/database-backup`. Access is protected by Laravel's `auth` middleware and the `viewDatabaseBackups` Gate ability. Define that ability in the host application and allow only trusted operators:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewDatabaseBackups', function ($user): bool {
    return $user->is_admin;
});
```

The route, middleware, and dashboard path can be changed in `config/database-backup.php`. The panel can be disabled there with `dashboard.enabled`.

## Storage destinations

The package uses the host application's Laravel filesystem disk, selected with `database-backup.backup.disk`. Configure that disk in the host application's `config/filesystems.php`; the package does not need storage credentials in its own config. This allows a deployment to use S3-compatible storage (including Cloudflare R2), FTP, or SFTP.

Laravel's FTP and SFTP filesystem adapters are optional dependencies. Install the adapter required by the host application:

```bash
composer require league/flysystem-ftp:^3.0
# or
composer require league/flysystem-sftp-v3:^3.0
```

Then set `database-backup.backup.disk` to the configured disk name, for example `backup-ftp` or `backup-sftp`. Keep credentials in the host application's environment or secret manager and reference them from `config/filesystems.php`.

The package is still in early development: backup generation and uploads are not implemented yet. These adapters become usable by the backup workflow when that functionality is added.

## Development status

- Package skeleton and Composer metadata
- Configurable, protected dashboard
- Database dump drivers: planned
- Streaming compression and authenticated encryption: planned
- S3-compatible, FTP, and SFTP uploads and integrity verification: planned
- Retention and restore workflow: planned
- Scheduled execution and failure notifications: planned

Backups contain sensitive production data. Before using the package for recovery, the implementation must verify completed uploads and exercise a restore into an isolated database.

## License

This project is licensed under the PolyForm Noncommercial License 1.0.0. Commercial use requires prior written authorization from Integrad at josue.gomes@integrad.com.br. Read the full [license terms](LICENSE.md). This is source-available software and is not an OSI-approved open source license.

Required Notice: Copyright Integrad. For commercial licensing, obtain prior written authorization at josue.gomes@integrad.com.br.

The license does not guarantee that a backup is complete or restorable. Read the operational [backup and recovery disclaimer](DISCLAIMER.md), and verify backups with integrity checks and restore drills before relying on them.
