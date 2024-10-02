<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Transporter
 * 
 * @property string $uuid
 * @property string $name
 * @property string $code
 * @property string $address
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Vehicle[] $vehicles
 *
 * @package App\Models
 */
class Transporter extends Model
{
	protected $table = 'transporters';
	protected $primaryKey = 'uuid';
	public $incrementing = false;

	protected $fillable = [
		'name',
		'code',
		'address'
	];

	public function vehicles()
	{
		return $this->hasMany(Vehicle::class, 'transporter_uuid');
	}

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->uuid = \Illuminate\Support\Str::uuid();
        });
    }
}
