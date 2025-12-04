<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentParentRelationship extends Model
{
    use GeneratesUuid;

    protected $fillable = [
         'name'
    ];

    public $table = 'stu_par_relationships';
    public $incrementing = 'false';
    public $keyType = 'string';

    public function student(): HasMany {
         return $this->hasMany(Student::class);
    }
}
