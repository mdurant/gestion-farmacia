<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resident extends Model
{
    use Auditable, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'rut',
        'first_name',
        'last_name',
        'birth_date',
        'admission_date',
        'cost_center_id',
        'health_insurance_id',
        'room_number',
        'allergies',
        'rescue_service',
        'diagnosis',
        'emergency_contact_name',
        'emergency_contact_phone',
        'medical_notes',
        'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'rut' => 'encrypted',
            'first_name' => 'encrypted',
            'last_name' => 'encrypted',
            'room_number' => 'encrypted',
            'allergies' => 'encrypted',
            'rescue_service' => 'encrypted',
            'diagnosis' => 'encrypted',
            'emergency_contact_name' => 'encrypted',
            'emergency_contact_phone' => 'encrypted',
            'medical_notes' => 'encrypted',
            'birth_date' => 'date',
            'admission_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /** @return Attribute<string, never> */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim("{$this->first_name} {$this->last_name}"));
    }

    /** @return Attribute<int|null, never> */
    protected function age(): Attribute
    {
        return Attribute::get(fn (): ?int => $this->birth_date?->age);
    }

    /** @return BelongsTo<CostCenter, $this> */
    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    /** @return BelongsTo<HealthInsurance, $this> */
    public function healthInsurance(): BelongsTo
    {
        return $this->belongsTo(HealthInsurance::class);
    }

    /** @return HasMany<InventoryMovement, $this> */
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /** @return HasMany<ResidentTreatment, $this> */
    public function treatments(): HasMany
    {
        return $this->hasMany(ResidentTreatment::class);
    }

    /** @return HasMany<ResidentAccessLog, $this> */
    public function accessLogs(): HasMany
    {
        return $this->hasMany(ResidentAccessLog::class);
    }
}
