<?php

namespace App\Http\Requests;

use App\Enums\MembershipStatus;
use App\Http\Requests\Concerns\NormalizesMaskedInputs;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveMemberRequest extends FormRequest
{
    use NormalizesMaskedInputs;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $member = $this->route('member');
        $userId = $member?->user_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'cpf' => ['required', 'digits:11', Rule::unique('users', 'cpf')->ignore($userId)],
            'birth_date' => ['required', 'date', 'before:today'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => [$member ? 'nullable' : 'required', 'confirmed', 'min:8'],
            'primary_branch_id' => ['required', 'exists:branches,id'],
            'additional_branch_ids' => ['nullable', 'array'],
            'additional_branch_ids.*' => ['exists:branches,id'],
            'plan_id' => ['required', 'exists:plans,id'],
            'custom_monthly_fee' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', Rule::enum(MembershipStatus::class)],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function maskedFields(): array
    {
        return ['cpf', 'phone'];
    }
}
