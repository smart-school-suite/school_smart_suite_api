<?php

namespace App\Models\Job;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use MongoDB\Laravel\Relations\HasMany;

class SystemJob extends Model
{
    use GeneratesUuid;

    protected $casts = [
        'payload' => 'json',
        'result' => 'json',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    protected $fillable = [
        'type',
        'context_type',
        'context_id',
        'initiated_by',
        'queue',
        'status',
        'stage',
        'progress',
        'payload',
        'result',
        'error_code',
        'error_message',
    ];

    public $incrementing = false;
    public $table = "system_jobs";
    public  $keyType = 'string';

    public function systemJobEvent(): HasMany
    {
        return $this->hasMany(SystemJobEvent::class);
    }
}
