<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferMovementRequest extends FormRequest
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
            'source_pharmacy_id' => ['required', 'integer', 'exists:pharmacies,id'],
            'destination_pharmacy_id' => [
                'required',
                'integer',
                'exists:pharmacies,id',
                Rule::notIn([$this->input('source_pharmacy_id')]),
            ],
            'cost_center_id' => ['required', 'integer', 'exists:cost_centers,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'authorization_code' => ['nullable', 'string', 'max:50'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'destination_pharmacy_id.not_in' => 'La bodega destino debe ser distinta a la origen.',
        ];
    }
}
