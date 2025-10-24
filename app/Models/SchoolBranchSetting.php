<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\GeneratesUuid;
class SchoolBranchSetting extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'school_branch_id',
        'setting_defination_id',
        'value'
    ];

    protected $casts = [
        'value' => 'json',
    ];

    public $incrementing = 'false';
    public $keyType = 'string';
    public $table = 'school_branch_settings';

    public function settingDefination(): BelongsTo
    {
        return $this->belongsTo(SettingDefination::class, 'setting_defination_id');
    }
    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }
    public function getTypedValueAttribute()
    {
        $definition = $this->settingDefinition;
        if (! $definition) {
            return $this->value;
        }

        $type = $definition->data_type;
        $value = $this->value;

        return match ($type) {
            'integer' => (int) $value,
            'decimal' => is_numeric($value) ? (float) $value : $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => $value,
            default => (string) $value,
        };
    }
}
