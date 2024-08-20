<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Parents extends Model
{
    use HasFactory;

    protected $fillable = [
       'student_id',
       'name',
       'address',
       'phone_number',
       'language_preference'
    ];

    public $keyType = 'string';
    public $table = 'parents';
    public $incrementing = 'false';

    public function school(): BelongsTo {
        return $this->belongsTo(Parents::class);
    }

    public function schoolbranches(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class);
    }

    public function student(): HasMany {
        return $this->hasMany(Student::class);
    }
}
