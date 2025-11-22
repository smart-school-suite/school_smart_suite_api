<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
class Country extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'country',
        'code',
        'status',
        'currency',
        'official_language'
    ];

    public $keyType = 'string';
    public $table = 'countries';
    public $incrementing = 'false';


    public function school(): HasMany {
        return $this->hasMany(School::class);
    }

}
