<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Audiences;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Parents extends Model
{
    use HasFactory, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'address',
        'email',
        'phone_one',
        'phone_two',
        'cultural_background',
        'preferred_contact_method',
        'preferred_language',
        'school_branch_id',
        'relationship_to_student',
     ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
    ];

    public $keyType = 'string';
    public $table = 'parents';
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


    public function school(): BelongsTo {
        return $this->belongsTo(Parents::class);
    }

   public function audience(): MorphMany {
        return $this->morphMany(Audiences::class, 'audienceable');
    }
    public function schoolbranches(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class);
    }

    public function student(): HasMany {
        return $this->hasMany(Student::class, 'guardian_id');
    }
    public function otps()
    {
        return $this->morphMany(Otp::class, 'otpable');
    }
   protected static function boot()
   {
    parent::boot();

    static::creating(function ($user) {
        $user->id = (string) Str::uuid();
    });
  }
}
