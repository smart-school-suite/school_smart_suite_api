<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
class AnnouncementAuthor extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'id',
        'authorable_id',
        'authorable_type',
        'announcement_id',
        'school_branch_id'
    ];

    public $keyType = 'string';
    public $table = 'annoucement_author';
    public $incrementing = false;

    public function announcement()
    {
        return $this->belongsTo(Announcement::class, 'announcement_id');
    }
    public function authorable(){

        return $this->morphTo();
    }
}
