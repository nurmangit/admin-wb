<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Area
 * 
 * @property string $uuid
 * @property string $name
 * @property string $code
 * @property string $region_uuid
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Region $region
 * @property Collection|TransporterRate[] $transporter_rates
 *
 * @package App\Models
 */
class Area extends Model
{
	protected $table = 'areas';
	protected $primaryKey = 'uuid';
	public $incrementing = false;

	protected $fillable = [
		'name',
		'code',
		'region_uuid'
	];

	public function region()
	{
		return $this->belongsTo(Region::class, 'region_uuid');
	}
	public function region_single()
	{
		return $this->hasOne(Region::class, 'uuid', 'region_uuid');
	}

	public function transporter_rates()
	{
		return $this->hasMany(TransporterRate::class, 'area_uuid');
	}

	protected static function boot()
	{
		parent::boot();

		static::creating(function ($model) {
			$model->uuid = \Illuminate\Support\Str::uuid();
		});
	}
}
