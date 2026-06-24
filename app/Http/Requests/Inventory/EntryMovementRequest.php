<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class EntryMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\InventoryMovement::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'drug_id' => ['required', 'integer', 'exists:drugs,id'],
            'pharmacy_id' => ['required', 'integer', 'exists:pharmacies,id'],
            'cost_center_id' => ['required', 'integer', 'exists:cost_centers,id'],
            'batch_number' => ['required', 'string', 'max:100'],
            'expiration_date' => ['required', 'date', 'after:today'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'supplier_document' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'drug_id.required' => 'Debe seleccionar un fármaco.',
            'batch_number.required' => 'Debe indicar el número de lote.',
            'expiration_date.after' => 'La fecha de vencimiento debe ser posterior a hoy.',
            'quantity.min' => 'La cantidad debe ser al menos 1 unidad.',
        ];
    }
}
