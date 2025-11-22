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
        'reciepient_count',
        'notification_sent_at',
        'label_id',
        'audience',
        'tags',
        'school_branch_id'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'audience' => 'json',
        'tags' => 'json',
        'reciepient_count' => 'integer'
    ];

    public $table = 'announcements';

    public $incrementing = false;
    protected $keyType = 'string';

    public function announcementEngagementStat(): HasMany {
         return $this->hasMany(AnnouncementEngagementStat::class);
    }
     public function schoolAdminAnnouncement(): HasMany {
         return $this->hasMany(SchoolAdminAnnouncement::class);
    }
    public function teacherAnnouncement(): HasMany {
        return $this->hasMany(TeacherAnnouncement::class);
    }
    public function studentAnnouncement(): HasMany {
         return $this->hasMany(StudentAnnouncement::class);
    }
    public function announcementCategory(): BelongsTo {
        return $this->belongsTo(AnnouncementCategory::class, 'category_id');
    }

    public function announcementLabel(): BelongsTo {
        return $this->belongsTo(AnnouncementLabel::class, 'label_id');
    }

    public function schoolBranch(): BelongsTo {
        return $this->belongsTo(SchoolBranches::class, 'school_branch_id');
    }

}


