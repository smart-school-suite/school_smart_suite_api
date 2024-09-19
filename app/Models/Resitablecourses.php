<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Resitablecourses extends Model
{
    use HasFactory;
  
    protected $fillable = [
        'school_branch_id',
        'courses_id',
        'specialty_id',
        'exam_id',
        'level_id'
    ];

    public $incrementing = 'false';
    public $table = 'resitable_courses';
    public $keyType = 'string';
    
    protected static function boot()
    {
        parent::boot();
       
         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });
      
    }
    

}
