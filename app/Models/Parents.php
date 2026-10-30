<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Parents extends Model
{
    use HasFactory, HasApiTokens, HasUuids;

    protected $fillable = [
        'name',
        'address',
        'email',
        'phone',
        'preferred_language',
        'preferred_contact_method',
        'school_branch_id'
    ];

    public $keyType = 'string';
    public $table = 'parents';
    public $incrementing = false;


    public function school(): BelongsTo
    {
        return $this->belongsTo(Parents::class);
    }

    public function schoolbranches(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class);
    }
    public function student(): HasMany
    {
        return $this->hasMany(Student::class, 'guardian_id');
    }
}
