<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\GeneratesUuid;
class StudentAnnouncement extends Model
{
     use HasFactory, GeneratesUuid;
    protected $fillable = [
        'id',
       'announcement_id',
       'student_id',
       'school_branch_id',
       'seen_at',
       'status'
    ];

    public $incrementing = false;
    public $table = 'student_announcements';
    public $keyType = 'string';

    public function announcement(): BelongsTo {
         return $this->belongsTo(Announcement::class, 'announcement_id');
    }

    public function student(): BelongsTo {
         return $this->belongsTo(Student::class, 'student_id');
    }
}
