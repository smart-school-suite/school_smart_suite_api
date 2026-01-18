<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivationCode extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'code',
        'code_type',
        'status',
        'used',
        'price',
        'duration',
        'expires_at',
        'school_branch_id',
        'country_id',
        'activation_code_type_id'
    ];

    public $keyType = 'string';
    public $table = 'activation_codes';
    public $incrementing = 'false';

    protected $casts = [
        'duration' => 'integer',
        "used" => "boolean"
    ];
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
    public function activationCodeUsage(): HasMany
    {
        return $this->hasMany(ActivationCodeUsage::class);
    }

    public function activationCodeType(): BelongsTo
    {
        return $this->belongsTo(ActivationCodeType::class, 'activation_code_type_id');
    }
    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }
}
