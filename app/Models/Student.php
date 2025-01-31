<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Student extends Model
{
    use HasFactory,  HasApiTokens;

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
        'phone_one',
        'phone_two',
        'level_id',
        'school_branch_id',
        'specialty_id',
        'department_id',
        'guadian_two_id',
        'guadian_one_id',
        'student_batch_id',
        'religion',
        'total_fee_debt',
        'email',
        'password',
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

    public function otps()
    {
        return $this->morphMany(Otp::class, 'otpable');
    }

    public function courses(): HasMany {
        return $this->hasMany(Courses::class);
    }

    public function department(): BelongsTo {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function exams(): HasMany {
        return $this->hasMany(Exams::class);
    }

    public function feepayment(): HasMany {
        return $this->hasMany(Feepayment::class);
    }

    public function marks(): HasMany {
        return $this->hasMany(Marks::class, 'student_id');
    }

    public function guardianOne(): BelongsTo {
        return $this->belongsTo(Parents::class, 'guadian_one_id');
    }

    public function guardianTwo(): BelongsTo {
         return $this->belongsTo(Parents::class, 'guadian_two_id');
    }

    public function school(): BelongsTo {
        return $this->belongsTo(School::class);
    }

    public function schoolbranches(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function specialty(): BelongsTo {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function level(): BelongsTo {
        return $this->belongsTo(Educationlevels::class, 'level_id');
    }

    public function transcript(): HasMany {
        return $this->hasMany(Reportcard::class, 'student_id');
    }

    public function studentBatch(): BelongsTo {
        return $this->belongsTo(studentBatch::class, 'student_batch_id');
    }

    public function studentresit(): HasMany {
         return $this->hasMany(Studentresit::class);
    }
}
