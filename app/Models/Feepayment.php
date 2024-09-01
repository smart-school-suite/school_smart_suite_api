<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feepayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'fee_name',
        'school_branch_id',
        'amount'
    ];

    public $keyType = 'string';
    public $table = 'fee_payment';
    public $incrementing = 'false';

    public function school(): BelongsTo {
        return $this->belongsTo(School::class);
    }

    public function schoolbranches(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class);
    }
   
    public function student(): BelongsTo {
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
