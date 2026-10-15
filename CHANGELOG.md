# Changelog

All notable changes to `laravel-db-deeper` will be documented in this file.

## [Unreleased]

## [0.1.0] - 2026-10-15

First public development release.

### Added

- Protected `/db-deeper` dashboard with database statistics, monitoring, and live refresh.
- Manual MySQL/MariaDB and PostgreSQL backups using the database's native dump utility.
- Gzip compression and chunked AES-256-GCM encryption for generated backups.
- Configurable Laravel filesystem destinations, including S3-compatible, FTP, and SFTP disks.
- Dashboard-managed backup encryption keys, schedules, preflight diagnostics, execution history, and downloads.
- Laravel Scheduler and queue integration for scheduled backup routines.

### Limitations

- Decryption, restore, retention, and remote read-back verification are not available yet.
- This version is early development; verify backup integrity and recovery procedures before relying on it.
