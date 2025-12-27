<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gender extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'name',
        'status'
    ];

    public $incrementing = false;
    public $table = "genders";
    public $keyType = 'string';

    public function student(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
