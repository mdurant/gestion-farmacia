<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDrugRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('drug')) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var \App\Models\Drug $drug */
        $drug = $this->route('drug');

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('drugs', 'code')->ignore($drug->id)],
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
}
