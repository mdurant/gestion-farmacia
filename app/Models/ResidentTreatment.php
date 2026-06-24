<?php

namespace App\Models;

use App\Enums\TreatmentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResidentTreatment extends Model
{
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'resident_id',
        'drug_id',
        'drug_presentation_id',
        'daily_dose',
        'monthly_dose',
        'schedule_time',
        'observations',
        'treatment_type',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'daily_dose' => 'decimal:2',
            'monthly_dose' => 'decimal:2',
            'treatment_type' => TreatmentType::class,
            'starts_at' => 'date',
            'ends_at' => 'date',
            'observations' => 'encrypted',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Resident, $this> */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    /** @return BelongsTo<Drug, $this> */
    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }

    /** @return BelongsTo<DrugPresentation, $this> */
    public function presentation(): BelongsTo
    {
        return $this->belongsTo(DrugPresentation::class, 'drug_presentation_id');
    }
}
