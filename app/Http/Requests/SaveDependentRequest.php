<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesMaskedInputs;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveDependentRequest extends FormRequest
{
    use NormalizesMaskedInputs;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $dependent = $this->route('dependent');
        $userId = $dependent?->user_id;

        return [
            'member_id' => ['required', 'exists:members,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'relationship' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'cpf' => ['required', 'digits:11', Rule::unique('users', 'cpf')->ignore($userId)],
            'birth_date' => ['required', 'date', 'before:today'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => [$dependent ? 'nullable' : 'required', 'confirmed', 'min:8'],
        ];
    }

    protected function maskedFields(): array
    {
        return ['cpf', 'phone'];
    }
}
