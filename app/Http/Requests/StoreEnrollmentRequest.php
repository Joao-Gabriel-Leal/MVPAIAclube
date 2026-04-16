<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesMaskedInputs;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreEnrollmentRequest extends FormRequest
{
    use NormalizesMaskedInputs;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'exists:plans,id'],
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'digits:11', 'unique:users,cpf'],
            'birth_date' => ['required', 'date', 'before:today'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    protected function maskedFields(): array
    {
        return ['cpf', 'phone'];
    }
}
