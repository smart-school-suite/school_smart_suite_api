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
        'description',
        'country_id',
        'status'
    ];

    public $incrementing = 'false';
    public $table = 'plans';
    public $keyType = 'string';

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
}
