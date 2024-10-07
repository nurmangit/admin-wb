<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditLog extends Model
{
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
    ];

    protected $casts = [
        'properties' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'uuid', 'user_uuid');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = \Illuminate\Support\Str::uuid();
        });
    }
}
