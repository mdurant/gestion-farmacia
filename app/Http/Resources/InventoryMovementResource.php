<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\InventoryMovement */
class InventoryMovementResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'movement_type' => $this->movement_type->value,
            'movement_type_label' => $this->movement_type->label(),
            'quantity' => $this->quantity,
            'unit_cost' => $this->unit_cost,
            'total_value' => $this->total_value,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'movement_at' => $this->movement_at?->timezone('America/Santiago')->toIso8601String(),
            'drug' => $this->whenLoaded('drug', fn () => [
                'id' => $this->drug?->id,
                'name' => $this->drug?->name,
                'code' => $this->drug?->code,
            ]),
            'pharmacy' => $this->whenLoaded('pharmacy', fn () => [
                'id' => $this->pharmacy?->id,
                'name' => $this->pharmacy?->name,
            ]),
            'batch' => $this->whenLoaded('batch', fn () => [
                'id' => $this->batch?->id,
                'batch_number' => $this->batch?->batch_number,
                'expiration_date' => $this->batch?->expiration_date?->toDateString(),
            ]),
            'cost_center' => $this->whenLoaded('costCenter', fn () => [
                'id' => $this->costCenter?->id,
                'name' => $this->costCenter?->name,
            ]),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user?->id,
                'name' => $this->user?->display_name,
            ]),
        ];
    }
}
