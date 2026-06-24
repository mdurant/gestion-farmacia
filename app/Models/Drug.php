<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Drug extends Model
{
    use Auditable, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'code',
        'name',
        'category',
        'presentation',
        'drug_presentation_id',
        'active_ingredient',
        'is_controlled',
        'is_narcotic',
        'min_stock',
        'max_stock',
        'unit_cost',
        'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_controlled' => 'boolean',
            'is_narcotic' => 'boolean',
            'unit_cost' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<Batch, $this> */
    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    /** @return HasMany<InventoryMovement, $this> */
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<DrugPresentation, $this> */
    public function drugPresentation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DrugPresentation::class);
    }

    /** @return HasMany<ResidentTreatment, $this> */
    public function residentTreatments(): HasMany
    {
        return $this->hasMany(ResidentTreatment::class);
    }
}
