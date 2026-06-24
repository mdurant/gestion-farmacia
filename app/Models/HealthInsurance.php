<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HealthInsurance extends Model
{
    /** @var list<string> */
    protected $fillable = ['code', 'name', 'is_active'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return HasMany<Resident, $this> */
    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }
}
