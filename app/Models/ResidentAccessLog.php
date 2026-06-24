<?php

namespace App\Models;

use App\Enums\ResidentAccessAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResidentAccessLog extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'resident_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'browser',
        'accessed_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'action' => ResidentAccessAction::class,
            'old_values' => 'array',
            'new_values' => 'array',
            'accessed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Resident, $this> */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }
}
