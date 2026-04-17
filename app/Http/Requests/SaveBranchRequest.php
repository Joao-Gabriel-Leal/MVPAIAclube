<?php

namespace App\Http\Requests;

use App\Enums\BranchType;
use App\Http\Requests\Concerns\NormalizesMaskedInputs;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveBranchRequest extends FormRequest
{
    use NormalizesMaskedInputs {
        prepareForValidation as prepareMaskedInputs;
    }

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
            'settings' => ['nullable', 'array'],
            'settings.city' => ['nullable', 'string', 'max:120'],
            'settings.summary' => ['nullable', 'string', 'max:300'],
            'settings.public_phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9()\s\-+]+$/'],
            'settings.public_whatsapp' => ['nullable', 'string', 'max:30', 'regex:/^[0-9()\s\-+]+$/'],
            'settings.public_hours' => ['nullable', 'string', 'max:120'],
        ];
    }

    protected function maskedFields(): array
    {
        return ['phone'];
    }

    protected function prepareForValidation(): void
    {
        $this->prepareMaskedInputs();

        $settings = (array) $this->input('settings', []);

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'slug' => trim((string) $this->input('slug')),
            'email' => trim((string) $this->input('email')),
            'address' => trim((string) $this->input('address')),
            'settings' => [
                'city' => trim((string) ($settings['city'] ?? '')),
                'summary' => trim((string) ($settings['summary'] ?? '')),
                'public_phone' => trim((string) ($settings['public_phone'] ?? '')),
                'public_whatsapp' => trim((string) ($settings['public_whatsapp'] ?? '')),
                'public_hours' => trim((string) ($settings['public_hours'] ?? '')),
            ],
        ]);
    }
}
