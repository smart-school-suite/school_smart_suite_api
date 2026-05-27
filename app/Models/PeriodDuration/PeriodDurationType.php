<?php

namespace App\Models\PeriodDuration;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodDurationType extends Model
{
    use HasUuids;
    protected $fillable = [
        'name',
        'description',
        'key',
        'status'
    ];

    public $incrementing = false;
    public $keyType = 'string';
    public $table = 'period_duration_types';

    public function periodDuration(): HasMany
    {
        return $this->hasMany(PeriodDuration::class);
    }
}
