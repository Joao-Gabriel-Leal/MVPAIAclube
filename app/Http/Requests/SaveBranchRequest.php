<?php

namespace App\Http\Requests;

use App\Enums\BranchType;
use App\Http\Requests\Concerns\NormalizesMaskedInputs;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveBranchRequest extends FormRequest
{
    use NormalizesMaskedInputs;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $branchId = $this->route('branch')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('branches', 'slug')->ignore($branchId)],
            'type' => ['required', Rule::enum(BranchType::class)],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'monthly_fee_default' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function maskedFields(): array
    {
        return ['phone'];
    }
}
