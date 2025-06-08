<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventSetting extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'title',
        'description'
    ];

    public $incrementing = 'false';
    public $table = 'event_settings';
    public $keyType = 'string';

    public function schoolEventSetting(): HasMany {
        return $this->hasMany(SchoolEventSetting::class, 'event_setting_id');
    }

}
