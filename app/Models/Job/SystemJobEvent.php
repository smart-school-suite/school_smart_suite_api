<?php

namespace App\Models\Job;

use Illuminate\Database\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class SystemJobEvent extends Model
{
    protected $casts = [
        'meta' => 'array',
    ];

    protected $fillable = [
        'system_job_id',
        'event_type',
        'message',
        'meta',
    ];

    public $incrementing = false;
    public $table = "system_job_events";
    public  $keyType = 'string';

    public function systemJob(): BelongsTo
    {
        return $this->belongsTo(SystemJob::class,  'system_job_id');
    }
}
