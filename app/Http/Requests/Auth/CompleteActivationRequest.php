<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CompleteActivationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->session()->has('activation.user_id');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'password.required' => 'Defina una contraseña segura.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}
