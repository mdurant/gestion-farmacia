<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyActivationOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->session()->has('activation.email');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'code' => ['required', 'digits:6'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'code.required' => 'Ingrese el código de 6 dígitos.',
            'code.digits' => 'El código debe tener exactamente 6 dígitos.',
        ];
    }
}
