<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Models\Country;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
       'id',
       'country_id',
       'name',
       'type',
       'school_logo',
       'motor',
       'established_year',
    ];

    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'schools';

    public function country(): BelongsTo {
        return $this->belongsTo(Country::class);
    }

    public function courses(): HasMany {
        return $this->hasMany(Courses::class);
    }

    public function department(): HasMany {
        return $this->hasMany(Department::class);
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

    public function schoolSubscriptions()
    {
        return $this->hasMany(SchoolSubscription::class);
    }

}
