<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveClubResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'max_capacity' => ['required', 'integer', 'min:1'],
            'default_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'allowed_plan_ids' => ['nullable', 'array'],
            'allowed_plan_ids.*' => ['exists:plans,id'],
            'schedules' => ['required', 'array', 'min:1'],
            'schedules.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'schedules.*.opens_at' => ['required', 'date_format:H:i'],
            'schedules.*.closes_at' => ['required', 'date_format:H:i'],
            'schedules.*.slot_interval_minutes' => ['required', 'integer', 'min:15', 'max:240'],
            'block_date' => ['nullable', 'date'],
            'block_start_time' => ['nullable', 'date_format:H:i'],
            'block_end_time' => ['nullable', 'date_format:H:i'],
            'block_reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
