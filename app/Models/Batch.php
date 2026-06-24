<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Batch extends Model
{
    use Auditable, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'drug_id',
        'pharmacy_id',
        'batch_number',
        'expiration_date',
        'quantity',
        'reserved_quantity',
        'unit_cost',
        'supplier_name',
        'supplier_document',
        'received_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'expiration_date' => 'date',
            'received_at' => 'datetime',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function availableQuantity(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    public function isExpiringWithinDays(int $days = 30): bool
    {
        return $this->expiration_date->lte(now()->addDays($days));
    }

    /** @return BelongsTo<Drug, $this> */
    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }

    /** @return BelongsTo<Pharmacy, $this> */
    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    /** @return HasMany<InventoryMovement, $this> */
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
