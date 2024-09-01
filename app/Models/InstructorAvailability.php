<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_branch_id',
        'teacher_id',
        'day_of_week',
        'start_time',
        'end_time',
        'level_id',
        'semester_id',
        'specialty_id'
    ];

    public $incrementing = 'false';
    public $table = 'instructor_availabilities';
    public $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
       
         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });
      
    }

    public function teacher() : BelongsTo {
        return $this->belongsTo(Teacher::class);
    }
}
