<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class LetterGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'letter_grade'
    ];

    public $incrementing = 'false';
    public $table = 'letter_grade';
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
