<?php

namespace App\Observers;

use App\Support\Audit\AuditLogger;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    public function __construct(private readonly AuditLogger $logger)
    {
    }

    public function created(Model $model): void
    {
        $this->logger->recordModel($model, 'created');
    }

    public function updated(Model $model): void
    {
        $this->logger->recordModel($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->logger->recordModel($model, 'deleted');
    }

    public function restored(Model $model): void
    {
        $this->logger->recordModel($model, 'restored');
    }

    public function forceDeleted(Model $model): void
    {
        $this->logger->recordModel($model, 'forceDeleted');
    }
}
