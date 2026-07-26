<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthorizationCode extends Model
{
    public const PURPOSE_CONTROLLED_DRUG = 'controlled_drug';

    public const PURPOSE_HIGH_VALUE_WASTE = 'high_value_waste';

    /** @var list<string> */
    protected $fillable = [
        'purpose',
        'drug_id',
        'issued_by',
        'used_by',
        'code_hash',
        'expires_at',
        'used_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function isUsable(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }

    /** @return BelongsTo<Drug, $this> */
    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }

    /** @return BelongsTo<User, $this> */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /** @return BelongsTo<User, $this> */
    public function consumer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }
}
