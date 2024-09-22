<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Country;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schoolbranches extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'branch_name',
        'address',
        'city',
        'state',
        'postal_code',
        'phone_one',
        'phone_two',
        'email',
    ];

    public $keyType = 'string';
    public $table = 'school_branches';
    public $incrementing = 'false';

    public function country(): BelongsTo {
        return $this->belongsTo(Country::class);
    }
     
    public function school(): BelongsTo {
        return $this->belongsTo(School::class);
    }
    public function courses(): HasMany {
        return $this->hasMany(Courses::class);
    }

    public function department(): HasMany {
        return $this->hasMany(Department::class);
    }
    
    public function events(): HasMany {
        return $this->hasMany(Events::class);
    }

    public function exams(): HasMany {
        return $this->hasMany(Exams::class);
    }

    public function feepayment(): HasMany {
        return $this->hasMany(Feepayment::class);
    }

    public function marks(): HasMany {
        return $this->hasMany(Marks::class);
    }

    public function parents(): HasMany {
        return $this->hasMany(Parents::class);
    }

    public function schooladmin() : HasMany {
        return $this->hasMany(Schooladmin::class);
    }

    public function schoolbranches(): HasMany {
        return $this->hasMany(Schoolbranches::class);
    }

    public function specialty(): HasMany {
        return $this->hasMany(Specialty::class);
    }

    public function student() : HasMany {
        return $this->hasMany(Student::class);
    }

    public function teacher() : HasMany {
        return $this->hasMany(Teacher::class);
    }


    protected static function boot()
    {
        parent::boot();
       
         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 25);
         });
      
    }
}
