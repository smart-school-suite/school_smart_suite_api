<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppSetting extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'title',
        'decription',
        'setting_category_id',
        'allowed_value'
    ];

    public $keyType = 'string';
    public $table = 'app_settings';
    public $incrementing = 'false';

    public function settingCategory(): BelongsTo {
        return $this->belongsTo(SettingCategory::class, 'setting_category_id');
    }
}
