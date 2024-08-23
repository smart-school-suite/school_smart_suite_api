<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Educationlevels extends Model
{
    use HasFactory;

    protected $fillable = [
       'name',
       'level'
    ];

    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'education_levels';

    protected static function boot()
    {
        parent::boot();
       
         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });
      
    }
}
