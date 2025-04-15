<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectionParticipants extends Model
{
    use HasFactory;

    protected $fillable = [
        'specialty_id',
        'level_id',
        'election_id',
        'school_branch_id'
    ];

    public function Specialty() : BelongsTo {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function level(): BelongsTo {
        return $this->belongsTo(Educationlevels::class, 'level_id');
    }
    public $table = 'election_participants';
    public $incrementing = 'false';
    public $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });

    }
}
