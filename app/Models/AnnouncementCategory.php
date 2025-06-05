<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnnouncementCategory extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'name',
        'description',
        'school_branch_id'
    ];

    public $keyType = 'string';
    public $table = 'announcement_categories';
    public $incrementing = false;

    public function announcement(): HasMany {
        return $this->hasMany(AnnouncementCategory::class, 'category_id');
    }

    public function schoolBranch(): BelongsTo {
        return $this->belongsTo(SchoolBranches::class, 'school_branch_id');
    }


}
