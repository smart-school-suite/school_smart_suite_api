<?php

namespace App\Models\Asset;

use App\Models\Schoolbranches;
use App\Models\Student;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAvatar extends Model
{
    use HasUuids, HasFactory;
    protected $fillable = [
        'object_key',
        'mime_type',
        'size_bytes',
        'height',
        'width',
        'is_current',
        'created_at',
        'replaced_at',
        'school_branch_id',
        'student_id'
    ];

    protected $casts = [
        'is_current' => "boolean",
        'replaced_at' => "datetime",
        'size_bytes' => "integer",
        'height' => 'integer',
        'width' => 'integer',
    ];

    const UPDATED_AT = null;
    public $incrementing = false;
    public $table = "student_avatars";
    public $keyType = 'string';

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }
}
