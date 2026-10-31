<?php

namespace App\Models\SchoolEvent;

use App\Models\Asset\SchoolEventImage;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'expires_at',
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
        'notification_sent_at' => 'datetime',
        'expires_at' => 'datetime'
    ];
    public $incrementing = false;
    public $table = "school_events";
    public $keyType = 'string';

    public function eventCategory(): BelongsTo
    {
        return $this->belongsTo(SchoolEventCategory::class, 'event_category_id');
    }
    public function eventAuthor(): HasOne
    {
        return $this->hasOne(SchoolEventAuthor::class, 'event_id');
    }
    public function schoolEventLikes(): HasMany
    {
        return $this->hasMany(SchoolEventLike::class, 'event_id');
    }
    public function invitees(): HasMany
    {
        return $this->hasMany(SchoolEventInvitee::class, 'event_id');
    }
    public function images(): HasMany
    {
        return $this->hasMany(SchoolEventImage::class, 'event_id');
    }
    public function currentImages(): HasMany
    {
        return $this->hasMany(SchoolEventImage::class, 'event_id')->where('is_current', true);
    }
}
