<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class WasteMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('registerWaste', \App\Models\InventoryMovement::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'batch_id' => ['required', 'integer', 'exists:batches,id'],
            'pharmacy_id' => ['required', 'integer', 'exists:pharmacies,id'],
            'cost_center_id' => ['required', 'integer', 'exists:cost_centers,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'authorization_code' => ['nullable', 'string', 'max:50'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'batch_id.required' => 'Debe seleccionar un lote.',
            'pharmacy_id.required' => 'Debe seleccionar una bodega.',
            'cost_center_id.required' => 'Debe seleccionar un centro de costos.',
            'quantity.min' => 'La cantidad debe ser al menos 1 unidad.',
            'reason.required' => 'Debe indicar el motivo de la merma.',
        ];
    }
}
