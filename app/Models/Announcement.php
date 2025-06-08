<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'title',
        'content',
        'status',
        'published_at',
        'expires_at',
        'category_id',
        'notification_sent_at',
        'label_id',
        'tag_id',
        'school_branch_id'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public $table = 'announcements';

    public $incrementing = false;
    protected $keyType = 'string';

    public function announcementCategory(): BelongsTo {
        return $this->belongsTo(AnnouncementCategory::class, 'category_id');
    }

    public function announcementTargetUser(): HasMany {
        return $this->hasMany(AnnouncementTargetUser::class, 'announcement_id');
    }
    public function announcementTag(): BelongsTo {
        return $this->belongsTo(AnnouncementTag::class, 'tag_id');
    }

    public function announcementLabel(): BelongsTo {
        return $this->belongsTo(AnnouncementLabel::class, 'label_id');
    }

    public function schoolBranch(): BelongsTo {
        return $this->belongsTo(SchoolBranches::class, 'school_branch_id');
    }

    public function announcementTargetGroup(): HasMany {
        return $this->hasMany(AnnouncementTargetGroup::class, 'announcement_id');
    }

    public function announcementPresetGroup(): HasMany {
        return $this->hasMany(AnnouncementTargetGroup::class, 'announcement_id');
    }

}


