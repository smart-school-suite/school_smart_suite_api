<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolEvent extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'id',
        'title',
        'description',
        'background_image',
        'organizer',
        'location',
        'likes',
        'invitee_count',
        'status',
        'start_date',
        'end_date',
        'published_at',
        'notification_sent_at',
        'expires_at',
        'school_branch_id',
        'event_category_id',
        'tags',
        'visibility_status',
        'audience'
    ];

    protected $casts = [
         'likes' => 'integer',
         'invitee_count' => 'integer',
         'tags' => 'json',
         'audience' => 'json'
    ];
    public $incrementing = 'false';
    public $table = 'school_events';
    public $keyType = 'string';

    public function eventCategory(): BelongsTo {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    public function schoolBranch(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function EventAuthor(): HasMany {
        return $this->hasMany(EventAuthor::class, 'event_id');
    }


}
