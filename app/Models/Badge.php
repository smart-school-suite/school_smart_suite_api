<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
class Badge extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'name',
        'color',
        'mobile_icon',
        'desktop_icon',
    ];

    public $incrementing = false;

    protected $keyType = 'string';
    protected $table = 'batches';

     public function assignments()
    {
        return $this->hasMany(BadgeAssignment::class);
    }
}
