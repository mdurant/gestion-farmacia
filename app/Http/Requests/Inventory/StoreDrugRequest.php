<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDrugRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Drug::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('drugs', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'presentation' => ['nullable', 'string', 'max:100'],
            'active_ingredient' => ['nullable', 'string', 'max:255'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'max_stock' => ['nullable', 'integer', 'min:0'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'is_controlled' => ['sometimes', 'boolean'],
            'is_narcotic' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'code.required' => 'El código del fármaco es obligatorio.',
            'code.unique' => 'Ya existe un fármaco con este código.',
            'name.required' => 'El nombre del fármaco es obligatorio.',
            'min_stock.required' => 'Debe indicar el stock mínimo.',
        ];
    }
}
