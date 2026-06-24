<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAccessLog extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'session_token',
        'browser',
        'ip_address',
        'user_agent',
        'location',
        'connected_at',
        'disconnected_at',
        'disconnect_reason',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'connected_at' => 'datetime',
            'disconnected_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->disconnected_at === null;
    }
}
