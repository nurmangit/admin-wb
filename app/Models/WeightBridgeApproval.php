<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\DynamicAttributeMapper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Yajra\Auditable\AuditableWithDeletesTrait;

class WeightBridgeApproval extends Model
{
	use SoftDeletes, AuditableWithDeletesTrait, Auditable, DynamicAttributeMapper;
	protected $table = 'Ice.UD100A';
	protected $primaryKey = 'Key1';
	public $incrementing = false;
	const CREATED_AT = 'Date02';
	const UPDATED_AT = 'Date03';
	public $timestamps = false;

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

	// Override the SoftDeletes deleted_at column to Date04
	public function getDeletedAtColumn()
	{
		return 'Date04';  // Use your custom soft delete column
	}

	protected static function boot()
	{
		parent::boot();

		static::setAttributeMapping([
			'uuid' => 'Key1',
			'weight_bridge_uuid' => 'ChildKey1',
			'action_date' => 'Character01',
			'action_by' => 'Key2',
			'is_approve' => 'CheckBox01',
			'is_reject' => 'CheckBox02',
			'created_at' => 'Date02',
			'updated_at' => 'Date03',
			'created_by' => 'Key3',
			'updated_by' => 'Key4',
		]);
		static::creating(function ($model) {
			$model->uuid = \Illuminate\Support\Str::uuid();
		});
	}
}
