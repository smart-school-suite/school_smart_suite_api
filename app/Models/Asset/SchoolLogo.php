<?php

namespace App\Models\Asset;

use App\Models\School;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolLogo extends Model
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
        'school_id',
    ];

    public $casts = [
        'is_current' => "boolean",
        "replaced_at" => "datetime",
        "height" => "integer",
        "width" => "integer",
        "size_bytes" => "integer"
    ];
    const UPDATED_AT = null;
    public $incrementing = false;
    public $table = "school_logos";
    public $keyType = 'string';

    protected function objectKey(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? str_replace('\\', '/', $value) : null,
        );
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }
}
