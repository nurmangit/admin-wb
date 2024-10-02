<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Vehicle
 * 
 * @property string $uuid
 * @property string $register_number
 * @property string $code
 * @property string $status
 * @property string $type
 * @property string $vehicle_type_uuid
 * @property string $description
 * @property string $transporter_rate_uuid
 * @property string $transporter_uuid
 * @property string $ownership
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property VehicleType $vehicle_type
 * @property TransporterRate $transporter_rate
 * @property Transporter $transporter
 *
 * @package App\Models
 */
class Vehicle extends Model
{
	protected $table = 'vehicles';
	protected $primaryKey = 'uuid';
	public $incrementing = false;

	protected $fillable = [
		'register_number',
		'code',
		'status',
		'type',
		'vehicle_type_uuid',
		'description',
		'transporter_rate_uuid',
		'transporter_uuid',
		'ownership'
	];

	public function vehicle_type()
	{
		return $this->belongsTo(VehicleType::class, 'vehicle_type_uuid');
	}

	public function transporter_rate()
	{
		return $this->belongsTo(TransporterRate::class, 'transporter_rate_uuid');
	}

	public function transporter()
	{
		return $this->belongsTo(Transporter::class, 'transporter_uuid');
	}

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->uuid = \Illuminate\Support\Str::uuid();
        });
    }
}
