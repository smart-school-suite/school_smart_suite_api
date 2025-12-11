<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feature extends Model
{
    use GeneratesUuid;

    protected $fillable = [
        'name',
        'description',
        'key',
        'status',
        'country_id',
        'limit_type',
        'default'
    ];

    public $table = "features";
    public $incrementing = false;
    public $keyType = 'string';

    protected function limitType(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value ? strtolower($value) : null,
            set: fn(string $value) => strtolower($value),
        );
    }

    protected function default(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value, array $attributes) => $this->castDefault($value, $attributes['limit_type'] ?? null),
            set: fn(mixed $value) => $this->prepareDefault($value),
        );
    }

    private function castDefault(?string $jsonValue, ?string $type): mixed
    {
        if ($jsonValue === null) {
            return null;
        }

        $decoded = json_decode($jsonValue, true);

        return match (strtolower($type ?? '')) {
            'boolean' => (bool) $decoded,
            'integer' => (int) $decoded,
            'decimal' => (float) $decoded,
            default   => $decoded,
        };
    }

    private function prepareDefault(mixed $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function planFeature(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }
}
