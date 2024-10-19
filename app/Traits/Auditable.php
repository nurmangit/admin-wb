<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function (Model $model) {
            self::audit('created', $model, []);
        });

        static::updated(function (Model $model) {
            self::audit('updated', $model, $model->getOriginal());
        });

        static::deleted(function (Model $model) {
            self::audit('deleted', $model, $model->getOriginal());
        });
    }

    protected static function audit($description, $model, $oldValues)
    {
        $newValues = $model->getAttributes();

        AuditLog::create([
            'description'  => $description,
            'subject_uuid'   => $model->uuid ?? null,
            'subject_type' => get_class($model) ?? null,
            'user_uuid'      => auth()->user()->uuid ?? 'System',
            'properties'   => json_encode([
                'old' => $oldValues,
                'new' => $newValues,
            ]),
            'host'         => request()->ip() ?? null,
        ]);
    }
}
