<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\DynamicAttributeMapper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Yajra\Auditable\AuditableWithDeletesTrait;

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
	use SoftDeletes, AuditableWithDeletesTrait, Auditable, DynamicAttributeMapper;
	protected $table = 'Ice.UD102A';
	protected $primaryKey = 'Key1';
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
		'end_date',
		'vehicle_type_uuid'
	];

	public function area()
	{
		return $this->belongsTo(Area::class, 'area_uuid');
	}

	public function vehicle_type()
	{
		return $this->belongsTo(Area::class, 'vehicle_type_uuid');
	}

	public function area_single()
	{
		return $this->hasOne(Area::class, 'uuid', 'area_uuid');
	}

	public function vehicles()
	{
		return $this->hasMany(Vehicle::class, 'transporter_rate_uuid');
	}

	// Override the SoftDeletes deleted_at column to Date04
	public function getDeletedAtColumn()
	{
		return 'Date05';  // Use your custom soft delete column
	}

	protected static function boot()
	{
		parent::boot();

		static::setAttributeMapping([
			'uuid' => 'Key1',
			'name' => 'Character01',
			'area_uuid' => 'Key2',
			'vehicle_type_uuid' => 'ChildKey1',
			'rate' => 'Number01',
			'charge' => 'Number02',
			'start_date' => 'Date01',
			'end_date' => 'Date02',
			'created_at' => 'Date03',
			'updated_at' => 'Date04',
			'deleted_at' => 'Date05',
			'created_by' => 'Key4',
			'updated_by' => 'Key5',
			'deleted_by' => 'Key6',
		]);
		static::creating(function ($model) {
			$model->uuid = \Illuminate\Support\Str::uuid();
		});
	}
}
