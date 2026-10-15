# Integrad Laravel Database Backup

An independent Laravel package for creating, protecting, storing, and restoring database backups. It is designed for Laravel applications and S3-compatible storage such as Cloudflare R2.

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

## Development status

- Package skeleton and Composer metadata
- Configurable, protected dashboard
- Database dump drivers: planned
- Streaming compression and authenticated encryption: planned
- S3-compatible uploads and integrity verification: planned
- Retention and restore workflow: planned
- Scheduled execution and failure notifications: planned

Backups contain sensitive production data. Before using the package for recovery, the implementation must verify completed uploads and exercise a restore into an isolated database.

## License

This project is released under the MIT License. You may use, copy, modify, distribute, sublicense, and sell copies, provided the copyright and permission notice remain with copies or substantial portions of the software. The MIT license also includes its warranty and liability disclaimer. See [LICENSE.md](LICENSE.md).

The license does not guarantee that a backup is complete or restorable. Read the operational [backup and recovery disclaimer](DISCLAIMER.md), and verify backups with integrity checks and restore drills before relying on them.
