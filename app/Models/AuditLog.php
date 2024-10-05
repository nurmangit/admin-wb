<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditLog extends Model
{
    use SoftDeletes;
    protected $table = 'audit_logs';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $fillable = [
        'description',
        'subject_uuid',
        'subject_type',
        'user_uuid',
        'properties',
        'host',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'properties' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = \Illuminate\Support\Str::uuid();
        });
    }
}
