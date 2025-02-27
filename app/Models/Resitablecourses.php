<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resitablecourses extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_branch_id',
        'course_id',
        'specialty_id',
        'exam_id',
        'level_id',
        'student_batch_id',
        'school_year'
    ];

    public $incrementing = 'false';
    public $table = 'resitable_courses';
    public $keyType = 'string';

    public function courses(): BelongsTo {
        return $this->belongsTo(Courses::class, 'course_id');
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
