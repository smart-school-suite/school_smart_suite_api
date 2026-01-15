<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use GeneratesUuid;
    protected $fillable  = [
        'key',
        'name',
        'price',
        'max_plan',
        'description',
        'country_id',
        'status'
    ];

    public $incrementing = 'false';
    public $table = 'plans';
    public $keyType = 'string';

    protected $casts = [
         'max_plan' => 'boolean'
    ];
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function schoolSubscription(): HasMany
    {
        return $this->hasMany(SchoolSubscription::class);
    }
    public function planFeature(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function sourcePlan(): HasMany
    {
        return $this->hasMany(Plan::class, 'source_plan_id');
    }

    public function targetPlan(): HasMany
    {
        return $this->hasMany(Plan::class, 'target_plan_id');
    }
}
