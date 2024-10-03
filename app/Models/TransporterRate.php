<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TransporterRate
 * 
 * @property string $uuid
 * @property string $name
 * @property string $area_uuid
 * @property string $rate
 * @property string $charge
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Area $area
 * @property Collection|Vehicle[] $vehicles
 *
 * @package App\Models
 */
class TransporterRate extends Model
{
	use SoftDeletes;
	protected $table = 'transporter_rates';
	protected $primaryKey = 'uuid';
	public $incrementing = false;

	protected $casts = [
		'start_date' => 'datetime',
		'end_date' => 'datetime'
	];

	protected $fillable = [
		'name',
		'area_uuid',
		'rate',
		'charge',
		'start_date',
		'end_date'
	];

	public function area()
	{
		return $this->belongsTo(Area::class, 'area_uuid');
	}

	public function area_single()
	{
		return $this->hasOne(Area::class, 'uuid', 'area_uuid');
	}

	public function vehicles()
	{
		return $this->hasMany(Vehicle::class, 'transporter_rate_uuid');
	}

	protected static function boot()
	{
		parent::boot();

		static::creating(function ($model) {
			$model->uuid = \Illuminate\Support\Str::uuid();
		});
	}
}
