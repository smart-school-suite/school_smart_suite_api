<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Events extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'event_date',
        'start_date',
        'end_date',
        'location',
        'description',
        'attendance',
        'school_branch_id',
        'notes',
        'organizer',
        'status',
        'category',
        'urgency',
        'tags',
        'color',
        'duration',
        'url',
        'feedback_link',
        'attachments',
        'background_image'
    ];

    public $incrementing = 'false';
    public $keyType = 'string';
    public $table = 'events';

    public function department(): BelongsTo {
        return $this->belongsTo(Department::class);
    }

    public function school(): HasMany {
        return $this->hasMany(School::class);
    }

    public function schoolbranches(): HasMany {
        return $this->hasMany(Schoolbranches::class);
    }

    public function specialties(): HasMany {
        return $this->hasMany(Specialty::class);
    }

    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });

    }
}
