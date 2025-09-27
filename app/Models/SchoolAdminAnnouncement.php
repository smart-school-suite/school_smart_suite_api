<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\GeneratesUuid;

class SchoolAdminAnnouncement extends Model
{
   use HasFactory, GeneratesUuid;

   protected $fillable = [
       'school_admin_id',
        'announcement_id',
        'school_branch_id',
        'seen_at',
        'status'
   ];

    public $incrementing = false;
    public $table = 'school_admin_announcements';
    public $keyType = 'string';

    public function schoolAdmin(): BelongsTo {
         return $this->belongsTo(SchoolAdmin::class, 'school_admin_id');
    }

    public function announcement(): BelongsTo {
         return $this->belongsTo(Announcement::class, 'announcement_id');
    }
}
