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
 * Class Region
 * 
 * @property string $uuid
 * @property string $name
 * @property string $code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Area[] $areas
 *
 * @package App\Models
 */
class Device extends Model
{
	use SoftDeletes;
	protected $table = 'devices';
	protected $primaryKey = 'uuid';
	public $incrementing = false;

	protected $fillable = [
		'name',
		'secret',
		'current_weight',
		'previous_weight',
		'tolerance',
		'status',
		'used_at',
	];

	protected static function boot()
	{
		parent::boot();

		static::creating(function ($model) {
			$model->uuid = \Illuminate\Support\Str::uuid();
		});
	}
}
