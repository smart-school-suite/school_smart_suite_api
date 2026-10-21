<?php

namespace App\Models\Promotion;

use App\Models\Graduation\GraduationRecord;
use App\Models\Schoolbranches;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alumni extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = [
        'record_id',
        'school_branch_id',
        'admin_note'
    ];

    public $incrementing = false;
    public $table = "alumni";
    public $keyType = 'string';

    public function graduationRecord(): BelongsTo
    {
        return $this->belongsTo(GraduationRecord::class, 'record_id');
    }
    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }
}
