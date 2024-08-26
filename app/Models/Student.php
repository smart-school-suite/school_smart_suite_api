<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory, HasRoles, HasApiTokens ;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'DOB',
        'gender',
        'phone_number',
        'level_id',
        'school_branch_id',
        'specialty_id',
        'department_id',
        'religion',
        'email',
        'profile_picture'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public $keyType = 'string';
    public $table = 'student';
    public $incrementing = 'false';
    protected $authTokenColumn = 'token';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function boot()
    {
        parent::boot();
       
         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });
      
    }

    public function courses(): HasMany {
        return $this->hasMany(Courses::class);
    }

    public function department(): BelongsTo {
        return $this->belongsTo(Department::class);
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

    public function parents(): BelongsTo {
        return $this->belongsTo(Parents::class, 'parent_id');
    }

    public function school(): BelongsTo {
        return $this->belongsTo(School::class);
    }

    public function schoolbranches(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class);
    }

    public function specialty(): BelongsTo {
        return $this->belongsTo(Specialty::class);
    }

    public function level(): HasOne {
        return $this->hasOne(Educationlevels::class);
    }

}
