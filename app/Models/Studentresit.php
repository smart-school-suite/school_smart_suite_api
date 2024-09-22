<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Studentresit extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_branch_id',
        'student_id',
        'course_id',
        'exam_id',
        'specialty_id',
        'level_id',
        'exam_status',
        'paid_status',
        'resit_fee',
    ];

    public $keyType = 'string';
    public $table = 'student_resit';
    public $incrementing = 'false';

    protected static function boot()
    {
        parent::boot();
       
         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });
      
    }
}
