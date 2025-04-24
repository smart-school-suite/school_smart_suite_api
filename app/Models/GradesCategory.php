<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradesCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'status',
    ];

    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'grades_category';

    public function schoolGradesConfig() : HasMany {
         return $this->hasMany(SchoolGradesConfig::class);
    }
    public function examResit(): HasMany {
        return $this->hasMany(ResitExam::class);
    }
    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });

    }

}
