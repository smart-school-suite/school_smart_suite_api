<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\GeneratesUuid;
class TeacherAnnouncement extends Model
{
    use HasFactory, GeneratesUuid;
    protected $fillable = [
        'id',
        'teacher_id',
        'announcement_id',
        'school_branch_id',
        'seen_at',
        'status'
    ];

     public $incrementing = false;
    public $table = 'teacher_announcements';
    public $keyType = 'string';

    public function announcement(): BelongsTo {
         return $this->belongsTo(Announcement::class, 'announcement_id');
    }

    public function teacher(): BelongsTo {
         return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}
