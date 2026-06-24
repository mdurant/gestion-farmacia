<?php

namespace App\Http\Requests\Pharmacies;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCostCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('costCenter')) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var \App\Models\CostCenter $costCenter */
        $costCenter = $this->route('costCenter');

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('cost_centers', 'code')->ignore($costCenter->id)],
            'name' => ['required', 'string', 'max:255'],
            'floor' => ['nullable', 'string', 'max:50'],
            'pavilion' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
