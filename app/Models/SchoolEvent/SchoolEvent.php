<?php

namespace App\Models\SchoolEvent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolEvent extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = [
        'title',
        'description',
        'organizer',
        'location',
        'status',
        'start_date',
        'end_date',
        'published_at',
        'notification_sent_at',
        'school_branch_id',
        'event_category_id',
        'tags',
        'audience'
    ];

    protected $casts = [
        'tags' => 'array',
        'audience' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'published_at' => 'datetime',
        'notification_sent_at' => 'datetime'
    ];
    public $incrementing = false;
    public $table = "school_events";
    public $keyType = 'string';

    public function eventCategory(): BelongsTo
    {
        return $this->belongsTo(SchoolEventCategory::class, 'event_category_id');
    }
}
