<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SettingDefination extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'setting_category_id',
        'key',
        'name',
        'data_type',
        'default_value',
        'description'
    ];

     protected $casts = [
        'default_value' => 'json',
    ];

    public $incrementing = 'false';
    public $keyType = 'string';
    public $table = 'setting_definations';

    public function settingCategory(): BelongsTo {
         return $this->belongsTo(SettingCategory::class, 'setting_category_id');
    }

    public function schoolBranchSetting(): HasMany {
         return $this->hasMany(SchoolBranchSetting::class);
    }

    public function getTypedValueAttribute()
    {
        $type = $this->definition->data_type;
        $value = $this->value;

        return match ($type) {
            'integer' => (int) $value,
            'decimal' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => $value,
            default => (string) $value,
        };
    }
}
