<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentRecords extends Model
{
    use HasFactory;

    protected $fillable = [
       'school_branch_id',
       'student_id',
       'academic_year',
       'level_id',
       'exam_id',
       'student_name',
       'GPA',
       'records'
    ];

    public $incrementing = 'false';
    public $keyType = 'string';
    public $table = 'studentrecords';

    public function student() : BelongsTo {
        return $this->belongsTo(Student::class);
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
