<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\GeneratesUuid;

class AnnouncementEngagementStat extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'total_reciepient',
        'total_student',
        'total_school_admin',
        'total_teacher',
        'total_seen',
        'total_unseen',
        'announcement_id',
        'school_branch_id'
    ];

    protected $cast = [
        'total_reciepient' => 'integer',
        'total_student' => 'integer',
        'total_school_admin' => 'integer',
        'total_teacher' => 'integer',
        'total_seen' => 'integer',
        'total_unseen' => 'integer',
    ];
    public $table = 'announcement_engagement_stats';

    public $incrementing = false;
    protected $keyType = 'string';

    public function announcement(): BelongsTo {
         return $this->belongsTo(Announcement::class, 'announcement_id');
    }

}
