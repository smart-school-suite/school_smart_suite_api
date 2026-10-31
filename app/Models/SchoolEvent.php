<?php

namespace App\Models;

use App\Models\Asset\SchoolEventImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'expires_at' => 'datetime',
    ];
    public $incrementing = false;
    public $table = 'school_events';
    public $keyType = 'string';

    public function currentImages(): HasMany
    {
        return $this->hasMany(SchoolEventImage::class, 'event_id')->where('is_current', true);
    }

    public function images(): HasMany
    {
        return $this->hasMany(SchoolEventImage::class, 'event_id');
    }

    public function eventCategory(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function EventAuthor(): HasMany
    {
        return $this->hasMany(EventAuthor::class, 'event_id');
    }

    public function eventAudience(): HasMany
    {
        return $this->hasMany(EventAudience::class, 'event_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(EventLikeStatus::class, 'event_id');
    }
}
