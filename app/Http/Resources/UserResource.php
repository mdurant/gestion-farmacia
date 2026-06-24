<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'display_name' => $this->display_name,
            'first_name' => $this->when($request->user()?->can('users.manage'), $this->first_name),
            'last_name' => $this->when($request->user()?->can('users.manage'), $this->last_name),
            'rut' => $this->when($request->user()?->can('users.manage'), $this->rut),
            'email' => $this->email,
            'role' => $this->role?->value,
            'role_label' => $this->role?->label(),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->timezone('America/Santiago')->toIso8601String(),
            'updated_at' => $this->updated_at?->timezone('America/Santiago')->toIso8601String(),
            'deleted_at' => $this->deleted_at?->timezone('America/Santiago')->toIso8601String(),
        ];
    }
}
