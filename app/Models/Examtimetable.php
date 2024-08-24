<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Examtimetable extends Model
{
    use HasFactory;

    protected $fillable = [
      'school_branch_id',
      'exam_id',
      'course_id',
      'specialty_id',
      'day',
      'start_time',
      'end_time',
      'duration'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        // Update duration to be a string
        'duration' => 'string',
    ];

    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'examtimetable';
    

    protected static function boot()
    {
        parent::boot();
       
         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });
      
    }

    public function course(): HasMany {
        return $this->hasMany(Courses::class, 'course_id');
    }
}
