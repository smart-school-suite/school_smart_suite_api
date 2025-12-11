<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ActivationCodeType extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'name',
        'price',
        'status',
        'description',
        'type',
        'country_id'
    ];

    public $incrementing = false;
    public $keyType = 'string';
    public $table = 'activation_code_types';

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function activationCode(): HasMany
    {
        return $this->hasMany(ActivationCode::class);
    }
}
