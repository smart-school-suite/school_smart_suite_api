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
        'tags'
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

    public function EventInvitedCustomGroup(): HasMany {
        return $this->hasMany(EventInvitedCustomGroups::class, 'event_id');
    }

    public function EventInvitedPresetGroup(): HasMany {
        return $this->hasMany(EventInvitedPresetGroup::class, 'preset_group_id');
    }

    public function EventInvitedMember(): HasMany {
        return $this->hasMany(EventInvitedMember::class, 'event_id');
    }

    public function EventAuthor(): HasMany {
        return $this->hasMany(EventAuthor::class, 'event_id');
    }


}
