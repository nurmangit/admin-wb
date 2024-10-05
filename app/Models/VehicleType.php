<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Traits\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Yajra\Auditable\AuditableWithDeletesTrait;

/**
 * Class VehicleType
 * 
 * @property string $uuid
 * @property string $name
 * @property string $code
 * @property string $tolerance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Vehicle[] $vehicles
 *
 * @package App\Models
 */
class VehicleType extends Model
{
	use SoftDeletes, AuditableWithDeletesTrait, Auditable;
	protected $table = 'vehicle_types';
	protected $primaryKey = 'uuid';
	public $incrementing = false;

	protected $fillable = [
		'name',
		'code',
		'tolerance',
		'weight_standart',
	];

	public function vehicles()
	{
		return $this->hasMany(Vehicle::class, 'vehicle_type_uuid');
	}

	protected static function boot()
	{
		parent::boot();

		static::creating(function ($model) {
			$model->uuid = \Illuminate\Support\Str::uuid();
		});
	}
}
