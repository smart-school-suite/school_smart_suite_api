<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'country'
    ];

    public $keyType = 'string';
    public $table = 'country';
    public $incrementing = 'false';


    public function school(): HasMany {
        return $this->hasMany(School::class);
    }

    
}
