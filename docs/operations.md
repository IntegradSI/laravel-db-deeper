# Scheduled backups and workers

DB Deeper registers the `db-deeper:queue-due-backups` command with Laravel's Scheduler once per minute. That command finds enabled routines whose `next_run_at` is due, advances the next run time, and dispatches a `RunDatabaseBackup` job.

The backup job is sent to the host application's `default` queue and uses the connection configured by Laravel's `queue.default`. DB Deeper does not install or keep a scheduler process, queue worker, Supervisor, or Horizon process running. The host application must keep those processes available.

## Production scheduler

Add one cron entry for the Laravel application. Replace the path and PHP executable with the values used by the deployment:

```cron
* * * * * cd /path/to/laravel && php artisan schedule:run >> /dev/null 2>&1
```

This invokes all tasks registered with Laravel's Scheduler, including DB Deeper's due-backup dispatcher. No separate cron entry is needed for each backup routine.

For local development, run the Scheduler in a foreground terminal:

```bash
php artisan schedule:work
```

To inspect the registered command:

```bash
php artisan schedule:list
```

## Queue worker

Manual and scheduled backups dispatch `Integrad\DbDeeper\Jobs\RunDatabaseBackup` to the `default` queue. The job allows up to 3,600 seconds and one attempt. The configured queue connection must support asynchronous jobs for a separate worker to process them; a `sync` queue executes the job inline in the requesting process.

Run a worker against the application's default queue connection:

```bash
php artisan queue:work --queue=default --timeout=3600 --tries=1
```

Keep this process running with Supervisor, systemd, or another process manager. Configure the queue connection's `retry_after` value to be longer than the worker timeout. This prevents a still-running dump from being reserved and processed a second time.

For a Redis connection, the command can also name the connection explicitly:

```bash
php artisan queue:work redis --queue=default --timeout=3600 --tries=1
```

## Laravel Horizon

Horizon can run the same job when one of its supervisors listens to the `default` queue. Add that queue to an existing supervisor or create a dedicated supervisor. The job's own timeout is 3,600 seconds, so set the Horizon supervisor timeout above 3,600 seconds and the Redis connection's `retry_after` above the supervisor timeout. For example:

```php
'db-deeper-backups' => [
    'connection' => 'redis',
    'queue' => ['default'],
    'balance' => 'auto',
    'tries' => 1,
    'timeout' => 3660,
],
```

Set the Redis connection's `retry_after` to a value greater than `3660`, and set the process manager's `stopwaitsecs` above `3660` so a deployment does not terminate an active dump early. Restart Horizon after changing its configuration:

```bash
php artisan horizon:terminate
```

The process manager should start Horizon again after it exits. Horizon requires Redis; applications using another queue backend can use Laravel's `queue:work` worker instead.

## Understanding the two processes

| Process | Responsibility | Runs |
| --- | --- | --- |
| Laravel Scheduler | Finds routines whose scheduled time has arrived and queues a backup job | Every minute via cron or `schedule:work` |
| Laravel queue worker or Horizon | Runs the database dump, encrypts it, and stores it on the selected filesystem disk | Continuously under a process manager |

The Scheduler does not perform the dump itself when the application uses an asynchronous queue. A successful scheduler run can therefore enqueue a backup while the history stays `queued` until a worker consumes the job.
