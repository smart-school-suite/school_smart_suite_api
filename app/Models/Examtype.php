<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Examtype extends Model
{
    use HasFactory;

    protected $fillable = [
      'semester_id',
      'exam_name'
    ];


    public $incrementing = 'false';
    public $keyType = 'string';
    public $table = 'exam_type';

    protected static function boot()
    {
        parent::boot();
       
         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });
      
    }
     
   public function semesters(): HasMany {
      return $this->hasMany(Semester::class, 'semester_id');
   }

   public function exams(): BelongsTo {
      return $this->belongsTo(Exams::class);
   }
}
