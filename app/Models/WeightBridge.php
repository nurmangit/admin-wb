<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Traits\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Yajra\Auditable\AuditableWithDeletesTrait;

class WeightBridge extends Model
{
	use SoftDeletes, AuditableWithDeletesTrait, Auditable;
	protected $table = 'weight_bridges';
	protected $primaryKey = 'uuid';
	public $incrementing = false;

	protected $fillable = [
		'slip_no',
		'arrival_date',
		'weight_type',
		'vehicle_uuid',
		'weight_in',
		'weight_in_date',
		'weight_out',
		'weight_out_date',
		'weight_netto',
		'weight_standart',
		'po_do',
		'difference',
		'weight_in_by',
		'weight_out_by',
		'remark',
		'status'
	];

	public function vehicle()
	{
		return $this->belongsTo(Vehicle::class, 'vehicle_uuid', 'uuid');
	}

	protected static function boot()
	{
		parent::boot();

		static::creating(function ($model) {
			$model->uuid = \Illuminate\Support\Str::uuid();
		});
	}
}
