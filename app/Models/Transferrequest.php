<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Transferrequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'current_school_id',
        'target_school_id',
        'current_school_name',
        'target_school_name',
        'status',
        'specialty_name',
        'specialty_id',
        'student_name',
        'level_id',
        'level_name',
        'department_id',
        'department_name',
        'parent_id'
    ];

    public $incrementing = 'false';
    public $keyType = 'string';
    public $table = 'transfer_request';

    protected static function boot()
    {
        parent::boot();
       
         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });
      
    }
}
