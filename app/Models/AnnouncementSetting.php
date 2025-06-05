<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnnouncementSetting extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'title',
        'description'
    ];

    public $table = 'announcement_settings';
    public $keyType = 'string';
    public $incrementing = false;

    public function schoolAnnouncementSetting(): HasMany {
        return $this->hasMany(SchoolAnnouncementSetting::class, 'announcement_setting_id');
    }


}
