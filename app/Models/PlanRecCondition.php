<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanRecCondition extends Model
{
    protected $fillable = [
        'id',
        'plan_rec_id',
        'operator',
        'value'
    ];

    public $incrementing = false;
    public $table = "plan_rec_conds";
    public $keyType = 'string';

    public function planRecCopy(): HasMany
    {
        return $this->hasMany(PlanRecCopy::class);
    }
    public function planRecommendation(): BelongsTo
    {
        return $this->belongsTo(PlanRecommendation::class, 'plan_rec_id');
    }
}
