<?php

namespace App\Http\Requests;

use App\Enums\DiscountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdminMatrix() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('plans', 'name')->ignore($this->route('plan')),
            ],
            'description' => ['nullable', 'string'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'dependent_limit' => ['required', 'integer', 'min:0'],
            'guest_limit_per_reservation' => ['required', 'integer', 'min:0'],
            'free_reservations_per_month' => ['required', 'integer', 'min:0'],
            'extra_reservation_discount_type' => ['required', Rule::enum(DiscountType::class)],
            'extra_reservation_discount_value' => ['required', 'numeric', 'min:0'],
            'dependents_inherit_benefits' => ['nullable', 'boolean'],
            'resource_ids' => ['nullable', 'array'],
            'resource_ids.*' => ['exists:club_resources,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
