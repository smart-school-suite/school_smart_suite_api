<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanRecommendation extends Model
{
    protected $fillable = [
        'id',
        'priority',
        'status',
        'source_plan_id',
        'target_plan_id',
        'feature_id'
    ];


    public $incrementing =  false;
    public $keyType = 'string';
    public $table = 'plan_recs';

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class, 'feature_id');
    }

    public function sourcePlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'source_plan_id');
    }

    public function targetPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'target_plan_id');
    }

    public function planRecCondition(): HasMany
    {
        return $this->hasMany(PlanRecCondition::class, 'plan_rec_id');
    }
}
