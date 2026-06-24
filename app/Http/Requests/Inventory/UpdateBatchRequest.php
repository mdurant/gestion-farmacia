<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('batch')) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'expiration_date' => ['required', 'date'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'supplier_document' => ['nullable', 'string', 'max:100'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'expiration_date.required' => 'La fecha de vencimiento es obligatoria.',
            'expiration_date.after' => 'El lote debe tener una fecha de vencimiento futura.',
        ];
    }
}
