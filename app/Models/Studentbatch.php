<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Studentbatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'school_branch_id'
    ];

    public $incrementing = 'false';
    public $table = 'student_batch';
    public $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
       
         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });
      
    }

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class, 'student_batch_id');
    }
}
