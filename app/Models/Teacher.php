<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory, HasApiTokens, Notifiable, HasRoles, HasPermissions;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_branch_id',
        'email',
        'name',
        'phone_one',
        'phone_two',
        'first_name',
        'last_name',
        'status',
        'profile_picture',
        'address',
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

    public function otp()
    {
        return $this->morphMany(Otp::class, 'actorable');
    }

    public function announcementTargetUser(): MorphMany
    {
        return $this->morphMany(AnnouncementTargetUser::class, 'actorable');
    }

    public function devices()
    {
        return $this->morphMany(UserDevices::class, 'devicesable');
    }
    public function passwordResetTokens()
    {
        return $this->morphMany(PasswordResetToken::class, 'actorable');
    }

    public function audience(): MorphMany
    {
        return $this->morphMany(Audiences::class, 'audienceable');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function schoolbranches(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class);
    }

    public function specialtyPreference(): HasMany
    {
        return $this->hasMany(TeacherSpecailtyPreference::class, 'teacher_id');
    }
    public function courses(): HasMany
    {
        return $this->hasMany(Courses::class);
    }

    public function instructoravailability(): HasMany
    {
        return $this->hasMany(InstructorAvailability::class);
    }

    public function hod()
    {
        return $this->morphMany(Hod::class, 'hodable');
    }
    public function timetable(): HasMany
    {
        return $this->hasMany(Timetable::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
        });
    }
}
