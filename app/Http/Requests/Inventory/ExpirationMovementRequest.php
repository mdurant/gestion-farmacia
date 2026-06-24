<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class ExpirationMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\InventoryMovement::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'batch_id' => ['required', 'integer', 'exists:batches,id'],
            'pharmacy_id' => ['required', 'integer', 'exists:pharmacies,id'],
            'cost_center_id' => ['required', 'integer', 'exists:cost_centers,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'authorization_code' => ['nullable', 'string', 'max:50'],
        ];
    }
}
