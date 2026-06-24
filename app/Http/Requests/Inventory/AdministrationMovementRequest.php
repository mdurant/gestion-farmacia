<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class AdministrationMovementRequest extends FormRequest
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
            'resident_id' => ['required', 'integer', 'exists:residents,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'prescription_id' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'authorization_code' => ['nullable', 'string', 'max:50'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'resident_id.required' => 'Debe seleccionar un residente.',
            'prescription_id.required' => 'Debe indicar el número de receta o prescripción médica.',
            'batch_id.required' => 'Debe seleccionar un lote.',
        ];
    }
}
