<?php

namespace App\Models;

use App\Enums\MovementType;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use Auditable;

    /** @var list<string> */
    protected $fillable = [
        'movement_type',
        'pharmacy_id',
        'destination_pharmacy_id',
        'batch_id',
        'drug_id',
        'cost_center_id',
        'resident_id',
        'user_id',
        'prescription_id',
        'quantity',
        'unit_cost',
        'total_value',
        'reason',
        'notes',
        'movement_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'movement_type' => MovementType::class,
            'movement_at' => 'datetime',
            'unit_cost' => 'decimal:2',
            'total_value' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Pharmacy, $this> */
    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    /** @return BelongsTo<Pharmacy, $this> */
    public function destinationPharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'destination_pharmacy_id');
    }

    /** @return BelongsTo<Batch, $this> */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /** @return BelongsTo<Drug, $this> */
    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }

    /** @return BelongsTo<CostCenter, $this> */
    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    /** @return BelongsTo<Resident, $this> */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
