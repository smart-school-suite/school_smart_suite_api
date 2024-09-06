<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Transferedstudents extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_branch_id',
        'student_name',
        'from',
        'to',
        'status',
        'level',
        'specialty',
        'department',
    ];

    public $incrementing = 'false';
    public $table = 'transfered_students';
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
