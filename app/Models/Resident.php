<?php

namespace App\Models;

use App\Casts\EncryptedDate;
use App\Casts\EncryptedString;
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
            'rut' => EncryptedString::class,
            'first_name' => EncryptedString::class,
            'last_name' => EncryptedString::class,
            'room_number' => EncryptedString::class,
            'allergies' => EncryptedString::class,
            'rescue_service' => EncryptedString::class,
            'diagnosis' => EncryptedString::class,
            'emergency_contact_name' => EncryptedString::class,
            'emergency_contact_phone' => EncryptedString::class,
            'medical_notes' => EncryptedString::class,
            'birth_date' => EncryptedDate::class,
            'admission_date' => EncryptedDate::class,
            'is_active' => 'boolean',
        ];
    }

    /** @return Attribute<string, never> */
    protected function fullName(): Attribute
    {
        return Attribute::get(function (): string {
            $composed = trim("{$this->first_name} {$this->last_name}");

            return $composed !== '' ? $composed : 'Residente #'.$this->id;
        });
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
