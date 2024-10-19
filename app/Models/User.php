<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\Auditable;
use App\Traits\DynamicAttributeMapper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles;
use Yajra\Auditable\AuditableWithDeletesTrait;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, AuditableWithDeletesTrait, DynamicAttributeMapper;

    protected $primaryKey = 'Key1';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $table = 'Ice.UD01';
    protected $deleted_at = 'Date04';
    const CREATED_AT = 'Date02';
    const UPDATED_AT = 'Date03';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Override the SoftDeletes deleted_at column to Date04
    public function getDeletedAtColumn()
    {
        return 'Date04';  // Use your custom soft delete column
    }

    /**
     * A permission can be applied to roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.role'),
            config('permission.table_names.role_has_permissions'),
            app(PermissionRegistrar::class)->pivotPermission,
            app(PermissionRegistrar::class)->pivotRole
        );
    }

    protected static function boot()
    {
        parent::boot();
        static::setAttributeMapping([
            'uuid' => 'Key1',
            'name' => 'Character01',
            'email' => 'Character02',
            'password' => 'Character03',
            'is_active' => 'CheckBox01',
            'remember_token' => 'Character04',
            'email_verified_at' => 'Date01',
            'created_at' => 'Date02',
            'updated_at' => 'Date03',
            'deleted_at' => 'Date04',
            'created_by' => 'Key2',
            'updated_by' => 'Key3',
            'deleted_by' => 'Key4',
        ]);
        static::creating(function ($model) {
            $model->uuid = \Illuminate\Support\Str::uuid();
        });
    }
}
