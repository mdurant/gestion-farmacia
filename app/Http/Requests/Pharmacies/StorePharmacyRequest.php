<?php

namespace App\Http\Requests\Pharmacies;

use App\Enums\PharmacyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePharmacyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Pharmacy::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('pharmacies', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(PharmacyType::class)],
            'cost_center_id' => ['required', 'integer', 'exists:cost_centers,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'code.required' => 'El código de bodega es obligatorio.',
            'code.unique' => 'Ya existe una bodega con este código.',
            'cost_center_id.required' => 'Debe asociar un centro de costo.',
        ];
    }
}
