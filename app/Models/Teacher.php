<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory, HasApiTokens;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_branch_id',
        'phone_number',
        'email',
        'name',
        'profile_picture',
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'teacher';
    protected $authTokenColumn = 'token';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function otps()
    {
        return $this->morphMany(Otp::class, 'otpable');
    }

    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });

    }
    public function school(): BelongsTo {
        return $this->belongsTo(School::class);
    }

    public function schoolbranches(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class);
    }

    public function SpecailyPreference(): HasMany {
         return $this->hasMany(TeacherSpecailtyPreference::class, 'teacher_id');
    }
    public function courses(): HasMany {
        return $this->hasMany(Courses::class);
    }

    public function instructoravailability(): HasMany {
        return $this->hasMany(InstructorAvailability::class);
    }

    public function timetable(): HasMany {
        return $this->hasMany(Timetable::class);
    }

}
