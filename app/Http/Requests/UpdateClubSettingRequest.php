<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClubSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdminMatrix() ?? false;
    }

    public function rules(): array
    {
        return [
            'card_prefix' => ['required', 'string', 'regex:/^[A-Z0-9]{2,6}$/'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'card_prefix' => strtoupper(trim((string) $this->input('card_prefix'))),
        ]);
    }
}
