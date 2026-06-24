<?php

namespace App\Traits;

use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            app(AuditService::class)->logModelEvent($model, 'creacion');
        });

        static::updated(function (Model $model): void {
            if ($model->wasChanged()) {
                app(AuditService::class)->logModelEvent($model, 'modificacion', $model->getOriginal());
            }
        });

        static::deleted(function (Model $model): void {
            app(AuditService::class)->logModelEvent(
                $model,
                'eliminacion',
                $model->getAttributes(),
            );
        });
    }
}
