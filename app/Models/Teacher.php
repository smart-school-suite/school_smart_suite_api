<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_branches_id',
        'email',
        'name',
        'password',
        'profile_picture',
        'role'
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

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function school(): BelongsTo {
        return $this->belongsTo(School::class);
    }

    public function schoolbranches(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class);
    }

    public function courses(): HasMany {
        return $this->hasMany(Courses::class);
    }

}
