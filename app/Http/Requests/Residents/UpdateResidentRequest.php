<?php

namespace App\Http\Requests\Residents;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('resident')) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return (new StoreResidentRequest)->rules();
    }
}
