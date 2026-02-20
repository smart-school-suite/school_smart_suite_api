<?php

namespace App\Models;

use App\Models\Course\JointCourseSlot;
use App\Models\SemesterTimetable\SemesterTimetableSlot;
use App\Traits\Currency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory, HasApiTokens, Notifiable, HasRoles, HasPermissions, Currency;

    protected $fillable = [
        'id',
        'school_branch_id',
        'email',
        'name',
        'phone',
        'first_name',
        'last_name',
        'status',
        'profile_picture',
        'address',
        'gender_id',
        'num_assigned_courses',
        'course_assignment_status',
        'num_assigned_specialties',
        'specialty_assignment_status',
        'sub_status'
    ];

    protected $hidden = [
        'password',
    ];

    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'teachers';
    protected $authTokenColumn = 'token';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'num_assigned_courses' => 'integer',
            'num_assigned_specialties' => 'integer'
        ];
    }

    public function jointCourseSlot(): HasMany
    {
        return $this->hasMany(JointCourseSlot::class);
    }
    public function activationCode(): MorphMany
    {
        return $this->morphMany(ActivationCodeUsage::class, 'actorable');
    }
    public function routeNotificationForFcm()
    {
        return $this->devices()->pluck('token')->toArray();
    }
    public function teacherAnnouncement(): HasMany
    {
        return $this->hasMany(TeacherAnnouncement::class);
    }
    public function teacherCoursePreference(): HasMany
    {
        return $this->hasMany(TeacherCoursePreference::class, 'teacher_id');
    }
    public function otp()
    {
        return $this->morphMany(Otp::class, 'actorable');
    }
    public function vote()
    {
        return $this->morphMany(ElectionVotes::class, 'votable');
    }

    public function voteStatus()
    {
        return $this->morphMany(VoterStatus::class, 'votableStatus');
    }

    public function instructorAvailability(): HasMany
    {
        return $this->hasMany(InstructorAvailability::class);
    }
    public function devices()
    {
        return $this->morphMany(UserDevices::class, 'devicesable');
    }
    public function passwordResetTokens()
    {
        return $this->morphMany(PasswordResetToken::class, 'actorable');
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

    public function instructoravailabilitySlots(): HasMany
    {
        return $this->hasMany(InstructorAvailabilitySlot::class);
    }
    public function eventAudience()
    {
        return $this->morphMany(EventAudience::class, 'audienceable');
    }
    public function announcementAudience()
    {
        return $this->morphMany(AnnouncementAudience::class, 'audienceable');
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class, "gender_id");
    }

    public function semesterTimetableSlot(): HasMany
    {
        return $this->hasMany(SemesterTimetableSlot::class);
    }
}
