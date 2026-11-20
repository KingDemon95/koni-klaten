<?php

return [
    'directory' => storage_path('app/private/backups'),
    'connection' => env('KONI_BACKUP_CONNECTION'),
    'retention_days' => (int) env('KONI_BACKUP_RETENTION_DAYS', 30),
    'schedule_time' => env('KONI_BACKUP_SCHEDULE_TIME', '02:00'),
    'process_timeout' => (int) env('KONI_BACKUP_PROCESS_TIMEOUT', 1800),
    'mysqldump_binary' => env('KONI_MYSQLDUMP_BINARY', 'mysqldump'),
    'mysql_binary' => env('KONI_MYSQL_BINARY', 'mysql'),

    // Off-site backup is optional. Configure an S3-compatible disk only after
    // installing its Flysystem adapter and setting credentials in .env.
    'remote_disk' => env('KONI_BACKUP_REMOTE_DISK'),
    'remote_prefix' => env('KONI_BACKUP_REMOTE_PREFIX', 'koni-klaten/backups'),
    'max_age_hours' => (int) env('KONI_BACKUP_MAX_AGE_HOURS', 36),
    'scheduler_heartbeat_max_age_minutes' => (int) env('KONI_SCHEDULER_HEARTBEAT_MAX_AGE_MINUTES', 3),
    'scheduler_heartbeat_path' => storage_path('framework/koni-scheduler-heartbeat'),
];
