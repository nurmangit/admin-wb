<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeightBridgeApproval extends Model
{
	use SoftDeletes;
	protected $table = 'weight_bridge_approvals';
	protected $primaryKey = 'uuid';
	public $incrementing = false;

	protected $fillable = [
		'weight_bridge_uuid',
		'action_date',
		'action_by',
		'is_approve',
		'is_reject',
		'is_active',
	];

	public function weight_bridge()
	{
		return $this->belongsTo(WeightBridge::class, 'weight_bridge_uuid');
	}

	protected static function boot()
	{
		parent::boot();

		static::creating(function ($model) {
			$model->uuid = \Illuminate\Support\Str::uuid();
		});
	}
}
