<?php

namespace App\Http\Requests\Pharmacies;

use App\Enums\PharmacyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePharmacyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('pharmacy')) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var \App\Models\Pharmacy $pharmacy */
        $pharmacy = $this->route('pharmacy');

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('pharmacies', 'code')->ignore($pharmacy->id)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(PharmacyType::class)],
            'cost_center_id' => ['required', 'integer', 'exists:cost_centers,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
