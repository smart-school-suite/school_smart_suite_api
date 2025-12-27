<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentSource extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'name',
        'description',
        'status'
    ];

    public $keyType = 'string';
    public $incrementing = false;
    public $table = 'student_sources';

    public function student(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
