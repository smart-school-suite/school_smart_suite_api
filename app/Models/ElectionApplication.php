<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectionApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'isApproved',
        'school_branch_id',
        'election_id',
        'election_role_id',
        'student_id'
    ];

    public $incrementing = 'false';

    public $table = 'election_application';

    public $keyType = 'string';

    public function student(): BelongsTo {
         return $this->belongsTo(Student::class, 'student_id');
    }

    public function schoolbranches(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function election(): BelongsTo {
         return $this->belongsTo(Elections::class, "election_id");
    }

    public function electionRole(): BelongsTo {
         return $this->belongsTo(ElectionRoles::class,"election_role_id");
    }

}
