<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Schooladmin extends Authenticatable
{
    use HasFactory, HasApiTokens, HasRoles, HasPermissions, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'role',
        'email',
        'password',
        'profile_picture',
        'date_of_birth',
        'address',
        'employment_status',
        'hire_date',
        'emergency_contact_name',
        'emergency_contact_phone',
        'last_performance_review',
        'work_location',
        'highest_qualification',
        'field_of_study',
        'cultural_background',
        'religion',
        'years_experience',
        'salary',
        'city',
        'school_branch_id',
        'status'
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
    public $table = 'school_admin';
    protected $authTokenColumn = 'token';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function devices() {
        return $this->morphMany(UserDevices::class, 'devicesable');
    }
    public function school(): BelongsTo {
        return $this->belongsTo(School::class, 'school_branch_id');
    }

    public function otps()
    {
        return $this->morphMany(Otp::class, 'otpable');
    }

      public function announcementTargetUser(): MorphMany {
        return $this->morphMany(AnnouncementTargetUser::class, 'actorable');
    }

    public function audience(): MorphMany {
        return $this->morphMany(Audiences::class, 'audienceable');
    }
    public function passwordResetTokens()
    {
        return $this->morphMany(PasswordResetToken::class, 'actorable');
    }
    public function hod()
    {
        return $this->morphMany(Hod::class, 'hodable');
    }
    public function otp() {
        return $this->morphMany(Otp::class, 'actorable');
    }

    public function eventInvitedMember(): MorphMany {
        return $this->morphMany(EventInvitedMember::class, 'actorable');
    }
    public function announcementAuthor(): MorphMany {
        return $this->morphMany(AnnouncementAuthor::class, 'authorable');
    }

    public function eventAuthor(): MorphMany {
        return $this->morphMany(EventAuthor::class, 'actorable');
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
