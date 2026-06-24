<?php

namespace App\Http\Requests\Pharmacies;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCostCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\CostCenter::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('cost_centers', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'floor' => ['nullable', 'string', 'max:50'],
            'pavilion' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'code.required' => 'El código del centro de costo es obligatorio.',
            'code.unique' => 'Ya existe un centro de costo con este código.',
        ];
    }
}
