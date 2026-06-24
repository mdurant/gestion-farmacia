<?php

namespace App\Http\Requests\Residents;

use Illuminate\Foundation\Http\FormRequest;

class StoreResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Resident::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'rut' => ['required', 'string', 'max:20'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'admission_date' => ['nullable', 'date'],
            'cost_center_id' => ['nullable', 'integer', 'exists:cost_centers,id'],
            'health_insurance_id' => ['nullable', 'integer', 'exists:health_insurances,id'],
            'room_number' => ['nullable', 'string', 'max:20'],
            'allergies' => ['nullable', 'string', 'max:1000'],
            'rescue_service' => ['nullable', 'string', 'max:1000'],
            'diagnosis' => ['nullable', 'string', 'max:4000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'medical_notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'rut.required' => 'El RUT del residente es obligatorio.',
            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'El apellido es obligatorio.',
        ];
    }
}
