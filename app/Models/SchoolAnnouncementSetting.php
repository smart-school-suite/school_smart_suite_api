<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolAnnouncementSetting extends Model
{
    use HasFactory;

    protected $fillable = [
         'value',
         'enabled',
         'announcement_setting_id',
         'school_branch_id'
    ];

    public $incrementing = false;
    public $keyType = 'string';
    public $table = 'school_announcement_settings';

    public function schoolBranch(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function announcementSetting(): BelongsTo {
        return $this->belongsTo(AnnouncementSetting::class, 'announcement_setting_id');
    }


}
