<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesMaskedInputs;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class SaveAdminUserRequest extends FormRequest
{
    use NormalizesMaskedInputs;

    public function authorize(): bool
    {
        return $this->user()?->isAdminMatrix() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'branch_id' => ['required', 'exists:branches,id'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    protected function maskedFields(): array
    {
        return ['phone'];
    }
}
