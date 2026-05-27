<?php

namespace App\Models\PeriodDuration;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodDuration extends Model
{
    use HasUuids;
    protected $fillable = [
        'name',
        'minutes',
        'description',
        'status',
        'key',
        'type_id'
    ];

    public $incrementing = false;
    public $keyType = 'string';
    public $table = "period_durations";

    public function type(): BelongsTo
    {
        return $this->belongsTo(PeriodDurationType::class, 'type_id');
    }
}
