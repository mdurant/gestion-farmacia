<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CostCenter extends Model
{
    use Auditable, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'code',
        'name',
        'floor',
        'pavilion',
        'description',
        'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<Pharmacy, $this> */
    public function pharmacies(): HasMany
    {
        return $this->hasMany(Pharmacy::class);
    }

    /** @return HasMany<Resident, $this> */
    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }

    /** @return HasMany<InventoryMovement, $this> */
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
