<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'program_name'
    ];

    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'semesters';

    public function exams(): HasMany {
        return $this->hasMany(Exams::class);
    }

    public function examtype(): HasMany {
        return $this->hasMany(Examtype::class);
    }

    public function timetable(): BelongsTo {
        return $this->belongsTo(Timetable::class, 'semeter_id');
    }
    public function courses(): HasMany {
         return $this->hasMany(Courses::class);
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
